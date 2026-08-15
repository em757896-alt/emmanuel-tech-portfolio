import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const { email, password } = await req.json();
    if (!email || !password) {
      return NextResponse.json({ error: "Email and password are required" }, { status: 400 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const normalizedEmail = String(email).toLowerCase().trim();

    const { data: user } = await supabase.from("users").select("id, role").eq("email", normalizedEmail).single();
    if (!user || user.role !== "STUDENT") {
      return NextResponse.json({ error: "No student account found with that email" }, { status: 404 });
    }

    const { data: student } = await supabase.from("students").select("studentId").eq("userId", user.id).single();
    if (!student) {
      return NextResponse.json({ error: "Student record not found" }, { status: 404 });
    }

    if (!supabaseAdmin) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: created, error: createErr } = await supabaseAdmin.auth.admin.createUser({
      email: normalizedEmail,
      password: String(password),
      email_confirm: true,
      user_metadata: {
        adm_no: student.studentId,
        migrated: true,
      },
    });

    if (createErr) {
      return NextResponse.json({ error: createErr.message }, { status: 400 });
    }

    const { error: updateErr } = await supabase
      .from("students")
      .update({ emailVerified: true })
      .eq("userId", user.id);
    if (updateErr) {
      console.error("Migrate flag update error:", updateErr.message);
    }

    return NextResponse.json({
      message: "Student migrated to email-verified auth",
      email: created?.user?.email,
      admNo: student.studentId,
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error("Migrate error:", error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
