import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";

export async function GET(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { id } = await params;

    const { data, error } = await supabase
      .from("students")
      .select("id, studentId, firstName, lastName, phone, dateOfBirth, address, status, enrollmentDate, departmentId, users(id, email, avatar), departments(id, name, code)")
      .eq("id", id)
      .single();
    if (error || !data) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const [enrollmentsRes, gradesRes] = await Promise.all([
      supabase
        .from("course_enrollments")
        .select("id, courseId, enrolledAt, courses(id, name, code, credits)")
        .eq("studentId", id)
        .order("enrolledAt", { ascending: false }),
      supabase
        .from("grades")
        .select("id, finalGrade, gpa, semester, year, calculatedAt, courses(id, name, code, credits)")
        .eq("studentId", id)
        .order("calculatedAt", { ascending: false }),
    ]);

    const mapCourse = (r: { courses?: unknown }) => {
      const { courses, ...rest } = r as { courses?: unknown };
      return { ...rest, course: courses };
    };

    const { users, departments, ...rest } = data as { users?: unknown; departments?: unknown };

    return NextResponse.json({
      student: {
        ...rest,
        user: users,
        department: departments,
        enrollments: (enrollmentsRes.data || []).map(mapCourse),
        grades: (gradesRes.data || []).map(mapCourse),
      },
    });
  } catch (error) {
    console.error("Error fetching student:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function PUT(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { id } = await params;
    const body = await req.json();
    const { firstName, lastName, phone, dateOfBirth, address, departmentId, status } = body;

    const { data: existing } = await supabase.from("students").select("userId, firstName, lastName").eq("id", id).single();
    if (!existing) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const update: Record<string, unknown> = {};
    if (firstName) update.firstName = firstName;
    if (lastName) update.lastName = lastName;
    if (phone !== undefined) update.phone = phone;
    if (dateOfBirth) update.dateOfBirth = new Date(dateOfBirth).toISOString();
    if (address !== undefined) update.address = address;
    if (departmentId !== undefined) update.departmentId = departmentId || null;
    if (status) update.status = status;

    const { data: student, error } = await supabase
      .from("students")
      .update(update)
      .eq("id", id)
      .select("id, studentId, firstName, lastName, phone, dateOfBirth, address, status, departmentId, users(id, email, avatar), departments(id, name, code)")
      .single();
    if (error) throw error;

    if (firstName || lastName) {
      const name = `${student.firstName} ${student.lastName}`;
      await supabase.from("users").update({ name }).eq("id", existing.userId);
    }

    const { users, departments, ...rest } = student as { users?: unknown; departments?: unknown };

    return NextResponse.json({ student: { ...rest, user: users, department: departments } });
  } catch (error) {
    console.error("Error updating student:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function DELETE(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const session = await auth();
    if (!session || (session.user as { role: string }).role !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { id } = await params;

    const { data: student } = await supabase.from("students").select("userId").eq("id", id).single();
    if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

    const { error } = await supabase.from("users").delete().eq("id", student.userId);
    if (error) throw error;

    return NextResponse.json({ message: "Student deleted" });
  } catch (error) {
    console.error("Error deleting student:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
