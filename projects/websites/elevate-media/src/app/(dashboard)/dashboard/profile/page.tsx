"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { User } from "lucide-react";
import { useState, useEffect } from "react";

export default function StudentProfile() {
  const { data, loading } = useFetch<{ student: { id: string; studentId: string; firstName: string; lastName: string; phone: string | null; dateOfBirth: string | null; address: string | null; status: string; departmentName: string | null; courseName: string | null; courseCode: string | null; country: string | null; region: string | null; city: string | null; modeOfLearning: string | null; department: { name: string } | null; user: { email: string; avatar: string | null } } }>("/api/student/profile");
  const [form, setForm] = useState({ firstName: "", lastName: "", phone: "", address: "" });
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    if (data?.student) {
      setForm({
        firstName: data.student.firstName,
        lastName: data.student.lastName,
        phone: data.student.phone || "",
        address: data.student.address || "",
      });
    }
  }, [data]);

  const handleSave = async () => {
    setSaving(true);
    try {
      await fetch(`/api/students/${data?.student.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      setSaved(true);
      setTimeout(() => setSaved(false), 3000);
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setSaving(false);
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">My Profile</h1>

        <div className="grid gap-6 lg:grid-cols-3">
          <Card className="lg:col-span-1">
            <CardContent className="p-6 text-center">
              <div className="h-24 w-24 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <User className="h-12 w-12 text-primary" />
              </div>
              {data?.student && (
                <>
                  <h3 className="font-semibold text-lg">{data.student.firstName} {data.student.lastName}</h3>
                  <p className="text-sm text-muted-foreground">{data.student.user.email}</p>
                  <p className="text-xs text-muted-foreground mt-1">ID: {data.student.studentId}</p>
                  <p className="text-xs mt-2">Status: <span className="font-medium">{data.student.status}</span></p>
                  {(data.student.courseName || data.student.departmentName || data.student.department) && (
                    <p className="text-xs font-medium mt-2">
                      {data.student.courseName || data.student.departmentName || data.student.department?.name}
                      {data.student.courseCode ? ` (${data.student.courseCode})` : ""}
                    </p>
                  )}
                  {[data.student.city, data.student.region, data.student.country].filter(Boolean).length > 0 && (
                    <p className="text-xs text-muted-foreground mt-1">
                      {[data.student.city, data.student.region, data.student.country].filter(Boolean).join(", ")}
                    </p>
                  )}
                  {data.student.modeOfLearning && (
                    <p className="text-xs text-muted-foreground mt-1">{data.student.modeOfLearning}</p>
                  )}
                </>
              )}
            </CardContent>
          </Card>

          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle>Personal Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {loading ? (
                <div className="text-muted-foreground">Loading...</div>
              ) : (
                <>
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
                    <Label>Phone</Label>
                    <Input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                  </div>
                  <div className="space-y-2">
                    <Label>Address</Label>
                    <Input value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} />
                  </div>
                  <div className="flex items-center gap-2">
                    <Button onClick={handleSave} disabled={saving}>
                      {saving ? "Saving..." : "Save Changes"}
                    </Button>
                    {saved && <span className="text-sm text-green-600">Saved!</span>}
                  </div>
                </>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
