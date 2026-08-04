"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { SiteFooter } from "@/components/layout/SiteFooter";

export default function VerificationSentPage() {
  const [email, setEmail] = useState("");
  const [admNo, setAdmNo] = useState("");

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    setEmail(params.get("email") || "");
    setAdmNo(params.get("admNo") || "");
  }, []);

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex items-center justify-center bg-background px-4">
        <Card className="w-full max-w-md text-center">
          <CardContent className="p-8">
            <div className="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
              <span className="text-3xl">&#9993;</span>
            </div>
            <h2 className="text-2xl font-bold mb-2">Check Your Email</h2>
            <p className="text-muted-foreground mb-2">
              A confirmation link has been sent to <span className="font-medium text-foreground">{email || "your email address"}</span>.
            </p>
            {admNo && (
              <p className="text-sm font-medium text-primary mb-2">
                Your Admission Number (Adm No): <span className="font-bold">{admNo}</span>
              </p>
            )}
            <p className="text-sm text-muted-foreground mb-6">
              Click the activation link in the email to confirm your address. You can sign in to the student portal only after your email is confirmed.
            </p>
            <div className="flex flex-col gap-3">
              <Link href="/student-login">
                <Button className="w-full bg-accent text-primary hover:bg-accent/90">Go to Student Login</Button>
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
