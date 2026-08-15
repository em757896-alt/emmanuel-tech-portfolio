"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { ClipboardCheck, Download, Lock, ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { formatDate } from "@/lib/utils";

interface ExamRow {
  id: string;
  title: string;
  date: string;
  totalMarks: number;
  weight: number;
  released: boolean;
  lecturerApproved: boolean;
  hodApproved: boolean;
  fileUrl?: string | null;
  fileName?: string | null;
  course: { name: string; code: string };
}

export default function StudentExams() {
  const { data, loading } = useFetch<{ exams: ExamRow[] }>("/api/exams");

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Exams</h1>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <ClipboardCheck className="h-5 w-5" />
              Upcoming & Past Exams
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-muted-foreground">Loading exams...</div>
            ) : data?.exams?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Exam</TableHead>
                    <TableHead>Course</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead>Marks</TableHead>
                    <TableHead>Weight</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.exams.map((exam) => {
                    const isPast = new Date(exam.date) < new Date();
                    const verified = exam.lecturerApproved && exam.hodApproved;
                    return (
                      <TableRow key={exam.id}>
                        <TableCell className="font-medium">{exam.title}</TableCell>
                        <TableCell>
                          <Badge variant="outline">{exam.course.code}</Badge>
                        </TableCell>
                        <TableCell>{formatDate(exam.date)}</TableCell>
                        <TableCell>{exam.totalMarks}</TableCell>
                        <TableCell>{exam.weight}%</TableCell>
                        <TableCell>
                          <div className="flex flex-col items-start gap-1">
                            <Badge variant={isPast ? "secondary" : "info"}>
                              {isPast ? "Completed" : "Upcoming"}
                            </Badge>
                            {exam.fileUrl && (verified ? (
                              <a href={exam.fileUrl} target="_blank" rel="noopener noreferrer">
                                <Button variant="outline" size="sm"><Download className="h-4 w-4" /> {exam.fileName ?? "Paper"}</Button>
                              </a>
                            ) : (
                              <Button variant="outline" size="sm" disabled title="Download unlocks after the HOD verifies this paper.">
                                <Lock className="h-4 w-4" /> Locked
                              </Button>
                            ))}
                            {exam.fileUrl && (verified ? (
                              <Badge variant="success"><ShieldCheck className="h-3 w-3" /> Verified</Badge>
                            ) : (
                              <Badge variant="warning"><Lock className="h-3 w-3" /> Waiting HOD</Badge>
                            ))}
                          </div>
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            ) : (
              <p className="text-muted-foreground text-center py-4">No exams scheduled.</p>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
