"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { FileText, Upload, Download, Lock, ShieldCheck, Clock, CheckCircle2, MessageSquare } from "lucide-react";
import { formatDate } from "@/lib/utils";
import { useState } from "react";

interface Submission {
  id: string;
  status: string;
  marks: number | null;
  feedback: string | null;
  submittedAt: string;
  fileUrl: string | null;
  fileName: string | null;
}

interface Assignment {
  id: string;
  title: string;
  description: string | null;
  dueDate: string;
  totalMarks: number;
  fileUrl: string | null;
  fileName: string | null;
  released: boolean;
  lecturerApproved: boolean;
  hodApproved: boolean;
  course: { name: string; code: string };
  submission: Submission | null;
}

export default function StudentAssignments() {
  const { data, loading, refetch } = useFetch<{ assignments: Assignment[] }>("/api/assignments");
  const [submitting, setSubmitting] = useState<string | null>(null);
  const [message, setMessage] = useState<{ id: string; text: string; isError: boolean } | null>(null);
  const [selectedFile, setSelectedFile] = useState<Record<string, File | null>>({});

  const handleSubmit = async (assignmentId: string) => {
    const file = selectedFile[assignmentId];
    if (!file) {
      setMessage({ id: assignmentId, text: "Choose a file to upload.", isError: true });
      return;
    }
    setSubmitting(assignmentId);
    setMessage(null);
    try {
      const formData = new FormData();
      formData.append("file", file);
      formData.append("bucket", "assignment-submissions");
      formData.append("folder", "assignments");

      const uploadRes = await fetch("/api/upload", { method: "POST", body: formData });
      const uploadData = await uploadRes.json();

      if (!uploadRes.ok) {
        setMessage({ id: assignmentId, text: uploadData.error || "Upload failed", isError: true });
        return;
      }

      const res = await fetch("/api/submissions", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          assignmentId,
          fileUrl: uploadData.url,
          fileName: uploadData.fileName,
        }),
      });

      if (!res.ok) {
        const err = await res.json();
        setMessage({ id: assignmentId, text: err.error || "Submission failed", isError: true });
        return;
      }

      setSelectedFile((prev) => ({ ...prev, [assignmentId]: null }));
      setMessage({ id: assignmentId, text: "Assignment submitted successfully.", isError: false });
      refetch();
    } catch {
      setMessage({ id: assignmentId, text: "Network error", isError: true });
    } finally {
      setSubmitting(null);
    }
  };

  const statusVariant = (status: string) =>
    status === "GRADED" ? "success" : status === "RETURNED" ? "warning" : "secondary";

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Assignments</h1>

        {loading ? (
          <div className="text-muted-foreground">Loading assignments...</div>
        ) : data?.assignments?.length ? (
          <div className="space-y-4">
            {data.assignments.map((assignment) => (
              <Card key={assignment.id}>
                <CardContent className="p-6">
                  <div className="flex items-start justify-between gap-4">
                    <div className="space-y-1">
                      <div className="flex items-center gap-2">
                        <FileText className="h-5 w-5 text-primary" />
                        <h3 className="font-semibold">{assignment.title}</h3>
                        <Badge variant="outline">{assignment.course.code}</Badge>
                      </div>
                      {assignment.description && (
                        <p className="text-sm text-muted-foreground">{assignment.description}</p>
                      )}
                      <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                        <span className="flex items-center gap-1">
                          <Clock className="h-4 w-4" />
                          Due: {formatDate(assignment.dueDate)}
                        </span>
                        <span>Max Marks: {assignment.totalMarks}</span>
                        {assignment.fileUrl && (assignment.lecturerApproved && assignment.hodApproved ? (
                          <a
                            href={assignment.fileUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex items-center gap-1 text-primary hover:underline"
                          >
                            <Download className="h-4 w-4" />
                            {assignment.fileName || "Download brief"}
                          </a>
                        ) : (
                          <span className="flex items-center gap-1 text-muted-foreground" title="Download unlocks after the HOD verifies this brief.">
                            <Lock className="h-4 w-4" />
                            Brief locked
                          </span>
                        ))}
                        {assignment.fileUrl && (assignment.lecturerApproved && assignment.hodApproved ? (
                          <Badge variant="success"><ShieldCheck className="h-3 w-3" /> Verified</Badge>
                        ) : (
                          <Badge variant="warning"><Lock className="h-3 w-3" /> Waiting HOD</Badge>
                        ))}
                      </div>

                      {assignment.submission && (
                        <div className="mt-3 rounded-lg border bg-muted/50 p-3 space-y-2">
                          <div className="flex flex-wrap items-center gap-3 text-sm">
                            <Badge variant={statusVariant(assignment.submission.status)}>
                              {assignment.submission.status === "GRADED" ? "Marked" : "Submitted"}
                            </Badge>
                            {assignment.submission.status === "GRADED" && assignment.submission.marks !== null && (
                              <span className="font-medium">
                                Marks: {assignment.submission.marks}/{assignment.totalMarks}
                              </span>
                            )}
                            <span className="text-xs text-muted-foreground">
                              Submitted {formatDate(assignment.submission.submittedAt)}
                            </span>
                            {assignment.submission.fileUrl && (
                              <a
                                href={assignment.submission.fileUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-1 text-xs text-primary hover:underline"
                              >
                                <CheckCircle2 className="h-3 w-3" />
                                {assignment.submission.fileName || "View submission"}
                              </a>
                            )}
                          </div>
                          {assignment.submission.feedback && (
                            <p className="flex items-start gap-1 text-xs text-muted-foreground">
                              <MessageSquare className="h-3 w-3 mt-0.5 shrink-0" />
                              Feedback: {assignment.submission.feedback}
                            </p>
                          )}
                        </div>
                      )}

                      {message?.id === assignment.id && (
                        <p className={`text-sm mt-2 ${message.isError ? "text-destructive" : "text-green-600"}`}>
                          {message.text}
                        </p>
                      )}
                    </div>

                    <div className="flex flex-col items-end gap-2 shrink-0">
                      <Input
                        type="file"
                        className="w-56 text-xs"
                        onChange={(e) =>
                          setSelectedFile((prev) => ({
                            ...prev,
                            [assignment.id]: e.target.files?.[0] || null,
                          }))
                        }
                        accept=".pdf,.doc,.docx,.zip,.jpg,.png"
                      />
                      <Button
                        size="sm"
                        onClick={() => handleSubmit(assignment.id)}
                        disabled={submitting === assignment.id}
                      >
                        <Upload className="h-4 w-4" />
                        {submitting === assignment.id ? "Uploading..." : "Submit"}
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No assignments available.
            </CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
