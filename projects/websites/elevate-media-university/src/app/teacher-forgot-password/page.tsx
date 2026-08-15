"use client";

import Link from "next/link";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Loader2, Mail, KeyRound } from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";

export default function TeacherForgotPasswordPage() {
  const [email, setEmail] = useState("");
  const [error, setError] = useState("");
  const [sent, setSent] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await fetch("/api/forgot-password-staff", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
      });
      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Request failed. Please try again.");
        setLoading(false);
        return;
      }
      setSent(true);
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
            <CardTitle className="text-2xl">Staff Password Reset</CardTitle>
            <CardDescription>Teachers and admins reset with their email</CardDescription>
          </CardHeader>
          <CardContent>
            {sent ? (
              <div className="space-y-4 text-center py-4">
                <div className="h-14 w-14 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                  <span className="text-2xl">&#9993;</span>
                </div>
                <p className="text-muted-foreground text-sm">
                  If <span className="font-medium text-foreground">{email}</span> belongs to a teacher or admin account, a
                  reset link has been sent. Use it to set a new password.
                </p>
                <Link href="/teacher-login">
                  <Button variant="outline" className="w-full">Back to Teacher Login</Button>
                </Link>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-4">
                {error && (
                  <Alert variant="destructive">
                    <AlertDescription>{error}</AlertDescription>
                  </Alert>
                )}
                <div className="space-y-2">
                  <Label htmlFor="email">Staff Email</Label>
                  <div className="relative">
                    <Mail className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="email"
                      type="email"
                      placeholder="you@elevatemedia.edu"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      className="pl-10"
                      required
                    />
                  </div>
                </div>
                <Button type="submit" className="w-full bg-accent text-primary hover:bg-accent/90" disabled={loading}>
                  {loading ? (<><Loader2 className="h-4 w-4 animate-spin" /> Sending...</>) : (<><KeyRound className="h-4 w-4" /> Send Reset Link</>)}
                </Button>
              </form>
            )}
            <div className="mt-6 text-center text-sm">
              <span className="text-muted-foreground">Remembered your password? </span>
              <Link href="/teacher-login" className="text-primary hover:underline font-medium">Teacher Login</Link>
            </div>
          </CardContent>
        </Card>
      </div>
      <SiteFooter />
    </div>
  );
}
