import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import bcrypt from "bcryptjs";
import crypto from "crypto";

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

/** Constant-time comparison so the token cannot be brute-forced byte by byte. */
function safeEqual(a: string, b: string): boolean {
  const bufA = Buffer.from(a, "utf8");
  const bufB = Buffer.from(b, "utf8");
  if (bufA.length !== bufB.length) return false;
  return crypto.timingSafeEqual(bufA, bufB);
}

/**
 * Provisions the initial department + demo accounts.
 *
 * SECURITY: this route uses the service-role key, so it MUST NOT be publicly callable.
 * It is disabled entirely unless SETUP_TOKEN is configured, and requires the same value in
 * the `x-setup-token` header. Passwords are read from env vars and are NEVER returned in
 * the response — an unauthenticated caller could otherwise mint or disclose an admin account.
 */
export async function POST(req: Request) {
  const expected = process.env.SETUP_TOKEN;
  if (!expected) {
    return NextResponse.json({ error: "Not found" }, { status: 404 });
  }

  const provided = req.headers.get("x-setup-token") ?? "";
  if (!provided || !safeEqual(provided, expected)) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const results: string[] = [];
  const created: string[] = [];

  try {
    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Missing Supabase env vars" }, { status: 500 });

    const ts = now();

    const { data: deptCheck } = await supabase.from("departments").select("id").eq("code", "CS").single();
    let deptId = deptCheck?.id;
    if (!deptId) {
      deptId = genId();
      const { error } = await supabase.from("departments").insert({
        id: deptId, name: "Computer Science", code: "CS", description: "Department of Computer Science",
      });
      if (error) throw new Error(`Dept: ${error.message}`);
      results.push("Department CS created");
    }

    const accounts = [
      {
        email: process.env.SETUP_ADMIN_EMAIL ?? "admin@elevatemedia.edu",
        password: process.env.SETUP_ADMIN_PASSWORD,
        name: "System Administrator",
        role: "ADMIN",
        profile: null as Record<string, unknown> | null,
      },
      {
        email: process.env.SETUP_TEACHER_EMAIL ?? "sarah.jones@elevatemedia.edu",
        password: process.env.SETUP_TEACHER_PASSWORD,
        name: "Sarah Jones",
        role: "TEACHER",
        profile: { employeeId: "T2026001", firstName: "Sarah", lastName: "Jones", position: "Senior Lecturer" },
      },
      {
        email: process.env.SETUP_STUDENT_EMAIL ?? "john.doe@student.elevatemedia.edu",
        password: process.env.SETUP_STUDENT_PASSWORD,
        name: "John Doe",
        role: "STUDENT",
        profile: { studentId: "EM20261001", firstName: "John", lastName: "Doe" },
      },
    ];

    for (const account of accounts) {
      if (!account.password) {
        results.push(`${account.role}: skipped — no password configured`);
        continue;
      }

      const { data: existing } = await supabase
        .from("users")
        .select("id")
        .eq("email", account.email)
        .maybeSingle();
      if (existing) {
        results.push(`${account.role}: already exists`);
        continue;
      }

      const uid = genId();
      const { error } = await supabase.from("users").insert({
        id: uid,
        email: account.email,
        name: account.name,
        passwordHash: await bcrypt.hash(account.password, 12),
        role: account.role,
        createdAt: ts,
        updatedAt: ts,
      });
      if (error) throw new Error(`${account.role}: ${error.message}`);

      if (account.role === "TEACHER" && account.profile) {
        const { error: te } = await supabase.from("teachers").insert({
          id: genId(),
          userId: uid,
          employeeId: account.profile.employeeId as string,
          firstName: account.profile.firstName as string,
          lastName: account.profile.lastName as string,
          departmentId: deptId,
          position: account.profile.position as string,
          hireDate: ts,
        });
        if (te) throw new Error(`Teacher profile: ${te.message}`);
      }

      if (account.role === "STUDENT" && account.profile) {
        const { error: se } = await supabase.from("students").insert({
          id: genId(),
          userId: uid,
          studentId: account.profile.studentId as string,
          firstName: account.profile.firstName as string,
          lastName: account.profile.lastName as string,
          departmentId: deptId,
          enrollmentDate: ts,
        });
        if (se) throw new Error(`Student profile: ${se.message}`);
      }

      results.push(`${account.role}: created`);
      created.push(account.email);
    }

    return NextResponse.json({ status: "success", created, results });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message, results }, { status: 500 });
  }
}
