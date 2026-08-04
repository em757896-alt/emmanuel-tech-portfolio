import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";
import { resolveCourseContext, resolveTeacherFaculty } from "@/lib/institution";

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

    let courseIds: string[] = [];
    let teacherRow: { id: string } | null = null;

    if (userRole === "ADMIN") {
      const { data: allCourses } = await supabase.from("courses").select("id");
      courseIds = (allCourses || []).map((c: { id: string }) => c.id);
    } else {
      const { data: teacher } = await supabase
        .from("teachers")
        .select("id, isHod, departmentId")
        .eq("userId", session.user!.id)
        .single();
      if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });
      teacherRow = { id: teacher.id };

      const fac = await resolveTeacherFaculty(supabase, teacher.departmentId);
      const isFacultyHod = fac.hodTeacherId === teacher.id;

      const { data: assignments } = await supabase.from("course_assignments").select("courseId").eq("teacherId", teacher.id);
      courseIds = (assignments || []).map((a: { courseId: string }) => a.courseId);

      if ((teacher.isHod || isFacultyHod) && teacher.departmentId) {
        let deptIds = [teacher.departmentId];
        if (isFacultyHod && fac.facultyId) {
          const { data: depts } = await supabase.from("departments").select("id").eq("facultyId", fac.facultyId);
          deptIds = (depts || []).map((d: { id: string }) => d.id);
        }
        const { data: deptCourses } = await supabase.from("courses").select("id").in("departmentId", deptIds);
        const deptCourseIds = (deptCourses || []).map((c: { id: string }) => c.id);
        courseIds = [...new Set([...courseIds, ...deptCourseIds])];
      }
    }

    const empty = { research: [], results: [], exams: [], assignments: [] };

    if (!courseIds.length) return NextResponse.json(empty);

    const canApproveAsFor = async (courseId: string) => {
      const can = { lecturer: userRole === "ADMIN", hod: userRole === "ADMIN" };
      if (teacherRow && courseId) {
        const ctx = await resolveCourseContext(supabase, courseId);
        can.lecturer = ctx.unitLecturerId !== null && ctx.unitLecturerId === teacherRow.id;
        can.hod = (ctx.hodId !== null && ctx.hodId === teacherRow.id) || (ctx.facultyHodId !== null && ctx.facultyHodId === teacherRow.id);
      }
      return can;
    };

    const [researchRes, resultsRes, examsRes, assignmentsRes] = await Promise.all([
      supabase
        .from("research")
        .select("id, title, abstract, fileUrl, fileName, courseId, uploadedAt, lecturerApproved, hodApproved, rejected, rejectionReason, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, students(id, firstName, lastName, studentId)")
        .in("courseId", courseIds)
        .order("uploadedAt", { ascending: false })
        .limit(300),
      supabase
        .from("result_documents")
        .select("id, title, courseId, uploadedAt, released, status, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, students(id, firstName, lastName, studentId)")
        .in("courseId", courseIds)
        .order("uploadedAt", { ascending: false })
        .limit(300),
      supabase
        .from("exams")
        .select("id, title, courseId, date, released, status, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, fileUrl, fileName, courses(id, name, code)")
        .in("courseId", courseIds)
        .order("date", { ascending: false })
        .limit(300),
      supabase
        .from("assignments")
        .select("id, title, courseId, dueDate, released, status, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, fileUrl, fileName, courses(id, name, code)")
        .in("courseId", courseIds)
        .order("dueDate", { ascending: false })
        .limit(300),
    ]);

    const research = await Promise.all((researchRes.data || []).map(async (r: any) => {
      const { students: st, ...rest } = r as { students?: unknown };
      return { ...rest, student: st, canApproveAs: await canApproveAsFor(r.courseId) };
    }));

    const results = await Promise.all((resultsRes.data || []).map(async (r: any) => {
      const { students: st, ...rest } = r as { students?: unknown };
      return { ...rest, student: st, canApproveAs: await canApproveAsFor(r.courseId) };
    }));

    const exams = await Promise.all((examsRes.data || []).map(async (e: any) => {
      const { courses: c, ...rest } = e as { courses?: unknown };
      return { ...rest, course: c, canApproveAs: await canApproveAsFor(e.courseId) };
    }));

    const assignments = await Promise.all((assignmentsRes.data || []).map(async (a: any) => {
      const { courses: c, ...rest } = a as { courses?: unknown };
      return { ...rest, course: c, canApproveAs: await canApproveAsFor(a.courseId) };
    }));

    return NextResponse.json({ research, results, exams, assignments });
  } catch (error) {
    console.error("Error fetching HOD approvals:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
