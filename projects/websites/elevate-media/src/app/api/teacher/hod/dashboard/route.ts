import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";
import { resolveTeacherFaculty } from "@/lib/institution";

function approvalStatus(r: any) {
  if (r.type === "CORRECTION") {
    return r.lecturerApproved && r.adminApproved
      ? "APPROVED"
      : r.lecturerApproved || r.adminApproved
        ? "PARTIALLY_APPROVED"
        : "PENDING";
  }
  return r.lecturerApproved && r.hodApproved
    ? "APPROVED"
    : r.lecturerApproved || r.hodApproved
      ? "PARTIALLY_APPROVED"
      : "PENDING";
}

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
      const deptIds = (allDepts || []).map((d: { id: string }) => d.id);
      return NextResponse.json({
        scope: { id: "ALL", name: "All Departments", code: "ALL", type: "FACULTY" },
        stats: { departmentCount: deptIds.length, courseCount: 0, studentCount: 0, pendingPoe: 0, pendingAttendance: 0, pendingResearch: 0, pendingResults: 0, pendingExams: 0, pendingAssignments: 0 },
        departments: (allDepts || []).map((d: { id: string; name: string; code: string }) => ({ ...d, courses: [] })),
      });
    }

    const { data: teacher } = await supabase
      .from("teachers")
      .select("id, isHod, departmentId")
      .eq("userId", session.user!.id)
      .single();
    if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

    const fac = await resolveTeacherFaculty(supabase, teacher.departmentId);
    const isFacultyHod = fac.hodTeacherId === teacher.id;

    if (!teacher.isHod && !isFacultyHod) {
      return NextResponse.json({ error: "Only HODs can access this dashboard" }, { status: 403 });
    }

    if (!teacher.departmentId) {
      return NextResponse.json({
        scope: null,
        stats: { departmentCount: 0, courseCount: 0, studentCount: 0, pendingPoe: 0, pendingAttendance: 0, pendingResearch: 0, pendingResults: 0, pendingExams: 0, pendingAssignments: 0 },
        departments: [],
      });
    }

    // Determine scope: a faculty HOD sees every department under the faculty.
    let scopeDeptIds: string[] = [teacher.departmentId];
    let scopeName = "";
    let scopeCode = "";
    let isFacultyScope = false;

    if (isFacultyHod && fac.facultyId) {
      isFacultyScope = true;
      scopeName = fac.facultyName || "";
      scopeCode = fac.facultyId;
      const { data: depts } = await supabase.from("departments").select("id").eq("facultyId", fac.facultyId);
      scopeDeptIds = (depts || []).map((d: any) => d.id);
    }

    const { data: scopeDepts } = await supabase
      .from("departments")
      .select("id, name, code")
      .in("id", scopeDeptIds)
      .order("name", { ascending: true });

    const { data: scopeCourses } = await supabase
      .from("courses")
      .select("id, name, code, credits, semester, year, departmentId")
      .in("departmentId", scopeDeptIds)
      .order("code", { ascending: true });

    const courseIds = (scopeCourses || []).map((c: any) => c.id);

    let studentCount = 0;
    let pendingPoe = 0;
    let pendingAttendance = 0;
    let pendingResearch = 0;
    let pendingResults = 0;
    let pendingExams = 0;
    let pendingAssignments = 0;
    const enrollCounts: Record<string, number> = {};

    if (courseIds.length) {
      const { data: enrollments } = await supabase.from("course_enrollments").select("studentId, courseId");
      const relevant = (enrollments || []).filter((e: any) => courseIds.includes(e.courseId));
      const seen = new Set<string>();
      relevant.forEach((e: any) => {
        seen.add(e.studentId);
        enrollCounts[e.courseId] = (enrollCounts[e.courseId] || 0) + 1;
      });
      studentCount = seen.size;

      const { data: poe } = await supabase.from("poe_documents").select("status").in("courseId", courseIds);
      pendingPoe = (poe || []).filter((d: any) => d.status !== "APPROVED").length;

      const { data: att } = await supabase
        .from("attendance_records")
        .select("id, type, lecturerApproved, hodApproved, adminApproved")
        .in("courseId", courseIds);
      pendingAttendance = (att || []).filter((r: any) => approvalStatus(r) !== "APPROVED").length;

      const { data: research } = await supabase.from("research").select("id, lecturerApproved, hodApproved, rejected").in("courseId", courseIds);
      pendingResearch = (research || []).filter((p: any) => !p.rejected && !(p.lecturerApproved && p.hodApproved)).length;

      const { data: results } = await supabase.from("result_documents").select("id, lecturerApproved, hodApproved").in("courseId", courseIds);
      pendingResults = (results || []).filter((r: any) => !(r.lecturerApproved && r.hodApproved)).length;

      const { data: exams } = await supabase.from("exams").select("id, released, status, lecturerApproved, hodApproved").in("courseId", courseIds);
      pendingExams = (exams || []).filter((e: any) => !(e.lecturerApproved && e.hodApproved)).length;

      const { data: assignments } = await supabase.from("assignments").select("id, released, status, lecturerApproved, hodApproved").in("courseId", courseIds);
      pendingAssignments = (assignments || []).filter((a: any) => !(a.lecturerApproved && a.hodApproved)).length;
    }

    const departments = (scopeDepts || []).map((d: any) => ({
      id: d.id,
      name: d.name,
      code: d.code,
      courses: (scopeCourses || [])
        .filter((c: any) => c.departmentId === d.id)
        .map((c: any) => ({ ...c, _count: { enrollments: enrollCounts[c.id] || 0 } })),
    }));

    return NextResponse.json({
      scope: isFacultyScope
        ? { id: fac.facultyId, name: scopeName, code: scopeCode, type: "FACULTY" }
        : { id: teacher.departmentId, name: (departments[0]?.name ?? scopeName) || undefined, code: (departments[0]?.code ?? scopeCode) || undefined, type: "DEPARTMENT" },
      stats: {
        departmentCount: departments.length,
        courseCount: scopeCourses?.length || 0,
        studentCount,
        pendingPoe,
        pendingAttendance,
        pendingResearch,
        pendingResults,
        pendingExams,
        pendingAssignments,
      },
      departments,
    });
  } catch (error) {
    console.error("Error fetching HOD dashboard:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
