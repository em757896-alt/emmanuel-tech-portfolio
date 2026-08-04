import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";

export async function GET() {
  try {
    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data, error } = await supabase
      .from("geo_countries")
      .select("code, name, flag")
      .order("name", { ascending: true });
    if (error) throw error;
    return NextResponse.json({ countries: data || [] });
  } catch (error) {
    console.error("Error fetching countries:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
