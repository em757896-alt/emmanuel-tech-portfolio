export type Role = "ADMIN" | "TEACHER" | "STUDENT";

export interface SessionUser {
  id: string;
  email: string;
  name: string | null;
  role: Role;
  avatar: string | null;
}

export interface DashboardStats {
  totalStudents: number;
  totalTeachers: number;
  totalCourses: number;
  totalDepartments: number;
}

export interface StudentProfile {
  id: string;
  userId: string;
  studentId: string;
  firstName: string;
  lastName: string;
  phone: string | null;
  dateOfBirth: string | null;
  address: string | null;
  departmentId: string | null;
  enrollmentDate: string;
  status: string;
  user: {
    id: string;
    email: string;
    avatar: string | null;
  };
  department?: {
    id: string;
    name: string;
    code: string;
  } | null;
}

export interface TeacherProfile {
  id: string;
  userId: string;
  employeeId: string;
  firstName: string;
  lastName: string;
  phone: string | null;
  departmentId: string | null;
  position: string | null;
  hireDate: string;
  user: {
    id: string;
    email: string;
    avatar: string | null;
  };
  department?: {
    id: string;
    name: string;
    code: string;
  } | null;
}

export interface CourseWithDetails {
  id: string;
  name: string;
  code: string;
  description: string | null;
  credits: number;
  semester: number;
  year: number;
  department?: {
    id: string;
    name: string;
    code: string;
  } | null;
  _count?: {
    enrollments: number;
    assignments: number;
    exams: number;
  };
}

export interface AssignmentWithDetails {
  id: string;
  title: string;
  description: string | null;
  dueDate: string;
  totalMarks: number;
  createdAt: string;
  course: {
    id: string;
    name: string;
    code: string;
  };
  _count?: {
    submissions: number;
  };
}

export interface GradeWithDetails {
  id: string;
  finalGrade: string;
  gpa: number;
  semester: number;
  year: number;
  course: {
    id: string;
    name: string;
    code: string;
    credits: number;
  };
}

export interface AttendanceWithDetails {
  id: string;
  date: string;
  status: string;
  checkedInAt: string;
  session: {
    id: string;
    course: {
      name: string;
      code: string;
    };
  };
}

export interface Notification {
  id: string;
  title: string;
  message: string;
  read: boolean;
  link: string | null;
  createdAt: string;
}
