"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Settings, Database, Shield, Globe } from "lucide-react";

export default function AdminSettings() {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Settings</h1>

        <div className="grid gap-6 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Globe className="h-5 w-5" />
                General
              </CardTitle>
              <CardDescription>Basic system information</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Institution Name</span>
                <span className="font-medium">Elevate Media</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">System Version</span>
                <span className="font-medium">1.0.0</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Platform</span>
                <span className="font-medium">Next.js + Supabase</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Database className="h-5 w-5" />
                Database
              </CardTitle>
              <CardDescription>PostgreSQL via Supabase</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Provider</span>
                <span className="font-medium">PostgreSQL (Supabase)</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">ORM</span>
                <span className="font-medium">Prisma</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Storage</span>
                <span className="font-medium">Supabase Storage</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Shield className="h-5 w-5" />
                Authentication
              </CardTitle>
              <CardDescription>Auth.js configuration</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Provider</span>
                <span className="font-medium">Auth.js (NextAuth)</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Strategy</span>
                <span className="font-medium">JWT</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Roles</span>
                <span className="font-medium">Admin, Teacher, Student</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Settings className="h-5 w-5" />
                Deployment
              </CardTitle>
              <CardDescription>Hosting and deployment</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Platform</span>
                <span className="font-medium">Vercel</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">CI/CD</span>
                <span className="font-medium">GitHub Actions</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Domain</span>
                <span className="font-medium">elevate-media.vercel.app</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
