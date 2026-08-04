import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}

function now() {
  return new Date().toISOString();
}

const FILE_FIELDS = "fileUrl, fileName";
const VERIFY_FIELDS = "released, status, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy";

type SupabaseRow = {
  id: string;
  title?: string;
  description?: string | null;
  dueDate?: string;
  totalMarks?: number;
  fileUrl?: string | null;
  fileName?: string | null;
  createdAt?: string;
  courses?: unknown;
  course?: unknown;
  submission?: unknown;
  _count?: unknown;
};

async function findStudent(supabase: NonNullable<typeof supabaseAdmin>, userId: string) {
  const { data } = await supabase.from("students").select("id, studentId").eq("userId", userId).single();
  return data || null;
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

    if (userRole === "STUDENT") {
      const student = await findStudent(supabase, session.user!.id);
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const { data: enrolled } = await supabase
        .from("course_enrollments")
        .select("courseId")
        .eq("studentId", student.id);

      const courseIds = (enrolled || []).map((e: { courseId: string }) => e.courseId);
      if (courseIds.length === 0) return NextResponse.json({ assignments: [] });

      let query = supabase
        .from("assignments")
        .select(`id, title, description, dueDate, totalMarks, ${FILE_FIELDS}, ${VERIFY_FIELDS}, courses(id, name, code)`)
        .in("courseId", courseIds)
        .eq("released", true)
        .order("dueDate", { ascending: false });

      let res: { data: any; error: any } = await query;
      if (res.error) {
        res = await supabase
          .from("assignments")
          .select("id, title, description, dueDate, totalMarks, courses(id, name, code)")
          .in("courseId", courseIds)
          .eq("released", true)
          .order("dueDate", { ascending: false });
      }
      if (res.error) throw res.error;

      const { data: submissions } = await supabase
        .from("submissions")
        .select("id, assignmentId, status, marks, feedback, submittedAt, fileUrl, fileName")
        .eq("studentId", student.id);

      const submissionMap: Record<string, unknown> = {};
      (submissions || []).forEach((s: { assignmentId: string }) => {
        submissionMap[s.assignmentId] = s;
      });

      const assignments = (res.data || []).map((a: SupabaseRow) => {
        const { courses, ...rest } = a;
        return {
          ...rest,
          course: courses,
          submission: submissionMap[a.id] || null,
        };
      });

      return NextResponse.json({ assignments });
    }

    let where: { teacherId?: string; courseId?: string } = {};
    if (courseId) where.courseId = courseId;

    if (userRole === "TEACHER") {
      const teacher = await supabase.from("teachers").select("id").eq("userId", session.user!.id).single();
      if (!teacher.data) return NextResponse.json({ error: "Teacher profile not found" }, { status: 404 });
      where.teacherId = teacher.data.id;
    }

    let query: any = supabase
      .from("assignments")
      .select(`id, title, description, dueDate, totalMarks, ${FILE_FIELDS}, ${VERIFY_FIELDS}, courses(id, name, code)`)
      .order("dueDate", { ascending: false });
    if (where.teacherId) query = query.eq("teacherId", where.teacherId);
    if (where.courseId) query = query.eq("courseId", where.courseId);

    let res: { data: any; error: any } = await query;
    if (res.error) {
      query = supabase
        .from("assignments")
        .select("id, title, description, dueDate, totalMarks, courses(id, name, code)")
        .order("dueDate", { ascending: false });
      if (where.teacherId) query = query.eq("teacherId", where.teacherId);
      if (where.courseId) query = query.eq("courseId", where.courseId);
      res = await query;
    }
    if (res.error) throw res.error;

    const assignments = res.data || [];

    let countQuery = supabase.from("submissions").select("assignmentId");
    if (where.teacherId) {
      const ids = assignments.map((a: SupabaseRow) => a.id);
      if (ids.length > 0) countQuery = countQuery.in("assignmentId", ids);
      else return NextResponse.json({ assignments });
    }
    const { data: allSubs } = await countQuery;
    const countMap: Record<string, number> = {};
    (allSubs || []).forEach((s: { assignmentId: string }) => {
      countMap[s.assignmentId] = (countMap[s.assignmentId] || 0) + 1;
    });

    const result = (assignments as SupabaseRow[]).map((a) => {
      const { courses, ...rest } = a;
      return { ...rest, course: courses, _count: { submissions: countMap[a.id] || 0 } };
    });

    return NextResponse.json({ assignments: result });
  } catch (error) {
    console.error("Error fetching assignments:", error);
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
    const { title, description, courseId, dueDate, totalMarks, fileUrl, fileName } = body;

    if (!title || !courseId || !dueDate) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    const teacher = await supabase.from("teachers").select("id").eq("userId", session.user!.id).single();
    if (!teacher.data) return NextResponse.json({ error: "Teacher profile not found" }, { status: 404 });

    const payload = {
      id: genId(),
      title,
      description: description || null,
      courseId,
      teacherId: teacher.data.id,
      dueDate: new Date(dueDate).toISOString(),
      totalMarks: totalMarks || 100,
      createdAt: now(),
      released: true,
      status: "PARTIALLY_APPROVED",
      lecturerApproved: true,
      lecturerVerifiedBy: teacher.data.id,
      lecturerVerifiedByRole: "LECTURER",
      hodApproved: false,
    };

    let res;
    if (fileUrl) {
      res = await supabase.from("assignments").insert({ ...payload, fileUrl, fileName: fileName || null });
      if (res.error) res = await supabase.from("assignments").insert(payload);
    } else {
      res = await supabase.from("assignments").insert(payload);
    }

    if (res.error) throw res.error;

    return NextResponse.json({
      assignment: {
        ...payload,
        fileUrl: fileUrl || null,
        fileName: fileName || null,
        course: null,
        _count: { submissions: 0 },
      },
    }, { status: 201 });
  } catch (error) {
    console.error("Error creating assignment:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
