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

export async function POST() {
  const results: string[] = [];
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = getSupabase();
    if (!supabase || !supabaseAdmin) {
      return NextResponse.json({ error: "Database not configured" }, { status: 500 });
    }

    const { data: staffUsers, error: listErr } = await supabase
      .from("users")
      .select("id, email, role, passwordHash")
      .in("role", ["TEACHER", "ADMIN"]);
    if (listErr) throw listErr;

    for (const user of (staffUsers || []) as { id: string; email: string; role: string; passwordHash: string }[]) {
      const tempPassword = "Reset-" + Math.random().toString(36).slice(2, 12);
      const { data: created, error: createErr } = await supabaseAdmin.auth.admin.createUser({
        email: user.email,
        password: tempPassword,
        email_confirm: true,
        user_metadata: { role: user.role, migrated_staff: true },
      });
      if (createErr) {
        const msg = (createErr.message || "").toLowerCase();
        if (msg.includes("already registered") || msg.includes("already been registered")) {
          results.push(`SKIP ${user.role} ${user.email} (already migrated)`);
        } else {
          results.push(`FAIL ${user.role} ${user.email}: ${createErr.message}`);
        }
        continue;
      }
      results.push(`OK ${user.role} ${user.email} (auth identity created for email reset)`);
      void created;
    }

    return NextResponse.json({ results });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message, results }, { status: 500 });
  }
}
