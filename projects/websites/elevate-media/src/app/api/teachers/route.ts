import { NextResponse } from "next/server";
import bcrypt from "bcryptjs";
import { createClient } from "@supabase/supabase-js";
import { auth } from "@/lib/auth";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}

function now() {
  return new Date().toISOString();
}

export async function GET(req: Request) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const { searchParams } = new URL(req.url);
    const search = searchParams.get("search") || "";

    let query = supabase
      .from("teachers")
      .select("*, users!teachers_userId_fkey(email, name), departments!teachers_departmentId_fkey(name, code)")
      .order("firstName", { ascending: true });

    if (search) {
      const or = `firstName.ilike.%${search}%,lastName.ilike.%${search}%,employeeId.ilike.%${search}%`;
      query = query.or(or);
    }

    const { data: teachers, error } = await query;
    if (error) throw new Error(error.message);

    const mapped = (teachers || []).map((t: any) => {
      const { users, departments, ...rest } = t;
      return { ...rest, user: users, department: departments };
    });

    return NextResponse.json({ teachers: mapped });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const body = await req.json();
    const { firstName, lastName, email, password, phone, departmentId, position } = body;

    if (!firstName || !lastName || !email || !password) {
      return NextResponse.json({ error: "Missing required fields: firstName, lastName, email, password" }, { status: 400 });
    }

    const { data: existing } = await supabase.from("users").select("id").eq("email", email).single();
    if (existing) {
      return NextResponse.json({ error: "Email already registered" }, { status: 409 });
    }

    const passwordHash = await bcrypt.hash(password, 12);

    const year = new Date().getFullYear();
    const randomNum = Math.floor(100 + Math.random() * 900);
    const employeeId = `T${year}${randomNum}`;

    const { data: user, error: userErr } = await supabase.from("users").insert({
      id: genId(),
      email,
      name: `${firstName} ${lastName}`,
      passwordHash,
      role: "TEACHER",
      createdAt: now(),
      updatedAt: now(),
    }).select("id").single();

    if (userErr) throw new Error(`User insert: ${userErr.message}`);

    const { error: teacherErr } = await supabase.from("teachers").insert({
      id: genId(),
      userId: user!.id,
      employeeId,
      firstName,
      lastName,
      phone: phone || null,
      departmentId: departmentId || null,
      position: position || null,
    });

    if (teacherErr) throw new Error(`Teacher insert: ${teacherErr.message}`);

    return NextResponse.json({
      message: "Application successful",
      employeeId,
    }, { status: 201 });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error("Teacher registration error:", error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
