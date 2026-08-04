import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";
import { resolveTeacherFaculty } from "@/lib/institution";

export async function GET() {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const userRole = (session.user as { role: string }).role;
    if (userRole !== "TEACHER" && userRole !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    if (userRole === "ADMIN") {
      const { data: allDepts } = await supabase.from("departments").select("id, name, code").order("name");
      return NextResponse.json({
        scope: { id: "ALL", name: "All Departments", code: "ALL", type: "FACULTY" },
        departments: (allDepts || []).map((d: { id: string; name: string; code: string }) => ({ ...d, courses: [] })),
      });
    }

    const { data: teacher } = await supabase
      .from("teachers")
      .select("id, isHod, departmentId")
      .eq("userId", session.user!.id)
      .single();
    if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });
    if (!teacher.isHod) return NextResponse.json({ error: "Only HODs can access department students" }, { status: 403 });

    const fac = await resolveTeacherFaculty(supabase, teacher.departmentId);
    const isFacultyHod = fac.hodTeacherId === teacher.id;

    if (!teacher.departmentId) {
      return NextResponse.json({ scope: null, departments: [] });
    }

    let scopeDeptIds: string[] = [teacher.departmentId];
    let scopeName = "";
    let scopeCode = "";
    let isFacultyScope = false;

    if (isFacultyHod && fac.facultyId) {
      isFacultyScope = true;
      scopeName = fac.facultyName || "";
      scopeCode = fac.facultyId;
      const { data: depts } = await supabase.from("departments").select("id").eq("facultyId", fac.facultyId);
      scopeDeptIds = (depts || []).map((d: { id: string }) => d.id);
    }

    const { data: scopeDepts } = await supabase
      .from("departments")
      .select("id, name, code")
      .in("id", scopeDeptIds)
      .order("name", { ascending: true });

    const { data: scopeCourses } = await supabase
      .from("courses")
      .select("id, name, code, departmentId")
      .in("departmentId", scopeDeptIds)
      .order("code", { ascending: true });

    const courseIds = (scopeCourses || []).map((c: { id: string }) => c.id);
    const studentsByCourse: Record<string, any[]> = {};

    if (courseIds.length) {
      const { data: enrollments } = await supabase
        .from("course_enrollments")
        .select("id, enrolledAt, courseId, students(id, firstName, lastName, studentId, emailVerified)")
        .in("courseId", courseIds)
        .order("enrolledAt", { ascending: true });

      for (const e of enrollments || []) {
        const st = e.students;
        if (!st) continue;
        const student = Array.isArray(st) ? st[0] : st;
        if (!student) continue;
        if (!studentsByCourse[e.courseId]) studentsByCourse[e.courseId] = [];
        if (!studentsByCourse[e.courseId].find((s) => s.id === student.id)) {
          studentsByCourse[e.courseId].push(student);
        }
      }
    }

    const departments = (scopeDepts || []).map((d: any) => ({
      id: d.id,
      name: d.name,
      code: d.code,
      courses: (scopeCourses || [])
        .filter((c: any) => c.departmentId === d.id)
        .map((c: any) => ({
          id: c.id,
          name: c.name,
          code: c.code,
          students: studentsByCourse[c.id] || [],
        })),
    }));

    return NextResponse.json({
      scope: isFacultyScope
        ? { id: fac.facultyId, name: scopeName, code: scopeCode, type: "FACULTY" }
        : { id: teacher.departmentId, name: departments[0]?.name ?? undefined, code: departments[0]?.code ?? undefined, type: "DEPARTMENT" },
      departments,
    });
  } catch (error) {
    console.error("Error fetching HOD students:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
