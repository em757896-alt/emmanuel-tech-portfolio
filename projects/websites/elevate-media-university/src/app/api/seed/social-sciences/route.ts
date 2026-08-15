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

async function findOrCreateDepartment(supabase: any, name: string, code: string) {
  const { data } = await supabase.from("departments").select("id").eq("code", code).single();
  if (data) return data.id;
  const id = "seed-" + code.toLowerCase();
  const { data: byId } = await supabase.from("departments").select("id").eq("id", id).single();
  if (byId) return byId.id;
  const { error } = await supabase.from("departments").insert({
    id,
    name,
    code,
    description: `${name} (Faculty of Social Sciences)`,
  });
  if (error) throw new Error(`Department ${code}: ${error.message}`);
  return id;
}

async function findOrCreateTeacher(
  supabase: any,
  email: string,
  password: string,
  employeeId: string,
  firstName: string,
  lastName: string,
  departmentId: string,
  position: string,
  isHod: boolean,
  facultyId: string | null,
  results: string[]
) {
  const { data: user } = await supabase.from("users").select("id").eq("email", email).single();
  if (!user) {
    const uid = genId();
    const { error } = await supabase.from("users").insert({
      id: uid,
      email,
      name: `${firstName} ${lastName}`,
      passwordHash: await bcrypt.hash(password, 12),
      role: "TEACHER",
      createdAt: now(),
      updatedAt: now(),
    });
    if (error) throw new Error(`User ${email}: ${error.message}`);
    const { data: teacher, error: te } = await supabase
      .from("teachers")
      .insert({
        id: genId(),
        userId: uid,
        employeeId,
        firstName,
        lastName,
        departmentId,
        position,
        hireDate: now(),
        isHod,
        facultyId,
      })
      .select("id")
      .single();
    if (te) throw new Error(`Teacher profile ${email}: ${te.message}`);
    results.push(`${position} created: ${email} / ${password} (${employeeId})`);
    return teacher.id;
  }
  const { data: t } = await supabase.from("teachers").select("id").eq("userId", user.id).single();
  if (t) {
    const { error } = await supabase
      .from("teachers")
      .update({ isHod, departmentId, facultyId })
      .eq("id", t.id);
    if (error) throw new Error(`Teacher update ${email}: ${error.message}`);
    results.push(`${position} exists (${email})`);
    return t.id;
  }
  return undefined;
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
    const deptId = await findOrCreateDepartment(supabase, "Sociology & Political Science", "SP");
    results.push(`Department ready: Sociology & Political Science (SP) -> ${deptId}`);

    const FACULTY_ID = "seed-fac-social";
    const { data: faculty } = await supabase.from("faculties").select("id").eq("id", FACULTY_ID).single();
    if (!faculty) {
      const { error } = await supabase.from("faculties").insert({
        id: FACULTY_ID,
        name: "Faculty of Social Sciences",
        code: "SOC",
        description: "Faculty of Social Sciences - departments and courses under the social sciences.",
      });
      if (error) throw new Error(`Faculty: ${error.message}`);
      results.push("Faculty 'Faculty of Social Sciences' created");
    } else {
      results.push("Faculty 'Faculty of Social Sciences' exists");
    }

    const { data: deptFac } = await supabase.from("departments").select("facultyId").eq("id", deptId).maybeSingle();
    if (!deptFac?.facultyId) {
      const { error } = await supabase.from("departments").update({ facultyId: FACULTY_ID }).eq("id", deptId);
      if (error) throw new Error(`Department faculty link: ${error.message}`);
      results.push("Linked Sociology & Political Science to the Social Sciences faculty");
    } else {
      results.push("Department already linked to a faculty");
    }

    const { data: course } = await supabase.from("courses").select("id").eq("id", "seed-pad301").single();
    if (!course) {
      const { error } = await supabase.from("courses").insert({
        id: "seed-pad301",
        name: "Public Administration",
        code: "PAD301",
        description: "Principles and practices of public administration, public policy and governance.",
        credits: 3,
        departmentId: deptId,
        semester: 1,
        year: 2026,
      });
      if (error) throw new Error(`PAD301: ${error.message}`);
      results.push("Course PAD301 (Public Administration) created");
    } else {
      results.push("Course PAD301 exists");
    }

    const lecturerId = await findOrCreateTeacher(
      supabase,
      "patricia.mwangi@elevatemedia.edu",
      "lecturer789",
      "T2026003",
      "Patricia",
      "Mwangi",
      deptId,
      "Lecturer - Sociology & Political Science",
      false,
      FACULTY_ID,
      results
    );
    if (!lecturerId) throw new Error("Could not resolve lecturer id");

    const hodId = await findOrCreateTeacher(
      supabase,
      "daniel.otieno@elevatemedia.edu",
      "hod12345",
      "T2026004",
      "Daniel",
      "Otieno",
      deptId,
      "Head of Department - Faculty of Social Sciences",
      true,
      FACULTY_ID,
      results
    );
    if (!hodId) throw new Error("Could not resolve HOD id");

    const { data: facHod } = await supabase.from("faculties").select("hodTeacherId").eq("id", FACULTY_ID).maybeSingle();
    if (!facHod?.hodTeacherId || facHod.hodTeacherId !== hodId) {
      const { error } = await supabase.from("faculties").update({ hodTeacherId: hodId }).eq("id", FACULTY_ID);
      if (error) throw new Error(`Faculty HOD: ${error.message}`);
      results.push("Daniel (T2026004) set as Faculty HOD of Social Sciences");
    } else {
      results.push("Faculty already has Daniel as HOD");
    }

    await supabase.from("teachers").update({ facultyId: FACULTY_ID }).eq("id", hodId);

    const { data: assign } = await supabase.from("course_assignments").select("id").eq("id", "seed-assign-pm").single();
    if (!assign) {
      const { error } = await supabase.from("course_assignments").insert({
        id: "seed-assign-pm",
        teacherId: lecturerId,
        courseId: "seed-pad301",
        semester: 1,
        year: 2026,
      });
      if (error) throw new Error(`Course assignment: ${error.message}`);
      results.push("Assigned Patricia (T2026003) to PAD301");
    } else {
      results.push("Course assignment exists");
    }

    const { data: john } = await supabase.from("students").select("id").eq("studentId", "EM20261001").single();
    if (john) {
      const { data: enroll } = await supabase.from("course_enrollments").select("id").eq("id", "seed-enroll-jd3").single();
      if (!enroll) {
        const { error } = await supabase.from("course_enrollments").insert({
          id: "seed-enroll-jd3",
          studentId: john.id,
          courseId: "seed-pad301",
          enrolledAt: ts,
        });
        if (error) throw new Error(`Enrollment: ${error.message}`);
        results.push("Enrolled John Doe (EM20261001) in PAD301");
      } else {
        results.push("Enrollment exists");
      }

      const { data: assignment } = await supabase.from("assignments").select("id").eq("id", "seed-hw-pad").single();
      if (!assignment) {
        const { error } = await supabase.from("assignments").insert({
          id: "seed-hw-pad",
          title: "Public Policy Analysis Paper",
          description: "Analyse a public policy of your choice covering formulation, implementation and evaluation.",
          courseId: "seed-pad301",
          teacherId: lecturerId,
          dueDate: new Date(Date.now() + 14 * 86400000).toISOString(),
          totalMarks: 100,
          createdAt: ts,
        });
        if (error) throw new Error(`Assignment: ${error.message}`);
        results.push("Assignment seed-hw-pad created");
      } else {
        results.push("Assignment exists");
      }

      const { data: sub } = await supabase.from("submissions").select("id").eq("assignmentId", "seed-hw-pad").eq("studentId", john.id).maybeSingle();
      if (!sub) {
        const { error } = await supabase.from("submissions").insert({
          id: genId(),
          assignmentId: "seed-hw-pad",
          studentId: john.id,
          fileUrl: null,
          fileName: null,
          status: "PENDING",
          submittedAt: ts,
        });
        if (error) throw new Error(`Submission: ${error.message}`);
        results.push("Pending submission created (John -> seed-hw-pad)");
      } else {
        results.push("Submission exists");
      }
    } else {
      results.push("Skipped enrollment/assignment (John Doe not found)");
    }

    return NextResponse.json({
      status: "success",
      faculty: "Social Sciences",
      department: "Sociology & Political Science",
      course: "PAD301 - Public Administration",
      accounts: {
        unitLecturer: { email: "patricia.mwangi@elevatemedia.edu", password: "lecturer789", employeeId: "T2026003", isHod: false },
        hod: { email: "daniel.otieno@elevatemedia.edu", password: "hod12345", employeeId: "T2026004", isHod: true },
      },
      results,
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return NextResponse.json({ error: message, results }, { status: 500 });
  }
}
