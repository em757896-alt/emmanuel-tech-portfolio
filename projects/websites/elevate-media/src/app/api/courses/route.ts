import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

export async function GET() {
  try {
    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: courses, error } = await supabase
      .from("courses")
      .select("*, departments(name)")
      .order("code", { ascending: true });

    if (error) throw new Error(error.message);

    const courseIds = (courses || []).map((c: { id: string }) => c.id);
    const enrollCounts: Record<string, number> = {};
    if (courseIds.length) {
      const { data: enrollments } = await supabase.from("course_enrollments").select("courseId").in("courseId", courseIds);
      (enrollments || []).forEach((e: { courseId: string }) => {
        enrollCounts[e.courseId] = (enrollCounts[e.courseId] || 0) + 1;
      });
    }

    const normalized = (courses || []).map((c: any) => ({
      ...c,
      department: Array.isArray(c.departments) ? c.departments[0] : c.departments || undefined,
      _count: { enrollments: enrollCounts[c.id] || 0 },
    }));

    return NextResponse.json({ courses: normalized });
  } catch (error) {
    console.error("Error fetching courses:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
