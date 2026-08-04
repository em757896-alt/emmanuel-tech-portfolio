"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { FileText, Plus } from "lucide-react";
import { formatDate } from "@/lib/utils";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function TeacherAssignments() {
  const { data, loading, refetch } = useFetch<{ assignments: { id: string; title: string; description: string | null; dueDate: string; totalMarks: number; course: { name: string; code: string }; _count: { submissions: number } }[] }>("/api/assignments");
  const { data: coursesData } = useFetch<{ courseAssignments: { id: string; course: { id: string; name: string; code: string } }[] }>("/api/teacher/dashboard");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ title: "", description: "", courseId: "", dueDate: "", totalMarks: "100" });
  const [briefFile, setBriefFile] = useState<File | null>(null);

  const handleCreate = async () => {
    if (!form.title || !form.courseId || !form.dueDate) return;
    setCreating(true);
    try {
      let fileUrl: string | undefined;
      let fileName: string | undefined;

      if (briefFile) {
        const formData = new FormData();
        formData.append("file", briefFile);
        formData.append("bucket", "assignment-submissions");
        formData.append("folder", "briefs");
        const uploadRes = await fetch("/api/upload", { method: "POST", body: formData });
        const uploadData = await uploadRes.json();
        if (uploadRes.ok) {
          fileUrl = uploadData.url;
          fileName = uploadData.fileName;
        }
      }

      await fetch("/api/assignments", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ...form,
          totalMarks: parseInt(form.totalMarks),
          fileUrl,
          fileName,
        }),
      });
      setShowCreate(false);
      setForm({ title: "", description: "", courseId: "", dueDate: "", totalMarks: "100" });
      setBriefFile(null);
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
          <h1 className="text-3xl font-bold">Assignments</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Create Assignment
          </Button>
        </div>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.assignments?.length ? (
          <div className="space-y-4">
            {data.assignments.map((assignment) => (
              <Card key={assignment.id}>
                <CardContent className="p-4">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <FileText className="h-5 w-5 text-primary" />
                      <div>
                        <p className="font-medium">{assignment.title}</p>
                        <div className="flex items-center gap-2 text-xs text-muted-foreground">
                          <Badge variant="outline">{assignment.course.code}</Badge>
                          <span>Due: {formatDate(assignment.dueDate)}</span>
                          <span>Max: {assignment.totalMarks} marks</span>
                        </div>
                      </div>
                    </div>
                    <Badge variant="secondary">{assignment._count.submissions} submissions</Badge>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No assignments created yet.
            </CardContent>
          </Card>
        )}

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Create Assignment</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Title</Label>
                <Input placeholder="Assignment title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Description</Label>
                <Textarea placeholder="Assignment description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
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
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Due Date</Label>
                  <Input type="datetime-local" value={form.dueDate} onChange={(e) => setForm({ ...form, dueDate: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <Label>Total Marks</Label>
                  <Input type="number" value={form.totalMarks} onChange={(e) => setForm({ ...form, totalMarks: e.target.value })} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Assignment Brief (Optional)</Label>
                <Input
                  type="file"
                  onChange={(e) => setBriefFile(e.target.files?.[0] || null)}
                  accept=".pdf,.doc,.docx,.zip"
                />
                {briefFile && <p className="text-xs text-muted-foreground">Attached: {briefFile.name}</p>}
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.title || !form.courseId || !form.dueDate}>
                {creating ? "Creating..." : "Create"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
