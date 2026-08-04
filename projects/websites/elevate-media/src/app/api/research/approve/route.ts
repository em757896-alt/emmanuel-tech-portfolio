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

    const { data: paper } = await supabase
      .from("research")
      .select("id, title, status, lecturerApproved, hodApproved, rejected, reviewNotes, reviewedAt, students!research_studentId_fkey(*)")
      .eq("id", id)
      .maybeSingle();

    if (!paper) return NextResponse.json({ error: "Paper not found" }, { status: 404 });

    const ctx = await resolveStudentContext(supabase, paper.students);

    if (!canAct(approver, ctx, as)) {
      if (as === "hod") {
        return NextResponse.json({ error: "Only the HOD can verify this paper" }, { status: 403 });
      }
      return NextResponse.json(
        { error: "Only the unit lecturer (or the HOD, by the HOD's powers) can verify this paper" },
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

    const nextLecturer = as === "lecturer" ? approve : (paper.lecturerApproved ?? false);
    const nextHod = as === "hod" ? approve : (paper.hodApproved ?? false);

    if (!approve) {
      patch.rejected = true;
      patch.status = "REJECTED";
      patch.reviewedAt = now();
      patch.reviewNotes = body.reviewNotes ?? paper.reviewNotes ?? null;
    } else {
      patch.rejected = false;
      patch.status = computeApprovalStatus(nextLecturer, nextHod);
      patch.reviewedAt = computeApprovalStatus(nextLecturer, nextHod) === "APPROVED" ? now() : paper.reviewedAt ?? null;
    }

    const { data: updated, error } = await supabase
      .from("research")
      .update(patch)
      .eq("id", id)
      .select("id, title, status, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy, rejected, rejectionReason, reviewNotes, reviewedAt")
      .single();

    if (error) throw error;

    return NextResponse.json({ paper: updated });
  } catch (error) {
    console.error("Error verifying research:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
