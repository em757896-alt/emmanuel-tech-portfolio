import NextAuth from "next-auth";
import Credentials from "next-auth/providers/credentials";
import bcrypt from "bcryptjs";
import { createClient } from "@supabase/supabase-js";
import { supabase } from "@/lib/supabase";

function getSupabase() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  if (!url || !key) return null;
  return createClient(url, key);
}

async function findUserByEmail(email: string) {
  const supabase = getSupabase();
  if (!supabase) return null;
  const { data } = await supabase.from("users").select("*").eq("email", email).single();
  return data;
}

async function findUserById(id: string) {
  const supabase = getSupabase();
  if (!supabase) return null;
  const { data } = await supabase.from("users").select("*").eq("id", id).single();
  return data;
}

async function findStudentByAdmNo(admNo: string) {
  const supabase = getSupabase();
  if (!supabase) return null;
  const { data } = await supabase.from("students").select("*").eq("studentId", admNo).single();
  return data;
}

async function findTeacherByEmployeeId(employeeId: string) {
  const supabase = getSupabase();
  if (!supabase) return null;
  const { data } = await supabase.from("teachers").select("*").eq("employeeId", employeeId).single();
  return data;
}

async function buildTeacherSession(
  credentials: Record<string, unknown>,
  user: { id: string; email: string; name: string; role: string; avatar: string | null },
  teacher: { id: string; employeeId: string; isHod?: boolean; facultyId?: string | null; departmentId?: string | null }
) {
  const hodClaim =
    (credentials.hodClaim as string | undefined) === "yes" ||
    (credentials.hodClaim as string | undefined) === "true";
  const isDeptHod = teacher.isHod === true;
  const supabaseAuth = getSupabase();
  let isFacultyHod = false;
  let facultyId: string | null = teacher.facultyId ?? null;
  if (supabaseAuth) {
    const { data: faculty } = await supabaseAuth
      .from("faculties")
      .select("id")
      .eq("hodTeacherId", teacher.id)
      .maybeSingle();
    if (faculty) {
      isFacultyHod = true;
      facultyId = faculty.id;
    }
  }
  const isHod = isDeptHod || isFacultyHod;
  if (hodClaim && !isHod) return null;

  return {
    id: user.id,
    email: user.email,
    name: user.name,
    role: user.role,
    avatar: user.avatar,
    employeeId: teacher.employeeId,
    isHod: isDeptHod,
    facultyHod: isFacultyHod,
    facultyId,
    departmentId: teacher.departmentId ?? null,
    hod: isHod && hodClaim,
  };
}

export const { handlers, signIn, signOut, auth } = NextAuth({
  providers: [
    Credentials({
      credentials: {
        email: { label: "Email", type: "email" },
        admNo: { label: "Admission Number", type: "text" },
        employeeId: { label: "Employee ID", type: "text" },
        password: { label: "Password", type: "password" },
        hodClaim: { label: "HOD", type: "text" },
      },
      async authorize(credentials) {
        if (!credentials?.password) return null;

        // Student portal: email + Adm No + password, verified via Supabase Auth
        if (credentials.admNo) {
          if (!credentials.email) return null;

          const student = await findStudentByAdmNo(credentials.admNo as string);
          if (!student) return null;

          const user = await findUserById(student.userId);
          if (!user || user.role !== "STUDENT") return null;
          if (user.email.toLowerCase() !== (credentials.email as string).toLowerCase()) return null;

          const { error } = await supabase.auth.signInWithPassword({
            email: user.email,
            password: credentials.password as string,
          });
          if (error) return null;

          return {
            id: user.id,
            email: user.email,
            name: user.name,
            role: user.role,
            avatar: user.avatar,
            studentId: student.studentId,
          };
        }

        // Teacher portal: Employee ID + Email + Password (bcrypt). The employee
        // ID must match a real teacher record and the email must match that
        // teacher's account email.
        if (credentials.employeeId) {
          if (!credentials.email) return null;

          const teacher = await findTeacherByEmployeeId(credentials.employeeId as string);
          if (!teacher) return null;

          const user = await findUserById(teacher.userId);
          if (!user || user.role !== "TEACHER") return null;
          if (user.email.toLowerCase() !== (credentials.email as string).toLowerCase()) return null;

          const isValid = await bcrypt.compare(
            credentials.password as string,
            user.passwordHash
          );
          if (!isValid) return null;

          return buildTeacherSession(credentials, user, teacher);
        }

        // Admin portal: email + password (bcrypt) only. Staff/teacher accounts
        // cannot log in through this path.
        if (!credentials.email) return null;

        const user = await findUserByEmail(credentials.email as string);
        if (!user) return null;
        if (user.role !== "ADMIN") return null;

        const isValid = await bcrypt.compare(
          credentials.password as string,
          user.passwordHash
        );
        if (!isValid) return null;

        return {
          id: user.id,
          email: user.email,
          name: user.name,
          role: user.role,
          avatar: user.avatar,
        };
      },
    }),
  ],
  session: { strategy: "jwt" },
  pages: {
    signIn: "/admin-login",
  },
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.role = (user as { role: string }).role;
        token.avatar = (user as { avatar: string | null }).avatar;
        token.studentId = (user as { studentId?: string }).studentId;
        token.employeeId = (user as { employeeId?: string }).employeeId;
        token.isHod = (user as { isHod?: boolean }).isHod ?? false;
        token.facultyHod = (user as { facultyHod?: boolean }).facultyHod ?? false;
        token.facultyId = (user as { facultyId?: string | null }).facultyId ?? null;
        token.departmentId = (user as { departmentId?: string | null }).departmentId ?? null;
        token.hod = (user as { hod?: boolean }).hod ?? false;
      }
      return token;
    },
    async session({ session, token }) {
      if (session.user) {
        session.user.id = token.sub!;
        (session.user as { role: string }).role = token.role as string;
        (session.user as { avatar: string | null }).avatar = token.avatar as string | null;
        (session.user as { studentId?: string }).studentId = token.studentId as string | undefined;
        (session.user as { employeeId?: string }).employeeId = token.employeeId as string | undefined;
        (session.user as { isHod?: boolean }).isHod = token.isHod as boolean;
        (session.user as { facultyHod?: boolean }).facultyHod = token.facultyHod as boolean;
        (session.user as { facultyId?: string | null }).facultyId = token.facultyId as string | null;
        (session.user as { departmentId?: string | null }).departmentId = token.departmentId as string | null;
        (session.user as { hod?: boolean }).hod = token.hod as boolean;
      }
      return session;
    },
  },
});
