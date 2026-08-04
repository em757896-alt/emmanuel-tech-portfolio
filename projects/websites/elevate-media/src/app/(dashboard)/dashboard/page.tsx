"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { StatCard } from "@/components/dashboard/StatCard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { BookOpen, Trophy, FileText, Award, Clock, GraduationCap } from "lucide-react";

interface DashboardData {
  programme: {
    facultyName: string | null;
    departmentName: string | null;
    courseName: string | null;
    courseCode: string | null;
    duration: string | null;
    credits: number | null;
    modeOfLearning: string | null;
  } | null;
  enrollments: { id: string; course: { id: string; name: string; code: string } }[];
  grades: { id: string; finalGrade: string; gpa: number; course: { name: string; code: string } }[];
  submissions: { id: string; status: string; assignment: { title: string } }[];
  records: { id: string; status: string; session: { course: { name: string } } }[];
}

export default function StudentDashboard() {
  const { data, loading } = useFetch<DashboardData>("/api/student/dashboard");

  const appliedCourse = data?.programme?.courseName ? 1 : 0;
  const enrolledCourses = (data?.enrollments?.length || 0) || appliedCourse;
  const averageGpa = data?.grades?.length
    ? (data.grades.reduce((sum, g) => sum + g.gpa, 0) / data.grades.length).toFixed(2)
    : "N/A";
  const pendingSubmissions = data?.submissions?.filter((s) => s.status === "PENDING").length || 0;
  const attendanceRate = data?.records?.length
    ? Math.round((data.records.filter((r) => r.status === "PRESENT").length / data.records.length) * 100) + "%"
    : "N/A";

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold">Student Dashboard</h1>
          <p className="text-muted-foreground">Welcome back! Here&apos;s your academic overview.</p>
        </div>

        {!loading && data?.programme?.courseName && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <GraduationCap className="h-5 w-5" />
                My Programme
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                  <p className="text-xs text-muted-foreground">Faculty</p>
                  <p className="text-sm font-medium">{data.programme.facultyName ?? "—"}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Department</p>
                  <p className="text-sm font-medium">{data.programme.departmentName ?? "—"}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Course</p>
                  <p className="text-sm font-medium">
                    {data.programme.courseName}
                    {data.programme.courseCode && (
                      <span className="text-xs text-muted-foreground"> ({data.programme.courseCode})</span>
                    )}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Duration / Mode</p>
                  <p className="text-sm font-medium">
                    {data.programme.duration ?? "—"}
                    {data.programme.modeOfLearning && ` · ${data.programme.modeOfLearning}`}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard title="Enrolled Courses" value={enrolledCourses} icon={BookOpen} />
          <StatCard title="Cumulative GPA" value={averageGpa} icon={Trophy} />
          <StatCard title="Pending Submissions" value={pendingSubmissions} icon={FileText} />
          <StatCard title="Attendance Rate" value={attendanceRate} icon={Award} />
        </div>

        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Recent Grades</CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-sm text-muted-foreground">Loading...</div>
              ) : data?.grades?.length ? (
                <div className="space-y-3">
                  {data.grades.slice(0, 5).map((grade) => (
                    <div key={grade.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                      <div>
                        <p className="font-medium text-sm">{grade.course.name}</p>
                        <p className="text-xs text-muted-foreground">{grade.course.code}</p>
                      </div>
                      <Badge variant={grade.gpa >= 3.0 ? "success" : grade.gpa >= 2.0 ? "warning" : "destructive"}>
                        {grade.finalGrade} ({grade.gpa.toFixed(1)} GPA)
                      </Badge>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">No grades available yet.</p>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>My Courses</CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="text-sm text-muted-foreground">Loading...</div>
              ) : data?.enrollments?.length ? (
                <div className="space-y-3">
                  {data.enrollments.map((enrollment) => (
                    <div key={enrollment.id} className="flex items-center gap-3 border-b pb-2 last:border-0">
                      <div className="h-9 w-9 rounded bg-primary/10 flex items-center justify-center">
                        <BookOpen className="h-4 w-4 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium text-sm">{enrollment.course.name}</p>
                        <p className="text-xs text-muted-foreground">{enrollment.course.code}</p>
                      </div>
                    </div>
                  ))}
                </div>
              ) : data?.programme?.courseName ? (
                <div className="space-y-3">
                  <div className="flex items-center gap-3 border-b pb-2 last:border-0">
                    <div className="h-9 w-9 rounded bg-primary/10 flex items-center justify-center">
                      <BookOpen className="h-4 w-4 text-primary" />
                    </div>
                    <div>
                      <p className="font-medium text-sm">{data.programme.courseName}</p>
                      <p className="text-xs text-muted-foreground">
                        {[data.programme.courseCode, data.programme.duration, data.programme.modeOfLearning].filter(Boolean).join(" · ")}
                      </p>
                    </div>
                  </div>
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">Not enrolled in any courses.</p>
              )}
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Clock className="h-5 w-5" />
              Recent Submissions
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-sm text-muted-foreground">Loading...</div>
            ) : data?.submissions?.length ? (
              <div className="space-y-3">
                {data.submissions.slice(0, 5).map((sub) => (
                  <div key={sub.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                    <p className="text-sm font-medium">{sub.assignment.title}</p>
                    <Badge variant={
                      sub.status === "GRADED" ? "success" :
                      sub.status === "RETURNED" ? "warning" : "secondary"
                    }>
                      {sub.status}
                    </Badge>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-sm text-muted-foreground">No submissions yet.</p>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
