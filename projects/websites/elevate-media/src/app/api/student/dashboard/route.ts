import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import { auth } from "@/lib/auth";
import { findCourse } from "@/lib/courseCatalog";

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

    const { data: student } = await supabase.from("students").select("*").eq("userId", session.user!.id).single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const match = findCourse(student.departmentId, student.courseCode);
    const programme = {
      facultyName: match?.faculty.name ?? null,
      departmentName: student.departmentName ?? match?.department.name ?? null,
      courseName: student.courseName ?? match?.course?.title ?? null,
      courseCode: student.courseCode ?? match?.course?.code ?? null,
      duration: match?.course?.duration ?? null,
      credits: match?.course?.credits ?? null,
      modeOfLearning: student.modeOfLearning ?? null,
    };

    const [enrollmentsRes, gradesRes, submissionsRes, recordsRes] = await Promise.all([
      supabase.from("course_enrollments").select("id, courseId, enrolledAt, courses(id, name, code, credits)").eq("studentId", student.id),
      supabase.from("grades").select("id, finalGrade, gpa, calculatedAt, courses(name, code, credits)").eq("studentId", student.id).order("calculatedAt", { ascending: false }),
      supabase.from("submissions").select("id, status, submittedAt, assignments(id, title, dueDate)").eq("studentId", student.id).order("submittedAt", { ascending: false }).limit(10),
      supabase.from("attendance_records").select("id, status, checkedInAt, attendance_sessions!attendance_records_sessionId_fkey(id, date, courses(name, code))").eq("studentId", student.id).order("checkedInAt", { ascending: false }).limit(50),
    ]);

    const mapCourse = (r: any) => {
      const { courses, ...rest } = r;
      return { ...rest, course: courses };
    };

    const mapAssignment = (r: any) => {
      const { assignments, ...rest } = r;
      return { ...rest, assignment: assignments };
    };

    const mapSession = (r: any) => {
      const { attendance_sessions, ...rest } = r;
      const { courses, ...sessionRest } = attendance_sessions || {};
      return { ...rest, session: { ...sessionRest, course: courses } };
    };

    return NextResponse.json({
      programme,
      enrollments: (enrollmentsRes.data || []).map(mapCourse),
      grades: (gradesRes.data || []).map(mapCourse),
      submissions: (submissionsRes.data || []).map(mapAssignment),
      records: (recordsRes.data || []).map(mapSession),
    });
  } catch (error) {
    console.error("Error fetching dashboard:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
