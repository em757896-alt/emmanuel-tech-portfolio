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

    const userRole = (session.user as { role: string }).role;
    if (userRole !== "STUDENT") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { resultId } = body;
    if (!resultId) return NextResponse.json({ error: "resultId required" }, { status: 400 });

    const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const { data: result } = await supabase
      .from("result_documents")
      .select("id, studentId, courseId, title, fileUrl, fileName, fileType, fileSize, semester, year, grade")
      .eq("id", resultId)
      .maybeSingle();

    if (!result) return NextResponse.json({ error: "Result not found" }, { status: 404 });
    if (result.studentId !== student.id) {
      return NextResponse.json({ error: "This result does not belong to you" }, { status: 403 });
    }

    const { data: existing } = await supabase
      .from("poe_documents")
      .select("id")
      .eq("resultDocumentId", result.id)
      .eq("studentId", student.id)
      .maybeSingle();

    if (existing) {
      return NextResponse.json({ error: "This result is already saved as POE." }, { status: 409 });
    }

    const { data: document, error } = await supabase
      .from("poe_documents")
      .insert({
        id: genId(),
        studentId: student.id,
        title: result.title,
        description: result.grade ? `Verified result - Grade: ${result.grade}` : "Verified result",
        fileUrl: result.fileUrl,
        fileName: result.fileName,
        fileType: result.fileType || "application/pdf",
        fileSize: result.fileSize || 0,
        uploadedAt: now(),
        status: "APPROVED",
        lecturerApproved: true,
        hodApproved: true,
        approvedAt: now(),
        source: "RESULT",
        resultDocumentId: result.id,
        courseId: result.courseId,
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json({ document }, { status: 201 });
  } catch (error) {
    console.error("Error saving result as POE:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
