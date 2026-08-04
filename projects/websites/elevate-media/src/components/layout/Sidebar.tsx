"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useSession } from "next-auth/react";
import { cn } from "@/lib/cn";
import {
  LayoutDashboard, BookOpen, GraduationCap, FileText, ClipboardCheck,
  Trophy, FolderOpen, Megaphone, User, Settings, Users, Building2,
  BookMarked, Star, Award, Brain, Bell, ChevronLeft, ChevronRight, X
} from "lucide-react";
import { useState } from "react";

interface SidebarProps {
  role: "student" | "teacher" | "admin";
  mobileOpen?: boolean;
  onClose?: () => void;
}

const studentLinks = [
  { href: "/dashboard", label: "Overview", icon: LayoutDashboard },
  { href: "/dashboard/courses", label: "My Courses", icon: BookOpen },
  { href: "/dashboard/results", label: "Results", icon: Trophy },
  { href: "/dashboard/assignments", label: "Assignments", icon: FileText },
  { href: "/dashboard/exams", label: "Exams", icon: ClipboardCheck },
  { href: "/dashboard/attendance", label: "Attendance", icon: Award },
  { href: "/dashboard/poe", label: "POE Documents", icon: FolderOpen },
  { href: "/dashboard/research", label: "Research", icon: Brain },
  { href: "/dashboard/announcements", label: "Announcements", icon: Megaphone },
  { href: "/dashboard/notifications", label: "Notifications", icon: Bell },
  { href: "/dashboard/profile", label: "Profile", icon: User },
];

const lecturerLinks = [
  { href: "/teacher", label: "Overview", icon: LayoutDashboard },
  { href: "/teacher/courses", label: "My Courses", icon: BookOpen },
  { href: "/teacher/assignments", label: "Assignments", icon: FileText },
  { href: "/teacher/submissions", label: "Submissions", icon: Star },
  { href: "/teacher/exams", label: "Exams", icon: ClipboardCheck },
  { href: "/teacher/grades", label: "Grades", icon: Trophy },
  { href: "/teacher/results", label: "Results Upload", icon: FileText },
  { href: "/teacher/attendance", label: "Attendance", icon: Award },
  { href: "/teacher/poe-approvals", label: "POE Approvals", icon: FolderOpen },
  { href: "/teacher/research", label: "Research", icon: Brain },
  { href: "/teacher/announcements", label: "Announcements", icon: Megaphone },
  { href: "/teacher/notifications", label: "Notifications", icon: Bell },
  { href: "/teacher/profile", label: "Profile", icon: User },
];

function hodLinks(isFaculty: boolean) {
  const prefix = isFaculty ? "Faculty" : "Department";
  return [
    { href: "/teacher/hod", label: `${prefix} Overview`, icon: LayoutDashboard },
    { href: "/teacher/hod/students", label: `${prefix} Students`, icon: Users },
    { href: "/teacher/hod/courses", label: `${prefix} Courses`, icon: Building2 },
    { href: "/teacher/hod/approvals", label: `${prefix} Approvals`, icon: ClipboardCheck },
    { href: "/teacher/profile", label: "Profile", icon: User },
  ];
}

const adminLinks = [
  { href: "/admin", label: "Overview", icon: LayoutDashboard },
  { href: "/admin/students", label: "Students", icon: GraduationCap },
  { href: "/admin/teachers", label: "Teachers", icon: Users },
  { href: "/admin/courses", label: "Courses", icon: BookOpen },
  { href: "/admin/departments", label: "Departments", icon: Building2 },
  { href: "/admin/enrollments", label: "Enrollments", icon: BookMarked },
  { href: "/admin/announcements", label: "Announcements", icon: Megaphone },
  { href: "/admin/notifications", label: "Notifications", icon: Bell },
  { href: "/admin/settings", label: "Settings", icon: Settings },
];

export function Sidebar({ role, mobileOpen = false, onClose }: SidebarProps) {
  const pathname = usePathname();
  const { data: session } = useSession();
  const [collapsed, setCollapsed] = useState(false);

  const isHod = (session?.user as { hod?: boolean } | undefined)?.hod === true;
  const isFacultyHod = (session?.user as { facultyHod?: boolean } | undefined)?.facultyHod === true;

  const links = role === "admin" ? adminLinks : role === "teacher" ? (isHod ? hodLinks(isFacultyHod) : lecturerLinks) : studentLinks;
  const baseHref = role === "admin" ? "/admin" : role === "teacher" ? (isHod ? "/teacher/hod" : "/teacher") : "/dashboard";

  return (
    <>
      {mobileOpen && (
        <div
          className="fixed inset-0 z-40 bg-black/40 md:hidden"
          onClick={onClose}
          aria-hidden
        />
      )}
      <aside
        className={cn(
          "flex flex-col bg-white border-r transition-all duration-300",
          // Mobile: slide-in drawer below the navbar
          "fixed inset-y-0 left-0 top-16 z-50 h-[calc(100vh-4rem)] w-64 -translate-x-full",
          mobileOpen && "translate-x-0",
          // Desktop: unchanged sticky sidebar
          "md:sticky md:top-16 md:z-auto md:h-[calc(100vh-4rem)] md:translate-x-0",
          collapsed ? "md:w-16" : "md:w-64"
        )}
      >
        <div className="flex items-center justify-between border-b px-4 py-3 md:hidden">
          <span className="text-sm font-bold text-foreground">Menu</span>
          <button
            onClick={onClose}
            aria-label="Close menu"
            className="rounded-md p-1 text-muted-foreground hover:bg-muted"
          >
            <X className="h-5 w-5" />
          </button>
        </div>
        <div className="flex-1 overflow-y-auto py-4">
          <nav className="space-y-1 px-2">
            {role === "teacher" && (
              <p className={cn("mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground", collapsed && "md:hidden")}>
                {isHod ? "Head of Department" : "Unit Lecturer"}
              </p>
            )}
            {links.map((link) => {
              const isActive = link.href === baseHref ? pathname === baseHref : pathname.startsWith(link.href);
              return (
                <Link
                  key={link.href}
                  href={link.href}
                  onClick={onClose}
                  className={cn(
                    "flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors",
                    isActive
                      ? "bg-primary text-primary-foreground"
                      : "text-muted-foreground hover:bg-muted hover:text-foreground",
                    collapsed && "md:justify-center md:px-2"
                  )}
                  title={collapsed ? link.label : undefined}
                >
                  <link.icon className="h-4 w-4 shrink-0" />
                  {!collapsed && <span>{link.label}</span>}
                </Link>
              );
            })}
          </nav>
        </div>
        <div className="hidden border-t p-2 md:block">
          <button
            onClick={() => setCollapsed(!collapsed)}
            className="flex w-full items-center justify-center rounded-md p-2 text-muted-foreground hover:bg-muted transition-colors"
          >
            {collapsed ? <ChevronRight className="h-4 w-4" /> : <ChevronLeft className="h-4 w-4" />}
          </button>
        </div>
      </aside>
    </>
  );
}
