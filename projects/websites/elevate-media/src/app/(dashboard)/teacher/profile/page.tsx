"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { User } from "lucide-react";
import { useState, useEffect } from "react";

export default function TeacherProfile() {
  const { data, loading } = useFetch<{ profile: { id: string; employeeId: string; firstName: string; lastName: string; phone: string | null; position: string | null; department: { name: string } | null; user: { email: string; avatar: string | null } } }>("/api/teacher/profile");
  const [form, setForm] = useState({ firstName: "", lastName: "", phone: "", position: "" });
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    if (data?.profile) {
      setForm({
        firstName: data.profile.firstName,
        lastName: data.profile.lastName,
        phone: data.profile.phone || "",
        position: data.profile.position || "",
      });
    }
  }, [data]);

  const handleSave = async () => {
    setSaving(true);
    try {
      await fetch("/api/teacher/profile", {
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
              {data?.profile && (
                <>
                  <h3 className="font-semibold text-lg">{data.profile.firstName} {data.profile.lastName}</h3>
                  <p className="text-sm text-muted-foreground">{data.profile.user.email}</p>
                  <p className="text-xs text-muted-foreground mt-1">ID: {data.profile.employeeId}</p>
                  {data.profile.position && <p className="text-xs mt-2">{data.profile.position}</p>}
                  {data.profile.department && <p className="text-xs text-muted-foreground">{data.profile.department.name}</p>}
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
                    <Label>Position</Label>
                    <Input value={form.position} onChange={(e) => setForm({ ...form, position: e.target.value })} />
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
