import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

async function main() {
  console.log("Seeding database...");

  // Create departments
  const departments = await Promise.all([
    prisma.department.create({
      data: { name: "Computer Science", code: "CS", description: "Department of Computer Science and Information Technology" },
    }),
    prisma.department.create({
      data: { name: "Business Administration", code: "BA", description: "Department of Business Administration and Management" },
    }),
    prisma.department.create({
      data: { name: "Engineering", code: "ENG", description: "Department of Engineering and Technology" },
    }),
    prisma.department.create({
      data: { name: "Media Studies", code: "MS", description: "Department of Media and Communication Studies" },
    }),
  ]);

  console.log(`Created ${departments.length} departments`);

  // Create admin user
  const adminPassword = await bcrypt.hash("admin123", 12);
  await prisma.user.create({
    data: {
      email: "admin@elevatemedia.edu",
      name: "System Admin",
      passwordHash: adminPassword,
      role: "ADMIN",
    },
  });
  console.log("Created admin user (admin@elevatemedia.edu / admin123)");

  // Create teacher users
  const teacherPassword = await bcrypt.hash("teacher123", 12);
  const teachers = await Promise.all([
    prisma.user.create({
      include: { teacher: true },
      data: {
        email: "sarah.jones@elevatemedia.edu",
        name: "Sarah Jones",
        passwordHash: teacherPassword,
        role: "TEACHER",
        teacher: {
          create: { employeeId: "T2026001", firstName: "Sarah", lastName: "Jones", departmentId: departments[0].id, position: "Senior Lecturer" },
        },
      },
    }),
    prisma.user.create({
      include: { teacher: true },
      data: {
        email: "michael.smith@elevatemedia.edu",
        name: "Michael Smith",
        passwordHash: teacherPassword,
        role: "TEACHER",
        teacher: {
          create: { employeeId: "T2026002", firstName: "Michael", lastName: "Smith", departmentId: departments[1].id, position: "Lecturer" },
        },
      },
    }),
  ]);
  console.log(`Created ${teachers.length} teachers`);

  // Create student users
  const studentPassword = await bcrypt.hash("student123", 12);
  const students = await Promise.all([
    prisma.user.create({
      include: { student: true },
      data: {
        email: "john.doe@student.elevatemedia.edu",
        name: "John Doe",
        passwordHash: studentPassword,
        role: "STUDENT",
        student: {
          create: { studentId: "EM20260001", firstName: "John", lastName: "Doe", phone: "+27 123 456 001", departmentId: departments[0].id },
        },
      },
    }),
    prisma.user.create({
      include: { student: true },
      data: {
        email: "jane.smith@student.elevatemedia.edu",
        name: "Jane Smith",
        passwordHash: studentPassword,
        role: "STUDENT",
        student: {
          create: { studentId: "EM20260002", firstName: "Jane", lastName: "Smith", phone: "+27 123 456 002", departmentId: departments[1].id },
        },
      },
    }),
    prisma.user.create({
      include: { student: true },
      data: {
        email: "ali.khan@student.elevatemedia.edu",
        name: "Ali Khan",
        passwordHash: studentPassword,
        role: "STUDENT",
        student: {
          create: { studentId: "EM20260003", firstName: "Ali", lastName: "Khan", phone: "+27 123 456 003", departmentId: departments[2].id },
        },
      },
    }),
  ]);
  console.log(`Created ${students.length} students`);

  // Create courses
  const courses = await Promise.all([
    prisma.course.create({
      data: { name: "Introduction to Programming", code: "CS101", description: "Fundamentals of programming using Python and JavaScript", credits: 3, departmentId: departments[0].id, semester: 1, year: 2026 },
    }),
    prisma.course.create({
      data: { name: "Data Structures & Algorithms", code: "CS201", description: "Advanced data structures and algorithm design", credits: 4, departmentId: departments[0].id, semester: 2, year: 2026 },
    }),
    prisma.course.create({
      data: { name: "Business Management 101", code: "BA101", description: "Introduction to business management principles", credits: 3, departmentId: departments[1].id, semester: 1, year: 2026 },
    }),
    prisma.course.create({
      data: { name: "Digital Marketing", code: "MS101", description: "Modern digital marketing strategies and tools", credits: 3, departmentId: departments[3].id, semester: 1, year: 2026 },
    }),
  ]);
  console.log(`Created ${courses.length} courses`);

  // Create course assignments (teachers teaching courses)
  await Promise.all([
    prisma.courseAssignment.create({ data: { teacherId: teachers[0].teacher!.id, courseId: courses[0].id, semester: 1, year: 2026 } }),
    prisma.courseAssignment.create({ data: { teacherId: teachers[0].teacher!.id, courseId: courses[1].id, semester: 2, year: 2026 } }),
    prisma.courseAssignment.create({ data: { teacherId: teachers[1].teacher!.id, courseId: courses[2].id, semester: 1, year: 2026 } }),
  ]);
  console.log("Created course assignments");

  // Create enrollments
  await Promise.all([
    prisma.courseEnrollment.create({ data: { studentId: students[0].student!.id, courseId: courses[0].id } }),
    prisma.courseEnrollment.create({ data: { studentId: students[0].student!.id, courseId: courses[1].id } }),
    prisma.courseEnrollment.create({ data: { studentId: students[1].student!.id, courseId: courses[2].id } }),
    prisma.courseEnrollment.create({ data: { studentId: students[2].student!.id, courseId: courses[0].id } }),
    prisma.courseEnrollment.create({ data: { studentId: students[2].student!.id, courseId: courses[3].id } }),
  ]);
  console.log("Created enrollments");

  // Create assignments
  await Promise.all([
    prisma.assignment.create({
      data: { title: "Python Basics", description: "Complete exercises on Python fundamentals", courseId: courses[0].id, teacherId: teachers[0].teacher!.id, dueDate: new Date("2026-08-15"), totalMarks: 100 },
    }),
    prisma.assignment.create({
      data: { title: "Binary Tree Implementation", description: "Implement a binary search tree in your preferred language", courseId: courses[1].id, teacherId: teachers[0].teacher!.id, dueDate: new Date("2026-09-01"), totalMarks: 100 },
    }),
    prisma.assignment.create({
      data: { title: "Business Plan Draft", description: "Submit a preliminary business plan for a startup", courseId: courses[2].id, teacherId: teachers[1].teacher!.id, dueDate: new Date("2026-08-20"), totalMarks: 50 },
    }),
  ]);
  console.log("Created assignments");

  // Create announcements
  await Promise.all([
    prisma.announcement.create({
      data: { title: "Welcome to Elevate Media 2026", content: "Welcome to the new academic year! We are excited to have you on board. Please check your course schedules and complete your registration.", authorId: teachers[0].id, target: "ALL" },
    }),
    prisma.announcement.create({
      data: { title: "CS101 Lab Session Change", content: "Starting next week, the CS101 lab sessions will be held in Room 302 instead of Room 101. Please update your schedules accordingly.", authorId: teachers[0].id, courseId: courses[0].id, target: "STUDENTS" },
    }),
  ]);
  console.log("Created announcements");

  console.log("\nSeed completed!");
  console.log("\nTest accounts:");
  console.log("  Admin:    admin@elevatemedia.edu / admin123");
  console.log("  Teacher:  sarah.jones@elevatemedia.edu / teacher123");
  console.log("  Student:  john.doe@student.elevatemedia.edu / student123");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
