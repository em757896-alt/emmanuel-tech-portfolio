import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import { auth } from "@/lib/auth";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

export async function PATCH(req: Request, { params }: { params: Promise<{ id: string }> }) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const { id } = await params;
    const body = await req.json();
    const isHod = body.isHod;

    if (typeof isHod !== "boolean") {
      return NextResponse.json({ error: "isHod (boolean) required" }, { status: 400 });
    }

    const { data: teacher, error } = await supabase
      .from("teachers")
      .update({ isHod })
      .eq("id", id)
      .select("id, firstName, lastName, departmentId, isHod")
      .single();

    if (error) throw new Error(error.message);

    return NextResponse.json({ teacher });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
