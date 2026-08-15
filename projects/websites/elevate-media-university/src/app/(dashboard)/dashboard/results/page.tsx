"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Download, FileCheck2, FolderOpen, Lock, ShieldCheck } from "lucide-react";
import { formatDate } from "@/lib/utils";
import { useState } from "react";

interface GradeRow {
  id: string;
  finalGrade: string;
  gpa: number;
  semester: number;
  year: number;
  course: { name: string; code: string; credits: number };
}

interface ResultDoc {
  id: string;
  title: string;
  description: string | null;
  grade: string | null;
  fileName: string;
  fileSize: number;
  fileUrl: string;
  uploadedAt: string;
  released: boolean;
  status: string;
  lecturerApproved: boolean;
  hodApproved: boolean;
  savedToPoe: boolean;
  course: { name: string; code: string } | null;
}

const isVerified = (r: ResultDoc) => r.lecturerApproved && r.hodApproved;

export default function StudentResults() {
  const { data, loading } = useFetch<{ grades: GradeRow[]; cumulativeGpa: number }>("/api/grades");
  const { data: resultData, loading: resultLoading, refetch } = useFetch<{ results: ResultDoc[] }>("/api/results");
  const [saving, setSaving] = useState<string | null>(null);
  const [saveError, setSaveError] = useState<string | null>(null);

  const handleSaveAsPoe = async (resultId: string) => {
    setSaving(resultId);
    setSaveError(null);
    try {
      const res = await fetch("/api/results/save-poe", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ resultId }),
      });
      const result = await res.json();
      if (!res.ok) {
        setSaveError(result.error || "Could not save as POE");
      }
      refetch();
    } catch {
      setSaveError("Network error");
    } finally {
      setSaving(null);
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Results & Transcript</h1>
          {data?.cumulativeGpa !== undefined && (
            <Card className="px-6 py-3">
              <div className="text-center">
                <p className="text-sm text-muted-foreground">Cumulative GPA</p>
                <p className="text-2xl font-bold">{data.cumulativeGpa}</p>
              </div>
            </Card>
          )}
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Academic Transcript</CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-muted-foreground">Loading grades...</div>
            ) : data?.grades?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course Code</TableHead>
                    <TableHead>Course Name</TableHead>
                    <TableHead>Credits</TableHead>
                    <TableHead>Grade</TableHead>
                    <TableHead>GPA</TableHead>
                    <TableHead>Semester</TableHead>
                    <TableHead>Year</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.grades.map((grade) => (
                    <TableRow key={grade.id}>
                      <TableCell className="font-mono">{grade.course.code}</TableCell>
                      <TableCell>{grade.course.name}</TableCell>
                      <TableCell>{grade.course.credits}</TableCell>
                      <TableCell>
                        <Badge variant={grade.gpa >= 3.0 ? "success" : grade.gpa >= 2.0 ? "warning" : "destructive"}>
                          {grade.finalGrade}
                        </Badge>
                      </TableCell>
                      <TableCell>{grade.gpa.toFixed(1)}</TableCell>
                      <TableCell>Sem {grade.semester}</TableCell>
                      <TableCell>{grade.year}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <p className="text-muted-foreground text-center py-4">No grades available yet.</p>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FileCheck2 className="h-5 w-5" />
              Verified Results (from unit lecturer / HOD)
            </CardTitle>
          </CardHeader>
          <CardContent>
            {saveError && <p className="text-sm text-destructive mb-3">{saveError}</p>}
            {resultLoading ? (
              <div className="text-muted-foreground">Loading results...</div>
            ) : resultData?.results?.length ? (
              <div className="space-y-3">
                {resultData.results.map((r) => (
                  <div key={r.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                    <div className="flex items-center gap-3">
                      <div className="h-10 w-10 rounded bg-primary/10 flex items-center justify-center">
                        <FolderOpen className="h-5 w-5 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium text-sm">{r.title}</p>
                        <p className="text-xs text-muted-foreground">
                          {r.course?.name} ({r.course?.code}) · {formatDate(r.uploadedAt)}
                          {r.grade && <> · Grade: <span className="font-semibold text-primary">{r.grade}</span></>}
                        </p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      {isVerified(r) ? (
                        <Badge variant="success"><ShieldCheck className="h-3 w-3" /> Verified</Badge>
                      ) : (
                        <Badge variant="warning"><Lock className="h-3 w-3" /> Waiting HOD verification</Badge>
                      )}
                      {isVerified(r) ? (
                        <a href={r.fileUrl} target="_blank" rel="noopener noreferrer">
                          <Button variant="outline" size="sm"><Download className="h-4 w-4" /> Download</Button>
                        </a>
                      ) : (
                        <Button variant="outline" size="sm" disabled title="Download unlocks after the HOD verifies this result.">
                          <Lock className="h-4 w-4" /> Download locked
                        </Button>
                      )}
                      {r.savedToPoe ? (
                        <Badge variant="success">Saved to POE</Badge>
                      ) : (
                        <Button size="sm" onClick={() => handleSaveAsPoe(r.id)} disabled={saving === r.id}>
                          {saving === r.id ? "Saving..." : "Save as POE"}
                        </Button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-muted-foreground text-center py-4">
                No verified results released yet. Results released by your unit lecturer or HOD will appear here.
              </p>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
