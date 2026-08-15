import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";

const SITE = process.env.NEXTAUTH_URL || "https://elevate-media-dun.vercel.app";

export async function GET(req: Request) {
  const { searchParams } = new URL(req.url);
  const admNo = searchParams.get("admNo");

  const loginUrl = new URL("/student-login?verified=1", SITE);

  if (admNo) {
    try {
      const supabase = supabaseAdmin;
      if (supabase) {
        await supabase
          .from("students")
          .update({ emailVerified: true })
          .eq("studentId", admNo);
      }
    } catch {
      // Email verification is still enforced by Supabase Auth on login.
    }
  }

  return NextResponse.redirect(loginUrl);
}
