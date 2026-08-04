import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

export async function GET() {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data, error } = await supabase
      .from("students")
      .select("id, studentId, firstName, lastName, phone, dateOfBirth, address, status, enrollmentDate, departmentId, departmentName, courseName, courseCode, country, region, city, modeOfLearning, users(id, email, avatar), departments(id, name, code)")
      .eq("userId", session.user!.id)
      .single();

    if (error || !data) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const { users, departments, ...rest } = data as { users?: unknown; departments?: unknown };

    return NextResponse.json({ student: { ...rest, user: users, department: departments } });
  } catch (error) {
    console.error("Error:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: student } = await supabase
      .from("students")
      .select("id, userId, firstName, lastName")
      .eq("userId", session.user!.id)
      .single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const body = await req.json();
    const update: Record<string, unknown> = {};
    if (body.firstName) update.firstName = body.firstName;
    if (body.lastName) update.lastName = body.lastName;
    if (body.phone !== undefined) update.phone = body.phone || null;
    if (body.address !== undefined) update.address = body.address || null;

    const { data: updated, error } = await supabase
      .from("students")
      .update(update)
      .eq("id", student.id)
      .select("id, studentId, firstName, lastName, phone, dateOfBirth, address, status")
      .single();

    if (error) throw error;

    if (body.firstName || body.lastName) {
      const name = `${updated.firstName} ${updated.lastName}`;
      await supabase.from("users").update({ name }).eq("id", student.userId);
    }

    return NextResponse.json({ student: updated });
  } catch (error) {
    console.error("Error:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
