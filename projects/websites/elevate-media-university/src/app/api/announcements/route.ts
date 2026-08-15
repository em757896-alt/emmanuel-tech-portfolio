import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}

function now() {
  return new Date().toISOString();
}

export async function GET(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { searchParams } = new URL(req.url);
    const courseId = searchParams.get("courseId");
    const target = searchParams.get("target");

    let query = supabase
      .from("announcements")
      .select("id, title, content, target, createdAt, users(id, name, role), courses(id, name, code)")
      .order("createdAt", { ascending: false });

    if (courseId) query = query.eq("courseId", courseId);
    if (target) query = query.eq("target", target);

    const { data, error } = await query;
    if (error) throw error;

    const announcements = (data || []).map((a: any) => {
      const { users: u, courses: c, ...rest } = a as { users?: unknown; courses?: unknown };
      return { ...rest, author: u, course: c };
    });

    return NextResponse.json({ announcements });
  } catch (error) {
    console.error("Error fetching announcements:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const userRole = (session.user as { role: string }).role;
    if (userRole !== "TEACHER" && userRole !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { title, content, courseId, target } = body;

    if (!title || !content) {
      return NextResponse.json({ error: "Title and content are required" }, { status: 400 });
    }

    const { data, error } = await supabase
      .from("announcements")
      .insert({
        id: genId(),
        title,
        content,
        authorId: session.user!.id,
        courseId: courseId || null,
        target: target || "ALL",
        createdAt: now(),
      })
      .select("id, title, content, target, createdAt, users(id, name, role), courses(id, name, code)")
      .single();

    if (error) throw error;

    const { users: u, courses: c, ...rest } = data as { users?: unknown; courses?: unknown };

    return NextResponse.json({ announcement: { ...rest, author: u, course: c } }, { status: 201 });
  } catch (error) {
    console.error("Error creating announcement:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
