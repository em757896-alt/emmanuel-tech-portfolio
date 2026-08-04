"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { FolderOpen, Award, FileText, FlaskConical, GraduationCap, ClipboardList, ThumbsUp, ThumbsDown, Download, Send } from "lucide-react";
import { useState } from "react";
import { useSession } from "next-auth/react";
import { formatDate } from "@/lib/utils";

interface PoeDoc {
  id: string;
  title: string;
  fileUrl: string;
  fileName: string;
  uploadedAt: string;
  status: string;
  lecturerApproved: boolean;
  hodApproved: boolean;
  source: string;
  student: { firstName: string; lastName: string; studentId: string } | null;
  canApproveAs: { lecturer: boolean; hod: boolean };
}

interface AttRecord {
  id: string;
  recordDate: string;
  type: string;
  lecturerApproved: boolean;
  hodApproved: boolean;
  adminApproved: boolean;
  approvalStatus: string;
  student: { firstName: string; lastName: string; studentId: string } | null;
  canApproveAs: { lecturer: boolean; hod: boolean; admin: boolean };
}

interface ReleaseItem {
  id: string;
  title: string;
  courseId: string;
  released: boolean;
  status: string;
  lecturerApproved: boolean;
  hodApproved: boolean;
  student?: { firstName: string; lastName: string; studentId: string } | null;
  course?: { name: string; code: string } | null;
  date?: string;
  dueDate?: string;
  uploadedAt?: string;
  canApproveAs: { lecturer: boolean; hod: boolean };
}

function twoState(d: PoeDoc) {
  if (d.status === "APPROVED") return <Badge variant="success">Verified</Badge>;
  if (d.status === "REJECTED") return <Badge variant="destructive">Rejected</Badge>;
  if (d.lecturerApproved || d.hodApproved) return <Badge variant="warning">Partial</Badge>;
  return <Badge variant="outline">Pending</Badge>;
}

function releaseBadge(r: ReleaseItem) {
  if (r.released) {
    return r.lecturerApproved && r.hodApproved
      ? <Badge variant="success">Released · Verified</Badge>
      : <Badge variant="warning">Released · Waiting HOD</Badge>;
  }
  if (r.lecturerApproved) return <Badge variant="outline">Ready to release</Badge>;
  if (r.hodApproved) return <Badge variant="warning">HOD verified · Lecturer pending</Badge>;
  return <Badge variant="outline">Pending</Badge>;
}

export default function HodApprovals() {
  const { data: session } = useSession();
  const isFacultyHod = (session?.user as { facultyHod?: boolean } | undefined)?.facultyHod === true;
  const poe = useFetch<{ documents: PoeDoc[] }>("/api/poe?scope=approvals");
  const att = useFetch<{ records: AttRecord[] }>("/api/attendance?action=approvals");
  const releases = useFetch<{ research: ReleaseItem[]; results: ReleaseItem[]; exams: ReleaseItem[]; assignments: ReleaseItem[] }>("/api/teacher/hod/approvals");
  const [busy, setBusy] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const post = async (url: string, body: Record<string, unknown>, key: string) => {
    setBusy(key);
    setError(null);
    try {
      const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (!res.ok) setError(data.error || "Action failed");
      poe.refetch();
      att.refetch();
      releases.refetch();
    } catch {
      setError("Network error");
    } finally {
      setBusy(null);
    }
  };

  const actPoe = (doc: PoeDoc, approve: boolean, as: "lecturer" | "hod") =>
    post("/api/poe/approve", { id: doc.id, approve, as }, `p-${doc.id}-${as}`);

  const actAtt = (rec: AttRecord, approve: boolean, as: "lecturer" | "hod") =>
    post("/api/attendance/approve", { id: rec.id, approve, as }, `a-${rec.id}-${as}`);

  const actResearch = (id: string, approve: boolean, as: "lecturer" | "hod") =>
    post("/api/research/approve", { id, approve, as }, `r-${id}-${as}`);

  const actRelease = (kind: "results" | "exams" | "assignments", item: ReleaseItem, approve: boolean, as: "lecturer" | "hod", release: boolean) =>
    post(`/api/${kind}/approve`, { id: item.id, approve, as, release }, `${kind}-${item.id}-${as}-${release}`);

  const pendingDocs = (poe.data?.documents || []).filter((d) => d.status !== "APPROVED");
  const pendingAtt = (att.data?.records || []).filter((r) => r.approvalStatus !== "APPROVED");
  const research = releases.data?.research || [];
  const results = releases.data?.results || [];
  const exams = releases.data?.exams || [];
  const assignments = releases.data?.assignments || [];

  const renderReleaseActions = (kind: "results" | "exams" | "assignments", item: ReleaseItem) => (
    <div className="flex flex-wrap items-center gap-2">
      <span className="flex-1" />
      {item.canApproveAs.lecturer && (
        <Button size="sm" variant={item.lecturerApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actRelease(kind, item, !item.lecturerApproved, "lecturer", false)}>
          <ThumbsUp className="h-4 w-4" /> {item.lecturerApproved ? "Undo Lecturer" : "Verify as Lecturer"}
        </Button>
      )}
      {item.canApproveAs.hod && (
        <Button size="sm" variant={item.hodApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actRelease(kind, item, !item.hodApproved, "hod", false)}>
          <ThumbsUp className="h-4 w-4" /> {item.hodApproved ? "Undo HOD" : "Verify as HOD"}
        </Button>
      )}
      {item.canApproveAs.hod && item.lecturerApproved && !item.released && (
        <Button size="sm" variant="default" disabled={busy !== null} onClick={() => actRelease(kind, item, true, "hod", true)}>
          <Send className="h-4 w-4" /> Release to Students
        </Button>
      )}
    </div>
  );

  const renderResearchActions = (item: ReleaseItem) => (
    <div className="flex flex-wrap items-center gap-2">
      {item.canApproveAs.lecturer && (
        <Button size="sm" variant={item.lecturerApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actResearch(item.id, !item.lecturerApproved, "lecturer")}>
          <ThumbsUp className="h-4 w-4" /> {item.lecturerApproved ? "Undo Lecturer" : "Verify as Lecturer"}
        </Button>
      )}
      {item.canApproveAs.hod && (
        <Button size="sm" variant={item.hodApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actResearch(item.id, !item.hodApproved, "hod")}>
          <ThumbsUp className="h-4 w-4" /> {item.hodApproved ? "Undo HOD" : "Verify as HOD"}
        </Button>
      )}
      {item.canApproveAs.hod && !item.hodApproved && item.lecturerApproved && (
        <Button size="sm" variant="destructive" disabled={busy !== null} onClick={() => actResearch(item.id, false, "hod")}>
          <ThumbsDown className="h-4 w-4" /> Reject
        </Button>
      )}
    </div>
  );

  const releaseSection = (kind: "results" | "exams" | "assignments", icon: React.ReactNode, title: string, items: ReleaseItem[], meta: (i: ReleaseItem) => string) => (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">{icon} {title} ({items.length})</CardTitle>
      </CardHeader>
      <CardContent>
        {releases.loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : items.length ? (
          <div className="space-y-4">
            {items.map((item) => (
              <div key={item.id} className="rounded-lg border p-4 space-y-3">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p className="font-medium">{item.title}</p>
                    <p className="text-sm text-muted-foreground">
                      {item.student ? `${item.student.firstName} ${item.student.lastName} (${item.student.studentId}) · ` : ""}
                      {item.course?.name ?? item.courseId}
                      {meta(item) ? ` · ${meta(item)}` : ""}
                    </p>
                  </div>
                  {releaseBadge(item)}
                </div>
                {renderReleaseActions(kind, item)}
              </div>
            ))}
          </div>
        ) : (
          <p className="text-muted-foreground text-center py-4">Nothing here.</p>
        )}
      </CardContent>
    </Card>
  );

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 className="text-3xl font-bold">{isFacultyHod ? "Faculty Approvals" : "Department Approvals"}</h1>
            <p className="text-muted-foreground">
              Verify uploads (POE, attendance, research) and releases (results, exams, assignments) across {isFacultyHod ? "every department under your faculty." : "your department."}
              The HOD can verify on the unit lecturer&apos;s behalf; that action is recorded as &quot;by HOD, by HOD&apos;s powers&quot;.
            </p>
          </div>
          {isFacultyHod && <Badge variant="info">Faculty scope</Badge>}
        </div>

        {error && <p className="text-sm text-destructive">{error}</p>}

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FolderOpen className="h-5 w-5" /> POE Documents ({pendingDocs.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            {poe.loading ? (
              <div className="text-muted-foreground">Loading...</div>
            ) : pendingDocs.length ? (
              <div className="space-y-4">
                {pendingDocs.map((doc) => (
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
                      </div>
                      {twoState(doc)}
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                      <a href={doc.fileUrl} target="_blank" rel="noopener noreferrer">
                        <Button variant="outline" size="sm"><Download className="h-4 w-4" /> {doc.fileName}</Button>
                      </a>
                      <span className="flex-1" />
                      {doc.canApproveAs.lecturer && (
                        <Button size="sm" variant={doc.lecturerApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actPoe(doc, !doc.lecturerApproved, "lecturer")}>
                          <ThumbsUp className="h-4 w-4" /> {doc.lecturerApproved ? "Undo Lecturer" : "Approve as Lecturer"}
                        </Button>
                      )}
                      {doc.canApproveAs.hod && (
                        <Button size="sm" variant={doc.hodApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actPoe(doc, !doc.hodApproved, "hod")}>
                          <ThumbsUp className="h-4 w-4" /> {doc.hodApproved ? "Undo HOD" : "Approve as HOD"}
                        </Button>
                      )}
                      {doc.canApproveAs.lecturer && (
                        <Button size="sm" variant="destructive" disabled={busy !== null || doc.lecturerApproved} onClick={() => actPoe(doc, false, "lecturer")}>
                          <ThumbsDown className="h-4 w-4" /> Reject
                        </Button>
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

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Award className="h-5 w-5" /> Attendance ({pendingAtt.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            {att.loading ? (
              <div className="text-muted-foreground">Loading...</div>
            ) : pendingAtt.length ? (
              <div className="space-y-4">
                {pendingAtt.map((rec) => (
                  <div key={rec.id} className="rounded-lg border p-4 space-y-3">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <p className="font-medium">
                          {rec.student?.firstName} {rec.student?.lastName} ({rec.student?.studentId})
                        </p>
                        <p className="text-sm text-muted-foreground">
                          {rec.type === "CORRECTION" ? "Correction" : "Daily sign-in"} · {rec.recordDate}
                        </p>
                      </div>
                      {rec.approvalStatus === "PARTIALLY_APPROVED" ? <Badge variant="warning">Partial</Badge> : <Badge variant="outline">Pending</Badge>}
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                      <span className="flex-1" />
                      {rec.canApproveAs.lecturer && (
                        <Button size="sm" variant={rec.lecturerApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actAtt(rec, !rec.lecturerApproved, "lecturer")}>
                          <ThumbsUp className="h-4 w-4" /> {rec.lecturerApproved ? "Undo Lecturer" : "Approve as Lecturer"}
                        </Button>
                      )}
                      {rec.canApproveAs.hod && rec.type !== "CORRECTION" && (
                        <Button size="sm" variant={rec.hodApproved ? "outline" : "default"} disabled={busy !== null} onClick={() => actAtt(rec, !rec.hodApproved, "hod")}>
                          <ThumbsUp className="h-4 w-4" /> {rec.hodApproved ? "Undo HOD" : "Approve as HOD"}
                        </Button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-muted-foreground text-center py-4">No attendance records awaiting approval.</p>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FlaskConical className="h-5 w-5" /> Research Papers ({research.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            {releases.loading ? (
              <div className="text-muted-foreground">Loading...</div>
            ) : research.length ? (
              <div className="space-y-4">
                {research.map((item) => (
                  <div key={item.id} className="rounded-lg border p-4 space-y-3">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <p className="font-medium">{item.title}</p>
                        <p className="text-sm text-muted-foreground">
                          {item.student ? `${item.student.firstName} ${item.student.lastName} (${item.student.studentId})` : ""}
                        </p>
                      </div>
                      {item.lecturerApproved && item.hodApproved
                        ? <Badge variant="success">Verified</Badge>
                        : item.lecturerApproved || item.hodApproved
                          ? <Badge variant="warning">Partial</Badge>
                          : <Badge variant="outline">Pending</Badge>}
                    </div>
                    {renderResearchActions(item)}
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-muted-foreground text-center py-4">No research papers in your scope.</p>
            )}
          </CardContent>
        </Card>

        {releaseSection("results", <FileText className="h-5 w-5" />, "Results", results, (i) => i.uploadedAt ? formatDate(i.uploadedAt) : "")}
        {releaseSection("exams", <GraduationCap className="h-5 w-5" />, "Exams", exams, (i) => i.date ? formatDate(i.date) : "")}
        {releaseSection("assignments", <ClipboardList className="h-5 w-5" />, "Assignments", assignments, (i) => i.dueDate ? formatDate(i.dueDate) : "")}
      </div>
    </DashboardLayout>
  );
}
