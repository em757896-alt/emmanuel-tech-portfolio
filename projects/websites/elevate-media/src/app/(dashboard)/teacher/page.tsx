"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { StatCard } from "@/components/dashboard/StatCard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { BookOpen, Users, FileText, Star } from "lucide-react";

export default function TeacherDashboard() {
  const { data, loading } = useFetch<{ totalCourses: number; totalStudents: number; pendingSubmissions: { id: string; assignment: { title: string }; student: { firstName: string; lastName: string; studentId: string } }[]; courseAssignments: { id: string; course: { name: string; code: string; _count: { enrollments: number } } }[] }>("/api/teacher/dashboard");

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold">Teacher Dashboard</h1>
          <p className="text-muted-foreground">Welcome back! Here&apos;s your teaching overview.</p>
        </div>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard title="My Courses" value={data?.totalCourses || 0} icon={BookOpen} />
          <StatCard title="Total Students" value={data?.totalStudents || 0} icon={Users} />
          <StatCard title="Pending Submissions" value={data?.pendingSubmissions?.length || 0} icon={FileText} />
          <StatCard title="Research Papers" value="—" icon={Star} />
        </div>

        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>My Courses</CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-muted-foreground">Loading...</div>
              ) : data?.courseAssignments?.length ? (
                <div className="space-y-3">
                  {data.courseAssignments.map((ca) => (
                    <div key={ca.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                      <div>
                        <p className="font-medium text-sm">{ca.course.name}</p>
                        <p className="text-xs text-muted-foreground">{ca.course.code}</p>
                      </div>
                      <Badge variant="info">{ca.course._count.enrollments} students</Badge>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No courses assigned.</p>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Pending Submissions</CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-muted-foreground">Loading...</div>
              ) : data?.pendingSubmissions?.length ? (
                <div className="space-y-3">
                  {data.pendingSubmissions.slice(0, 5).map((sub) => (
                    <div key={sub.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                      <div>
                        <p className="text-sm font-medium">{sub.assignment.title}</p>
                        <p className="text-xs text-muted-foreground">{sub.student.firstName} {sub.student.lastName} ({sub.student.studentId})</p>
                      </div>
                      <Badge variant="warning">Pending</Badge>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No pending submissions.</p>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
