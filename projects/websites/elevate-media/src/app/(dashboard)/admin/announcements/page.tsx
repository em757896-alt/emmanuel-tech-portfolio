"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select } from "@/components/ui/select";
import { Megaphone, Plus } from "lucide-react";
import { formatDateTime } from "@/lib/utils";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function AdminAnnouncements() {
  const { data, loading, refetch } = useFetch<{ announcements: { id: string; title: string; content: string; target: string; createdAt: string; author: { name: string | null }; course: { name: string; code: string } | null }[] }>("/api/announcements");
  const { data: coursesData } = useFetch<{ courses: { id: string; name: string; code: string }[] }>("/api/courses");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ title: "", content: "", courseId: "", target: "ALL" });

  const handleCreate = async () => {
    if (!form.title || !form.content) return;
    setCreating(true);
    try {
      await fetch("/api/announcements", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...form, courseId: form.courseId || undefined }),
      });
      setShowCreate(false);
      setForm({ title: "", content: "", courseId: "", target: "ALL" });
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
          <h1 className="text-3xl font-bold">Announcements</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Post Announcement
          </Button>
        </div>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.announcements?.length ? (
          <div className="space-y-4">
            {data.announcements.map((ann) => (
              <Card key={ann.id}>
                <CardContent className="p-4">
                  <div className="flex items-start gap-3">
                    <Megaphone className="h-5 w-5 text-primary shrink-0 mt-1" />
                    <div className="flex-1">
                      <h3 className="font-semibold">{ann.title}</h3>
                      <p className="text-sm text-muted-foreground whitespace-pre-wrap">{ann.content}</p>
                      <div className="flex items-center gap-2 mt-2 text-xs text-muted-foreground">
                        <span>By {ann.author.name || "Admin"}</span>
                        {ann.course && <span>{ann.course.code}</span>}
                        <span>Target: {ann.target}</span>
                        <span>{formatDateTime(ann.createdAt)}</span>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">No announcements yet.</CardContent>
          </Card>
        )}

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Post Announcement</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Title</Label>
                <Input value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Content</Label>
                <Textarea rows={4} value={form.content} onChange={(e) => setForm({ ...form, content: e.target.value })} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Course (Optional)</Label>
                  <Select value={form.courseId} onChange={(e) => setForm({ ...form, courseId: e.target.value })}>
                    <option value="">All courses</option>
                    {coursesData?.courses?.map((c) => (
                      <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Target</Label>
                  <Select value={form.target} onChange={(e) => setForm({ ...form, target: e.target.value })}>
                    <option value="ALL">All</option>
                    <option value="STUDENTS">Students</option>
                    <option value="TEACHERS">Teachers</option>
                  </Select>
                </div>
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.title || !form.content}>
                {creating ? "Posting..." : "Post"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
