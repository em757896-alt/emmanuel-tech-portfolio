import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}

function now() {
  return new Date().toISOString();
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { assignmentId, fileUrl, fileName } = body;

    if (!assignmentId) {
      return NextResponse.json({ error: "Assignment ID is required" }, { status: 400 });
    }

    const { data: student } = await supabase
      .from("students")
      .select("id")
      .eq("userId", session.user!.id)
      .single();
    if (!student) return NextResponse.json({ error: "Student profile not found" }, { status: 404 });

    const { data: existing } = await supabase
      .from("submissions")
      .select("id")
      .eq("assignmentId", assignmentId)
      .eq("studentId", student.id)
      .maybeSingle();

    if (existing) {
      const { data: submission, error } = await supabase
        .from("submissions")
        .update({
          ...(fileUrl !== undefined && { fileUrl: fileUrl || null }),
          ...(fileName !== undefined && { fileName: fileName || null }),
          status: "PENDING",
          submittedAt: now(),
        })
        .eq("id", existing.id)
        .select()
        .single();

      if (error) throw error;
      return NextResponse.json({ submission });
    }

    const { data: submission, error } = await supabase
      .from("submissions")
      .insert({
        id: genId(),
        assignmentId,
        studentId: student.id,
        fileUrl: fileUrl || null,
        fileName: fileName || null,
        status: "PENDING",
        submittedAt: now(),
      })
      .select()
      .single();

    if (error) throw error;
    return NextResponse.json({ submission }, { status: 201 });
  } catch (error) {
    console.error("Error submitting assignment:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function GET(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { searchParams } = new URL(req.url);
    const assignmentId = searchParams.get("assignmentId");
    const userRole = (session.user as { role: string }).role;

    if (userRole === "TEACHER" || userRole === "ADMIN") {
      let query = supabase
        .from("submissions")
        .select("id, assignmentId, status, marks, feedback, submittedAt, fileUrl, fileName, assignments(id, title, totalMarks), students(id, firstName, lastName, studentId)")
        .order("submittedAt", { ascending: false });

      if (userRole === "TEACHER") {
        const { data: teacher } = await supabase.from("teachers").select("id").eq("userId", session.user!.id).single();
        if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });
        const { data: teacherAssignments } = await supabase
          .from("assignments")
          .select("id")
          .eq("teacherId", teacher.id);
        const ids = (teacherAssignments || []).map((a: { id: string }) => a.id);
        if (ids.length === 0) return NextResponse.json({ submissions: [] });
        query = query.in("assignmentId", ids);
      } else if (assignmentId) {
        query = query.eq("assignmentId", assignmentId);
      }

      const { data, error } = await query;
      if (error) throw error;

      const submissions = (data || []).map((s: any) => {
        const { assignments: a, students: st, ...rest } = s as {
          assignments?: unknown;
          students?: unknown;
        };
        return { ...rest, assignment: a, student: st };
      });

      return NextResponse.json({ submissions });
    }

    const { data: student } = await supabase
      .from("students")
      .select("id")
      .eq("userId", session.user!.id)
      .single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    let query = supabase
      .from("submissions")
      .select("id, assignmentId, status, marks, feedback, submittedAt, fileUrl, fileName, assignments(id, title, totalMarks, dueDate)")
      .eq("studentId", student.id)
      .order("submittedAt", { ascending: false });
    if (assignmentId) query = query.eq("assignmentId", assignmentId);

    const { data, error } = await query;
    if (error) throw error;

    const submissions = (data || []).map((s: any) => {
      const { assignments: a, ...rest } = s as { assignments?: unknown };
      return { ...rest, assignment: a };
    });

    return NextResponse.json({ submissions });
  } catch (error) {
    console.error("Error fetching submissions:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function PUT(req: Request) {
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
    const { id, marks, feedback, status } = body;

    if (!id) return NextResponse.json({ error: "Submission ID required" }, { status: 400 });

    const update: Record<string, unknown> = {};
    if (marks !== undefined) update.marks = marks;
    if (feedback !== undefined) update.feedback = feedback;
    if (status) update.status = status;
    if (marks !== undefined) update.gradedAt = now();

    const { data: submission, error } = await supabase
      .from("submissions")
      .update(update)
      .eq("id", id)
      .select()
      .single();

    if (error) throw error;
    return NextResponse.json({ submission });
  } catch (error) {
    console.error("Error grading submission:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
