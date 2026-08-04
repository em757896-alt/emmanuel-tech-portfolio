"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { Brain, Upload, Send, Lock, ShieldCheck } from "lucide-react";
import { formatDate } from "@/lib/utils";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function StudentResearch() {
  const { data, loading, refetch } = useFetch<{ papers: { id: string; title: string; abstract: string | null; status: string; lecturerApproved: boolean; hodApproved: boolean; rejected: boolean; reviewNotes: string | null; fileName: string | null; fileUrl: string | null; uploadedAt: string; advisor: { firstName: string; lastName: string } | null }[] }>("/api/research");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ title: "", abstract: "" });
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  const handleCreate = async () => {
    if (!form.title) return;
    setCreating(true);
    try {
      let fileUrl = null;
      let fileName = null;

      if (selectedFile) {
        const formData = new FormData();
        formData.append("file", selectedFile);
        formData.append("bucket", "research-papers");
        formData.append("folder", "research");
        const uploadRes = await fetch("/api/upload", { method: "POST", body: formData });
        const uploadData = await uploadRes.json();
        fileUrl = uploadData.url;
        fileName = uploadData.fileName;
      }

      await fetch("/api/research", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ title: form.title, abstract: form.abstract, fileUrl, fileName }),
      });

      setShowCreate(false);
      setForm({ title: "", abstract: "" });
      setSelectedFile(null);
      refetch();
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setCreating(false);
    }
  };

  const handleSubmit = async (id: string) => {
    await fetch("/api/research", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, status: "SUBMITTED" }),
    });
    refetch();
  };

  const verificationBadge = (paper: { lecturerApproved: boolean; hodApproved: boolean; rejected: boolean }) => {
    if (paper.rejected) return <Badge variant="destructive">Rejected</Badge>;
    if (paper.lecturerApproved && paper.hodApproved) return <Badge variant="success"><ShieldCheck className="h-3 w-3" /> Verified</Badge>;
    if (paper.lecturerApproved) return <Badge variant="warning"><Lock className="h-3 w-3" /> Waiting HOD</Badge>;
    if (paper.hodApproved) return <Badge variant="warning"><Lock className="h-3 w-3" /> Waiting Lecturer</Badge>;
    return <Badge variant="outline">Pending</Badge>;
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Research Papers</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Upload className="h-4 w-4" /> New Research Paper
          </Button>
        </div>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.papers?.length ? (
          <div className="space-y-4">
            {data.papers.map((paper) => (
              <Card key={paper.id}>
                <CardContent className="p-6">
                  <div className="flex items-start justify-between">
                    <div className="space-y-1">
                      <div className="flex items-center gap-2">
                        <Brain className="h-5 w-5 text-primary" />
                        <h3 className="font-semibold">{paper.title}</h3>
                        {verificationBadge(paper)}
                      </div>
                      {paper.abstract && <p className="text-sm text-muted-foreground line-clamp-2">{paper.abstract}</p>}
                      {paper.advisor && (
                        <p className="text-xs text-muted-foreground">Advisor: {paper.advisor.firstName} {paper.advisor.lastName}</p>
                      )}
                      {paper.reviewNotes && (
                        <div className="mt-2 p-2 bg-muted rounded text-sm">
                          <p className="font-medium text-xs">Reviewer Notes:</p>
                          {paper.reviewNotes}
                        </div>
                      )}
                      <p className="text-xs text-muted-foreground">Uploaded: {formatDate(paper.uploadedAt)}</p>
                    </div>
                    <div className="flex gap-2">
                      {paper.fileUrl && (
                        <a href={paper.fileUrl} target="_blank" rel="noopener noreferrer">
                          <Button variant="outline" size="sm">View File</Button>
                        </a>
                      )}
                      {paper.status === "DRAFT" && (
                        <Button size="sm" onClick={() => handleSubmit(paper.id)}>
                          <Send className="h-4 w-4" /> Submit
                        </Button>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No research papers yet. Create your first one!
            </CardContent>
          </Card>
        )}

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>New Research Paper</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Title</Label>
                <Input placeholder="Research paper title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Abstract</Label>
                <Textarea placeholder="Brief abstract of your research" value={form.abstract} onChange={(e) => setForm({ ...form, abstract: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Paper File (Optional)</Label>
                <Input type="file" onChange={(e) => setSelectedFile(e.target.files?.[0] || null)} accept=".pdf,.doc,.docx" />
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.title}>
                {creating ? "Creating..." : "Create"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
