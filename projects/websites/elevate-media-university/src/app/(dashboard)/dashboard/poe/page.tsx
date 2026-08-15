"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { FolderOpen, Upload, Trash2, Download } from "lucide-react";
import { formatDate } from "@/lib/utils";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function StudentPOE() {
  const { data, loading, refetch } = useFetch<{ documents: { id: string; title: string; description: string | null; fileName: string; fileType: string; fileSize: number; fileUrl: string; uploadedAt: string; status: string; lecturerApproved: boolean; hodApproved: boolean; source: string }[] }>("/api/poe");
  const [showUpload, setShowUpload] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [form, setForm] = useState({ title: "", description: "" });
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  const uploadCount = data?.documents?.filter((d) => d.source === "UPLOAD").length || 0;
  const uploadsRemaining = Math.max(0, 5 - uploadCount);
  const atLimit = uploadCount >= 5;

  const statusBadge = (doc: { status: string; source: string; lecturerApproved: boolean; hodApproved: boolean }) => {
    if (doc.source === "RESULT") {
      return <Badge variant="success">Verified Result</Badge>;
    }
    if (doc.status === "REJECTED") {
      return <Badge variant="destructive">Rejected</Badge>;
    }
    if (doc.status === "APPROVED") {
      return <Badge variant="success">Verified</Badge>;
    }
    if (doc.status === "PARTIALLY_APPROVED") {
      return <Badge variant="warning">
        {doc.lecturerApproved ? "Awaiting HOD" : "Awaiting Lecturer"}
      </Badge>;
    }
    return <Badge variant="secondary">Pending — awaiting unit lecturer & HOD</Badge>;
  };

  const handleUpload = async () => {
    if (!form.title || !selectedFile) return;
    setUploading(true);
    try {
      const formData = new FormData();
      formData.append("file", selectedFile);
      formData.append("bucket", "poe-documents");
      formData.append("folder", "poe");

      const uploadRes = await fetch("/api/upload", { method: "POST", body: formData });
      const uploadData = await uploadRes.json();

      if (uploadRes.ok) {
        await fetch("/api/poe", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            title: form.title,
            description: form.description,
            fileUrl: uploadData.url,
            fileName: uploadData.fileName,
            fileType: uploadData.fileType,
            fileSize: uploadData.fileSize,
          }),
        });
        setShowUpload(false);
        setForm({ title: "", description: "" });
        setSelectedFile(null);
        refetch();
      }
    } catch (error) {
      console.error("Upload error:", error);
    } finally {
      setUploading(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Delete this document?")) return;
    await fetch(`/api/poe?id=${id}`, { method: "DELETE" });
    refetch();
  };

  const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">POE Documents</h1>
            <p className="text-sm text-muted-foreground mt-1">
              {atLimit
                ? "Upload limit reached (5/5). Earlier uploads must be verified by the unit lecturer and the HOD before you can add more."
                : `${uploadCount} of 5 uploads used (${uploadsRemaining} remaining).`}
            </p>
          </div>
          <Button onClick={() => setShowUpload(true)} disabled={atLimit}>
            <Upload className="h-4 w-4" /> Upload Document
          </Button>
        </div>

        {loading ? (
          <div className="text-muted-foreground">Loading documents...</div>
        ) : data?.documents?.length ? (
          <div className="space-y-4">
            {data.documents.map((doc) => (
              <Card key={doc.id}>
                <CardContent className="p-4">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <div className="h-10 w-10 rounded bg-primary/10 flex items-center justify-center">
                        <FolderOpen className="h-5 w-5 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium">{doc.title}</p>
                        <div className="flex items-center gap-2 text-xs text-muted-foreground">
                          <span>{doc.fileName}</span>
                          <span>({formatFileSize(doc.fileSize)})</span>
                          <span>{formatDate(doc.uploadedAt)}</span>
                        </div>
                        {statusBadge(doc)}
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      <a href={doc.fileUrl} target="_blank" rel="noopener noreferrer">
                        <Button variant="outline" size="sm"><Download className="h-4 w-4" /></Button>
                      </a>
                      {doc.source === "UPLOAD" && doc.status !== "APPROVED" && (
                        <Button variant="destructive" size="sm" onClick={() => handleDelete(doc.id)}>
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      )}
                    </div>
                  </div>
                  {doc.description && (
                    <p className="text-sm text-muted-foreground mt-2 ml-13">{doc.description}</p>
                  )}
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No POE documents uploaded yet.
            </CardContent>
          </Card>
        )}

        <Dialog open={showUpload} onOpenChange={setShowUpload}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Upload POE Document</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Title</Label>
                <Input placeholder="Document title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Description (Optional)</Label>
                <Textarea placeholder="Brief description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>File</Label>
                <Input type="file" onChange={(e) => setSelectedFile(e.target.files?.[0] || null)} accept=".pdf,.doc,.docx,.jpg,.png" />
              </div>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowUpload(false)}>Cancel</Button>
              <Button onClick={handleUpload} disabled={uploading || !form.title || !selectedFile}>
                {uploading ? "Uploading..." : "Upload"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
