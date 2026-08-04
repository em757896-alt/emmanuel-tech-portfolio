"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { GraduationCap, Users } from "lucide-react";

interface Scope {
  id: string;
  name: string;
  code: string;
  type: "FACULTY" | "DEPARTMENT";
}

interface Student {
  id: string;
  firstName: string;
  lastName: string;
  studentId: string;
  emailVerified: boolean;
}

interface CourseGroup {
  id: string;
  name: string;
  code: string;
  students: Student[];
}

interface DeptGroup {
  id: string;
  name: string;
  code: string;
  courses: CourseGroup[];
}

export default function HodStudents() {
  const { data, loading } = useFetch<{ scope: Scope | null; departments: DeptGroup[] }>("/api/teacher/hod/students");

  const isFaculty = data?.scope?.type === "FACULTY";
  const totalStudents = data?.departments?.reduce((n, d) => n + d.courses.reduce((m, c) => m + c.students.length, 0), 0) || 0;

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold">{isFaculty ? "Faculty Students" : "Department Students"}</h1>
          <p className="text-muted-foreground">
            {data?.scope
              ? `${data.scope.name} (${data.scope.code}) — students organised by ${isFaculty ? "department, then course." : "course."}`
              : "Students enrolled in your department&apos;s courses."}
          </p>
        </div>

        {!loading && data?.departments?.length ? (
          <div className="space-y-6">
            {data.departments.map((dept) => {
              const deptStudents = dept.courses.reduce((n, c) => n + c.students.length, 0);
              return (
                <Card key={dept.id}>
                  <CardHeader>
                    <CardTitle className="flex flex-wrap items-center justify-between gap-2">
                      <span className="flex items-center gap-2">
                        <GraduationCap className="h-5 w-5" />
                        {dept.name}
                        <span className="text-sm font-normal text-muted-foreground">{dept.code}</span>
                      </span>
                      <span className="flex items-center gap-2">
                        <Badge variant="secondary">{dept.courses.length} course{dept.courses.length === 1 ? "" : "s"}</Badge>
                        <Badge variant="info">{deptStudents} student{deptStudents === 1 ? "" : "s"}</Badge>
                      </span>
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-4">
                      {dept.courses.length ? dept.courses.map((course) => (
                        <div key={course.id} className="rounded-lg border">
                          <div className="flex flex-wrap items-center justify-between gap-2 border-b bg-muted/40 px-4 py-2.5">
                            <p className="font-semibold text-sm">{course.name}</p>
                            <Badge variant="secondary">{course.code} · {course.students.length} student{course.students.length === 1 ? "" : "s"}</Badge>
                          </div>
                          <div className="divide-y">
                            {course.students.length ? course.students.map((s) => (
                              <div key={s.id} className="flex flex-wrap items-center justify-between gap-2 px-4 py-2">
                                <div>
                                  <p className="font-medium text-sm">{s.firstName} {s.lastName}</p>
                                  <p className="text-xs text-muted-foreground">Adm No: {s.studentId}</p>
                                </div>
                                {s.emailVerified ? (
                                  <Badge variant="success">Verified</Badge>
                                ) : (
                                  <Badge variant="outline">Unverified</Badge>
                                )}
                              </div>
                            )) : (
                              <p className="px-4 py-3 text-sm text-muted-foreground">No students enrolled in this course yet.</p>
                            )}
                          </div>
                        </div>
                      )) : (
                        <p className="text-sm text-muted-foreground">No courses in this department.</p>
                      )}
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>
        ) : loading ? (
          <div className="text-sm text-muted-foreground">Loading...</div>
        ) : (
          <Card>
            <CardContent>
              <div className="flex flex-col items-center gap-2 py-8 text-muted-foreground">
                <Users className="h-8 w-8" />
                <p className="text-sm">{isFaculty ? "No departments under this faculty yet." : "No students enrolled in this department yet."}</p>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
