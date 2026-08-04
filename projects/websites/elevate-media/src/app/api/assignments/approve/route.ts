import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { resolveCourseContext } from "@/lib/institution";
import { getApprover, canAct, buildSidePatch, computeApprovalStatus } from "@/lib/approval";

export async function POST(req: Request) {
  try {
    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const approver = await getApprover(supabase);
    if (!approver) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const body = await req.json();
    const { id, approve, as, release } = body;

    if (!id || typeof approve !== "boolean" || !["lecturer", "hod"].includes(as)) {
      return NextResponse.json({ error: "id, approve and as (lecturer|hod) are required" }, { status: 400 });
    }

    const { data: doc } = await supabase
      .from("assignments")
      .select("id, title, courseId, released, status, lecturerApproved, hodApproved")
      .eq("id", id)
      .maybeSingle();

    if (!doc) return NextResponse.json({ error: "Assignment not found" }, { status: 404 });
    if (!doc.courseId) return NextResponse.json({ error: "Assignment has no course; cannot verify" }, { status: 400 });

    const ctx = await resolveCourseContext(supabase, doc.courseId);

    if (!canAct(approver, ctx, as)) {
      if (as === "hod") {
        return NextResponse.json({ error: "Only the HOD can verify this assignment" }, { status: 403 });
      }
      return NextResponse.json(
        { error: "Only the unit lecturer (or the HOD, by the HOD's powers) can verify this assignment" },
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
    patch.status = computeApprovalStatus(nextLecturer, nextHod);

    if (release === true) {
      if (!nextLecturer) {
        return NextResponse.json(
          { error: "The unit lecturer side must be verified before this can be released." },
          { status: 400 },
        );
      }
      patch.released = true;
    }

    const { data: updated, error } = await supabase
      .from("assignments")
      .update(patch)
      .eq("id", id)
      .select("id, title, status, released, lecturerApproved, hodApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy")
      .single();

    if (error) throw error;

    return NextResponse.json({ assignment: updated });
  } catch (error) {
    console.error("Error verifying assignment:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
