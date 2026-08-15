import { auth } from "@/lib/auth";
import { resolveTeacherFaculty, StudentContext } from "@/lib/institution";

export interface Approver {
  teacherId: string | null;
  teacherName: string | null;
  isAdmin: boolean;
  isHod: boolean;
  isFacultyHod: boolean;
}

// Loads the acting teacher from the session. Returns null when the caller is
// not a logged-in teacher or admin.
export async function getApprover(supabase: any): Promise<Approver | null> {
  const session = await auth();
  if (!session) return null;
  const userRole = (session.user as { role: string })?.role;
  if (userRole !== "TEACHER" && userRole !== "ADMIN") return null;

  if (userRole === "ADMIN") {
    return { teacherId: null, teacherName: null, isAdmin: true, isHod: true, isFacultyHod: true };
  }

  const { data: teacher } = await supabase
    .from("teachers")
    .select("id, firstName, lastName, isHod, departmentId")
    .eq("userId", session.user!.id)
    .maybeSingle();

  if (!teacher) return null;

  const fac = await resolveTeacherFaculty(supabase, teacher.departmentId);

  return {
    teacherId: teacher.id,
    teacherName: `${teacher.firstName} ${teacher.lastName}`,
    isAdmin: false,
    isHod: teacher.isHod === true,
    isFacultyHod: fac.hodTeacherId === teacher.id,
  };
}

export type ApproveSide = "lecturer" | "hod";

// Checks whether the acting teacher may act on the given side for the student
// context. A HOD (department or faculty) may always act on the lecturer side
// (recorded as verified "by HOD, by HOD's powers"); the lecturer side can also
// be done by the actual unit lecturer. The HOD side requires a real HOD.
export function canAct(
  approver: Approver,
  ctx: StudentContext,
  side: ApproveSide,
): boolean {
  if (approver.isAdmin) return true;
  if (!approver.teacherId) return false;

  if (side === "lecturer") {
    const isUnitLecturer = ctx.unitLecturerId !== null && approver.teacherId === ctx.unitLecturerId;
    const isDeptHod = ctx.hodId !== null && approver.teacherId === ctx.hodId;
    const isFacHod = ctx.facultyHodId !== null && approver.teacherId === ctx.facultyHodId;
    return isUnitLecturer || isDeptHod || isFacHod;
  }

  if (side === "hod") {
    const isDeptHod = ctx.hodId !== null && approver.teacherId === ctx.hodId;
    const isFacHod = ctx.facultyHodId !== null && approver.teacherId === ctx.facultyHodId;
    return isDeptHod || isFacHod;
  }

  return false;
}

// Builds the patch for one approval side including accountability columns.
export function buildSidePatch(
  approver: Approver,
  ctx: StudentContext,
  side: ApproveSide,
  approve: boolean,
  columns: { lecturerApproved: string; hodApproved: string; lecturerVerifiedBy: string; lecturerVerifiedByRole: string; hodVerifiedBy: string },
): Record<string, unknown> {
  const patch: Record<string, unknown> = {};

  if (side === "lecturer") {
    patch[columns.lecturerApproved] = approve;
    if (approve) {
      patch[columns.lecturerVerifiedBy] = approver.teacherId ?? "admin";
      const isUnitLecturer = ctx.unitLecturerId !== null && approver.teacherId === ctx.unitLecturerId;
      patch[columns.lecturerVerifiedByRole] = isUnitLecturer ? "LECTURER" : "HOD";
    } else {
      patch[columns.lecturerVerifiedBy] = null;
      patch[columns.lecturerVerifiedByRole] = null;
    }
  }

  if (side === "hod") {
    patch[columns.hodApproved] = approve;
    if (approve) {
      patch[columns.hodVerifiedBy] = approver.teacherId ?? "admin";
    } else {
      patch[columns.hodVerifiedBy] = null;
    }
  }

  return patch;
}

// Derives the workflow status from the two sides.
export function computeApprovalStatus(
  lecturerApproved: boolean | null | undefined,
  hodApproved: boolean | null | undefined,
): "PENDING" | "PARTIALLY_APPROVED" | "APPROVED" {
  const l = lecturerApproved === true;
  const h = hodApproved === true;
  if (l && h) return "APPROVED";
  if (l || h) return "PARTIALLY_APPROVED";
  return "PENDING";
}
