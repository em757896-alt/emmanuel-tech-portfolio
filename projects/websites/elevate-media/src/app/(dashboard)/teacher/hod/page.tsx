"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { StatCard } from "@/components/dashboard/StatCard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Users, BookOpen, ClipboardCheck, Building2, FlaskConical, Trophy, FileText } from "lucide-react";

interface Scope {
  id: string;
  name: string;
  code: string;
  type: "FACULTY" | "DEPARTMENT";
}

interface HodData {
  scope: Scope | null;
  stats: {
    departmentCount: number;
    courseCount: number;
    studentCount: number;
    pendingPoe: number;
    pendingAttendance: number;
    pendingResearch: number;
    pendingResults: number;
    pendingExams: number;
    pendingAssignments: number;
  };
  departments: {
    id: string;
    name: string;
    code: string;
    courses: { id: string; name: string; code: string; credits: number; semester: number; year: number; _count: { enrollments: number } }[];
  }[];
}

export default function HodDashboard() {
  const { data, loading } = useFetch<HodData>("/api/teacher/hod/dashboard");

  const isFaculty = data?.scope?.type === "FACULTY";
  const pendingTotal = (data?.stats ? data.stats.pendingPoe + data.stats.pendingAttendance + data.stats.pendingResearch + data.stats.pendingResults + data.stats.pendingExams + data.stats.pendingAssignments : 0);

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 className="text-3xl font-bold">{isFaculty ? "Faculty Overview" : "Department Overview"}</h1>
            <p className="text-muted-foreground">
              {data?.scope
                ? `${data.scope.name} (${data.scope.code}) — ${isFaculty ? "every department and course under this faculty." : "your department and its courses."}`
                : "Welcome back! Here's your overview."}
            </p>
          </div>
          {data?.scope && (
            <Badge variant={isFaculty ? "info" : "secondary"}>
              {isFaculty ? "Faculty HOD" : "Department HOD"}
            </Badge>
          )}
        </div>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard title={isFaculty ? "Departments" : "Department"} value={data?.stats?.departmentCount || 0} icon={Building2} />
          <StatCard title={isFaculty ? "Faculty Students" : "Department Students"} value={data?.stats?.studentCount || 0} icon={Users} />
          <StatCard title="Courses" value={data?.stats?.courseCount || 0} icon={BookOpen} />
          <StatCard title="Pending Approvals" value={pendingTotal} icon={ClipboardCheck} />
        </div>

        <div className="grid gap-6 lg:grid-cols-2">
          <StatCard title="Pending POE" value={data?.stats?.pendingPoe || 0} icon={ClipboardCheck} />
          <StatCard title="Pending Attendance" value={data?.stats?.pendingAttendance || 0} icon={Users} />
          <StatCard title="Pending Research" value={data?.stats?.pendingResearch || 0} icon={FlaskConical} />
          <StatCard title="Pending Results" value={data?.stats?.pendingResults || 0} icon={Trophy} />
          <StatCard title="Pending Exams" value={data?.stats?.pendingExams || 0} icon={FileText} />
          <StatCard title="Pending Assignments" value={data?.stats?.pendingAssignments || 0} icon={FileText} />
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Building2 className="h-5 w-5" />
              {isFaculty ? "Departments & Courses" : "Department Courses"}
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-sm text-muted-foreground">Loading...</div>
            ) : data?.departments?.length ? (
              <div className="space-y-6">
                {data.departments.map((dept) => (
                  <div key={dept.id} className="rounded-lg border">
                    <div className="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
                      <div>
                        <p className="font-semibold">{dept.name}</p>
                        <p className="text-xs text-muted-foreground">{dept.code} · {dept.courses.length} course{dept.courses.length === 1 ? "" : "s"}</p>
                      </div>
                      <Badge variant="secondary">{dept.courses.reduce((n, c) => n + c._count.enrollments, 0)} students</Badge>
                    </div>
                    <div className="divide-y">
                      {dept.courses.length ? dept.courses.map((c) => (
                        <div key={c.id} className="flex flex-wrap items-center justify-between gap-2 px-4 py-2.5">
                          <div>
                            <p className="font-medium text-sm">{c.name}</p>
                            <p className="text-xs text-muted-foreground">{c.code} · Semester {c.semester} {c.year}</p>
                          </div>
                          <Badge variant="info">{c._count.enrollments} students</Badge>
                        </div>
                      )) : (
                        <p className="px-4 py-3 text-sm text-muted-foreground">No courses in this department.</p>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-sm text-muted-foreground">{isFaculty ? "No departments under this faculty yet." : "No courses in this department."}</p>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
