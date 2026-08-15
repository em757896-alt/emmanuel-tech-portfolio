"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { BookOpen, Users } from "lucide-react";

export default function TeacherCourses() {
  const { data, loading } = useFetch<{ courseAssignments: { id: string; course: { id: string; name: string; code: string; description: string | null; credits: number; _count: { enrollments: number; assignments: number; exams: number } } }[] }>("/api/teacher/dashboard");

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">My Courses</h1>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.courseAssignments?.length ? (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {data.courseAssignments.map((ca) => (
              <Card key={ca.id} className="hover:shadow-md transition-shadow">
                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between">
                    <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                      <BookOpen className="h-5 w-5 text-primary" />
                    </div>
                    <Badge variant="info">{ca.course.credits} Credits</Badge>
                  </div>
                  <CardTitle className="text-lg mt-2">{ca.course.name}</CardTitle>
                  <p className="text-sm text-muted-foreground">{ca.course.code}</p>
                </CardHeader>
                <CardContent>
                  {ca.course.description && (
                    <p className="text-sm text-muted-foreground line-clamp-2 mb-3">{ca.course.description}</p>
                  )}
                  <div className="flex items-center gap-4 text-sm text-muted-foreground">
                    <span className="flex items-center gap-1"><Users className="h-4 w-4" /> {ca.course._count.enrollments} enrolled</span>
                    <span>{ca.course._count.assignments} assignments</span>
                    <span>{ca.course._count.exams} exams</span>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No courses assigned to you.
            </CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
