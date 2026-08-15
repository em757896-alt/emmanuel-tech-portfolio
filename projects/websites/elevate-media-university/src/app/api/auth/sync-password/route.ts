import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import bcrypt from "bcryptjs";
import { supabase as anonSupabase } from "@/lib/supabase";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

// After a teacher/admin resets their password through Supabase Auth, this keeps
// the local users.passwordHash in sync. The new password is verified against
// Supabase Auth first so that only the account holder can update the hash.
export async function POST(req: Request) {
  try {
    const { email, password } = await req.json();
    if (!email || !password || String(password).length < 6) {
      return NextResponse.json({ error: "Email and a valid password are required" }, { status: 400 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const normalizedEmail = String(email).toLowerCase().trim();

    const { error: signInErr } = await anonSupabase.auth.signInWithPassword({
      email: normalizedEmail,
      password: String(password),
    });
    if (signInErr) {
      return NextResponse.json({ error: "Reset could not be verified" }, { status: 403 });
    }

    const { data: user } = await supabase
      .from("users")
      .select("id, role")
      .eq("email", normalizedEmail)
      .maybeSingle();
    if (!user || (user.role !== "TEACHER" && user.role !== "ADMIN")) {
      return NextResponse.json({ error: "No staff account found" }, { status: 404 });
    }

    const hash = await bcrypt.hash(String(password), 12);
    const { error: updateErr } = await supabase
      .from("users")
      .update({ passwordHash: hash, updatedAt: new Date().toISOString() })
      .eq("email", normalizedEmail);
    if (updateErr) throw updateErr;

    return NextResponse.json({ message: "Password updated" });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error("Sync password error:", error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
