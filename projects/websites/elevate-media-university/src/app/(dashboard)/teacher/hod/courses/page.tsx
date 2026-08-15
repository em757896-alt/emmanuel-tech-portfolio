"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Building2 } from "lucide-react";

interface Scope {
  id: string;
  name: string;
  code: string;
  type: "FACULTY" | "DEPARTMENT";
}

interface HodData {
  scope: Scope | null;
  departments: {
    id: string;
    name: string;
    code: string;
    courses: { id: string; name: string; code: string; credits: number; semester: number; year: number; _count: { enrollments: number } }[];
  }[];
}

export default function HodCourses() {
  const { data, loading } = useFetch<HodData>("/api/teacher/hod/dashboard");

  const isFaculty = data?.scope?.type === "FACULTY";
  const allCourses = data?.departments?.flatMap((d) => d.courses) || [];

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold">{isFaculty ? "Faculty Courses" : "Department Courses"}</h1>
          <p className="text-muted-foreground">
            {data?.scope ? `${data.scope.name} (${data.scope.code}) — ${isFaculty ? "all courses under this faculty, grouped by department." : "courses offered by your department."}` : "Courses under your scope."}
          </p>
        </div>

        {loading ? (
          <div className="text-sm text-muted-foreground">Loading...</div>
        ) : data?.departments?.length ? (
          <div className="space-y-6">
            {data.departments.map((dept) => (
              <Card key={dept.id}>
                <CardHeader>
                  <CardTitle className="flex flex-wrap items-center justify-between gap-2">
                    <span className="flex items-center gap-2">
                      <Building2 className="h-5 w-5" /> {dept.name}
                      <span className="text-sm font-normal text-muted-foreground">{dept.code}</span>
                    </span>
                    <Badge variant="secondary">{dept.courses.length} course{dept.courses.length === 1 ? "" : "s"}</Badge>
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  {dept.courses.length ? (
                    <div className="grid gap-4 md:grid-cols-2">
                      {dept.courses.map((c) => (
                        <div key={c.id} className="rounded-lg border p-4">
                          <div className="flex items-start justify-between gap-2">
                            <div>
                              <p className="font-semibold text-sm">{c.name}</p>
                              <p className="text-xs text-muted-foreground">{c.code}</p>
                            </div>
                            <Badge variant="info">{c._count.enrollments} students</Badge>
                          </div>
                          <div className="mt-2 text-xs text-muted-foreground">
                            {c.credits} Credits · Semester {c.semester} {c.year}
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-sm text-muted-foreground">No courses in this department.</p>
                  )}
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <p className="text-sm text-muted-foreground">{isFaculty ? "No departments under this faculty yet." : "No courses in this department."}</p>
        )}
      </div>
    </DashboardLayout>
  );
}
