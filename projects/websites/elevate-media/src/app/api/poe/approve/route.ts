import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { resolveStudentContext } from "@/lib/institution";
import { getApprover, canAct, buildSidePatch, computeApprovalStatus } from "@/lib/approval";

function now() {
  return new Date().toISOString();
}

export async function POST(req: Request) {
  try {
    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const approver = await getApprover(supabase);
    if (!approver) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const body = await req.json();
    const { id, approve, as } = body;

    if (!id || typeof approve !== "boolean" || !["lecturer", "hod"].includes(as)) {
      return NextResponse.json({ error: "id, approve and as (lecturer|hod) are required" }, { status: 400 });
    }

    const { data: doc } = await supabase
      .from("poe_documents")
      .select("id, title, status, lecturerApproved, hodApproved, source, students!poe_documents_studentId_fkey(*)")
      .eq("id", id)
      .maybeSingle();

    if (!doc) return NextResponse.json({ error: "Document not found" }, { status: 404 });
    if (doc.status === "APPROVED" && approve) {
      return NextResponse.json({ error: "Document already approved" }, { status: 400 });
    }

    const ctx = await resolveStudentContext(supabase, doc.students);

    if (!canAct(approver, ctx, as)) {
      if (as === "hod") {
        return NextResponse.json({ error: "Only the HOD can approve this document" }, { status: 403 });
      }
      return NextResponse.json(
        { error: "Only the unit lecturer (or the HOD, by the HOD's powers) can approve this document" },
        { status: 403 },
      );
    }

    const patch = buildSidePatch(approver, ctx, as, approve, {
      lecturerApproved: "lecturerApproved",
      hodApproved: "hodApproved",
      lecturerVerifiedBy: "lecturerVerifiedBy",
      lecturerVerifiedByRole: "lecturerVerifiedByRole",
      hodVerifiedBy: "hodVerifiedBy",
    });

    const nextLecturer = as === "lecturer" ? approve : (doc.lecturerApproved ?? false);
    const nextHod = as === "hod" ? approve : (doc.hodApproved ?? false);

    if (!approve) {
      patch.status = "REJECTED";
      patch.rejectedAt = now();
      patch.rejectedBy = approver.teacherId ?? "admin";
    } else {
      patch.status = computeApprovalStatus(nextLecturer, nextHod);
      patch.rejectedAt = null;
      patch.rejectedBy = null;
      if (patch.status === "APPROVED") patch.approvedAt = now();
    }

    const { data: updated, error } = await supabase
      .from("poe_documents")
      .update(patch)
      .eq("id", id)
      .select("id, title, status, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, approvedAt, rejectedAt, rejectedBy, source")
      .single();

    if (error) throw error;

    return NextResponse.json({ document: updated });
  } catch (error) {
    console.error("Error approving POE:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
