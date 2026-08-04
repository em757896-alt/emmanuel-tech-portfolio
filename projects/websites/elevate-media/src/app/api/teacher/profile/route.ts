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
      .from("teachers")
      .select("id, employeeId, firstName, lastName, phone, position, hireDate, departmentId, users(id, email, avatar), departments(id, name, code)")
      .eq("userId", session.user!.id)
      .single();

    if (error || !data) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

    const { users, departments, ...rest } = data as { users?: unknown; departments?: unknown };

    return NextResponse.json({ profile: { ...rest, user: users, department: departments } });
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

    const { data: teacher } = await supabase
      .from("teachers")
      .select("id, userId, firstName, lastName")
      .eq("userId", session.user!.id)
      .single();
    if (!teacher) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

    const body = await req.json();
    const update: Record<string, unknown> = {};
    if (body.firstName) update.firstName = body.firstName;
    if (body.lastName) update.lastName = body.lastName;
    if (body.phone !== undefined) update.phone = body.phone || null;
    if (body.position !== undefined) update.position = body.position || null;

    const { data: updated, error } = await supabase
      .from("teachers")
      .update(update)
      .eq("id", teacher.id)
      .select("id, employeeId, firstName, lastName, phone, position, hireDate, departmentId")
      .single();

    if (error) throw error;

    if (body.firstName || body.lastName) {
      const name = `${updated.firstName} ${updated.lastName}`;
      await supabase.from("users").update({ name }).eq("id", teacher.userId);
    }

    return NextResponse.json({ profile: updated });
  } catch (error) {
    console.error("Error:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
