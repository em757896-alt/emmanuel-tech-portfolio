"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { StatCard } from "@/components/dashboard/StatCard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { GraduationCap, Users, BookOpen, Building2 } from "lucide-react";
import { formatDate } from "@/lib/utils";

export default function AdminDashboard() {
  const { data, loading } = useFetch<{ stats: { totalStudents: number; totalTeachers: number; totalCourses: number; totalDepartments: number }; recentStudents: { id: string; firstName: string; lastName: string; studentId: string; status: string; enrollmentDate: string }[]; recentEnrollments: { id: string; enrolledAt: string; student: { firstName: string; lastName: string; studentId: string }; course: { name: string; code: string } }[] }>("/api/admin/dashboard");

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold">Admin Dashboard</h1>
          <p className="text-muted-foreground">System overview and management.</p>
        </div>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard title="Total Students" value={data?.stats?.totalStudents || 0} icon={GraduationCap} />
          <StatCard title="Total Teachers" value={data?.stats?.totalTeachers || 0} icon={Users} />
          <StatCard title="Total Courses" value={data?.stats?.totalCourses || 0} icon={BookOpen} />
          <StatCard title="Departments" value={data?.stats?.totalDepartments || 0} icon={Building2} />
        </div>

        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Recent Students</CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-muted-foreground">Loading...</div>
              ) : data?.recentStudents?.length ? (
                <div className="space-y-3">
                  {data.recentStudents.map((s) => (
                    <div key={s.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                      <div>
                        <p className="font-medium text-sm">{s.firstName} {s.lastName}</p>
                        <p className="text-xs text-muted-foreground">{s.studentId}</p>
                      </div>
                      <Badge variant={s.status === "ACTIVE" ? "success" : "secondary"}>{s.status}</Badge>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No students yet.</p>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Recent Enrollments</CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-muted-foreground">Loading...</div>
              ) : data?.recentEnrollments?.length ? (
                <div className="space-y-3">
                  {data.recentEnrollments.map((e) => (
                    <div key={e.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                      <div>
                        <p className="text-sm font-medium">{e.student.firstName} {e.student.lastName}</p>
                        <p className="text-xs text-muted-foreground">{e.course.name} ({e.course.code})</p>
                      </div>
                      <span className="text-xs text-muted-foreground">{formatDate(e.enrolledAt)}</span>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No enrollments yet.</p>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
