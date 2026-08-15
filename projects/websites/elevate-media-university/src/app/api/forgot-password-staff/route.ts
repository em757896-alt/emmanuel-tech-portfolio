import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import { supabase as anonSupabase } from "@/lib/supabase";

const SITE = process.env.NEXTAUTH_URL || "https://elevate-media-dun.vercel.app";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

export async function POST(req: Request) {
  try {
    const { email } = await req.json();
    if (!email) {
      return NextResponse.json({ error: "Email is required" }, { status: 400 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const normalizedEmail = String(email).toLowerCase().trim();

    const { data: user } = await supabase
      .from("users")
      .select("email, role")
      .eq("email", normalizedEmail)
      .maybeSingle();

    if (user && (user.role === "TEACHER" || user.role === "ADMIN")) {
      const { error } = await anonSupabase.auth.resetPasswordForEmail(normalizedEmail, {
        redirectTo: `${SITE}/staff-reset-password`,
      });
      if (error) {
        console.error("Staff reset email error:", error.message);
      }
    }

    return NextResponse.json({
      message: "If that email belongs to a staff account (teacher or admin), a password reset link has been sent.",
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error("Staff forgot password error:", error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
