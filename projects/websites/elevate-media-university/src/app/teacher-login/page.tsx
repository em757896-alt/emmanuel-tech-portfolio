"use client";

import Link from "next/link";
import { useState } from "react";
import { signIn } from "next-auth/react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Loader2, Mail, Lock, ShieldCheck, GraduationCap, IdCard } from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";

export default function TeacherLoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [employeeId, setEmployeeId] = useState("");
  const [password, setPassword] = useState("");
  const [roleClaim, setRoleClaim] = useState<"lecturer" | "hod" | "">("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");

    if (!roleClaim) {
      setError("Select whether you are a unit lecturer or a Head of Department.");
      return;
    }

    setLoading(true);

    try {
      const result = await signIn("credentials", {
        email,
        employeeId,
        password,
        hodClaim: roleClaim === "hod" ? "yes" : "no",
        redirect: false,
      });

      if (result?.error) {
        setError(roleClaim === "hod"
          ? "HOD access was not granted. The system did not verify you as an HOD, or the credentials are incorrect."
          : "Invalid Employee ID, email or password. Please try again.");
        setLoading(false);
        return;
      }

      const sessionRes = await fetch("/api/auth/session");
      const sessionData = await sessionRes.json();
      const isHod = sessionData?.user?.hod === true;

      window.location.href = isHod ? "/teacher/hod" : "/teacher";
    } catch {
      setError("An error occurred. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex items-center justify-center bg-background px-4">
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <Link href="/" className="inline-flex items-center justify-center gap-2 font-bold text-3xl mb-2">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold text-sm">EM</div>
              Elevate Media University
            </Link>
            <CardTitle className="text-2xl">Teacher Portal Login</CardTitle>
            <CardDescription>Sign in with your Employee ID, staff email and password</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              {error && (
                <Alert variant="destructive">
                  <AlertDescription>{error}</AlertDescription>
                </Alert>
              )}
              <div className="space-y-2">
                <Label htmlFor="employeeId">Employee ID</Label>
                <div className="relative">
                  <IdCard className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="employeeId"
                    type="text"
                    placeholder="e.g. T2026002"
                    value={employeeId}
                    onChange={(e) => setEmployeeId(e.target.value)}
                    className="pl-10"
                    required
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Staff Email</Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="email"
                    type="email"
                    placeholder="e.g. jane.smith@elevatemedia.edu"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="pl-10"
                    required
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Password</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="password"
                    type="password"
                    placeholder="Enter your password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="pl-10"
                    required
                  />
                </div>
              </div>
              <div className="text-center">
                <Link href="/teacher-forgot-password" className="text-sm text-primary hover:underline">
                  Forgot your password? Reset it with your email
                </Link>
              </div>
              <div className="space-y-2">
                <Label>Are you a Unit Lecturer or a Head of Department (HOD)?</Label>
                <div className="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    onClick={() => setRoleClaim("lecturer")}
                    className={`flex flex-col items-start gap-1 rounded-lg border-2 p-3 text-left transition-colors ${
                      roleClaim === "lecturer"
                        ? "border-accent bg-accent/10"
                        : "border-border hover:border-accent/50"
                    }`}
                  >
                    <span className="flex items-center gap-2 text-sm font-semibold">
                      <GraduationCap className="h-4 w-4" /> Unit Lecturer
                    </span>
                    <span className="text-xs text-muted-foreground">My own courses, students & tasks</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => setRoleClaim("hod")}
                    className={`flex flex-col items-start gap-1 rounded-lg border-2 p-3 text-left transition-colors ${
                      roleClaim === "hod"
                        ? "border-accent bg-accent/10"
                        : "border-border hover:border-accent/50"
                    }`}
                  >
                    <span className="flex items-center gap-2 text-sm font-semibold">
                      <ShieldCheck className="h-4 w-4" /> Head of Department
                    </span>
                    <span className="text-xs text-muted-foreground">Whole department overview & approvals</span>
                  </button>
                </div>
                {roleClaim === "hod" && (
                  <p className="text-xs text-muted-foreground">
                    HOD access is verified by the system. Only registered HODs are granted this dashboard.
                  </p>
                )}
              </div>
              <Button type="submit" className="w-full bg-accent text-primary hover:bg-accent/90" disabled={loading}>
                {loading ? (<><Loader2 className="h-4 w-4 animate-spin" /> Signing in...</>) : "Sign In"}
              </Button>
            </form>
            <div className="mt-6 text-center text-sm space-y-2">
              <p>
                <span className="text-muted-foreground">Want to join our teaching staff? </span>
                <Link href="/teacher-apply" className="text-primary hover:underline font-medium">Apply Here</Link>
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
      <SiteFooter />
    </div>
  );
}
