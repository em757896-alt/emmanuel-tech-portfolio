import { NextResponse } from "next/server";
import { createClient } from "@supabase/supabase-js";
import bcrypt from "bcryptjs";
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

export async function POST() {
  const results: string[] = [];
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = getSupabase();
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const ts = now();

    const { data: dept } = await supabase.from("departments").select("id").eq("code", "CS").single();
    const deptId = dept?.id || "mry6izv1wvf6kf16";
    if (!dept) results.push("CS department not found, using known id");

    const { data: course } = await supabase.from("courses").select("id").eq("id", "seed-cs102").single();
    if (!course) {
      const { error } = await supabase.from("courses").insert({
        id: "seed-cs102",
        name: "Data Structures and Algorithms",
        code: "CS102",
        description: "Stacks, queues, trees, graphs, sorting and searching algorithms with complexity analysis.",
        credits: 3,
        departmentId: deptId,
        semester: 1,
        year: 2026,
      });
      if (error) throw new Error(`CS102: ${error.message}`);
      results.push("Course CS102 created");
    } else {
      results.push("Course CS102 exists");
    }

    const lecturerEmail = "jane.smith@elevatemedia.edu";
    const { data: user } = await supabase.from("users").select("id").eq("email", lecturerEmail).single();
    let teacherId: string | undefined;
    if (!user) {
      const uid = genId();
      const { error } = await supabase.from("users").insert({
        id: uid,
        email: lecturerEmail,
        name: "Jane Smith",
        passwordHash: await bcrypt.hash("teacher456", 12),
        role: "TEACHER",
        createdAt: ts,
        updatedAt: ts,
      });
      if (error) throw new Error(`Lecturer user: ${error.message}`);
      teacherId = genId();
      const { error: te } = await supabase.from("teachers").insert({
        id: teacherId,
        userId: uid,
        employeeId: "T2026002",
        firstName: "Jane",
        lastName: "Smith",
        departmentId: deptId,
        position: "Lecturer",
        hireDate: ts,
        isHod: false,
      });
      if (te) throw new Error(`Lecturer profile: ${te.message}`);
      results.push(`Unit lecturer created: ${lecturerEmail} / teacher456 (T2026002)`);
    } else {
      const { data: t } = await supabase.from("teachers").select("id").eq("userId", user.id).single();
      teacherId = t?.id;
      results.push("Unit lecturer exists");
    }
    if (!teacherId) throw new Error("Could not resolve unit lecturer id");

    const { data: assign } = await supabase.from("course_assignments").select("id").eq("id", "seed-assign-jane").single();
    if (!assign) {
      const { error } = await supabase.from("course_assignments").insert({
        id: "seed-assign-jane",
        teacherId,
        courseId: "seed-cs102",
        semester: 1,
        year: 2026,
      });
      if (error) throw new Error(`Course assignment: ${error.message}`);
      results.push("Assigned Jane to CS102");
    } else {
      results.push("Course assignment exists");
    }

    const { data: john } = await supabase.from("students").select("id").eq("studentId", "EM20261001").single();
    if (!john) throw new Error("John Doe (EM20261001) not found; run /api/setup first");

    const { data: enroll } = await supabase.from("course_enrollments").select("id").eq("id", "seed-enroll-jd2").single();
    if (!enroll) {
      const { error } = await supabase.from("course_enrollments").insert({
        id: "seed-enroll-jd2",
        studentId: john.id,
        courseId: "seed-cs102",
        enrolledAt: ts,
      });
      if (error) throw new Error(`Enrollment: ${error.message}`);
      results.push("Enrolled John Doe in CS102");
    } else {
      results.push("Enrollment exists");
    }

    const { data: assignment } = await supabase.from("assignments").select("id").eq("id", "seed-hw1").single();
    if (!assignment) {
      const { error } = await supabase.from("assignments").insert({
        id: "seed-hw1",
        title: "Array & Linked List Implementation",
        description: "Implement a dynamic array and a singly linked list with insert, delete and search operations.",
        courseId: "seed-cs102",
        teacherId,
        dueDate: new Date(Date.now() + 14 * 86400000).toISOString(),
        totalMarks: 100,
        createdAt: ts,
      });
      if (error) throw new Error(`Assignment: ${error.message}`);
      results.push("Assignment seed-hw1 created");
    } else {
      results.push("Assignment exists");
    }

    const { data: sub } = await supabase.from("submissions").select("id").eq("assignmentId", "seed-hw1").eq("studentId", john.id).maybeSingle();
    if (!sub) {
      const { error } = await supabase.from("submissions").insert({
        id: genId(),
        assignmentId: "seed-hw1",
        studentId: john.id,
        fileUrl: null,
        fileName: null,
        status: "PENDING",
        submittedAt: ts,
      });
      if (error) throw new Error(`Submission: ${error.message}`);
      results.push("Pending submission created (John -> seed-hw1)");
    } else {
      results.push("Submission exists");
    }

    return NextResponse.json({
      status: "success",
      lecturer: { email: lecturerEmail, password: "teacher456", employeeId: "T2026002" },
      results,
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message, results }, { status: 500 });
  }
}
