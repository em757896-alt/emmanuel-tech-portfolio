"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { BookOpen, Plus, Trash2 } from "lucide-react";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function AdminCourses() {
  const { data, loading, refetch } = useFetch<{ courses: { id: string; name: string; code: string; credits: number; semester: number; year: number; department: { name: string } | null; _count: { enrollments: number } }[] }>("/api/courses");
  const { data: deptData } = useFetch<{ departments: { id: string; name: string; code: string }[] }>("/api/departments");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ name: "", code: "", description: "", credits: "3", departmentId: "", semester: "1", year: new Date().getFullYear().toString() });

  const handleCreate = async () => {
    if (!form.name || !form.code) return;
    setCreating(true);
    try {
      await fetch("/api/courses", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...form, credits: parseInt(form.credits), semester: parseInt(form.semester), year: parseInt(form.year) }),
      });
      setShowCreate(false);
      setForm({ name: "", code: "", description: "", credits: "3", departmentId: "", semester: "1", year: new Date().getFullYear().toString() });
      refetch();
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setCreating(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Delete this course?")) return;
    await fetch(`/api/courses/${id}`, { method: "DELETE" });
    refetch();
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Courses</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Add Course
          </Button>
        </div>

        <Card>
          <CardContent className="p-0">
            {loading ? (
              <div className="p-6 text-muted-foreground">Loading...</div>
            ) : data?.courses?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Code</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Credits</TableHead>
                    <TableHead>Department</TableHead>
                    <TableHead>Sem/Year</TableHead>
                    <TableHead>Enrolled</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.courses.map((course) => (
                    <TableRow key={course.id}>
                      <TableCell className="font-mono">{course.code}</TableCell>
                      <TableCell>{course.name}</TableCell>
                      <TableCell>{course.credits}</TableCell>
                      <TableCell>{course.department?.name || "—"}</TableCell>
                      <TableCell>Sem {course.semester}, {course.year}</TableCell>
                      <TableCell>{course._count.enrollments}</TableCell>
                      <TableCell>
                        <Button variant="destructive" size="sm" onClick={() => handleDelete(course.id)}>
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <div className="p-6 text-center text-muted-foreground">No courses found.</div>
            )}
          </CardContent>
        </Card>

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Course</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Course Name</Label>
                  <Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <Label>Course Code</Label>
                  <Input value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Description</Label>
                <Textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
              </div>
              <div className="grid grid-cols-3 gap-4">
                <div className="space-y-2">
                  <Label>Credits</Label>
                  <Input type="number" value={form.credits} onChange={(e) => setForm({ ...form, credits: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <Label>Semester</Label>
                  <Select value={form.semester} onChange={(e) => setForm({ ...form, semester: e.target.value })}>
                    <option value="1">1</option>
                    <option value="2">2</option>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Year</Label>
                  <Input type="number" value={form.year} onChange={(e) => setForm({ ...form, year: e.target.value })} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Department</Label>
                <Select value={form.departmentId} onChange={(e) => setForm({ ...form, departmentId: e.target.value })}>
                  <option value="">Select department</option>
                  {deptData?.departments?.map((d) => (
                    <option key={d.id} value={d.id}>{d.name}</option>
                  ))}
                </Select>
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.name || !form.code}>
                {creating ? "Creating..." : "Create"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
