import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";
import geoSeed from "@/data/geoSeed.json";

function chunk<T>(arr: T[], size: number): T[][] {
  const out: T[][] = [];
  for (let i = 0; i < arr.length; i += size) {
    out.push(arr.slice(i, i + size));
  }
  return out;
}

export async function POST() {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    await supabase.from("geo_cities").delete().neq("id", 0);
    await supabase.from("geo_regions").delete().neq("id", 0);
    await supabase.from("geo_countries").delete().neq("code", "");

    for (const batch of chunk(geoSeed.countries, 1000)) {
      const { error } = await supabase.from("geo_countries").insert(batch);
      if (error) throw error;
    }
    for (const batch of chunk(geoSeed.cities, 3000)) {
      const { error } = await supabase.from("geo_cities").insert(batch);
      if (error) throw error;
    }

    return NextResponse.json({
      message: "Geo data seeded",
      countries: geoSeed.countries.length,
      cities: geoSeed.cities.length,
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error("Seed error:", error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
