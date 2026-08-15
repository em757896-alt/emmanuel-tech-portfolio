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
    const userRole = (session.user as { role: string }).role;

    let query = supabase
      .from("exams")
      .select("id, title, date, totalMarks, weight, released, status, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, fileUrl, fileName, courses(id, name, code)")
      .order("date", { ascending: true });

    if (courseId) {
      query = query.eq("courseId", courseId);
    } else if (userRole === "STUDENT") {
      const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });
      const { data: enrolled } = await supabase
        .from("course_enrollments")
        .select("courseId")
        .eq("studentId", student.id);
      const courseIds = (enrolled || []).map((e: { courseId: string }) => e.courseId);
      if (courseIds.length === 0) return NextResponse.json({ exams: [] });
      query = query.in("courseId", courseIds).eq("released", true);
    }

    const { data, error } = await query;
    if (error) throw error;

    let countMap: Record<string, number> = {};
    try {
      const { data: results } = await supabase.from("exam_results").select("examId");
      (results || []).forEach((r: { examId: string }) => {
        countMap[r.examId] = (countMap[r.examId] || 0) + 1;
      });
    } catch {
      countMap = {};
    }

    const exams = (data || []).map((e: any) => {
      const { courses: c, ...rest } = e as { courses?: unknown };
      return { ...rest, course: c, _count: { examResults: countMap[e.id as string] || 0 } };
    });

    return NextResponse.json({ exams });
  } catch (error) {
    console.error("Error fetching exams:", error);
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
    const { title, courseId, date, totalMarks, weight } = body;

    if (!title || !courseId || !date) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    const teacher = await supabase.from("teachers").select("id").eq("userId", session.user!.id).single();
    if (!teacher.data) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

    const { data: exam, error } = await supabase
      .from("exams")
      .insert({
        id: genId(),
        title,
        courseId,
        teacherId: teacher.data.id,
        date: new Date(date).toISOString(),
        totalMarks: totalMarks || 100,
        weight: weight || 30,
        createdAt: now(),
        released: true,
        status: "PARTIALLY_APPROVED",
        lecturerApproved: true,
        lecturerVerifiedBy: teacher.data.id,
        lecturerVerifiedByRole: "LECTURER",
        hodApproved: false,
      })
      .select("id, title, date, totalMarks, weight, courses(id, name, code)")
      .single();

    if (error) throw error;

    const { courses: c, ...rest } = exam as { courses?: unknown };

    return NextResponse.json({ exam: { ...rest, course: c } }, { status: 201 });
  } catch (error) {
    console.error("Error creating exam:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
