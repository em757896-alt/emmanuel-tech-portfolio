"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { BookMarked, Plus, Trash2 } from "lucide-react";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function AdminEnrollments() {
  const { data, loading, refetch } = useFetch<{ enrollments: { id: string; enrolledAt: string; student: { id: string; firstName: string; lastName: string; studentId: string }; course: { id: string; name: string; code: string } }[] }>("/api/enrollments");
  const { data: studentsData } = useFetch<{ students: { id: string; firstName: string; lastName: string; studentId: string }[] }>("/api/students?limit=500");
  const { data: coursesData } = useFetch<{ courses: { id: string; name: string; code: string }[] }>("/api/courses");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ studentId: "", courseId: "" });

  const handleCreate = async () => {
    if (!form.studentId || !form.courseId) return;
    setCreating(true);
    try {
      await fetch("/api/enrollments", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      setShowCreate(false);
      setForm({ studentId: "", courseId: "" });
      refetch();
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setCreating(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Remove this enrollment?")) return;
    await fetch(`/api/enrollments?id=${id}`, { method: "DELETE" });
    refetch();
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Enrollments</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Add Enrollment
          </Button>
        </div>

        <Card>
          <CardContent className="p-0">
            {loading ? (
              <div className="p-6 text-muted-foreground">Loading...</div>
            ) : data?.enrollments?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student</TableHead>
                    <TableHead>Student ID</TableHead>
                    <TableHead>Course</TableHead>
                    <TableHead>Course Code</TableHead>
                    <TableHead>Enrolled</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.enrollments.map((enrollment) => (
                    <TableRow key={enrollment.id}>
                      <TableCell>{enrollment.student.firstName} {enrollment.student.lastName}</TableCell>
                      <TableCell className="font-mono">{enrollment.student.studentId}</TableCell>
                      <TableCell>{enrollment.course.name}</TableCell>
                      <TableCell className="font-mono">{enrollment.course.code}</TableCell>
                      <TableCell>{new Date(enrollment.enrolledAt).toLocaleDateString()}</TableCell>
                      <TableCell>
                        <Button variant="destructive" size="sm" onClick={() => handleDelete(enrollment.id)}>
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <div className="p-6 text-center text-muted-foreground">No enrollments found.</div>
            )}
          </CardContent>
        </Card>

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Enrollment</DialogTitle>
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
                  {coursesData?.courses?.map((c) => (
                    <option key={c.id} value={c.id}>{c.name} ({c.code})</option>
                  ))}
                </Select>
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.studentId || !form.courseId}>
                {creating ? "Enrolling..." : "Enroll"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
