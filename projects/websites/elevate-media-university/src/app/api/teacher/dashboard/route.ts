import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import { auth } from "@/lib/auth";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

export async function GET() {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: teacher } = await supabase.from("teachers").select("*").eq("userId", session.user!.id).single();
    if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

    const { data: assignments } = await supabase
      .from("course_assignments")
      .select("id, semester, year, courses(id, name, code)")
      .eq("teacherId", teacher.id);

    const courseAssignments = assignments || [];
    const singleCourse = (a: { courses?: { id: string; name: string; code: string }[] | { id: string; name: string; code: string } | null }) =>
      Array.isArray(a.courses) ? a.courses[0] : a.courses || undefined;
    const courseIds = [...new Set(courseAssignments.map((a) => singleCourse(a)?.id))].filter((x): x is string => !!x);

    const [deptCoursesRes, pendingRes, enrollRes] = await Promise.all([
      teacher.departmentId
        ? supabase.from("courses").select("id, name, code").eq("departmentId", teacher.departmentId)
        : Promise.resolve({ data: [] }),
      courseIds.length
        ? supabase
            .from("assignments")
            .select("id")
            .in("courseId", courseIds)
            .then(async ({ data: ta }) => {
              const ids = (ta || []).map((a: { id: string }) => a.id);
              if (!ids.length) return { data: [] as never[] };
              return supabase
                .from("submissions")
                .select("id, status, submittedAt, assignments(id, title), students(id, firstName, lastName, studentId)")
                .eq("status", "PENDING")
                .in("assignmentId", ids)
                .order("submittedAt", { ascending: false })
                .limit(10);
            })
        : Promise.resolve({ data: [] }),
      courseIds.length
        ? supabase.from("course_enrollments").select("studentId, courseId").in("courseId", courseIds)
        : Promise.resolve({ data: [] }),
    ]);

    const enrollCounts: Record<string, number> = {};
    const studentIds = new Set<string>();
    for (const e of enrollRes.data || []) {
      enrollCounts[e.courseId] = (enrollCounts[e.courseId] || 0) + 1;
      studentIds.add(e.studentId);
    }

    const enriched = courseAssignments.map((a) => {
      const course = singleCourse(a);
      return {
        ...a,
        course: {
          ...(course || {}),
          _count: { enrollments: enrollCounts[course?.id || ""] || 0 },
        },
      };
    });

    const pendingSubmissions = (pendingRes.data || []).map((s: any) => {
      const assignment = Array.isArray(s.assignments) ? s.assignments[0] : s.assignments;
      const student = Array.isArray(s.students) ? s.students[0] : s.students;
      return { ...s, assignment, student };
    });

    return NextResponse.json({
      courseAssignments: enriched,
      pendingSubmissions,
      totalStudents: studentIds.size,
      totalCourses: courseIds.length,
      isHod: !!teacher.isHod,
      departmentId: teacher.departmentId,
      departmentCourses: deptCoursesRes.data || [],
    });
  } catch (error) {
    console.error("Error:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
