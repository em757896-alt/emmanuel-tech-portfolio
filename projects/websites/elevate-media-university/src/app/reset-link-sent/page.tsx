"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { SiteFooter } from "@/components/layout/SiteFooter";

export default function ResetLinkSentPage() {
  const [email, setEmail] = useState("");

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    setEmail(params.get("email") || "");
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
              A password reset link has been sent to <span className="font-medium text-foreground">{email || "your email address"}</span>.
            </p>
            <p className="text-sm text-muted-foreground mb-6">
              If the email and admission number match an active student account, the link will let you set a new password.
            </p>
            <div className="flex flex-col gap-3">
              <Link href="/student-login">
                <Button variant="outline" className="w-full">Back to Student Login</Button>
              </Link>
              <Link href="/">
                <Button variant="ghost" className="w-full">Back to Home</Button>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>
      <SiteFooter />
    </div>
  );
}
