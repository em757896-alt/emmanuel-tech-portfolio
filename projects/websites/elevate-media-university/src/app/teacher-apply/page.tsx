"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Loader2, Mail, Lock, User, Phone, Briefcase, FileText } from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";

interface Department {
  id: string;
  name: string;
  code: string;
}

export default function TeacherApplyPage() {
  const router = useRouter();
  const [departments, setDepartments] = useState<Department[]>([]);
  const [submitted, setSubmitted] = useState(false);
  const [employeeId, setEmployeeId] = useState("");
  const [formData, setFormData] = useState({
    firstName: "",
    lastName: "",
    email: "",
    password: "",
    phone: "",
    departmentId: "",
    position: "",
    title: "",
    course: "",
    resumeFileName: "",
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetch("/api/departments")
      .then((res) => res.json())
      .then((data) => setDepartments(data.departments || []))
      .catch(() => {});
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await fetch("/api/teachers", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          firstName: formData.firstName,
          lastName: formData.lastName,
          email: formData.email,
          password: formData.password,
          phone: formData.phone,
          departmentId: formData.departmentId || undefined,
          position: formData.position || formData.title,
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Application failed");
        return;
      }

      setEmployeeId(data.employeeId || "");
      setSubmitted(true);
    } catch {
      setError("An error occurred. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  if (submitted) {
    return (
      <div className="min-h-screen flex flex-col">
        <div className="flex-1 flex items-center justify-center bg-background px-4">
          <Card className="w-full max-w-md text-center">
            <CardContent className="p-8">
              <div className="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <span className="text-3xl">&#10003;</span>
              </div>
              <h2 className="text-2xl font-bold mb-2">Application Received!</h2>
              <p className="text-muted-foreground mb-2">Thank you for your interest in joining our teaching staff.</p>
              {employeeId && (
                <p className="text-sm font-medium text-primary mb-4">
                  Your Employee ID: <span className="font-bold">{employeeId}</span>
                </p>
              )}
              <p className="text-sm text-muted-foreground mb-6">
                We will review your application and contact you if successful. You can then log in to the teacher portal.
              </p>
              <div className="flex flex-col gap-3">
                <Link href="/teacher-login">
                  <Button className="w-full bg-accent text-primary hover:bg-accent/90">Go to Teacher Login</Button>
                </Link>
                <Link href="/">
                  <Button variant="outline" className="w-full">Back to Home</Button>
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>
        <SiteFooter />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex items-center justify-center bg-background px-4 py-8">
        <Card className="w-full max-w-lg">
          <CardHeader className="text-center">
            <Link href="/" className="inline-flex items-center justify-center gap-2 font-bold text-3xl mb-2">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold text-sm">EM</div>
              Elevate Media University
            </Link>
            <CardTitle className="text-2xl">Teacher Application</CardTitle>
            <CardDescription>Join our teaching staff at Elevate Media University</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              {error && (
                <Alert variant="destructive">
                  <AlertDescription>{error}</AlertDescription>
                </Alert>
              )}
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="firstName">First Name *</Label>
                  <div className="relative">
                    <User className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input id="firstName" name="firstName" placeholder="John" value={formData.firstName} onChange={handleChange} className="pl-10" required />
                  </div>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="lastName">Last Name *</Label>
                  <Input id="lastName" name="lastName" placeholder="Doe" value={formData.lastName} onChange={handleChange} required />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Email Address *</Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input id="email" name="email" type="email" placeholder="you@example.com" value={formData.email} onChange={handleChange} className="pl-10" required />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Password *</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input id="password" name="password" type="password" placeholder="Min. 6 characters" value={formData.password} onChange={handleChange} className="pl-10" required minLength={6} />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="phone">Phone Number</Label>
                <div className="relative">
                  <Phone className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input id="phone" name="phone" placeholder="+254 700 000 000" value={formData.phone} onChange={handleChange} className="pl-10" />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="title">Title</Label>
                  <Select name="title" value={formData.title} onChange={handleChange}>
                    <option value="">Select title</option>
                    <option value="Mr">Mr</option>
                    <option value="Mrs">Mrs</option>
                    <option value="Ms">Ms</option>
                    <option value="Dr">Dr</option>
                    <option value="Prof">Prof</option>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="position">Position</Label>
                  <div className="relative">
                    <Briefcase className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input id="position" name="position" placeholder="e.g. Lecturer" value={formData.position} onChange={handleChange} className="pl-10" />
                  </div>
                </div>
              </div>
              {departments.length > 0 && (
                <div className="space-y-2">
                  <Label htmlFor="departmentId">Department</Label>
                  <Select name="departmentId" value={formData.departmentId} onChange={handleChange}>
                    <option value="">Select a department</option>
                    {departments.map((dept) => (
                      <option key={dept.id} value={dept.id}>{dept.name} ({dept.code})</option>
                    ))}
                  </Select>
                </div>
              )}
              <div className="space-y-2">
                <Label htmlFor="course">Course to Teach</Label>
                <Input id="course" name="course" placeholder="e.g. Introduction to Programming" value={formData.course} onChange={handleChange} />
              </div>
              <div className="space-y-2">
                <Label>Resume / CV</Label>
                <div className="border-2 border-dashed rounded-lg p-4 text-center cursor-pointer hover:bg-muted/50 transition-colors">
                  <FileText className="h-8 w-8 mx-auto mb-2 text-muted-foreground" />
                  <p className="text-sm text-muted-foreground">Resume upload will be available after account creation</p>
                </div>
              </div>
              <Button type="submit" className="w-full bg-accent text-primary hover:bg-accent/90" disabled={loading}>
                {loading ? (<><Loader2 className="h-4 w-4 animate-spin" /> Submitting Application...</>) : "Submit Application"}
              </Button>
            </form>
            <div className="mt-6 text-center text-sm">
              <span className="text-muted-foreground">Already have an account? </span>
              <Link href="/teacher-login" className="text-primary hover:underline font-medium">Teacher Login</Link>
            </div>
          </CardContent>
        </Card>
      </div>
      <SiteFooter />
    </div>
  );
}
