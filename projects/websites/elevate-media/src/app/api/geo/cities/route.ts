import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";

const BATCH = 1000;

export async function GET(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const country = searchParams.get("country");
    if (!country) return NextResponse.json({ cities: [] });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const code = country.toLowerCase();
    let all: { id: number; name: string }[] = [];
    let offset = 0;

    while (true) {
      const { data, error } = await supabase
        .from("geo_cities")
        .select("id, name")
        .eq("country_code", code)
        .order("population", { ascending: false })
        .range(offset, offset + BATCH - 1);
      if (error) throw error;
      all = all.concat(data || []);
      if (!data || data.length < BATCH) break;
      offset += BATCH;
    }

    return NextResponse.json({ cities: all });
  } catch (error) {
    console.error("Error fetching cities:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
