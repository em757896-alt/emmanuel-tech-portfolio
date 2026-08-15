import { NextResponse } from "next/server";
import { supabaseAdmin, BUCKETS } from "@/lib/supabase";
import { auth } from "@/lib/auth";

export async function POST() {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const results: string[] = [];
    for (const name of Object.values(BUCKETS)) {
      const { data: bucket } = await supabase.storage.getBucket(name);
      if (bucket) {
        results.push(`${name}: exists`);
        continue;
      }
      const { error } = await supabase.storage.createBucket(name, { public: true });
      results.push(error ? `${name}: FAILED - ${error.message}` : `${name}: created`);
    }

    return NextResponse.json({ results });
  } catch (error) {
    console.error("Storage setup error:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
