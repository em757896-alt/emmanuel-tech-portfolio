"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { Brain } from "lucide-react";
import { formatDate } from "@/lib/utils";
import { useState } from "react";

export default function TeacherResearch() {
  const { data, loading, refetch } = useFetch<{ papers: { id: string; title: string; abstract: string | null; status: string; reviewNotes: string | null; fileName: string | null; fileUrl: string | null; uploadedAt: string; student: { firstName: string; lastName: string; studentId: string } }[] }>("/api/research");
  const [reviewingId, setReviewingId] = useState<string | null>(null);
  const [notes, setNotes] = useState("");

  const handleReview = async (id: string, status: string) => {
    await fetch("/api/research", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, status, reviewNotes: notes }),
    });
    setReviewingId(null);
    setNotes("");
    refetch();
  };

  const getStatusVariant = (status: string) => {
    switch (status) {
      case "APPROVED": return "success";
      case "REJECTED": return "destructive";
      case "SUBMITTED": case "UNDER_REVIEW": return "info";
      default: return "secondary";
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Research Papers</h1>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.papers?.length ? (
          <div className="space-y-4">
            {data.papers.map((paper) => (
              <Card key={paper.id}>
                <CardContent className="p-6">
                  <div className="space-y-3">
                    <div className="flex items-start justify-between">
                      <div className="space-y-1">
                        <div className="flex items-center gap-2">
                          <Brain className="h-5 w-5 text-primary" />
                          <h3 className="font-semibold">{paper.title}</h3>
                          <Badge variant={getStatusVariant(paper.status)}>{paper.status.replace("_", " ")}</Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">
                          By: {paper.student.firstName} {paper.student.lastName} ({paper.student.studentId})
                        </p>
                        {paper.abstract && <p className="text-sm text-muted-foreground line-clamp-2">{paper.abstract}</p>}
                        <p className="text-xs text-muted-foreground">Uploaded: {formatDate(paper.uploadedAt)}</p>
                      </div>
                      <div className="flex gap-2">
                        {paper.fileUrl && (
                          <a href={paper.fileUrl} target="_blank" rel="noopener noreferrer">
                            <Button variant="outline" size="sm">View File</Button>
                          </a>
                        )}
                      </div>
                    </div>

                    {reviewingId === paper.id ? (
                      <div className="flex flex-col gap-2 p-3 bg-muted rounded-lg">
                        <Textarea placeholder="Review notes..." value={notes} onChange={(e) => setNotes(e.target.value)} />
                        <div className="flex gap-2">
                          <Button size="sm" variant="default" onClick={() => handleReview(paper.id, "APPROVED")}>Approve</Button>
                          <Button size="sm" variant="destructive" onClick={() => handleReview(paper.id, "REJECTED")}>Reject</Button>
                          <Button size="sm" variant="outline" onClick={() => setReviewingId(null)}>Cancel</Button>
                        </div>
                      </div>
                    ) : paper.status === "SUBMITTED" || paper.status === "DRAFT" ? (
                      <Button size="sm" onClick={() => { setReviewingId(paper.id); setNotes(""); }}>
                        Review
                      </Button>
                    ) : paper.reviewNotes ? (
                      <div className="p-2 bg-muted rounded text-sm">
                        <p className="font-medium text-xs">Your review notes:</p>
                        {paper.reviewNotes}
                      </div>
                    ) : null}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No research papers to review.
            </CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
