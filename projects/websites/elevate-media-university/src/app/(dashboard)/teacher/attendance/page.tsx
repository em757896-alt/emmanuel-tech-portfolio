"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Select } from "@/components/ui/select";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Award, QrCode, Users, Clock } from "lucide-react";
import { formatDateTime } from "@/lib/utils";
import { useState } from "react";
import Image from "next/image";

interface ApprovalRecord {
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

export default function TeacherAttendance() {
  const { data: coursesData } = useFetch<{ courseAssignments: { id: string; course: { id: string; name: string; code: string } }[] }>("/api/teacher/dashboard");
  const [selectedCourse, setSelectedCourse] = useState("");
  const [sessionData, setSessionData] = useState<{ qrCode: string; id: string; qrExpiry: string } | null>(null);
  const [generating, setGenerating] = useState(false);
  const [records, setRecords] = useState<{ id: string; student: { firstName: string; lastName: string; studentId: string }; status: string; checkedInAt: string }[]>([]);
  const [activeSessionId, setActiveSessionId] = useState<string | null>(null);
  const [approvals, setApprovals] = useState<ApprovalRecord[]>([]);
  const [approvalsLoaded, setApprovalsLoaded] = useState(false);
  const [busy, setBusy] = useState<string | null>(null);
  const [approvalError, setApprovalError] = useState<string | null>(null);

  const loadApprovals = async () => {
    setApprovalsLoaded(true);
    const res = await fetch("/api/attendance?action=approvals");
    const data = await res.json();
    setApprovals(data.records || []);
  };

  const actApproval = async (rec: ApprovalRecord, approve: boolean, as: "lecturer" | "hod" | "admin") => {
    setBusy(`${rec.id}-${as}-${approve}`);
    setApprovalError(null);
    try {
      const res = await fetch("/api/attendance/approve", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: rec.id, approve, as }),
      });
      const body = await res.json();
      if (!res.ok) setApprovalError(body.error || "Action failed");
      await loadApprovals();
    } catch {
      setApprovalError("Network error");
    } finally {
      setBusy(null);
    }
  };

  const generateQR = async () => {
    if (!selectedCourse) return;
    setGenerating(true);
    try {
      const res = await fetch("/api/attendance", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ courseId: selectedCourse, expiryMinutes: 15 }),
      });
      const data = await res.json();
      setSessionData(data.session);
      setActiveSessionId(data.session.id);
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setGenerating(false);
    }
  };

  const loadRecords = async (sessionId: string) => {
    const res = await fetch(`/api/attendance?sessionId=${sessionId}&action=records`);
    const data = await res.json();
    setRecords(data.records || []);
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Attendance</h1>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <QrCode className="h-5 w-5" />
              Generate Attendance QR Code
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex gap-4 items-end">
              <div className="flex-1 space-y-2">
                <Label>Select Course</Label>
                <Select value={selectedCourse} onChange={(e) => setSelectedCourse(e.target.value)}>
                  <option value="">Select a course</option>
                  {coursesData?.courseAssignments?.map((ca) => (
                    <option key={ca.course.id} value={ca.course.id}>{ca.course.name} ({ca.course.code})</option>
                  ))}
                </Select>
              </div>
              <Button onClick={generateQR} disabled={generating || !selectedCourse}>
                {generating ? "Generating..." : "Generate QR Code"}
              </Button>
              {activeSessionId && (
                <Button variant="outline" onClick={() => loadRecords(activeSessionId)}>
                  <Users className="h-4 w-4" /> View Check-ins
                </Button>
              )}
            </div>

            {sessionData && (
              <div className="flex flex-col items-center gap-4 p-6 bg-muted rounded-lg">
                {sessionData.qrCode && (
                  <Image src={sessionData.qrCode} alt="QR Code" width={256} height={256} className="rounded-lg border" />
                )}
                <div className="text-center">
                  <p className="text-sm font-medium">Students scan this QR to check in</p>
                  <p className="text-xs text-muted-foreground flex items-center gap-1 justify-center mt-1">
                    <Clock className="h-3 w-3" />
                    Expires: {new Date(sessionData.qrExpiry).toLocaleTimeString()}
                  </p>
                </div>
                <Badge variant="info">Session ID: {sessionData.id.slice(0, 8)}...</Badge>
              </div>
            )}
          </CardContent>
        </Card>

        {records.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle>Checked-in Students ({records.length})</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                {records.map((record) => (
                  <div key={record.id} className="flex items-center justify-between border-b pb-2 last:border-0">
                    <div className="flex items-center gap-2">
                      <Award className="h-4 w-4 text-green-500" />
                      <span className="font-medium text-sm">{record.student.firstName} {record.student.lastName}</span>
                      <span className="text-xs text-muted-foreground">({record.student.studentId})</span>
                    </div>
                    <span className="text-xs text-muted-foreground">{formatDateTime(record.checkedInAt)}</span>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Award className="h-5 w-5" /> Attendance Approvals
            </CardTitle>
          </CardHeader>
          <CardContent>
            {!approvalsLoaded ? (
              <Button onClick={loadApprovals} variant="outline">Load Approvals</Button>
            ) : approvalError ? (
              <p className="text-sm text-destructive">{approvalError}</p>
            ) : approvals.length === 0 ? (
              <p className="text-muted-foreground text-center py-4">No attendance awaiting approval.</p>
            ) : (
              <div className="space-y-3">
                {approvals.map((rec) => (
                  <div key={rec.id} className="rounded-lg border p-3 flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <p className="text-sm font-medium">
                        {rec.student?.firstName} {rec.student?.lastName} ({rec.student?.studentId})
                      </p>
                      <p className="text-xs text-muted-foreground">
                        {rec.recordDate} · {rec.type === "CORRECTION" ? "Correction (lecturer + Admin)" : "Daily (lecturer + HOD)"}
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant={rec.approvalStatus === "APPROVED" ? "success" : "warning"}>{rec.approvalStatus.replace("_", " ")}</Badge>
                      {rec.canApproveAs.lecturer && (
                        <Button size="sm" disabled={busy !== null} onClick={() => actApproval(rec, !rec.lecturerApproved, "lecturer")}>
                          {rec.lecturerApproved ? "Undo Lecturer" : "Approve Lecturer"}
                        </Button>
                      )}
                      {rec.canApproveAs.hod && rec.type !== "CORRECTION" && (
                        <Button size="sm" disabled={busy !== null} onClick={() => actApproval(rec, !rec.hodApproved, "hod")}>
                          {rec.hodApproved ? "Undo HOD" : "Approve HOD"}
                        </Button>
                      )}
                      {rec.canApproveAs.admin && rec.type === "CORRECTION" && (
                        <Button size="sm" disabled={busy !== null} onClick={() => actApproval(rec, !rec.adminApproved, "admin")}>
                          {rec.adminApproved ? "Undo Admin" : "Approve Admin"}
                        </Button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
