"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Trophy, Plus } from "lucide-react";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function TeacherGrades() {
  const { data, loading, refetch } = useFetch<{ grades: { id: string; finalGrade: string; gpa: number; semester: number; year: number; student: { firstName: string; lastName: string; studentId: string }; course: { name: string; code: string; credits: number } }[] }>("/api/grades");
  const { data: coursesData } = useFetch<{ courseAssignments: { id: string; courses: { id: string; name: string; code: string } }[] }>("/api/teacher/dashboard");
  const { data: studentsData } = useFetch<{ students: { id: string; firstName: string; lastName: string; studentId: string }[] }>("/api/students?limit=200");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ studentId: "", courseId: "", semester: "1", year: new Date().getFullYear().toString(), finalGrade: "", gpa: "" });

  const handleCreate = async () => {
    if (!form.studentId || !form.courseId || !form.finalGrade || !form.gpa) return;
    setCreating(true);
    try {
      await fetch("/api/grades", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ...form,
          semester: parseInt(form.semester),
          year: parseInt(form.year),
          gpa: parseFloat(form.gpa),
        }),
      });
      setShowCreate(false);
      setForm({ studentId: "", courseId: "", semester: "1", year: new Date().getFullYear().toString(), finalGrade: "", gpa: "" });
      refetch();
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setCreating(false);
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Grades</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Enter Grade
          </Button>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Trophy className="h-5 w-5" /> Grade Book
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-muted-foreground">Loading...</div>
            ) : data?.grades?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student</TableHead>
                    <TableHead>Course</TableHead>
                    <TableHead>Grade</TableHead>
                    <TableHead>GPA</TableHead>
                    <TableHead>Credits</TableHead>
                    <TableHead>Semester</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.grades.map((g) => (
                    <TableRow key={g.id}>
                      <TableCell>{g.student.firstName} {g.student.lastName} ({g.student.studentId})</TableCell>
                      <TableCell>{g.course.name} ({g.course.code})</TableCell>
                      <TableCell className="font-bold">{g.finalGrade}</TableCell>
                      <TableCell>{g.gpa.toFixed(1)}</TableCell>
                      <TableCell>{g.course.credits}</TableCell>
                      <TableCell>Sem {g.semester}, {g.year}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <p className="text-muted-foreground text-center py-4">No grades entered yet.</p>
            )}
          </CardContent>
        </Card>

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Enter Grade</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Student</Label>
                <Select value={form.studentId} onChange={(e) => setForm({ ...form, studentId: e.target.value })}>
                  <option value="">Select student</option>
                  {studentsData?.students?.map((s) => (
                    <option key={s.id} value={s.id}>{s.firstName} {s.lastName} ({s.studentId})</option>
                  ))}
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Course</Label>
                <Select value={form.courseId} onChange={(e) => setForm({ ...form, courseId: e.target.value })}>
                  <option value="">Select course</option>
                  {coursesData?.courseAssignments?.map((ca) => (
                    <option key={ca.courses.id} value={ca.courses.id}>{ca.courses.name} ({ca.courses.code})</option>
                  ))}
                </Select>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Final Grade</Label>
                  <Select value={form.finalGrade} onChange={(e) => setForm({ ...form, finalGrade: e.target.value })}>
                    <option value="">Select grade</option>
                    {["A", "A-", "B+", "B", "B-", "C+", "C", "C-", "D+", "D", "F"].map((g) => (
                      <option key={g} value={g}>{g}</option>
                    ))}
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>GPA</Label>
                  <Input type="number" step="0.1" min="0" max="4" value={form.gpa} onChange={(e) => setForm({ ...form, gpa: e.target.value })} />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Semester</Label>
                  <Select value={form.semester} onChange={(e) => setForm({ ...form, semester: e.target.value })}>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Year</Label>
                  <Input type="number" value={form.year} onChange={(e) => setForm({ ...form, year: e.target.value })} />
                </div>
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.studentId || !form.courseId || !form.finalGrade}>
                {creating ? "Saving..." : "Save Grade"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
