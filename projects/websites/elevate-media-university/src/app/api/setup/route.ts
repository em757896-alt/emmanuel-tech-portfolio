import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import bcrypt from "bcryptjs";

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

export async function POST() {
  const results: string[] = [];
  try {
    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Missing Supabase env vars" }, { status: 500 });

    const ts = now();

    const adminEmail = "admin@elevatemedia.edu";
    const { data: adminCheck } = await supabase.from("users").select("id").eq("email", adminEmail).single();
    if (adminCheck) {
      results.push(`Admin already exists: ${adminEmail} / admin123`);
    } else {
      const { error } = await supabase.from("users").insert({
        id: genId(), email: adminEmail, name: "System Administrator",
        passwordHash: await bcrypt.hash("admin123", 12), role: "ADMIN",
        createdAt: ts, updatedAt: ts,
      });
      if (error) throw new Error(`Admin: ${error.message}`);
      results.push(`Admin: ${adminEmail} / admin123`);
    }

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

    const teacherEmail = "sarah.jones@elevatemedia.edu";
    const { data: tCheck } = await supabase.from("users").select("id").eq("email", teacherEmail).single();
    if (!tCheck) {
      const uid = genId();
      const { error } = await supabase.from("users").insert({
        id: uid, email: teacherEmail, name: "Sarah Jones",
        passwordHash: await bcrypt.hash("teacher123", 12), role: "TEACHER",
        createdAt: ts, updatedAt: ts,
      });
      if (error) throw new Error(`Teacher user: ${error.message}`);
      const { error: pe } = await supabase.from("teachers").insert({
        id: genId(), userId: uid, employeeId: "T2026001",
        firstName: "Sarah", lastName: "Jones", departmentId: deptId, position: "Senior Lecturer",
        hireDate: ts,
      });
      if (pe) throw new Error(`Teacher profile: ${pe.message}`);
      results.push(`Teacher: ${teacherEmail} / teacher123 (T2026001)`);
    } else {
      results.push("Teacher already exists");
    }

    const studentEmail = "john.doe@student.elevatemedia.edu";
    const { data: sCheck } = await supabase.from("users").select("id").eq("email", studentEmail).single();
    if (!sCheck) {
      const uid = genId();
      const { error } = await supabase.from("users").insert({
        id: uid, email: studentEmail, name: "John Doe",
        passwordHash: await bcrypt.hash("student123", 12), role: "STUDENT",
        createdAt: ts, updatedAt: ts,
      });
      if (error) throw new Error(`Student user: ${error.message}`);
      const { error: se } = await supabase.from("students").insert({
        id: genId(), userId: uid, studentId: "EM20261001",
        firstName: "John", lastName: "Doe", departmentId: deptId,
        enrollmentDate: ts,
      });
      if (se) throw new Error(`Student profile: ${se.message}`);
      results.push(`Student: ${studentEmail} / student123 (EM20261001)`);
    } else {
      results.push("Student already exists");
    }

    return NextResponse.json({
      status: "success",
      accounts: {
        admin: { email: adminEmail, password: "admin123" },
        teacher: { email: teacherEmail, password: "teacher123", employeeId: "T2026001" },
        student: { email: studentEmail, password: "student123", studentId: "EM20261001" },
      },
      results,
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message, results }, { status: 500 });
  }
}
