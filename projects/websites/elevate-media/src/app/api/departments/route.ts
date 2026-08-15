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

    const { data: departments, error } = await supabase
      .from("departments")
      .select("*")
      .order("name", { ascending: true });

    if (error) throw new Error(error.message);

    const deptIds = (departments || []).map((d: { id: string }) => d.id);
    const [studentsRes, teachersRes, coursesRes] = await Promise.all([
      deptIds.length ? supabase.from("students").select("departmentId").in("departmentId", deptIds) : Promise.resolve({ data: [] }),
      deptIds.length ? supabase.from("teachers").select("departmentId").in("departmentId", deptIds) : Promise.resolve({ data: [] }),
      deptIds.length ? supabase.from("courses").select("departmentId").in("departmentId", deptIds) : Promise.resolve({ data: [] }),
    ]);

    const countBy = (rows: { departmentId: string | null }[]) => {
      const counts: Record<string, number> = {};
      rows.forEach((r) => {
        if (r.departmentId) counts[r.departmentId] = (counts[r.departmentId] || 0) + 1;
      });
      return counts;
    };

    const studentCounts = countBy((studentsRes.data as { departmentId: string | null }[]) || []);
    const teacherCounts = countBy((teachersRes.data as { departmentId: string | null }[]) || []);
    const courseCounts = countBy((coursesRes.data as { departmentId: string | null }[]) || []);

    const normalized = (departments || []).map((d: { id: string }) => ({
      ...d,
      _count: {
        students: studentCounts[d.id] || 0,
        teachers: teacherCounts[d.id] || 0,
        courses: courseCounts[d.id] || 0,
      },
    }));

    return NextResponse.json({ departments: normalized });
  } catch (error) {
    console.error("Error fetching departments:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
