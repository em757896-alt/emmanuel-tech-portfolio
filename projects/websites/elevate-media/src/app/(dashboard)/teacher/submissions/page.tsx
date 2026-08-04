"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Star } from "lucide-react";
import { useState } from "react";

export default function TeacherSubmissions() {
  const { data, loading, refetch } = useFetch<{ submissions: { id: string; status: string; marks: number | null; feedback: string | null; submittedAt: string; assignment: { title: string; totalMarks: number }; student: { firstName: string; lastName: string; studentId: string } }[] }>("/api/submissions");
  const [gradingId, setGradingId] = useState<string | null>(null);
  const [gradeForm, setGradeForm] = useState({ marks: "", feedback: "" });

  const handleGrade = async (id: string) => {
    try {
      await fetch("/api/submissions", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id,
          marks: parseInt(gradeForm.marks),
          feedback: gradeForm.feedback,
          status: "GRADED",
        }),
      });
      setGradingId(null);
      setGradeForm({ marks: "", feedback: "" });
      refetch();
    } catch (error) {
      console.error("Error:", error);
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Submissions</h1>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Star className="h-5 w-5" />
              Student Submissions
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-muted-foreground">Loading...</div>
            ) : data?.submissions?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student</TableHead>
                    <TableHead>Assignment</TableHead>
                    <TableHead>Submitted</TableHead>
                    <TableHead>Marks</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.submissions.map((sub) => (
                    <TableRow key={sub.id}>
                      <TableCell>
                        <p className="font-medium">{sub.student.firstName} {sub.student.lastName}</p>
                        <p className="text-xs text-muted-foreground">{sub.student.studentId}</p>
                      </TableCell>
                      <TableCell>{sub.assignment.title}</TableCell>
                      <TableCell>{new Date(sub.submittedAt).toLocaleDateString()}</TableCell>
                      <TableCell>{sub.marks !== null ? `${sub.marks}/${sub.assignment.totalMarks}` : "—"}</TableCell>
                      <TableCell>
                        <Badge variant={sub.status === "GRADED" ? "success" : sub.status === "RETURNED" ? "warning" : "secondary"}>
                          {sub.status}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        {gradingId === sub.id ? (
                          <div className="flex gap-2 items-end">
                            <div>
                              <Label className="text-xs">Marks</Label>
                              <Input
                                type="number"
                                className="w-20 h-8"
                                placeholder="0"
                                value={gradeForm.marks}
                                onChange={(e) => setGradeForm({ ...gradeForm, marks: e.target.value })}
                              />
                            </div>
                            <div>
                              <Label className="text-xs">Feedback</Label>
                              <Textarea
                                className="w-48 h-8 text-xs"
                                placeholder="Feedback"
                                value={gradeForm.feedback}
                                onChange={(e) => setGradeForm({ ...gradeForm, feedback: e.target.value })}
                              />
                            </div>
                            <Button size="sm" onClick={() => handleGrade(sub.id)}>Save</Button>
                            <Button size="sm" variant="outline" onClick={() => setGradingId(null)}>Cancel</Button>
                          </div>
                        ) : (
                          <Button size="sm" variant="outline" onClick={() => setGradingId(sub.id)}>
                            {sub.status === "GRADED" ? "Re-grade" : "Grade"}
                          </Button>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <p className="text-muted-foreground text-center py-4">No submissions yet.</p>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
