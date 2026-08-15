import { NextResponse } from "next/server";
import bcrypt from "bcryptjs";
import { createClient } from "@supabase/supabase-js";
import { supabase, supabaseAdmin } from "@/lib/supabase";

const SITE = process.env.NEXTAUTH_URL || "https://elevate-media-dun.vercel.app";

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

async function generateUniqueStudentId(db: any) {
  const year = new Date().getFullYear();
  for (let i = 0; i < 200; i++) {
    const randomNum = Math.floor(1000 + Math.random() * 9000);
    const studentId = `EM${year}${randomNum}`;
    const { data } = await db.from("students").select("studentId").eq("studentId", studentId).maybeSingle();
    if (!data) return studentId;
  }
  throw new Error("Could not generate a unique admission number");
}

export async function POST(req: Request) {
  try {
    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Supabase not configured" }, { status: 500 });

    const body = await req.json();
    const {
      firstName, lastName, email, password, phone, departmentId,
      departmentName, courseName, courseCode, country, city, modeOfLearning, dateOfBirth,
    } = body;

    if (!firstName || !lastName || !email || !password) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    const { data: existing } = await supabase.from("users").select("id").eq("email", email).single();
    if (existing) {
      return NextResponse.json({ error: "Email already registered" }, { status: 409 });
    }

    const admNo = await generateUniqueStudentId(supabase);

    // 1) Create the Supabase Auth user - this triggers the activation email
    const { data: authData, error: signUpErr } = await supabase.auth.signUp({
      email,
      password,
      options: {
        data: {
          adm_no: admNo,
          first_name: firstName,
          last_name: lastName,
          department: departmentName || null,
          course: courseName || null,
          course_code: courseCode || null,
          mode: modeOfLearning || null,
          country: country || null,
          city: city || null,
        },
        emailRedirectTo: `${SITE}/api/verify-email?admNo=${admNo}`,
      },
    });

    if (signUpErr) {
      return NextResponse.json({ error: signUpErr.message }, { status: 400 });
    }
    if (!authData?.user) {
      return NextResponse.json({ error: "Account creation failed" }, { status: 500 });
    }

    // If Supabase returns a session, "Confirm email" is disabled in the project and
    // the email was auto-confirmed (no verification email was sent).
    const autoConfirmed = !!authData.session;
    const passwordHash = await bcrypt.hash(Math.random().toString(36), 12);

    const { data: user, error: userErr } = await supabase.from("users").insert({
      id: genId(),
      email,
      name: `${firstName} ${lastName}`,
      passwordHash,
      role: "STUDENT",
      createdAt: now(),
      updatedAt: now(),
    }).select("id").single();

    if (userErr) {
      try {
        await supabaseAdmin?.auth.admin.deleteUser(authData.user.id);
      } catch { /* ignore cleanup failure */ }
      throw new Error(`User insert: ${userErr.message}`);
    }

    const payload = {
      id: genId(),
      userId: user!.id,
      studentId: admNo,
      firstName,
      lastName,
      phone: phone || null,
      departmentId: departmentId || null,
      departmentName: departmentName || null,
      courseName: courseName || null,
      courseCode: courseCode || null,
      country: country || null,
      city: city || null,
      modeOfLearning: modeOfLearning || null,
      dateOfBirth: dateOfBirth || null,
      emailVerified: autoConfirmed,
    };

    const { error: studentErr } = await supabase.from("students").insert(payload);

    if (studentErr) {
      const { error: fallbackErr } = await supabase.from("students").insert({
        id: payload.id,
        userId: payload.userId,
        studentId: payload.studentId,
        firstName: payload.firstName,
        lastName: payload.lastName,
        phone: payload.phone,
        departmentId: payload.departmentId,
      });
      if (fallbackErr) {
        try {
          await supabaseAdmin?.auth.admin.deleteUser(authData.user.id);
        } catch { /* ignore cleanup failure */ }
        throw new Error(`Student insert: ${studentErr.message} / ${fallbackErr.message}`);
      }
    }

    return NextResponse.json({
      message: "Verification email sent",
      studentId: admNo,
    }, { status: 201 });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    console.error("Registration error:", error);
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
