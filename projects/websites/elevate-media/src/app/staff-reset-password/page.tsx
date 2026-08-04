"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { createClient } from "@supabase/supabase-js";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Loader2, Lock, KeyRound } from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";

export default function StaffResetPasswordPage() {
  const router = useRouter();
  const [ready, setReady] = useState(false);
  const [error, setError] = useState("");
  const [invalidLink, setInvalidLink] = useState(false);
  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [loading, setLoading] = useState(false);
  const [email, setEmail] = useState("");

  useEffect(() => {
    const hash = new URLSearchParams(window.location.hash.substring(1));
    const accessToken = hash.get("access_token");
    const refreshToken = hash.get("refresh_token");

    if (!accessToken || !refreshToken) {
      setInvalidLink(true);
      return;
    }

    const url = process.env.NEXT_PUBLIC_SUPABASE_URL!;
    const anonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!;
    const client = createClient(url, anonKey, {
      auth: { persistSession: false, autoRefreshToken: false, detectSessionInUrl: false },
    });

    client.auth
      .setSession({ access_token: accessToken, refresh_token: refreshToken })
      .then(async ({ error: sessionErr }) => {
        if (sessionErr) {
          setInvalidLink(true);
          return;
        }
        const { data: userData } = await client.auth.getUser();
        if (userData?.user?.email) setEmail(userData.user.email);
        setReady(true);
      })
      .catch(() => setInvalidLink(true));
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");

    if (password.length < 6) {
      setError("Password must be at least 6 characters.");
      return;
    }
    if (password !== confirm) {
      setError("Passwords do not match.");
      return;
    }

    setLoading(true);
    try {
      const url = process.env.NEXT_PUBLIC_SUPABASE_URL!;
      const anonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!;
      const client = createClient(url, anonKey, {
        auth: { persistSession: false, autoRefreshToken: false, detectSessionInUrl: false },
      });

      const { error: updateErr } = await client.auth.updateUser({ password });
      if (updateErr) {
        setError(updateErr.message);
        setLoading(false);
        return;
      }

      await client.auth.signOut();

      if (email) {
        await fetch("/api/auth/sync-password", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, password }),
        });
      }

      router.push("/teacher-login?reset=1");
    } catch {
      setError("An error occurred. Please try again.");
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
            <CardTitle className="text-2xl">Reset Staff Password</CardTitle>
            <CardDescription>Choose a new password for your staff account</CardDescription>
          </CardHeader>
          <CardContent>
            {invalidLink ? (
              <div className="space-y-4 text-center">
                <p className="text-muted-foreground text-sm">
                  This password reset link is invalid or has expired. Please request a new one.
                </p>
                <Link href="/teacher-forgot-password">
                  <Button className="w-full bg-accent text-primary hover:bg-accent/90">Request New Link</Button>
                </Link>
              </div>
            ) : !ready ? (
              <div className="flex items-center justify-center py-8">
                <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-4">
                {error && (
                  <Alert variant="destructive">
                    <AlertDescription>{error}</AlertDescription>
                  </Alert>
                )}
                <div className="space-y-2">
                  <Label htmlFor="password">New Password</Label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="password"
                      type="password"
                      placeholder="Min. 6 characters"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      className="pl-10"
                      required
                      minLength={6}
                    />
                  </div>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="confirm">Confirm New Password</Label>
                  <div className="relative">
                    <KeyRound className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="confirm"
                      type="password"
                      placeholder="Re-enter new password"
                      value={confirm}
                      onChange={(e) => setConfirm(e.target.value)}
                      className="pl-10"
                      required
                    />
                  </div>
                </div>
                <Button type="submit" className="w-full bg-accent text-primary hover:bg-accent/90" disabled={loading}>
                  {loading ? (<><Loader2 className="h-4 w-4 animate-spin" /> Updating...</>) : "Update Password"}
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
