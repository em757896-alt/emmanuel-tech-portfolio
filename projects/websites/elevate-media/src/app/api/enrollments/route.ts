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

      let query = supabase
        .from("course_enrollments")
        .select("id, courseId, enrolledAt, courses(id, name, code, credits, description)")
        .eq("studentId", student.id)
        .order("enrolledAt", { ascending: false });
      if (courseId) query = query.eq("courseId", courseId);

      const { data, error } = await query;
      if (error) throw error;

      const enrollments = (data || []).map((e: any) => {
        const { courses: c, ...rest } = e as { courses?: unknown };
        return { ...rest, course: c };
      });

      return NextResponse.json({ enrollments });
    }

    let query = supabase
      .from("course_enrollments")
      .select("id, courseId, enrolledAt, courses(id, name, code, credits, description), students(id, firstName, lastName, studentId)")
      .order("enrolledAt", { ascending: false });

    if (courseId) query = query.eq("courseId", courseId);
    if (studentId) query = query.eq("studentId", studentId);

    const { data, error } = await query;
    if (error) throw error;

    const enrollments = (data || []).map((e: any) => {
      const { courses: c, students: st, ...rest } = e as { courses?: unknown; students?: unknown };
      return { ...rest, course: c, student: st };
    });

    return NextResponse.json({ enrollments });
  } catch (error) {
    console.error("Error fetching enrollments:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { studentId, courseId } = body;

    if (!studentId || !courseId) {
      return NextResponse.json({ error: "Student ID and Course ID required" }, { status: 400 });
    }

    const { data, error } = await supabase
      .from("course_enrollments")
      .insert({
        id: genId(),
        studentId,
        courseId,
        enrolledAt: now(),
      })
      .select("id, courseId, enrolledAt, courses(id, name, code, credits, description), students(id, firstName, lastName, studentId)")
      .single();

    if (error) throw error;

    const { courses: c, students: st, ...rest } = data as { courses?: unknown; students?: unknown };

    return NextResponse.json({ enrollment: { ...rest, course: c, student: st } }, { status: 201 });
  } catch (error) {
    console.error("Error creating enrollment:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { searchParams } = new URL(req.url);
    const id = searchParams.get("id");
    if (!id) return NextResponse.json({ error: "ID required" }, { status: 400 });

    const { error } = await supabase.from("course_enrollments").delete().eq("id", id);
    if (error) throw error;

    return NextResponse.json({ message: "Enrollment removed" });
  } catch (error) {
    console.error("Error deleting enrollment:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
