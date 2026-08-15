import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

export async function POST(req: Request) {
  try {
    const { type, studentId, employeeId } = await req.json();
    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    if (type === "student" && studentId) {
      const { data: student } = await supabase.from("students").select("userId").eq("studentId", studentId).single();
      if (!student) return NextResponse.json({ error: "Student ID not found. Please register first." }, { status: 404 });
      const { data: user } = await supabase.from("users").select("email").eq("id", student.userId).single();
      if (!user) return NextResponse.json({ error: "User account not found." }, { status: 404 });
      return NextResponse.json({ email: user.email });
    }

    if (type === "teacher" && employeeId) {
      const { data: teacher } = await supabase.from("teachers").select("userId, isHod, departmentId").eq("employeeId", employeeId).single();
      if (!teacher) return NextResponse.json({ error: "Employee ID not found. Please apply first." }, { status: 404 });
      const { data: user } = await supabase.from("users").select("email").eq("id", teacher.userId).single();
      if (!user) return NextResponse.json({ error: "User account not found." }, { status: 404 });
      return NextResponse.json({ email: user.email, isHod: teacher.isHod === true, departmentId: teacher.departmentId ?? null });
    }

    return NextResponse.json({ error: "Invalid request" }, { status: 400 });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
