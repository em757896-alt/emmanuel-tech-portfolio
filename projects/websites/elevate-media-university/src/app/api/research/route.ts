import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";
import { resolveTeacherFaculty } from "@/lib/institution";

function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}

function now() {
  return new Date().toISOString();
}

const VERIFY_FIELDS = "courseId, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, rejected, rejectionReason, status, reviewNotes, uploadedAt, reviewedAt";

export async function GET(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const userRole = (session.user as { role: string }).role;

    if (userRole === "STUDENT") {
      const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const { data, error } = await supabase
        .from("research")
        .select(`id, title, abstract, fileUrl, fileName, ${VERIFY_FIELDS}, teachers(id, firstName, lastName)`)
        .eq("studentId", student.id)
        .order("uploadedAt", { ascending: false });
      if (error) throw error;

      const papers = (data || []).map((p: any) => {
        const { teachers: t, ...rest } = p as { teachers?: unknown };
        return { ...rest, advisor: t };
      });

      return NextResponse.json({ papers });
    }

    if (userRole === "TEACHER") {
      const { data: teacher } = await supabase.from("teachers").select("id, departmentId, isHod").eq("userId", session.user!.id).single();
      if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

      const fac = await resolveTeacherFaculty(supabase, teacher.departmentId);
      const isFacultyHod = fac.hodTeacherId === teacher.id;

      let query = supabase
        .from("research")
        .select(`id, title, abstract, fileUrl, fileName, ${VERIFY_FIELDS}, students(id, firstName, lastName, studentId)`)
        .order("uploadedAt", { ascending: false });

      if (teacher.isHod || isFacultyHod) {
        const { data: deptCourses } = await supabase.from("courses").select("id").eq("departmentId", teacher.departmentId);
        let courseIds = (deptCourses || []).map((c: any) => c.id);
        if (isFacultyHod && fac.facultyId) {
          const { data: depts } = await supabase.from("departments").select("id").eq("facultyId", fac.facultyId);
          const deptIds = (depts || []).map((d: any) => d.id);
          if (deptIds.length > 0) {
            const { data: facCourses } = await supabase.from("courses").select("id").in("departmentId", deptIds);
            courseIds = [...new Set([...courseIds, ...(facCourses || []).map((c: any) => c.id)])];
          }
        }
        if (courseIds.length > 0) query = query.in("courseId", courseIds);
        else query = query.eq("advisorId", teacher.id);
      } else {
        query = query.eq("advisorId", teacher.id);
      }

      const { data, error } = await query;
      if (error) throw error;

      const papers = (data || []).map((p: any) => {
        const { students: s, ...rest } = p as { students?: unknown };
        return { ...rest, student: s };
      });

      return NextResponse.json({ papers });
    }

    const { data, error } = await supabase
      .from("research")
      .select(`id, title, abstract, fileUrl, fileName, ${VERIFY_FIELDS}, students(id, firstName, lastName, studentId), teachers(id, firstName, lastName)`)
      .order("uploadedAt", { ascending: false });
    if (error) throw error;

    const papers = (data || []).map((p: any) => {
      const { students: s, teachers: t, ...rest } = p as { students?: unknown; teachers?: unknown };
      return { ...rest, student: s, advisor: t };
    });

    return NextResponse.json({ papers });
  } catch (error) {
    console.error("Error fetching research:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const body = await req.json();
    const { title, abstract, fileUrl, fileName, advisorId } = body;

    if (!title) {
      return NextResponse.json({ error: "Title is required" }, { status: 400 });
    }

    const { data: paper, error } = await supabase
      .from("research")
      .insert({
        id: genId(),
        studentId: student.id,
        title,
        abstract: abstract || null,
        fileUrl: fileUrl || null,
        fileName: fileName || null,
        advisorId: advisorId || null,
        status: "DRAFT",
        uploadedAt: now(),
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json({ paper }, { status: 201 });
  } catch (error) {
    console.error("Error creating research:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { id, status, reviewNotes, advisorId, fileUrl, fileName } = body;

    if (!id) return NextResponse.json({ error: "ID required" }, { status: 400 });

    const update: Record<string, unknown> = {};
    if (status) update.status = status;
    if (reviewNotes !== undefined) update.reviewNotes = reviewNotes;
    if (advisorId !== undefined) update.advisorId = advisorId;
    if (fileUrl) update.fileUrl = fileUrl;
    if (fileName) update.fileName = fileName;
    if (status === "APPROVED" || status === "REJECTED") update.reviewedAt = now();

    const { data: paper, error } = await supabase.from("research").update(update).eq("id", id).select().single();
    if (error) throw error;

    return NextResponse.json({ paper });
  } catch (error) {
    console.error("Error updating research:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
