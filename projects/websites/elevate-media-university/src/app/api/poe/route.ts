import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";
import { resolveStudentContext, resolveTeacherFaculty } from "@/lib/institution";

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
    const { searchParams } = new URL(req.url);
    const scope = searchParams.get("scope");

    if (scope === "approvals" && userRole !== "STUDENT") {
      let courseIds: string[] = [];

      if (userRole === "ADMIN") {
        const { data: allCourses } = await supabase.from("courses").select("id");
        courseIds = (allCourses || []).map((c: { id: string }) => c.id);
      } else if (userRole === "TEACHER") {
        const { data: teacher } = await supabase
          .from("teachers")
          .select("id, isHod, departmentId")
          .eq("userId", session.user!.id)
          .single();
        if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

        const fac = await resolveTeacherFaculty(supabase, teacher.departmentId);
        const isFacultyHod = fac.hodTeacherId === teacher.id;

        const { data: assignments } = await supabase
          .from("course_assignments")
          .select("courseId")
          .eq("teacherId", teacher.id);
        courseIds = (assignments || []).map((a: { courseId: string }) => a.courseId);

        if ((teacher.isHod || isFacultyHod) && teacher.departmentId) {
          let deptIds = [teacher.departmentId];
          if (isFacultyHod && fac.facultyId) {
            const { data: depts } = await supabase.from("departments").select("id").eq("facultyId", fac.facultyId);
            deptIds = (depts || []).map((d: { id: string }) => d.id);
          }
          const { data: deptCourses } = await supabase
            .from("courses")
            .select("id")
            .in("departmentId", deptIds);
          const deptCourseIds = (deptCourses || []).map((c: { id: string }) => c.id);
          courseIds = [...new Set([...courseIds, ...deptCourseIds])];
        }
      } else {
        return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
      }

      if (!courseIds.length) return NextResponse.json({ documents: [] });

      const { data: documents, error } = await supabase
        .from("poe_documents")
        .select("id, title, description, fileUrl, fileName, fileType, fileSize, uploadedAt, status, lecturerApproved, hodApproved, approvedAt, source, courseId, resultDocumentId, studentId, students(id, firstName, lastName, studentId, courseCode)")
        .in("courseId", courseIds)
        .order("uploadedAt", { ascending: false })
        .limit(500);

      if (error) throw error;

      let teacherRow: { id: string } | null = null;
      if (userRole === "TEACHER") {
        const { data: t } = await supabase.from("teachers").select("id").eq("userId", session.user!.id).maybeSingle();
        teacherRow = t;
      }

      const docs = await Promise.all((documents || []).map(async (d: any) => {
        const { students: st, ...rest } = d as { students?: unknown };
        let canApproveAs = { lecturer: userRole === "ADMIN", hod: userRole === "ADMIN" };
        if (teacherRow) {
          const ctx = await resolveStudentContext(supabase, d.students);
          canApproveAs = {
            lecturer: ctx.unitLecturerId !== null && ctx.unitLecturerId === teacherRow.id,
            hod: (ctx.hodId !== null && ctx.hodId === teacherRow.id) || (ctx.facultyHodId !== null && ctx.facultyHodId === teacherRow.id),
          };
        }
        return { ...rest, student: st, canApproveAs };
      }));

      return NextResponse.json({ documents: docs });
    }

    const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const { data: documents, error } = await supabase
      .from("poe_documents")
      .select("id, title, description, fileUrl, fileName, fileType, fileSize, uploadedAt, status, lecturerApproved, hodApproved, approvedAt, source, courseId, resultDocumentId")
      .eq("studentId", student.id)
      .order("uploadedAt", { ascending: false });

    if (error) throw error;

    return NextResponse.json({ documents: documents || [] });
  } catch (error) {
    console.error("Error fetching POE documents:", error);
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
    const { title, description, fileUrl, fileName, fileType, fileSize, courseId } = body;

    if (!title || !fileUrl) {
      return NextResponse.json({ error: "Title and file are required" }, { status: 400 });
    }

    const { data: uploads, error: countErr } = await supabase
      .from("poe_documents")
      .select("id", { count: "exact", head: true })
      .eq("studentId", student.id)
      .eq("source", "UPLOAD");

    if (countErr) throw countErr;

    if ((uploads?.length || 0) >= 5) {
      return NextResponse.json({
        error: "Upload limit reached (5). The earlier uploads must first be verified by the unit lecturer and the HOD before you can add more.",
      }, { status: 400 });
    }

    const { data: document, error } = await supabase
      .from("poe_documents")
      .insert({
        id: genId(),
        studentId: student.id,
        title,
        description: description || null,
        fileUrl,
        fileName: fileName || "document",
        fileType: fileType || "application/pdf",
        fileSize: fileSize || 0,
        uploadedAt: now(),
        status: "PENDING",
        lecturerApproved: false,
        hodApproved: false,
        source: "UPLOAD",
        courseId: courseId || null,
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json({ document }, { status: 201 });
  } catch (error) {
    console.error("Error uploading POE:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const { searchParams } = new URL(req.url);
    const id = searchParams.get("id");
    if (!id) return NextResponse.json({ error: "ID required" }, { status: 400 });

    const { data: doc } = await supabase
      .from("poe_documents")
      .select("id, source, status")
      .eq("id", id)
      .eq("studentId", student.id)
      .maybeSingle();

    if (!doc) return NextResponse.json({ error: "Document not found" }, { status: 404 });
    if (doc.source !== "UPLOAD" || doc.status === "APPROVED") {
      return NextResponse.json({ error: "This document cannot be deleted" }, { status: 400 });
    }

    const { error } = await supabase
      .from("poe_documents")
      .delete()
      .eq("id", id)
      .eq("studentId", student.id);

    if (error) throw error;

    return NextResponse.json({ message: "Document deleted" });
  } catch (error) {
    console.error("Error deleting POE:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
