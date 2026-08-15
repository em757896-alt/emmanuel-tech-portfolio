"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { FileCheck2, Upload, ShieldCheck, Lock } from "lucide-react";
import { useState } from "react";

interface CourseItem { id: string; name: string; code: string; }

export default function TeacherResults() {
  const { data, loading, refetch } = useFetch<{
    results: {
      id: string;
      title: string;
      grade: string | null;
      semester: number;
      year: number;
      fileName: string;
      uploadedAt: string;
      released: boolean;
      status: string;
      lecturerApproved: boolean;
      hodApproved: boolean;
      student: { firstName: string; lastName: string; studentId: string };
      course: { name: string; code: string };
    }[];
  }>("/api/results");

  const { data: dash } = useFetch<{
    courseAssignments: { courses: CourseItem }[];
    departmentCourses: CourseItem[];
    isHod: boolean;
  }>("/api/teacher/dashboard");

  const { data: studentsData } = useFetch<{ students: { id: string; firstName: string; lastName: string; studentId: string }[] }>("/api/students?limit=200");

  const [title, setTitle] = useState("");
  const [grade, setGrade] = useState("");
  const [semester, setSemester] = useState("1");
  const [year, setYear] = useState(new Date().getFullYear().toString());
  const [studentId, setStudentId] = useState("");
  const [courseId, setCourseId] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);
  const [acting, setActing] = useState<string | null>(null);

  const verifyHod = async (id: string) => {
    setActing(id);
    setError(null);
    try {
      const res = await fetch("/api/results/approve", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, approve: true, as: "hod" }),
      });
      const body = await res.json();
      if (!res.ok) setError(body.error || "Action failed");
      refetch();
    } catch {
      setError("Network error");
    } finally {
      setActing(null);
    }
  };

  const courses: CourseItem[] = dash?.isHod
    ? [
        ...(dash.courseAssignments?.map((ca) => ca.courses) || []),
        ...(dash.departmentCourses || []),
      ].filter((c, i, arr) => arr.findIndex((x) => x.id === c.id) === i)
    : (dash?.courseAssignments?.map((ca) => ca.courses) || []);

  const handleUpload = async () => {
    if (!file || !studentId || !courseId || !title) {
      setError("Title, student, course and file are required.");
      return;
    }
    setUploading(true);
    setError(null);
    setMessage(null);
    try {
      const form = new FormData();
      form.append("file", file);
      form.append("bucket", "results");
      form.append("folder", "results");
      const upRes = await fetch("/api/upload", { method: "POST", body: form });
      const up = await upRes.json();
      if (!upRes.ok) throw new Error(up.error || "Upload failed");

      const res = await fetch("/api/results", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          title,
          grade: grade || null,
          semester: parseInt(semester),
          year: parseInt(year),
          studentId,
          courseId,
          fileUrl: up.url,
          fileName: up.fileName,
          fileType: up.fileType,
          fileSize: up.fileSize,
        }),
      });
      const body = await res.json();
      if (!res.ok) throw new Error(body.error || "Could not save result");

      setMessage("Result released to the student. Downloads stay locked until the HOD verifies it.");
      setTitle("");
      setGrade("");
      setStudentId("");
      setCourseId("");
      setFile(null);
      refetch();
    } catch (e: any) {
      setError(e.message || "Upload failed");
    } finally {
      setUploading(false);
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Results Upload</h1>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Upload className="h-5 w-5" /> Release a Verified Result
            </CardTitle>
          </CardHeader>
          <CardContent>
            {dash?.isHod && (
              <p className="text-sm text-muted-foreground mb-4">
                You are an HOD - you can release results for any course in your department.
              </p>
            )}
            {error && <p className="text-sm text-destructive mb-3">{error}</p>}
            {message && <p className="text-sm text-green-600 mb-3">{message}</p>}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Result Title</Label>
                <Input value={title} onChange={(e) => setTitle(e.target.value)} placeholder="e.g. CS101 End of Term Exam" />
              </div>
              <div className="space-y-2">
                <Label>Grade (optional)</Label>
                <Select value={grade} onChange={(e) => setGrade(e.target.value)}>
                  <option value="">No grade</option>
                  {["A", "A-", "B+", "B", "B-", "C+", "C", "C-", "D+", "D", "F"].map((g) => (
                    <option key={g} value={g}>{g}</option>
                  ))}
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Student</Label>
                <Select value={studentId} onChange={(e) => setStudentId(e.target.value)}>
                  <option value="">Select student</option>
                  {studentsData?.students?.map((s) => (
                    <option key={s.id} value={s.id}>{s.firstName} {s.lastName} ({s.studentId})</option>
                  ))}
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Course</Label>
                <Select value={courseId} onChange={(e) => setCourseId(e.target.value)}>
                  <option value="">Select course</option>
                  {courses.map((c) => (
                    <option key={c.id} value={c.id}>{c.name} ({c.code})</option>
                  ))}
                </Select>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Semester</Label>
                  <Select value={semester} onChange={(e) => setSemester(e.target.value)}>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Year</Label>
                  <Input type="number" value={year} onChange={(e) => setYear(e.target.value)} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Result Document</Label>
                <Input type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" onChange={(e) => setFile(e.target.files?.[0] || null)} />
              </div>
            </div>
            <div className="mt-4">
              <Button onClick={handleUpload} disabled={uploading}>
                {uploading ? "Uploading..." : "Release Result"}
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FileCheck2 className="h-5 w-5" /> Released Results
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-muted-foreground">Loading...</div>
            ) : data?.results?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Title</TableHead>
                    <TableHead>Student</TableHead>
                    <TableHead>Course</TableHead>
                    <TableHead>Grade</TableHead>
                    <TableHead>Semester</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.results.map((r) => (
                    <TableRow key={r.id}>
                      <TableCell className="font-medium">{r.title}</TableCell>
                      <TableCell>{r.student?.firstName} {r.student?.lastName} ({r.student?.studentId})</TableCell>
                      <TableCell>{r.course?.name} ({r.course?.code})</TableCell>
                      <TableCell>{r.grade || "-"}</TableCell>
                      <TableCell>Sem {r.semester}, {r.year}</TableCell>
                      <TableCell>
                        <div className="flex flex-col items-start gap-2">
                          {r.hodApproved ? (
                            <Badge variant="success"><ShieldCheck className="h-3 w-3" /> Verified</Badge>
                          ) : (
                            <Badge variant="warning"><Lock className="h-3 w-3" /> Waiting HOD</Badge>
                          )}
                          {dash?.isHod && !r.hodApproved && (
                            <Button size="sm" variant="outline" disabled={acting === r.id} onClick={() => verifyHod(r.id)}>
                              {acting === r.id ? "Verifying..." : "Verify as HOD"}
                            </Button>
                          )}
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <p className="text-muted-foreground text-center py-4">No results released yet.</p>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
