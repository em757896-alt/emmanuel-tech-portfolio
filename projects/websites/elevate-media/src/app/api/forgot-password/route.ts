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
    const { email, admNo } = await req.json();
    if (!email || !admNo) {
      return NextResponse.json({ error: "Email and Admission Number are required" }, { status: 400 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const normalizedEmail = String(email).toLowerCase().trim();

    const { data: student } = await supabase
      .from("students")
      .select("userId, studentId, emailVerified")
      .eq("studentId", String(admNo).trim())
      .maybeSingle();

    let accountMatches = false;
    if (student) {
      const { data: user } = await supabase
        .from("users")
        .select("email, role")
        .eq("id", student.userId)
        .maybeSingle();
      if (user && user.role === "STUDENT" && user.email.toLowerCase() === normalizedEmail) {
        accountMatches = true;
      }
    }

    if (accountMatches) {
      const { error } = await anonSupabase.auth.resetPasswordForEmail(normalizedEmail, {
        redirectTo: `${SITE}/reset-password`,
      });
      if (error) {
        console.error("Reset email error:", error.message);
      }
    }

    return NextResponse.json({
      message: "If the email and admission number match an active student account, a password reset link has been sent.",
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error("Forgot password error:", error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
