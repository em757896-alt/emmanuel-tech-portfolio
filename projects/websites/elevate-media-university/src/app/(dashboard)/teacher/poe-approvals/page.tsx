"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { FolderOpen, Download, ThumbsUp, ThumbsDown } from "lucide-react";
import { useState } from "react";
import { formatDate } from "@/lib/utils";

interface PoeDoc {
  id: string;
  title: string;
  description: string | null;
  fileUrl: string;
  fileName: string;
  fileSize: number;
  uploadedAt: string;
  status: string;
  lecturerApproved: boolean;
  hodApproved: boolean;
  source: string;
  student: { firstName: string; lastName: string; studentId: string } | null;
  canApproveAs: { lecturer: boolean; hod: boolean };
}

function statusBadge(d: PoeDoc) {
  if (d.status === "APPROVED") return <Badge variant="success">Verified</Badge>;
  if (d.status === "REJECTED") return <Badge variant="destructive">Rejected</Badge>;
  if (d.lecturerApproved || d.hodApproved) return <Badge variant="warning">Partial</Badge>;
  return <Badge variant="outline">Pending</Badge>;
}

export default function TeacherPoeApprovals() {
  const { data, loading, refetch } = useFetch<{ documents: PoeDoc[] }>("/api/poe?scope=approvals");
  const [busy, setBusy] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const act = async (doc: PoeDoc, approve: boolean, as: "lecturer" | "hod") => {
    setBusy(`${doc.id}-${as}-${approve}`);
    setError(null);
    try {
      const res = await fetch("/api/poe/approve", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: doc.id, approve, as }),
      });
      const body = await res.json();
      if (!res.ok) {
        setError(body.error || "Action failed");
      }
      refetch();
    } catch {
      setError("Network error");
    } finally {
      setBusy(null);
    }
  };

  const pending = (data?.documents || []).filter((d) => d.status !== "APPROVED");

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">POE Approvals</h1>
        <p className="text-muted-foreground">
          Review evidence of achievement (POE) submitted by students. Approve as the unit lecturer and/or HOD.
        </p>

        {error && <p className="text-sm text-destructive">{error}</p>}

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FolderOpen className="h-5 w-5" /> Awaiting Approval ({pending.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-muted-foreground">Loading...</div>
            ) : pending.length ? (
              <div className="space-y-4">
                {pending.map((doc) => (
                  <div key={doc.id} className="rounded-lg border p-4 space-y-3">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <p className="font-medium">{doc.title}</p>
                        <p className="text-sm text-muted-foreground">
                          {doc.student?.firstName} {doc.student?.lastName} ({doc.student?.studentId})
                          {" · "}
                          {doc.source === "RESULT" ? "Verified Result" : "Student upload"}
                          {" · "}
                          {formatDate(doc.uploadedAt)}
                        </p>
                        {doc.description && <p className="text-sm text-muted-foreground mt-1">{doc.description}</p>}
                      </div>
                      {statusBadge(doc)}
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                      <a href={doc.fileUrl} target="_blank" rel="noopener noreferrer">
                        <Button variant="outline" size="sm"><Download className="h-4 w-4" /> {doc.fileName}</Button>
                      </a>
                      <span className="flex-1" />
                      {doc.canApproveAs.lecturer && (
                        <>
                          <Button size="sm" variant={doc.lecturerApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => act(doc, !doc.lecturerApproved, "lecturer")}>
                            <ThumbsUp className="h-4 w-4" /> {doc.lecturerApproved ? "Undo Lecturer" : "Approve as Lecturer"}
                          </Button>
                          <Button size="sm" variant="destructive" disabled={busy !== null || doc.lecturerApproved} onClick={() => act(doc, false, "lecturer")}>
                            <ThumbsDown className="h-4 w-4" /> Reject
                          </Button>
                        </>
                      )}
                      {doc.canApproveAs.hod && (
                        <Button size="sm" variant={doc.hodApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => act(doc, !doc.hodApproved, "hod")}>
                          <ThumbsUp className="h-4 w-4" /> {doc.hodApproved ? "Undo HOD" : "Approve as HOD"}
                        </Button>
                      )}
                      {!doc.canApproveAs.lecturer && !doc.canApproveAs.hod && (
                        <span className="text-xs text-muted-foreground">(Not your scope - you can view only)</span>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-muted-foreground text-center py-4">No POE documents awaiting approval.</p>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
