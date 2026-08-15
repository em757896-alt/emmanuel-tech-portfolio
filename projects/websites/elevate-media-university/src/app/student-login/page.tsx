"use client";

import Link from "next/link";
import { useState, useEffect, Suspense } from "react";
import { signIn } from "next-auth/react";
import { useSearchParams } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Loader2, Mail, Hash, Lock } from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { PageImages } from "@/components/layout/PageImages";

export default function StudentLoginPage() {
  return (
    <Suspense>
      <StudentLoginInner />
    </Suspense>
  );
}

function StudentLoginInner() {
  const searchParams = useSearchParams();
  const [email, setEmail] = useState("");
  const [studentId, setStudentId] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const verified = searchParams.get("verified") === "1";
  const reset = searchParams.get("reset") === "1";

  useEffect(() => {
    if (verified) {
      window.history.replaceState(null, "", "/student-login");
    }
  }, [verified]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const result = await signIn("credentials", {
        email,
        admNo: studentId,
        password,
        redirect: false,
      });

      if (result?.error) {
        setError("Invalid email, admission number, or password. Please check your details or verify your email.");
      } else {
        window.location.href = "/dashboard";
      }
    } catch {
      setError("An error occurred. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex flex-col items-center justify-center bg-background px-4 py-8">
        <div className="w-full max-w-md">
          <PageImages
            images={["https://images.unsplash.com/photo-1758270704522-f091f8064a81?w=1600&q=80&auto=format&fit=crop"]}
            captions={["Stay connected on campus"]}
          />
        </div>
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <Link href="/" className="inline-flex items-center justify-center gap-2 font-bold text-3xl mb-2">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold text-sm">EM</div>
              Elevate Media University
            </Link>
            <CardTitle className="text-2xl">Student Portal Login</CardTitle>
            <CardDescription>Sign in with your email, admission number, and password</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              {error && (
                <Alert variant="destructive">
                  <AlertDescription>{error}</AlertDescription>
                </Alert>
              )}
              {verified && (
                <Alert>
                  <AlertDescription>Your email has been confirmed. You can now sign in to your student portal.</AlertDescription>
                </Alert>
              )}
              {reset && (
                <Alert>
                  <AlertDescription>Your password has been updated successfully. Sign in with your new password.</AlertDescription>
                </Alert>
              )}
              <div className="space-y-2">
                <Label htmlFor="email">Email Address</Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="email"
                    type="email"
                    placeholder="you@example.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="pl-10"
                    required
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="studentId">Admission Number (Adm No)</Label>
                <div className="relative">
                  <Hash className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="studentId"
                    placeholder="e.g. EM20261001"
                    value={studentId}
                    onChange={(e) => setStudentId(e.target.value)}
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
              <div className="text-right text-sm">
                <Link href="/forgot-password" className="text-primary hover:underline font-medium">
                  Forgot password?
                </Link>
              </div>
              <Button type="submit" className="w-full bg-accent text-primary hover:bg-accent/90" disabled={loading}>
                {loading ? (<><Loader2 className="h-4 w-4 animate-spin" /> Signing in...</>) : "Sign In"}
              </Button>
            </form>
            <div className="mt-6 text-center text-sm">
              <p>
                <span className="text-muted-foreground">New student? </span>
                <Link href="/student-apply" className="text-primary hover:underline font-medium">Apply Now</Link>
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
      <SiteFooter />
    </div>
  );
}
