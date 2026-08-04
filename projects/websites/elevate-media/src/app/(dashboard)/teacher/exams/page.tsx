"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { ClipboardCheck, Plus } from "lucide-react";
import { formatDate } from "@/lib/utils";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function TeacherExams() {
  const { data, loading, refetch } = useFetch<{ exams: { id: string; title: string; date: string; totalMarks: number; weight: number; course: { name: string; code: string }; _count: { examResults: number } }[] }>("/api/exams");
  const { data: coursesData } = useFetch<{ courseAssignments: { id: string; course: { id: string; name: string; code: string } }[] }>("/api/teacher/dashboard");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ title: "", courseId: "", date: "", totalMarks: "100", weight: "30" });

  const handleCreate = async () => {
    if (!form.title || !form.courseId || !form.date) return;
    setCreating(true);
    try {
      await fetch("/api/exams", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ...form,
          totalMarks: parseInt(form.totalMarks),
          weight: parseFloat(form.weight),
        }),
      });
      setShowCreate(false);
      setForm({ title: "", courseId: "", date: "", totalMarks: "100", weight: "30" });
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
          <h1 className="text-3xl font-bold">Exams</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Create Exam
          </Button>
        </div>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.exams?.length ? (
          <div className="space-y-4">
            {data.exams.map((exam) => (
              <Card key={exam.id}>
                <CardContent className="p-4">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <ClipboardCheck className="h-5 w-5 text-primary" />
                      <div>
                        <p className="font-medium">{exam.title}</p>
                        <div className="flex items-center gap-2 text-xs text-muted-foreground">
                          <Badge variant="outline">{exam.course.code}</Badge>
                          <span>{formatDate(exam.date)}</span>
                          <span>Max: {exam.totalMarks} marks</span>
                          <span>Weight: {exam.weight}%</span>
                        </div>
                      </div>
                    </div>
                    <Badge variant="secondary">{exam._count.examResults} results</Badge>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No exams created yet.
            </CardContent>
          </Card>
        )}

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Create Exam</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Title</Label>
                <Input placeholder="Exam title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Course</Label>
                <Select value={form.courseId} onChange={(e) => setForm({ ...form, courseId: e.target.value })}>
                  <option value="">Select course</option>
                  {coursesData?.courseAssignments?.map((ca) => (
                    <option key={ca.course.id} value={ca.course.id}>{ca.course.name} ({ca.course.code})</option>
                  ))}
                </Select>
              </div>
              <div className="grid grid-cols-3 gap-4">
                <div className="space-y-2">
                  <Label>Date</Label>
                  <Input type="datetime-local" value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <Label>Total Marks</Label>
                  <Input type="number" value={form.totalMarks} onChange={(e) => setForm({ ...form, totalMarks: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <Label>Weight (%)</Label>
                  <Input type="number" value={form.weight} onChange={(e) => setForm({ ...form, weight: e.target.value })} />
                </div>
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.title || !form.courseId || !form.date}>
                {creating ? "Creating..." : "Create"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
