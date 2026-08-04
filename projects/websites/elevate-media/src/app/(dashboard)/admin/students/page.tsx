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
import { GraduationCap, Plus, Trash2, Search } from "lucide-react";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function AdminStudents() {
  const [search, setSearch] = useState("");
  const { data, loading, refetch } = useFetch<{ students: { id: string; studentId: string; firstName: string; lastName: string; status: string; departmentName: string | null; courseName: string | null; courseCode: string | null; country: string | null; region: string | null; city: string | null; modeOfLearning: string | null; user: { email: string }; department: { name: string } | null }[] }>(`/api/students?search=${search}`);
  const { data: deptData } = useFetch<{ departments: { id: string; name: string; code: string }[] }>("/api/departments");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState({ firstName: "", lastName: "", email: "", phone: "", departmentId: "" });

  const handleCreate = async () => {
    if (!form.firstName || !form.lastName || !form.email) return;
    setCreating(true);
    try {
      await fetch("/api/students", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      setShowCreate(false);
      setForm({ firstName: "", lastName: "", email: "", phone: "", departmentId: "" });
      refetch();
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setCreating(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Delete this student? This cannot be undone.")) return;
    await fetch(`/api/students/${id}`, { method: "DELETE" });
    refetch();
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Students</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Add Student
          </Button>
        </div>

        <div className="flex items-center gap-2">
          <Search className="h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Search by name, ID, or email..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="max-w-md"
          />
        </div>

        <Card>
          <CardContent className="p-0">
            {loading ? (
              <div className="p-6 text-muted-foreground">Loading...</div>
            ) : data?.students?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student ID</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Program</TableHead>
                    <TableHead>Location</TableHead>
                    <TableHead>Mode</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.students.map((student) => (
                    <TableRow key={student.id}>
                      <TableCell className="font-mono">{student.studentId}</TableCell>
                      <TableCell>{student.firstName} {student.lastName}</TableCell>
                      <TableCell>{student.user.email}</TableCell>
                      <TableCell>
                        {student.courseName
                          ? `${student.courseName}${student.courseCode ? ` (${student.courseCode})` : ""}`
                          : student.departmentName || student.department?.name || "—"}
                      </TableCell>
                      <TableCell>
                        {[student.city, student.region, student.country].filter(Boolean).join(", ") || "—"}
                      </TableCell>
                      <TableCell>{student.modeOfLearning || "—"}</TableCell>
                      <TableCell>
                        <Badge variant={student.status === "ACTIVE" ? "success" : student.status === "GRADUATED" ? "info" : "destructive"}>
                          {student.status}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <Button variant="destructive" size="sm" onClick={() => handleDelete(student.id)}>
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <div className="p-6 text-center text-muted-foreground">No students found.</div>
            )}
          </CardContent>
        </Card>

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Student</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>First Name</Label>
                  <Input value={form.firstName} onChange={(e) => setForm({ ...form, firstName: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <Label>Last Name</Label>
                  <Input value={form.lastName} onChange={(e) => setForm({ ...form, lastName: e.target.value })} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Email</Label>
                <Input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Phone</Label>
                <Input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
              </div>
              <div className="space-y-2">
                <Label>Department</Label>
                <Select value={form.departmentId} onChange={(e) => setForm({ ...form, departmentId: e.target.value })}>
                  <option value="">Select department</option>
                  {deptData?.departments?.map((d) => (
                    <option key={d.id} value={d.id}>{d.name}</option>
                  ))}
                </Select>
              </div>
              <p className="text-xs text-muted-foreground">Default password: student123</p>
            </div>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowCreate(false)}>Cancel</Button>
              <Button onClick={handleCreate} disabled={creating || !form.firstName || !form.lastName || !form.email}>
                {creating ? "Creating..." : "Create"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </DashboardLayout>
  );
}
