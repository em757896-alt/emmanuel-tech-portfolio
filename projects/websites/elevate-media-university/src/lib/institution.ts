import { findCourse } from "@/lib/courseCatalog";

export interface StudentContext {
  courseId: string | null;
  courseName: string | null;
  courseCode: string | null;
  departmentId: string | null;
  departmentName: string | null;
  facultyId: string | null;
  unitLecturerId: string | null;
  unitLecturerName: string | null;
  hodId: string | null;
  hodName: string | null;
  facultyHodId: string | null;
  facultyHodName: string | null;
}

const EMPTY: StudentContext = {
  courseId: null,
  courseName: null,
  courseCode: null,
  departmentId: null,
  departmentName: null,
  facultyId: null,
  unitLecturerId: null,
  unitLecturerName: null,
  hodId: null,
  hodName: null,
  facultyHodId: null,
  facultyHodName: null,
};

// Resolves a student's course/department and the teachers who must approve
// their POE documents and attendance sign-ins.
//
// The students table stores CATALOG department ids (e.g. "cs") while the
// teachers/courses tables use DATABASE department ids, so we bridge the two
// by matching the catalog department name against the departments table.
export async function resolveStudentContext(supabase: any, student: any): Promise<StudentContext> {
  const ctx: StudentContext = {
    ...EMPTY,
    courseCode: student.courseCode ?? null,
    courseName: student.courseName ?? null,
    departmentName: student.departmentName ?? null,
  };

  // Preferred: course linked through course_enrollments
  const { data: enrollments } = await supabase
    .from("course_enrollments")
    .select("courses(id, name, code, departmentId, departments!courses_departmentId_fkey(name))")
    .eq("studentId", student.id)
    .limit(5);

  const course = enrollments?.[0]?.courses ?? null;

  if (course) {
    ctx.courseId = course.id;
    ctx.courseName = course.name;
    ctx.courseCode = course.code;
    ctx.departmentId = course.departmentId ?? null;
    ctx.departmentName = course.departments?.name ?? ctx.departmentName;
  } else if (student.courseCode) {
    // Fallback: DB course matching the student's stored course code
    const { data: dbCourse } = await supabase
      .from("courses")
      .select("id, name, code, departmentId, departments!courses_departmentId_fkey(name)")
      .eq("code", student.courseCode)
      .maybeSingle();
    if (dbCourse) {
      ctx.courseId = dbCourse.id;
      ctx.courseName = dbCourse.name;
      ctx.courseCode = dbCourse.code;
      ctx.departmentId = dbCourse.departmentId ?? null;
      ctx.departmentName = dbCourse.departments?.name ?? ctx.departmentName;
    }
  }

  // Resolve the DB department from the catalog when not already known
  if (!ctx.departmentId) {
    const match = findCourse(student.departmentId, student.courseCode);
    const catalogDeptName = match?.department?.name;
    const deptName = ctx.departmentName ?? catalogDeptName;
    if (deptName) {
      const { data: dept } = await supabase
        .from("departments")
        .select("id, name")
        .eq("name", deptName)
        .maybeSingle();
      if (dept) {
        ctx.departmentId = dept.id;
        ctx.departmentName = dept.name;
      }
    }
  }

  // Unit lecturer: teacher assigned to the course
  if (ctx.courseId) {
    const { data: assignment } = await supabase
      .from("course_assignments")
      .select("teachers!course_assignments_teacherId_fkey(id, firstName, lastName)")
      .eq("courseId", ctx.courseId)
      .limit(1);
    const assigned = assignment?.[0]?.teachers;
    if (assigned) {
      ctx.unitLecturerId = assigned.id;
      ctx.unitLecturerName = `${assigned.firstName} ${assigned.lastName}`;
    }
  }

  // HOD: teacher flagged isHod in the same department
  if (ctx.departmentId) {
    const { data: hod } = await supabase
      .from("teachers")
      .select("id, firstName, lastName")
      .eq("departmentId", ctx.departmentId)
      .eq("isHod", true)
      .limit(1);
    const hodRow = hod?.[0];
    if (hodRow) {
      ctx.hodId = hodRow.id;
      ctx.hodName = `${hodRow.firstName} ${hodRow.lastName}`;
    }
  }

  // Faculty HOD: teacher flagged as the faculty's HOD
  if (ctx.departmentId) {
    const { data: dept } = await supabase
      .from("departments")
      .select("facultyId")
      .eq("id", ctx.departmentId)
      .maybeSingle();
    if (dept?.facultyId) {
      ctx.facultyId = dept.facultyId;
      const { data: fac } = await supabase
        .from("faculties")
        .select("name, hodTeacherId")
        .eq("id", dept.facultyId)
        .maybeSingle();
      if (fac?.hodTeacherId) {
        const { data: facHod } = await supabase
          .from("teachers")
          .select("id, firstName, lastName")
          .eq("id", fac.hodTeacherId)
          .maybeSingle();
        if (facHod) {
          ctx.facultyHodId = facHod.id;
          ctx.facultyHodName = `${facHod.firstName} ${facHod.lastName}`;
        }
      }
    }
  }

  // Fallback unit lecturer: any non-HOD teacher in the department
  if (!ctx.unitLecturerId && ctx.departmentId) {
    const { data: lecturer } = await supabase
      .from("teachers")
      .select("id, firstName, lastName")
      .eq("departmentId", ctx.departmentId)
      .eq("isHod", false)
      .limit(1);
    const lecturerRow = lecturer?.[0];
    if (lecturerRow) {
      ctx.unitLecturerId = lecturerRow.id;
      ctx.unitLecturerName = `${lecturerRow.firstName} ${lecturerRow.lastName}`;
    }
  }

  return ctx;
}

// Resolves the faculty attached to a teacher's department (by id, no FK
// constraint assumed on departments.facultyId).
export async function resolveTeacherFaculty(supabase: any, departmentId: string | null) {
  const result = { facultyId: null as string | null, facultyName: null as string | null, hodTeacherId: null as string | null };
  if (!departmentId) return result;
  const { data: dept } = await supabase.from("departments").select("facultyId").eq("id", departmentId).maybeSingle();
  if (!dept?.facultyId) return result;
  result.facultyId = dept.facultyId;
  const { data: fac } = await supabase.from("faculties").select("name, hodTeacherId").eq("id", dept.facultyId).maybeSingle();
  if (fac) {
    result.facultyName = fac.name ?? null;
    result.hodTeacherId = fac.hodTeacherId ?? null;
  }
  return result;
}

// Resolves the department/faculty and the teachers who must verify for a
// course-scoped release (result documents, assignments, exams).
export async function resolveCourseContext(supabase: any, courseId: string): Promise<StudentContext> {
  const ctx: StudentContext = { ...EMPTY };

  const { data: course } = await supabase
    .from("courses")
    .select("id, name, code, departmentId, departments!courses_departmentId_fkey(id, name, facultyId)")
    .eq("id", courseId)
    .maybeSingle();

  if (!course) return ctx;

  ctx.courseId = course.id;
  ctx.courseName = course.name;
  ctx.courseCode = course.code;
  ctx.departmentId = course.departmentId ?? null;
  ctx.departmentName = course.departments?.name ?? null;
  ctx.facultyId = course.departments?.facultyId ?? null;

  const { data: assignment } = await supabase
    .from("course_assignments")
    .select("teachers!course_assignments_teacherId_fkey(id, firstName, lastName)")
    .eq("courseId", courseId)
    .limit(1);
  const assigned = assignment?.[0]?.teachers;
  if (assigned) {
    ctx.unitLecturerId = assigned.id;
    ctx.unitLecturerName = `${assigned.firstName} ${assigned.lastName}`;
  }

  if (ctx.departmentId) {
    const { data: hod } = await supabase
      .from("teachers")
      .select("id, firstName, lastName")
      .eq("departmentId", ctx.departmentId)
      .eq("isHod", true)
      .limit(1);
    const hodRow = hod?.[0];
    if (hodRow) {
      ctx.hodId = hodRow.id;
      ctx.hodName = `${hodRow.firstName} ${hodRow.lastName}`;
    }
  }

  if (ctx.facultyId) {
    const { data: fac } = await supabase
      .from("faculties")
      .select("name, hodTeacherId")
      .eq("id", ctx.facultyId)
      .maybeSingle();
    if (fac?.hodTeacherId) {
      const { data: facHod } = await supabase
        .from("teachers")
        .select("id, firstName, lastName")
        .eq("id", fac.hodTeacherId)
        .maybeSingle();
      if (facHod) {
        ctx.facultyHodId = facHod.id;
        ctx.facultyHodName = `${facHod.firstName} ${facHod.lastName}`;
      }
    }
  }

  return ctx;
}
