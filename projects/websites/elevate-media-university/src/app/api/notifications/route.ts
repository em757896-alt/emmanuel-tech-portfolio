import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

export async function GET() {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { data: notifications, error } = await supabase
      .from("notifications")
      .select("id, title, message, read, link, createdAt")
      .eq("userId", session.user!.id)
      .order("createdAt", { ascending: false })
      .limit(50);

    if (error) throw error;

    return NextResponse.json({ notifications: notifications || [] });
  } catch (error) {
    console.error("Error fetching notifications:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { id, readAll } = body;

    if (readAll) {
      const { error } = await supabase
        .from("notifications")
        .update({ read: true })
        .eq("userId", session.user!.id)
        .eq("read", false);
      if (error) throw error;
      return NextResponse.json({ message: "All notifications marked as read" });
    }

    if (id) {
      const { error } = await supabase.from("notifications").update({ read: true }).eq("id", id);
      if (error) throw error;
      return NextResponse.json({ message: "Notification marked as read" });
    }

    return NextResponse.json({ error: "ID or readAll required" }, { status: 400 });
  } catch (error) {
    console.error("Error updating notification:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
