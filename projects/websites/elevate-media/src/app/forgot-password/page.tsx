"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Loader2, Mail, Hash, KeyRound } from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { PageImages } from "@/components/layout/PageImages";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState("");
  const [admNo, setAdmNo] = useState("");
  const [error, setError] = useState("");
  const [sent, setSent] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await fetch("/api/forgot-password", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, admNo }),
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

  useEffect(() => {
    if (sent) {
      window.location.href = `/reset-link-sent?email=${encodeURIComponent(email)}`;
    }
  }, [sent, email]);

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex flex-col items-center justify-center bg-background px-4 py-8">
        <div className="w-full max-w-md">
          <PageImages
            images={["https://images.unsplash.com/photo-1589652717521-10c0d092dea9?w=1600&q=80&auto=format&fit=crop"]}
            captions={["We'll help you get back in"]}
          />
        </div>
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <Link href="/" className="inline-flex items-center justify-center gap-2 font-bold text-3xl mb-2">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold text-sm">EM</div>
              Elevate Media University
            </Link>
            <CardTitle className="text-2xl">Forgot Password</CardTitle>
            <CardDescription>Enter your registered email and admission number</CardDescription>
          </CardHeader>
          <CardContent>
            {sent ? (
              <div className="space-y-4 text-center">
                <div className="h-14 w-14 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                  <Loader2 className="h-7 w-7 animate-spin text-primary" />
                </div>
                <p className="text-muted-foreground text-sm">Taking you to the confirmation page...</p>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-4">
                {error && (
                  <Alert variant="destructive">
                    <AlertDescription>{error}</AlertDescription>
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
                  <Label htmlFor="admNo">Admission Number (Adm No)</Label>
                  <div className="relative">
                    <Hash className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="admNo"
                      placeholder="e.g. EM20261001"
                      value={admNo}
                      onChange={(e) => setAdmNo(e.target.value)}
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
              <Link href="/student-login" className="text-primary hover:underline font-medium">Student Login</Link>
            </div>
          </CardContent>
        </Card>
      </div>
      <SiteFooter />
    </div>
  );
}
