import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { resolveStudentContext } from "@/lib/institution";
import { getApprover, canAct, buildSidePatch } from "@/lib/approval";

export async function POST(req: Request) {
  try {
    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const approver = await getApprover(supabase);
    if (!approver) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const body = await req.json();
    const { id, approve, as } = body;

    if (!id || typeof approve !== "boolean" || !["lecturer", "hod", "admin"].includes(as)) {
      return NextResponse.json({ error: "id, approve and as (lecturer|hod|admin) are required" }, { status: 400 });
    }

    const { data: record } = await supabase
      .from("attendance_records")
      .select("id, type, lecturerApproved, hodApproved, adminApproved, students!attendance_records_studentId_fkey(*)")
      .eq("id", id)
      .maybeSingle();

    if (!record) return NextResponse.json({ error: "Attendance record not found" }, { status: 404 });

    const ctx = await resolveStudentContext(supabase, record.students);

    const type = record.type || "DAILY";

    if (type === "CORRECTION") {
      if (as === "hod") {
        return NextResponse.json({ error: "Corrections are approved by the unit lecturer and Admin (not the HOD)." }, { status: 403 });
      }
      if (as === "lecturer" && !canAct(approver, ctx, "lecturer")) {
        return NextResponse.json({ error: "Only the unit lecturer can approve this correction" }, { status: 403 });
      }
      if (as === "admin" && !approver.isAdmin) {
        return NextResponse.json({ error: "Only an Admin can approve this correction" }, { status: 403 });
      }
    } else {
      if (as === "admin") {
        return NextResponse.json({ error: "Daily attendance is approved by the unit lecturer and the HOD." }, { status: 403 });
      }
      if (as === "lecturer" && !canAct(approver, ctx, "lecturer")) {
        return NextResponse.json({ error: "Only the unit lecturer can approve this attendance" }, { status: 403 });
      }
      if (as === "hod" && !canAct(approver, ctx, "hod")) {
        return NextResponse.json({ error: "Only the HOD can approve this attendance" }, { status: 403 });
      }
    }

    const patch: Record<string, unknown> = {};
    if (as === "lecturer") {
      Object.assign(patch, buildSidePatch(approver, ctx, "lecturer", approve, {
        lecturerApproved: "lecturerApproved",
        hodApproved: "hodApproved",
        lecturerVerifiedBy: "lecturerVerifiedBy",
        lecturerVerifiedByRole: "lecturerVerifiedByRole",
        hodVerifiedBy: "hodVerifiedBy",
      }));
    }
    if (as === "hod") {
      Object.assign(patch, buildSidePatch(approver, ctx, "hod", approve, {
        lecturerApproved: "lecturerApproved",
        hodApproved: "hodApproved",
        lecturerVerifiedBy: "lecturerVerifiedBy",
        lecturerVerifiedByRole: "lecturerVerifiedByRole",
        hodVerifiedBy: "hodVerifiedBy",
      }));
    }
    if (as === "admin") patch.adminApproved = approve;

    const { data: updated, error } = await supabase
      .from("attendance_records")
      .update(patch)
      .eq("id", id)
      .select("id, status, recordDate, type, lecturerApproved, hodApproved, adminApproved, lecturerVerifiedBy, lecturerVerifiedByRole, hodVerifiedBy")
      .single();

    if (error) throw error;

    return NextResponse.json({ record: updated });
  } catch (error) {
    console.error("Error approving attendance:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
