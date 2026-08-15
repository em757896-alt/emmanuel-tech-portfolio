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
    const search = searchParams.get("search") || "";
    const page = parseInt(searchParams.get("page") || "1");
    const limit = parseInt(searchParams.get("limit") || "20");
    const skip = (page - 1) * limit;

    let or = "";
    if (search) {
      const conds = [
        `firstName.ilike.%${search}%`,
        `lastName.ilike.%${search}%`,
        `studentId.ilike.%${search}%`,
      ];
      const { data: userRows } = await supabase
        .from("users")
        .select("id")
        .ilike("email", `%${search}%`);
      if (userRows && userRows.length > 0) {
        conds.push(`userId.in.(${userRows.map((u: { id: string }) => u.id).join(",")})`);
      }
      or = conds.join(",");
    }

    let query = supabase
      .from("students")
      .select("id, studentId, firstName, lastName, phone, status, enrollmentDate, departmentId, departmentName, courseName, courseCode, country, region, city, modeOfLearning, users(id, email, avatar), departments(id, name, code)")
      .order("enrollmentDate", { ascending: false })
      .range(skip, skip + limit - 1);

    let countQuery = supabase.from("students").select("*", { count: "exact", head: true });

    if (or) {
      query = query.or(or);
      countQuery = countQuery.or(or);
    }

    const [res, countRes] = await Promise.all([query, countQuery]);
    if (res.error) throw res.error;
    if (countRes.error) throw countRes.error;

    const students = (res.data || []).map((s: any) => {
      const { users: u, departments: d, ...rest } = s as { users?: unknown; departments?: unknown };
      return { ...rest, user: u, department: d };
    });

    const total = countRes.count || 0;

    return NextResponse.json({
      students,
      pagination: { page, limit, total, pages: Math.ceil(total / limit) },
    });
  } catch (error) {
    console.error("Error fetching students:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { firstName, lastName, email, phone, dateOfBirth, address, departmentId, status } = body;

    if (!firstName || !lastName || !email) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    const bcrypt = await import("bcryptjs");
    const passwordHash = await bcrypt.hash("student123", 12);

    const year = new Date().getFullYear();
    const randomNum = Math.floor(1000 + Math.random() * 9000);
    const studentId = `EM${year}${randomNum}`;
    const ts = now();

    const { data: user, error: userErr } = await supabase
      .from("users")
      .insert({
        id: genId(),
        email,
        name: `${firstName} ${lastName}`,
        passwordHash,
        role: "STUDENT",
        createdAt: ts,
        updatedAt: ts,
      })
      .select("id")
      .single();
    if (userErr) throw userErr;

    const { data: student, error: studentErr } = await supabase
      .from("students")
      .insert({
        id: genId(),
        userId: user.id,
        studentId,
        firstName,
        lastName,
        phone: phone || null,
        dateOfBirth: dateOfBirth ? new Date(dateOfBirth).toISOString() : null,
        address: address || null,
        departmentId: departmentId || null,
        status: status || "ACTIVE",
        enrollmentDate: ts,
      })
      .select("id, studentId, firstName, lastName, status, departmentId, users(id, email, avatar), departments(id, name, code)")
      .single();
    if (studentErr) throw studentErr;

    const { users, departments, ...rest } = student as { users?: unknown; departments?: unknown };

    return NextResponse.json({ student: { ...rest, user: users, department: departments } }, { status: 201 });
  } catch (error) {
    console.error("Error creating student:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
