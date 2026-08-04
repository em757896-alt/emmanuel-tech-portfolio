"use client";

import { SessionProvider } from "next-auth/react";
import { Navbar } from "./Navbar";
import { Sidebar } from "./Sidebar";
import { useSession } from "next-auth/react";
import { usePathname } from "next/navigation";
import { useState } from "react";
import { Menu } from "lucide-react";

function DashboardShell({ children, role }: { children: React.ReactNode; role: "student" | "teacher" | "admin" }) {
  return (
    <div className="flex min-h-screen">
      <Sidebar role={role} />
      <main className="flex-1 overflow-y-auto">
        <div className="container mx-auto p-6">{children}</div>
      </main>
    </div>
  );
}

function getRole(pathname: string): "student" | "teacher" | "admin" {
  if (pathname.startsWith("/admin")) return "admin";
  if (pathname.startsWith("/teacher")) return "teacher";
  return "student";
}

export function Providers({ children }: { children: React.ReactNode }) {
  return <SessionProvider>{children}</SessionProvider>;
}

export function AppLayout({ children }: { children: React.ReactNode }) {
  return (
    <Providers>
      <div className="flex min-h-screen flex-col">
        <Navbar />
        <main className="flex-1">{children}</main>
      </div>
    </Providers>
  );
}

export function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <Providers>
      <DashboardShellClient>{children}</DashboardShellClient>
    </Providers>
  );
}

function DashboardShellClient({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const role = getRole(pathname);
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="flex min-h-screen flex-col">
      <Navbar />
      <div className="flex flex-1">
        <Sidebar role={role} mobileOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
        <main className="flex-1 overflow-y-auto bg-background">
          <div className="flex items-center border-b bg-background px-3 py-1 md:hidden">
            <button
              onClick={() => setSidebarOpen(true)}
              className="flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-medium text-foreground hover:bg-muted transition-colors"
            >
              <Menu className="h-3.5 w-3.5" /> Menu
            </button>
          </div>
          <div className="container mx-auto p-4 md:p-6">{children}</div>
        </main>
      </div>
    </div>
  );
}
