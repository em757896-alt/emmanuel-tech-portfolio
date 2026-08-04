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

    const userRole = (session.user as { role: string }).role;

    if (userRole === "STUDENT") {
      const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const { data: results, error } = await supabase
        .from("result_documents")
        .select("*, courses!result_documents_courseId_fkey(id, name, code)")
        .eq("studentId", student.id)
        .eq("released", true)
        .order("uploadedAt", { ascending: false });

      if (error) throw error;

      const { data: savedPoe } = await supabase
        .from("poe_documents")
        .select("resultDocumentId")
        .eq("studentId", student.id)
        .eq("source", "RESULT");

      const savedIds = new Set((savedPoe || []).map((p: any) => p.resultDocumentId));

      const resultsMapped = (results || []).map((r: any) => {
        const { courses: c, ...rest } = r;
        return { ...rest, course: c, savedToPoe: savedIds.has(r.id) };
      });

      return NextResponse.json({ results: resultsMapped });
    }

    if (userRole === "TEACHER") {
      const { data: teacher } = await supabase.from("teachers").select("id, departmentId, isHod").eq("userId", session.user!.id).maybeSingle();
      if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

      const { data: assignments } = await supabase.from("course_assignments").select("courseId").eq("teacherId", teacher.id);
      let courseIds: string[] = (assignments || []).map((a: any) => a.courseId);

      if (teacher.isHod && teacher.departmentId) {
        const { data: deptCourses } = await supabase.from("courses").select("id").eq("departmentId", teacher.departmentId);
        courseIds = [...new Set([...courseIds, ...(deptCourses || []).map((c: any) => c.id)])];
      }

      if (courseIds.length === 0) {
        return NextResponse.json({ results: [] });
      }

      const { data: results, error } = await supabase
        .from("result_documents")
        .select("*, courses!result_documents_courseId_fkey(id, name, code), students!result_documents_studentId_fkey(id, firstName, lastName, studentId)")
        .in("courseId", courseIds)
        .order("uploadedAt", { ascending: false });

      if (error) throw error;

      const resultsMapped = (results || []).map((r: any) => {
        const { courses: c, students: st, ...rest } = r;
        return { ...rest, course: c, student: st };
      });

      return NextResponse.json({ results: resultsMapped });
    }

    const { data: results, error } = await supabase
      .from("result_documents")
      .select("*, courses!result_documents_courseId_fkey(id, name, code), students!result_documents_studentId_fkey(id, firstName, lastName, studentId)")
      .order("uploadedAt", { ascending: false });

    if (error) throw error;

    const resultsMapped = (results || []).map((r: any) => {
      const { courses: c, students: st, ...rest } = r;
      return { ...rest, course: c, student: st };
    });

    return NextResponse.json({ results: resultsMapped });
  } catch (error) {
    console.error("Error fetching results:", error);
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
    const { studentId, courseId, semester, year, title, description, grade, fileUrl, fileName, fileType, fileSize } = body;

    if (!studentId || !courseId || !title || !fileUrl) {
      return NextResponse.json({ error: "studentId, courseId, title and file are required" }, { status: 400 });
    }

    const { data: course } = await supabase.from("courses").select("id, departmentId").eq("id", courseId).maybeSingle();
    if (!course) return NextResponse.json({ error: "Course not found" }, { status: 400 });

    if (userRole === "TEACHER") {
      const { data: teacher } = await supabase.from("teachers").select("id, departmentId, isHod").eq("userId", session.user!.id).maybeSingle();
      if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

      const { data: assigned } = await supabase
        .from("course_assignments")
        .select("id")
        .eq("teacherId", teacher.id)
        .eq("courseId", courseId)
        .limit(1);

      const isAssigned = (assigned?.length || 0) > 0;
      const isHodOfDept = teacher.isHod && teacher.departmentId === course.departmentId;

      if (!isAssigned && !isHodOfDept) {
        return NextResponse.json({ error: "You can only upload results for courses you teach or are HOD of." }, { status: 403 });
      }
    }

    const { data: teacher } = await supabase.from("teachers").select("id").eq("userId", session.user!.id).maybeSingle();

    // The uploader is the unit lecturer (or HOD acting for the course). The
    // result is released to the student immediately with the lecturer side
    // verified; downloads stay locked until the HOD verifies.
    const { data: result, error } = await supabase
      .from("result_documents")
      .insert({
        id: genId(),
        studentId,
        courseId,
        teacherId: teacher?.id ?? null,
        semester: semester || null,
        year: year || null,
        title,
        description: description || null,
        grade: grade || null,
        fileUrl,
        fileName: fileName || "result",
        fileType: fileType || "application/pdf",
        fileSize: fileSize || 0,
        uploadedAt: now(),
        released: true,
        status: "PARTIALLY_APPROVED",
        lecturerApproved: true,
        lecturerVerifiedBy: teacher?.id ?? "admin",
        lecturerVerifiedByRole: "LECTURER",
        hodApproved: false,
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json({ result }, { status: 201 });
  } catch (error) {
    console.error("Error uploading result:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
