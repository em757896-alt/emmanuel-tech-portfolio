import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}

function now() {
  return new Date().toISOString();
}

export async function GET(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { searchParams } = new URL(req.url);
    const courseId = searchParams.get("courseId");
    const studentId = searchParams.get("studentId");
    const userRole = (session.user as { role: string }).role;

    if (userRole === "STUDENT") {
      const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const { data, error } = await supabase
        .from("grades")
        .select("id, finalGrade, gpa, semester, year, calculatedAt, courses(id, name, code, credits)")
        .eq("studentId", student.id)
        .order("year", { ascending: false })
        .order("semester", { ascending: false });

      if (error) throw error;

      const grades = (data || []).map((g: any) => {
        const { courses: c, ...rest } = g as { courses?: unknown };
        return { ...rest, course: c };
      });

      const totalCredits = grades.reduce((sum: number, g: any) => sum + (g.course?.credits || 0), 0);
      const weightedGpa = grades.reduce((sum: number, g: any) => sum + (g.gpa || 0) * (g.course?.credits || 0), 0);
      const cumulativeGpa = totalCredits > 0 ? Math.round((weightedGpa / totalCredits) * 100) / 100 : 0;

      return NextResponse.json({ grades, cumulativeGpa });
    }

    let query = supabase
      .from("grades")
      .select("id, finalGrade, gpa, semester, year, calculatedAt, courses(id, name, code, credits), students(id, firstName, lastName, studentId)")
      .order("year", { ascending: false })
      .order("semester", { ascending: false });

    if (courseId) query = query.eq("courseId", courseId);
    if (studentId) query = query.eq("studentId", studentId);

    const { data, error } = await query;
    if (error) throw error;

    const grades = (data || []).map((g: any) => {
      const { courses: c, students: st, ...rest } = g as { courses?: unknown; students?: unknown };
      return { ...rest, course: c, student: st };
    });

    return NextResponse.json({ grades });
  } catch (error) {
    console.error("Error fetching grades:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const userRole = (session.user as { role: string }).role;
    if (userRole !== "TEACHER" && userRole !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { studentId, courseId, semester, year, finalGrade, gpa } = body;

    if (!studentId || !courseId || !semester || !year || !finalGrade || gpa === undefined) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    const { data: existing } = await supabase
      .from("grades")
      .select("id")
      .eq("studentId", studentId)
      .eq("courseId", courseId)
      .eq("semester", semester)
      .eq("year", year)
      .maybeSingle();

    let data;
    let error;

    if (existing) {
      const res = await supabase
        .from("grades")
        .update({ finalGrade, gpa, calculatedAt: now() })
        .eq("id", existing.id)
        .select("id, finalGrade, gpa, semester, year, calculatedAt, courses(id, name, code, credits)")
        .single();
      data = res.data;
      error = res.error;
    } else {
      const res = await supabase
        .from("grades")
        .insert({
          id: genId(),
          studentId,
          courseId,
          semester,
          year,
          finalGrade,
          gpa,
          calculatedAt: now(),
        })
        .select("id, finalGrade, gpa, semester, year, calculatedAt, courses(id, name, code, credits)")
        .single();
      data = res.data;
      error = res.error;
    }

    if (error) throw error;

    const { courses: c, ...rest } = data as { courses?: unknown };

    return NextResponse.json({ grade: { ...rest, course: c } });
  } catch (error) {
    console.error("Error saving grade:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
