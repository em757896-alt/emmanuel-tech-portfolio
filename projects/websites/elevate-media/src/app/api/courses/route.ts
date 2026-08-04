import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

export async function GET() {
  try {
    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: courses, error } = await supabase
      .from("courses")
      .select("*")
      .order("code", { ascending: true });

    if (error) throw new Error(error.message);
    return NextResponse.json({ courses: courses || [] });
  } catch (error) {
    console.error("Error fetching courses:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
