"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Users, Plus, Trash2, Search, ShieldCheck } from "lucide-react";
import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";

export default function AdminTeachers() {
  const [search, setSearch] = useState("");
  const { data, loading, refetch } = useFetch<{ teachers: { id: string; employeeId: string; firstName: string; lastName: string; position: string | null; isHod: boolean; user: { email: string }; department: { name: string } | null }[] }>(`/api/teachers?search=${search}`);
  const { data: deptData } = useFetch<{ departments: { id: string; name: string; code: string }[] }>("/api/departments");
  const [showCreate, setShowCreate] = useState(false);
  const [creating, setCreating] = useState(false);
  const [toggling, setToggling] = useState<string | null>(null);
  const [form, setForm] = useState({ firstName: "", lastName: "", email: "", phone: "", departmentId: "", position: "" });

  const handleCreate = async () => {
    if (!form.firstName || !form.lastName || !form.email) return;
    setCreating(true);
    try {
      await fetch("/api/teachers", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      setShowCreate(false);
      setForm({ firstName: "", lastName: "", email: "", phone: "", departmentId: "", position: "" });
      refetch();
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setCreating(false);
    }
  };

  const handleToggleHod = async (id: string, isHod: boolean) => {
    setToggling(id);
    try {
      await fetch(`/api/teachers/${id}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ isHod: !isHod }),
      });
      refetch();
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setToggling(null);
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Teachers</h1>
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> Add Teacher
          </Button>
        </div>

        <div className="flex items-center gap-2">
          <Search className="h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Search by name, ID..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="max-w-md"
          />
        </div>

        <Card>
          <CardContent className="p-0">
            {loading ? (
              <div className="p-6 text-muted-foreground">Loading...</div>
            ) : data?.teachers?.length ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Employee ID</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Position</TableHead>
                    <TableHead>Department</TableHead>
                    <TableHead className="text-right">HOD</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {data.teachers.map((teacher) => (
                    <TableRow key={teacher.id}>
                      <TableCell className="font-mono">{teacher.employeeId}</TableCell>
                      <TableCell>{teacher.firstName} {teacher.lastName}</TableCell>
                      <TableCell>{teacher.user.email}</TableCell>
                      <TableCell>{teacher.position || "—"}</TableCell>
                      <TableCell>{teacher.department?.name || "—"}</TableCell>
                      <TableCell className="text-right">
                        <Button
                          variant={teacher.isHod ? "default" : "outline"}
                          size="sm"
                          disabled={toggling === teacher.id}
                          onClick={() => handleToggleHod(teacher.id, teacher.isHod)}
                        >
                          <ShieldCheck className="h-4 w-4" />
                          {teacher.isHod ? "HOD" : "Make HOD"}
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <div className="p-6 text-center text-muted-foreground">No teachers found.</div>
            )}
          </CardContent>
        </Card>

        <Dialog open={showCreate} onOpenChange={setShowCreate}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Teacher</DialogTitle>
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
                <Label>Position</Label>
                <Input placeholder="e.g. Senior Lecturer" value={form.position} onChange={(e) => setForm({ ...form, position: e.target.value })} />
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
              <p className="text-xs text-muted-foreground">Default password: teacher123</p>
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
