import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase";
import { auth } from "@/lib/auth";
import { resolveStudentContext, resolveTeacherFaculty } from "@/lib/institution";
import QRCode from "qrcode";

function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}

function now() {
  return new Date().toISOString();
}

function toDateStr(d: Date) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${y}-${m}-${day}`;
}

function addDays(d: Date, n: number) {
  const r = new Date(d);
  r.setDate(r.getDate() + n);
  return r;
}

function startOfDay(d: Date) {
  const r = new Date(d);
  r.setHours(0, 0, 0, 0);
  return r;
}

function countWeekdays(start: Date, end: Date) {
  let count = 0;
  const cur = startOfDay(start);
  const e = startOfDay(end);
  while (cur <= e) {
    const d = cur.getDay();
    if (d !== 0 && d !== 6) count++;
    cur.setDate(cur.getDate() + 1);
  }
  return count;
}

function recordApprovalStatus(r: any) {
  if (r.type === "CORRECTION") {
    return r.lecturerApproved && r.adminApproved
      ? "APPROVED"
      : r.lecturerApproved || r.adminApproved
        ? "PARTIALLY_APPROVED"
        : "PENDING";
  }
  return r.lecturerApproved && r.hodApproved
    ? "APPROVED"
    : r.lecturerApproved || r.hodApproved
      ? "PARTIALLY_APPROVED"
      : "PENDING";
}

export async function GET(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const { searchParams } = new URL(req.url);
    const courseId = searchParams.get("courseId");
    const sessionId = searchParams.get("sessionId");
    const action = searchParams.get("action");

    if (sessionId && action === "records") {
      const { data, error } = await supabase
        .from("attendance_records")
        .select("id, status, checkedInAt, students(id, firstName, lastName, studentId)")
        .eq("sessionId", sessionId)
        .order("checkedInAt", { ascending: true });
      if (error) throw error;

      const records = (data || []).map((r: any) => {
        const { students: st, ...rest } = r as { students?: unknown };
        return { ...rest, student: st };
      });

      return NextResponse.json({ records });
    }

    if (courseId) {
      const { data, error } = await supabase
        .from("attendance_sessions")
        .select("id, date, qrCode, qrExpiry, courses(name, code)")
        .eq("courseId", courseId)
        .order("date", { ascending: false });
      if (error) throw error;

      const { data: records } = await supabase.from("attendance_records").select("sessionId");
      const countMap: Record<string, number> = {};
      (records || []).forEach((r: { sessionId: string }) => {
        countMap[r.sessionId] = (countMap[r.sessionId] || 0) + 1;
      });

      const sessions = (data || []).map((s: any) => {
        const { courses: c, ...rest } = s as { courses?: unknown };
        return { ...rest, course: c, _count: { records: countMap[s.id as string] || 0 } };
      });

      return NextResponse.json({ sessions });
    }

    const userRole = (session.user as { role: string }).role;

    if (action === "approvals") {
      if (userRole !== "TEACHER" && userRole !== "ADMIN") {
        return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
      }

      let courseIds: string[] = [];
      let deptScope = "";
      let teacher: { id: string } | null = null;

      if (userRole === "ADMIN") {
        const { data: allCourses } = await supabase.from("courses").select("id");
        courseIds = (allCourses || []).map((c: { id: string }) => c.id);
      } else {
        const { data: teacherRow } = await supabase
          .from("teachers")
          .select("id, isHod, departmentId")
          .eq("userId", session.user!.id)
          .single();
        if (!teacherRow) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });
        teacher = teacherRow;

        const fac = await resolveTeacherFaculty(supabase, teacherRow.departmentId);
        const isFacultyHod = fac.hodTeacherId === teacherRow.id;

        const { data: assignments } = await supabase
          .from("course_assignments")
          .select("courseId")
          .eq("teacherId", teacherRow.id);
        courseIds = (assignments || []).map((a: { courseId: string }) => a.courseId);

        if ((teacherRow.isHod || isFacultyHod) && teacherRow.departmentId) {
          deptScope = teacherRow.departmentId;
          let deptIds = [teacherRow.departmentId];
          if (isFacultyHod && fac.facultyId) {
            const { data: depts } = await supabase.from("departments").select("id").eq("facultyId", fac.facultyId);
            deptIds = (depts || []).map((d: { id: string }) => d.id);
          }
          const { data: deptCourses } = await supabase
            .from("courses")
            .select("id")
            .in("departmentId", deptIds);
          const deptCourseIds = (deptCourses || []).map((c: { id: string }) => c.id);
          courseIds = [...new Set([...courseIds, ...deptCourseIds])];
        }
      }

      if (!courseIds.length) return NextResponse.json({ records: [] });

      const { data: raw, error } = await supabase
        .from("attendance_records")
        .select("id, status, checkedInAt, recordDate, type, lecturerApproved, hodApproved, adminApproved, courseId, studentId, students(id, firstName, lastName, studentId, courseCode)")
        .in("courseId", courseIds)
        .order("recordDate", { ascending: false })
        .limit(500);

      if (error) throw error;

      const records = await Promise.all((raw || [])
        .filter((r: any) => recordApprovalStatus(r) !== "APPROVED")
        .map(async (r: any) => {
          const { students: st, ...rest } = r as { students?: unknown };
          let canApproveAs = { lecturer: userRole === "ADMIN", hod: userRole === "ADMIN", admin: userRole === "ADMIN" };
          if (userRole === "TEACHER") {
            const ctx = await resolveStudentContext(supabase, r.students);
            canApproveAs = {
              lecturer: ctx.unitLecturerId !== null && ctx.unitLecturerId === teacher!.id,
              hod: (ctx.hodId !== null && ctx.hodId === teacher!.id) || (ctx.facultyHodId !== null && ctx.facultyHodId === teacher!.id),
              admin: false,
            };
          }
          return { ...rest, student: st, approvalStatus: recordApprovalStatus(r), canApproveAs };
        }));

      return NextResponse.json({ records, deptScope });
    }

    if (userRole === "STUDENT") {
      const { data: student } = await supabase.from("students").select("*").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const today = new Date();
      const dayOfWeek = today.getDay(); // 0 Sun .. 6 Sat
      const monday = startOfDay(addDays(today, -((dayOfWeek + 6) % 7)));

      const weekParam = searchParams.get("week");
      const weekStart = weekParam && !isNaN(new Date(weekParam).getTime())
        ? startOfDay(new Date(weekParam))
        : monday;
      const todayStr = toDateStr(today);
      const weekStartStr = toDateStr(weekStart);

      const { data: records, error } = await supabase
        .from("attendance_records")
        .select("id, status, checkedInAt, recordDate, type, lecturerApproved, hodApproved, adminApproved, courseId")
        .eq("studentId", student.id)
        .gte("recordDate", weekStartStr)
        .lt("recordDate", toDateStr(addDays(weekStart, 7)))
        .order("recordDate", { ascending: true });

      if (error) throw error;

      const byDate: Record<string, any> = {};
      (records || []).forEach((r: any) => {
        if (r.recordDate) byDate[r.recordDate] = r;
      });

      const WEEKDAY_LABELS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
      const days = [];

      for (let i = 0; i < 7; i++) {
        const d = addDays(weekStart, i);
        const dateStr = toDateStr(d);
        const isWeekend = d.getDay() === 0 || d.getDay() === 6;
        const isToday = dateStr === todayStr;
        const isFuture = d.getTime() > startOfDay(today).getTime();
        const rec = byDate[dateStr] || null;

        let status: string;
        if (isWeekend) status = "OFF";
        else if (isFuture) status = "UPCOMING";
        else if (rec) status = recordApprovalStatus(rec);
        else if (isToday) status = "AVAILABLE";
        else status = "MISSED";

        days.push({
          date: dateStr,
          label: WEEKDAY_LABELS[i],
          weekday: (i + 1) % 7,
          weekend: isWeekend,
          isToday,
          isFuture,
          status,
          record: rec
            ? {
                id: rec.id,
                type: rec.type,
                checkedInAt: rec.checkedInAt,
                lecturerApproved: rec.lecturerApproved,
                hodApproved: rec.hodApproved,
                adminApproved: rec.adminApproved,
              }
            : null,
        });
      }

      const { data: allRecords } = await supabase
        .from("attendance_records")
        .select("recordDate, type, lecturerApproved, hodApproved, adminApproved")
        .eq("studentId", student.id);

      const approved = (allRecords || []).filter(
        (r: any) => recordApprovalStatus(r) === "APPROVED"
      ).length;
      const pending = (allRecords || []).filter(
        (r: any) => recordApprovalStatus(r) !== "APPROVED"
      ).length;

      const termStart = student.enrollmentDate ? new Date(student.enrollmentDate) : new Date(today.getFullYear(), 0, 1);
      const expectedDays = countWeekdays(termStart, today);
      const rate = expectedDays > 0 ? Math.round((approved / expectedDays) * 100) : 0;

      return NextResponse.json({
        weekStart: weekStartStr,
        today: todayStr,
        days,
        stats: { approved, pending, expectedDays, rate },
      });
    }

    return NextResponse.json({ sessions: [] });
  } catch (error) {
    console.error("Error fetching attendance:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const supabase = supabaseAdmin;
    if (!supabase) return NextResponse.json({ error: "Database not configured" }, { status: 500 });

    const body = await req.json();
    const { courseId, action, qrData } = body;

    const userRole = (session.user as { role: string }).role;

    if (action === "signin" || action === "unsign") {
      if (userRole !== "STUDENT") {
        return NextResponse.json({ error: "Only students can sign attendance" }, { status: 403 });
      }

      const { data: student } = await supabase.from("students").select("*").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const today = new Date();
      const weekday = today.getDay();
      const dateStr = toDateStr(today);

      if (action === "unsign") {
        const { error: delErr } = await supabase
          .from("attendance_records")
          .delete()
          .eq("studentId", student.id)
          .eq("recordDate", dateStr)
          .eq("type", "DAILY");
        if (delErr) throw delErr;
        return NextResponse.json({ message: "Today's attendance sign removed." });
      }

      if (weekday === 0 || weekday === 6) {
        return NextResponse.json({ error: "It's the weekend - attendance is not required today." }, { status: 400 });
      }

      const { data: existing } = await supabase
        .from("attendance_records")
        .select("id")
        .eq("studentId", student.id)
        .eq("recordDate", dateStr)
        .eq("type", "DAILY")
        .maybeSingle();

      if (existing) {
        return NextResponse.json({ error: "Attendance already signed for today." }, { status: 409 });
      }

      const ctx = await resolveStudentContext(supabase, student);

      const { data: record, error } = await supabase
        .from("attendance_records")
        .insert({
          id: genId(),
          studentId: student.id,
          courseId: ctx.courseId,
          status: "PRESENT",
          checkedInAt: now(),
          recordDate: dateStr,
          type: "DAILY",
          lecturerApproved: false,
          hodApproved: false,
          adminApproved: false,
        })
        .select()
        .single();

      if (error) throw error;

      return NextResponse.json({
        record,
        message: "Attendance signed for today. Awaiting the unit lecturer and HOD approval.",
      }, { status: 201 });
    }

    if (action === "correction") {
      if (userRole !== "STUDENT") {
        return NextResponse.json({ error: "Only students can request attendance corrections" }, { status: 403 });
      }

      const { date } = body;
      if (!date) return NextResponse.json({ error: "Date required" }, { status: 400 });

      const target = new Date(date);
      if (isNaN(target.getTime())) return NextResponse.json({ error: "Invalid date" }, { status: 400 });

      const targetStr = toDateStr(target);
      const todayStr = toDateStr(new Date());

      if (targetStr >= todayStr) {
        return NextResponse.json({ error: "Corrections can only be requested for past days." }, { status: 400 });
      }
      const targetWeekday = target.getDay();
      if (targetWeekday === 0 || targetWeekday === 6) {
        return NextResponse.json({ error: "Weekends are off - no attendance required." }, { status: 400 });
      }

      const { data: student } = await supabase.from("students").select("*").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const { data: existing } = await supabase
        .from("attendance_records")
        .select("id")
        .eq("studentId", student.id)
        .eq("recordDate", targetStr)
        .maybeSingle();

      if (existing) {
        return NextResponse.json({ error: "Attendance already recorded for that day." }, { status: 409 });
      }

      const ctx = await resolveStudentContext(supabase, student);

      const { data: record, error } = await supabase
        .from("attendance_records")
        .insert({
          id: genId(),
          studentId: student.id,
          courseId: ctx.courseId,
          status: "PRESENT",
          checkedInAt: now(),
          recordDate: targetStr,
          type: "CORRECTION",
          lecturerApproved: false,
          hodApproved: false,
          adminApproved: false,
        })
        .select()
        .single();

      if (error) throw error;

      return NextResponse.json({
        record,
        message: "Correction submitted. Awaiting the unit lecturer and Admin approval.",
      }, { status: 201 });
    }

    if (action === "checkin" && qrData) {
      if (userRole !== "STUDENT") {
        return NextResponse.json({ error: "Only students can check in" }, { status: 403 });
      }

      const { data: student } = await supabase.from("students").select("id").eq("userId", session.user!.id).single();
      if (!student) return NextResponse.json({ error: "Student not found" }, { status: 404 });

      const { data: attendanceSession } = await supabase
        .from("attendance_sessions")
        .select("id")
        .eq("id", qrData)
        .gt("qrExpiry", now())
        .maybeSingle();

      if (!attendanceSession) {
        return NextResponse.json({ error: "Invalid or expired QR code" }, { status: 400 });
      }

      const { data: existing } = await supabase
        .from("attendance_records")
        .select("id")
        .eq("sessionId", attendanceSession.id)
        .eq("studentId", student.id)
        .maybeSingle();

      if (existing) {
        return NextResponse.json({ error: "Already checked in" }, { status: 409 });
      }

      const { data: record, error } = await supabase
        .from("attendance_records")
        .insert({
          id: genId(),
          sessionId: attendanceSession.id,
          studentId: student.id,
          status: "PRESENT",
          checkedInAt: now(),
        })
        .select()
        .single();

      if (error) throw error;
      return NextResponse.json({ record }, { status: 201 });
    }

    if (userRole !== "TEACHER" && userRole !== "ADMIN") {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    if (!courseId) {
      return NextResponse.json({ error: "Course ID required" }, { status: 400 });
    }

    const teacher = await supabase.from("teachers").select("id").eq("userId", session.user!.id).single();
    if (!teacher.data) return NextResponse.json({ error: "Teacher not found" }, { status: 404 });

    const expiryMinutes = body.expiryMinutes || 15;
    const qrExpiry = new Date(Date.now() + expiryMinutes * 60 * 1000).toISOString();

    const { data: attendanceSession, error: createError } = await supabase
      .from("attendance_sessions")
      .insert({
        id: genId(),
        courseId,
        teacherId: teacher.data.id,
        date: now(),
        qrExpiry,
        qrCode: "",
      })
      .select()
      .single();

    if (createError) throw createError;

    const qrCodeDataUrl = await QRCode.toDataURL(attendanceSession.id);

    const { data: updated, error: updateError } = await supabase
      .from("attendance_sessions")
      .update({ qrCode: qrCodeDataUrl })
      .eq("id", attendanceSession.id)
      .select()
      .single();

    if (updateError) throw updateError;

    return NextResponse.json({ session: updated }, { status: 201 });
  } catch (error) {
    console.error("Error with attendance:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
