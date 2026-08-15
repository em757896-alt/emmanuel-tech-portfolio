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
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const [studentsRes, teachersRes, coursesRes, deptsRes, recentStudentsRes, recentEnrollmentsRes] = await Promise.all([
      supabase.from("students").select("id", { count: "exact", head: true }),
      supabase.from("teachers").select("id", { count: "exact", head: true }),
      supabase.from("courses").select("id", { count: "exact", head: true }),
      supabase.from("departments").select("id", { count: "exact", head: true }),
      supabase.from("students").select("id, firstName, lastName, studentId, status, enrollmentDate").order("enrollmentDate", { ascending: false }).limit(5),
      supabase.from("course_enrollments").select("id, enrolledAt, students(firstName, lastName, studentId), courses(name, code)").order("enrolledAt", { ascending: false }).limit(5),
    ]);

    return NextResponse.json({
      stats: {
        totalStudents: studentsRes.count || 0,
        totalTeachers: teachersRes.count || 0,
        totalCourses: coursesRes.count || 0,
        totalDepartments: deptsRes.count || 0,
      },
      recentStudents: recentStudentsRes.data || [],
      recentEnrollments: recentEnrollmentsRes.data || [],
    });
  } catch (error) {
    console.error("Error:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
