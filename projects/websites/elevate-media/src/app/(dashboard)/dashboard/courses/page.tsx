"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { BookOpen } from "lucide-react";

export default function StudentCourses() {
  const { data, loading } = useFetch<{ enrollments: { id: string; course: { id: string; name: string; code: string; credits: number; description: string | null }; enrolledAt: string }[] }>("/api/enrollments?filter=student");

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">My Courses</h1>

        {loading ? (
          <div className="text-muted-foreground">Loading courses...</div>
        ) : data?.enrollments?.length ? (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {data.enrollments.map((enrollment) => (
              <Card key={enrollment.id} className="hover:shadow-md transition-shadow">
                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between">
                    <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                      <BookOpen className="h-5 w-5 text-primary" />
                    </div>
                    <Badge variant="info">{enrollment.course.credits} Credits</Badge>
                  </div>
                  <CardTitle className="text-lg mt-2">{enrollment.course.name}</CardTitle>
                  <p className="text-sm text-muted-foreground">{enrollment.course.code}</p>
                </CardHeader>
                <CardContent>
                  {enrollment.course.description && (
                    <p className="text-sm text-muted-foreground line-clamp-2">{enrollment.course.description}</p>
                  )}
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              You are not enrolled in any courses yet.
            </CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
