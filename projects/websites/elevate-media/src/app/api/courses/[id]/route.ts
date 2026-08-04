import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

export async function GET(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { id } = await params;

    const { data: course, error } = await supabase
      .from("courses")
      .select("id, name, code, description, credits, departmentId, semester, year, departments(id, name, code)")
      .eq("id", id)
      .single();
    if (error || !course) return NextResponse.json({ error: "Course not found" }, { status: 404 });

    const [enrollmentsRes, assignmentsRes, examsRes] = await Promise.all([
      supabase
        .from("course_enrollments")
        .select("id, studentId, enrolledAt, students(id, firstName, lastName, studentId)")
        .eq("courseId", id)
        .order("enrolledAt", { ascending: false }),
      supabase
        .from("assignments")
        .select("id, title, description, dueDate, totalMarks, createdAt")
        .eq("courseId", id)
        .order("createdAt", { ascending: false }),
      supabase
        .from("exams")
        .select("id, title, date, totalMarks, weight, createdAt")
        .eq("courseId", id)
        .order("date", { ascending: true }),
    ]);
    if (enrollmentsRes.error) throw enrollmentsRes.error;
    if (assignmentsRes.error) throw assignmentsRes.error;
    if (examsRes.error) throw examsRes.error;

    const { departments, ...rest } = course as { departments?: unknown };

    const enrollments = (enrollmentsRes.data || []).map((e: any) => {
      const { students: s, ...er } = e as { students?: unknown };
      return { ...er, student: s };
    });

    return NextResponse.json({
      course: {
        ...rest,
        department: departments,
        enrollments,
        assignments: assignmentsRes.data || [],
        exams: examsRes.data || [],
        _count: {
          enrollments: enrollmentsRes.data?.length || 0,
          assignments: assignmentsRes.data?.length || 0,
          exams: examsRes.data?.length || 0,
        },
      },
    });
  } catch (error) {
    console.error("Error fetching course:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function PUT(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { id } = await params;
    const body = await req.json();
    const { name, code, description, credits, departmentId, semester, year } = body;

    const update: Record<string, unknown> = {};
    if (name) update.name = name;
    if (code) update.code = code;
    if (description !== undefined) update.description = description;
    if (credits) update.credits = credits;
    if (departmentId !== undefined) update.departmentId = departmentId || null;
    if (semester) update.semester = semester;
    if (year) update.year = year;

    const { data: course, error } = await supabase.from("courses").update(update).eq("id", id).select().single();
    if (error) throw error;

    return NextResponse.json({ course });
  } catch (error) {
    console.error("Error updating course:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function DELETE(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { id } = await params;
    const { error } = await supabase.from("courses").delete().eq("id", id);
    if (error) throw error;

    return NextResponse.json({ message: "Course deleted" });
  } catch (error) {
    console.error("Error deleting course:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
