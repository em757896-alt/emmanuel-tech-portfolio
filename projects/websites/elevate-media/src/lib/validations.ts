import { z } from "zod";

export const loginSchema = z.object({
  email: z.string().email("Invalid email address"),
  password: z.string().min(6, "Password must be at least 6 characters"),
});

export const registerSchema = z.object({
  firstName: z.string().min(1, "First name is required"),
  lastName: z.string().min(1, "Last name is required"),
  email: z.string().email("Invalid email address"),
  password: z.string().min(6, "Password must be at least 6 characters"),
  phone: z.string().optional(),
  departmentId: z.string().optional(),
});

export const studentSchema = z.object({
  firstName: z.string().min(1, "First name is required"),
  lastName: z.string().min(1, "Last name is required"),
  email: z.string().email("Invalid email address"),
  phone: z.string().optional(),
  dateOfBirth: z.string().optional(),
  address: z.string().optional(),
  departmentId: z.string().optional(),
  status: z.enum(["ACTIVE", "GRADUATED", "SUSPENDED", "DEFERRED"]).optional(),
});

export const teacherSchema = z.object({
  firstName: z.string().min(1, "First name is required"),
  lastName: z.string().min(1, "Last name is required"),
  email: z.string().email("Invalid email address"),
  phone: z.string().optional(),
  departmentId: z.string().optional(),
  position: z.string().optional(),
});

export const courseSchema = z.object({
  name: z.string().min(1, "Course name is required"),
  code: z.string().min(1, "Course code is required"),
  description: z.string().optional(),
  credits: z.number().min(1).max(6).default(3),
  departmentId: z.string().optional(),
  semester: z.number().min(1).max(2),
  year: z.number().min(2020),
});

export const departmentSchema = z.object({
  name: z.string().min(1, "Department name is required"),
  code: z.string().min(1, "Department code is required"),
  description: z.string().optional(),
});

export const assignmentSchema = z.object({
  title: z.string().min(1, "Title is required"),
  description: z.string().optional(),
  courseId: z.string().min(1, "Course is required"),
  dueDate: z.string().min(1, "Due date is required"),
  totalMarks: z.number().min(1).max(1000).default(100),
});

export const examSchema = z.object({
  title: z.string().min(1, "Title is required"),
  courseId: z.string().min(1, "Course is required"),
  date: z.string().min(1, "Date is required"),
  totalMarks: z.number().min(1).max(1000).default(100),
  weight: z.number().min(0).max(100).default(30),
});

export const gradeSchema = z.object({
  studentId: z.string().min(1),
  courseId: z.string().min(1),
  semester: z.number().min(1).max(2),
  year: z.number(),
  finalGrade: z.string().min(1),
  gpa: z.number().min(0).max(4),
});

export const announcementSchema = z.object({
  title: z.string().min(1, "Title is required"),
  content: z.string().min(1, "Content is required"),
  courseId: z.string().optional(),
  target: z.enum(["ALL", "STUDENTS", "TEACHERS", "DEPARTMENT", "COURSE"]).default("ALL"),
});

export const poeSchema = z.object({
  title: z.string().min(1, "Title is required"),
  description: z.string().optional(),
});

export const researchSchema = z.object({
  title: z.string().min(1, "Title is required"),
  abstract: z.string().optional(),
});

export type LoginInput = z.infer<typeof loginSchema>;
export type RegisterInput = z.infer<typeof registerSchema>;
export type StudentInput = z.infer<typeof studentSchema>;
export type TeacherInput = z.infer<typeof teacherSchema>;
export type CourseInput = z.infer<typeof courseSchema>;
export type DepartmentInput = z.infer<typeof departmentSchema>;
export type AssignmentInput = z.infer<typeof assignmentSchema>;
export type ExamInput = z.infer<typeof examSchema>;
export type GradeInput = z.infer<typeof gradeSchema>;
export type AnnouncementInput = z.infer<typeof announcementSchema>;
export type POEInput = z.infer<typeof poeSchema>;
export type ResearchInput = z.infer<typeof researchSchema>;
