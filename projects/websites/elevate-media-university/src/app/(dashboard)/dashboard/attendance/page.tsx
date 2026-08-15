"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Award, CalendarDays, CheckCircle2, Clock, ChevronLeft, ChevronRight, FileWarning, Loader2 } from "lucide-react";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

interface DayRecord {
  id: string;
  type: string;
  checkedInAt: string;
  lecturerApproved: boolean;
  hodApproved: boolean;
  adminApproved: boolean;
}

interface DayCell {
  date: string;
  label: string;
  weekday: number;
  weekend: boolean;
  isToday: boolean;
  isFuture: boolean;
  status: string;
  record: DayRecord | null;
}

interface AttendanceData {
  weekStart: string;
  today: string;
  days: DayCell[];
  stats: { approved: number; pending: number; expectedDays: number; rate: number };
}

function toDateStr(d: Date) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${y}-${m}-${day}`;
}

export default function StudentAttendance() {
  const today = new Date();
  const monday = new Date(today);
  monday.setDate(today.getDate() - ((today.getDay() + 6) % 7));
  monday.setHours(0, 0, 0, 0);

  const [weekStart, setWeekStart] = useState(toDateStr(monday));
  const [signing, setSigning] = useState(false);
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null);
  const [showCorrection, setShowCorrection] = useState(false);
  const [correctionDate, setCorrectionDate] = useState("");
  const [correcting, setCorrecting] = useState(false);

  const { data, loading, refetch } = useFetch<AttendanceData>(`/api/attendance?week=${weekStart}`);

  const shiftWeek = (dir: number) => {
    const base = new Date(weekStart + "T00:00:00");
    base.setDate(base.getDate() + dir * 7);
    setWeekStart(toDateStr(base));
    setMessage(null);
  };

  const handleSign = async () => {
    setSigning(true);
    setMessage(null);
    try {
      const res = await fetch("/api/attendance", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "signin" }),
      });
      const result = await res.json();
      setMessage({ text: result.error || result.message || "Attendance signed.", ok: res.ok });
      refetch();
    } catch {
      setMessage({ text: "Network error", ok: false });
    } finally {
      setSigning(false);
    }
  };

  const handleUnsign = async () => {
    setSigning(true);
    setMessage(null);
    try {
      const res = await fetch("/api/attendance", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "unsign" }),
      });
      const result = await res.json();
      setMessage({ text: result.error || result.message || "Sign removed.", ok: res.ok });
      refetch();
    } catch {
      setMessage({ text: "Network error", ok: false });
    } finally {
      setSigning(false);
    }
  };

  const handleCorrection = async () => {
    if (!correctionDate) return;
    setCorrecting(true);
    setMessage(null);
    try {
      const res = await fetch("/api/attendance", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "correction", date: correctionDate }),
      });
      const result = await res.json();
      setMessage({ text: result.error || result.message || "Correction submitted.", ok: res.ok });
      if (res.ok) {
        setShowCorrection(false);
        setCorrectionDate("");
        refetch();
      }
    } catch {
      setMessage({ text: "Network error", ok: false });
    } finally {
      setCorrecting(false);
    }
  };

  const todayCell = data?.days.find((d) => d.isToday);

  const renderStatus = (d: DayCell) => {
    switch (d.status) {
      case "OFF":
        return <Badge variant="secondary">Off</Badge>;
      case "UPCOMING":
        return <span className="text-xs text-muted-foreground">—</span>;
      case "AVAILABLE":
        return (
          <Button size="sm" onClick={handleSign} disabled={signing} className="w-full">
            <CheckCircle2 className="h-4 w-4" /> Sign
          </Button>
        );
      case "MISSED":
        return (
          <div className="space-y-1">
            <Badge variant="destructive">Missed</Badge>
            <Button size="sm" variant="outline" className="w-full" onClick={() => setShowCorrection(true)}>
              Request Correction
            </Button>
          </div>
        );
      case "PENDING":
        return <Badge variant="secondary">Awaiting Approval</Badge>;
      case "PARTIALLY_APPROVED":
        return <Badge variant="warning">Awaiting Approval</Badge>;
      case "APPROVED":
        return <Badge variant="success">Approved</Badge>;
      default:
        return null;
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Attendance</h1>

        <div className="grid gap-4 md:grid-cols-3">
          <Card>
            <CardContent className="p-6 text-center">
              <p className="text-sm text-muted-foreground">Attendance Rate (approved)</p>
              <p className="text-3xl font-bold">{data?.stats?.rate ?? 0}%</p>
              <p className="text-xs text-muted-foreground mt-1">{data?.stats?.approved ?? 0} approved of {data?.stats?.expectedDays ?? 0} expected days</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6 text-center">
              <p className="text-sm text-muted-foreground">Approved Days</p>
              <p className="text-3xl font-bold text-green-600">{data?.stats?.approved ?? 0}</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6 text-center">
              <p className="text-sm text-muted-foreground">Pending Approvals</p>
              <p className="text-3xl font-bold text-amber-600">{data?.stats?.pending ?? 0}</p>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <CalendarDays className="h-5 w-5" />
              Weekly Attendance
            </CardTitle>
            <div className="flex items-center gap-2">
              <Button variant="outline" size="sm" onClick={() => shiftWeek(-1)}>
                <ChevronLeft className="h-4 w-4" /> Prev
              </Button>
              <span className="text-sm font-medium">Week of {weekStart}</span>
              <Button variant="outline" size="sm" onClick={() => shiftWeek(1)}>
                Next <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground mb-4">
              Sign in for each day (Monday–Friday). Saturday and Sunday are off. You can only sign for today - past days
              require a correction request approved by the unit lecturer and Admin.
            </p>
            {loading ? (
              <div className="flex items-center gap-2 text-muted-foreground py-8 justify-center">
                <Loader2 className="h-4 w-4 animate-spin" /> Loading...
              </div>
            ) : (
              <div className="grid grid-cols-7 gap-2">
                {data?.days.map((d) => (
                  <div key={d.date} className={`rounded-lg border p-3 text-center space-y-2 ${d.isToday ? "border-primary ring-1 ring-primary" : ""} ${d.weekend ? "bg-muted/40" : ""}`}>
                    <div className="text-xs font-semibold">{d.label}</div>
                    <div className="text-[11px] text-muted-foreground">{d.date}</div>
                    {d.isToday && <div className="text-[10px] text-primary font-semibold">TODAY</div>}
                    {renderStatus(d)}
                  </div>
                ))}
              </div>
            )}
            {message && (
              <p className={`mt-4 text-sm ${message.ok ? "text-green-600" : "text-destructive"}`}>
                {message.text}
              </p>
            )}
            {todayCell?.status === "PENDING" && (
              <div className="mt-4 flex items-center gap-2 text-sm text-muted-foreground">
                <Clock className="h-4 w-4" />
                Attendance signed for today - awaiting the unit lecturer and HOD approval.
                <Button variant="outline" size="sm" onClick={handleUnsign} disabled={signing}>
                  Undo
                </Button>
              </div>
            )}
          </CardContent>
        </Card>

        <Dialog open={showCorrection} onOpenChange={setShowCorrection}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <FileWarning className="h-5 w-5" /> Request Attendance Correction
              </DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Date missed (past weekday)</Label>
                <Input type="date" max={toDateStr(new Date())} value={correctionDate} onChange={(e) => setCorrectionDate(e.target.value)} />
              </div>
              <p className="text-xs text-muted-foreground">
                Corrections are submitted for approval and will be verified by the unit lecturer and Admin before counting.
              </p>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCorrection(false)}>Cancel</Button>
              <Button onClick={handleCorrection} disabled={correcting || !correctionDate}>
                {correcting ? "Submitting..." : "Submit Correction"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
