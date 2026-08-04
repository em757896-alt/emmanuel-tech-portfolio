"use client";

import Link from "next/link";
import { useSession, signOut } from "next-auth/react";
import { useState } from "react";
import { Menu, X, LogOut, ChevronDown } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { getInitials } from "@/lib/utils";
import { navItems } from "@/lib/site-nav";

export function Navbar() {
  const { data: session } = useSession();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const [openMobileNav, setOpenMobileNav] = useState<string | null>(null);

  const getDashboardLink = () => {
    if (!session) return "/admin-login";
    const role = (session.user as { role: string }).role;
    if (role === "ADMIN") return "/admin";
    if (role === "TEACHER") return "/teacher";
    return "/dashboard";
  };

  const resolveHref = (href: string) => {
    if (!session && href.startsWith("/dashboard")) return "/student-login";
    return href;
  };

  return (
    <header className="sticky top-0 z-50 w-full border-b bg-primary text-primary-foreground">
      {/* Top utility bar */}
      <div className="hidden md:block border-b border-primary-foreground/10">
        <div className="container mx-auto flex h-10 items-center justify-between px-4 text-sm font-medium">
          <p className="text-primary-foreground/75">Empowering the next generation of professionals</p>
          <div className="flex items-center gap-5">
            {session ? (
              <>
                <Link href={getDashboardLink()} className="text-accent hover:underline">My Dashboard</Link>
                <button onClick={() => signOut({ callbackUrl: "/" })} className="flex items-center gap-1 text-primary-foreground/75 hover:text-accent transition-colors">
                  <LogOut className="h-4 w-4" /> Sign Out
                </button>
              </>
            ) : (
              <>
                <Link href="/student-login" className="text-primary-foreground/75 hover:text-accent transition-colors">Student Portal</Link>
                <Link href="/teacher-login" className="text-primary-foreground/75 hover:text-accent transition-colors">Teacher Portal</Link>
                <Link href="/admin-login" className="text-primary-foreground/75 hover:text-accent transition-colors">Admin Portal</Link>
              </>
            )}
          </div>
        </div>
      </div>

      {/* Main bar */}
      <div className="container mx-auto flex h-16 items-center justify-between gap-4 px-4">
        <Link href="/" className="flex items-center gap-2 font-bold text-2xl shrink-0">
          <div className="h-8 w-8 rounded-lg bg-accent flex items-center justify-center text-primary font-bold text-sm">
            EM
          </div>
          <span className="hidden sm:inline">Elevate Media University</span>
          <span className="sm:hidden">EMU</span>
        </Link>

        <nav className="hidden xl:flex items-center gap-0.5">
          {navItems.map((item) =>
            item.columns ? (
              <div key={item.label} className="group relative">
                <button className="flex items-center gap-1 px-3 py-2 text-sm font-medium hover:text-accent transition-colors">
                  {item.label}
                  <ChevronDown className="h-3.5 w-3.5 transition-transform group-hover:rotate-180" />
                </button>
                <div className="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition-all duration-150 absolute left-0 top-full z-50 pt-0">
                  <div className="mt-1 w-max max-w-[min(92vw,80rem)] rounded-lg border border-primary-foreground/10 bg-white p-5 text-foreground shadow-2xl">
                    <div className={`grid gap-8 ${item.wide ? "grid-cols-3" : item.columns.length === 3 ? "grid-cols-3" : item.columns.length === 2 ? "grid-cols-2" : "grid-cols-1"}`}>
                      {item.columns.map((col) => (
                        <div key={col.heading} className="min-w-44">
                          <p className="text-xs font-bold uppercase tracking-wider text-accent mb-3">{col.heading}</p>
                          <ul className="space-y-1">
                            {col.links.map((link) => (
                              <li key={link.href}>
                                <Link
                                  href={resolveHref(link.href)}
                                  className="block rounded-md px-2 py-1.5 text-sm text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                >
                                  {link.label}
                                </Link>
                              </li>
                            ))}
                          </ul>
                        </div>
                      ))}
                    </div>
                    <div className="mt-4 border-t pt-3 flex items-center justify-between">
                      <span className="text-xs text-muted-foreground">{item.label} — Elevate Media University</span>
                      <Link href="/student-apply" className="text-xs font-semibold text-accent hover:underline">Apply Now →</Link>
                    </div>
                  </div>
                </div>
              </div>
            ) : (
              <Link key={item.label} href={item.href!} className="px-3 py-2 text-sm font-medium hover:text-accent transition-colors">
                {item.label}
              </Link>
            )
          )}
        </nav>

        <div className="hidden xl:flex items-center gap-3">
          {session ? (
            <div className="relative">
              <button onClick={() => setUserMenuOpen(!userMenuOpen)} className="flex items-center gap-2 text-sm hover:text-accent transition-colors">
                <Avatar className="h-7 w-7">
                  <AvatarImage src={session.user?.image || undefined} />
                  <AvatarFallback className="bg-accent text-primary text-xs">
                    {getInitials(session.user?.name?.split(" ")[0] || "U", session.user?.name?.split(" ")[1] || "")}
                  </AvatarFallback>
                </Avatar>
                <ChevronDown className="h-4 w-4" />
              </button>
              {userMenuOpen && (
                <div className="absolute right-0 top-full mt-2 w-48 rounded-md border bg-white text-foreground shadow-md">
                  <div className="p-2">
                    <p className="text-sm font-medium px-2">{session.user?.name}</p>
                    <p className="text-xs text-muted-foreground px-2">{session.user?.email}</p>
                  </div>
                  <div className="border-t p-1">
                    <button onClick={() => signOut({ callbackUrl: "/" })} className="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-muted transition-colors">
                      <LogOut className="h-4 w-4" /> Sign out
                    </button>
                  </div>
                </div>
              )}
            </div>
          ) : (
            <Link href="/student-apply">
              <Button size="sm" className="bg-accent text-primary hover:bg-accent/90">Apply Now</Button>
            </Link>
          )}
        </div>

        <button className="xl:hidden" onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
          {mobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
        </button>
      </div>

      {/* Mobile menu */}
      {mobileMenuOpen && (
        <div className="xl:hidden border-t border-primary-foreground/20 px-4 py-3 space-y-1 bg-primary max-h-[calc(100vh-8rem)] overflow-y-auto">
          {navItems.map((item) =>
            item.columns ? (
              <div key={item.label} className="border-b border-primary-foreground/10 py-1">
                <button
                  onClick={() => setOpenMobileNav(openMobileNav === item.label ? null : item.label)}
                  className="flex w-full items-center justify-between text-sm py-2 hover:text-accent"
                >
                  {item.label}
                  <ChevronDown className={`h-4 w-4 transition-transform ${openMobileNav === item.label ? "rotate-180" : ""}`} />
                </button>
                {openMobileNav === item.label && (
                  <div className="pb-3 space-y-3">
                    {item.columns.map((col) => (
                      <div key={col.heading}>
                        <p className="text-xs font-bold uppercase tracking-wider text-accent/80 mb-1">{col.heading}</p>
                        {col.links.map((link) => (
                          <Link key={link.href} href={resolveHref(link.href)} className="block text-sm py-1 pl-2 hover:text-accent" onClick={() => setMobileMenuOpen(false)}>
                            {link.label}
                          </Link>
                        ))}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            ) : (
              <Link key={item.label} href={item.href!} className="block text-sm py-2 hover:text-accent" onClick={() => setMobileMenuOpen(false)}>
                {item.label}
              </Link>
            )
          )}
          <div className="pt-2 space-y-2">
            {session ? (
              <>
                <Link href={getDashboardLink()} className="block text-sm py-2 hover:text-accent" onClick={() => setMobileMenuOpen(false)}>My Dashboard</Link>
                <button onClick={() => signOut({ callbackUrl: "/" })} className="flex items-center gap-2 text-sm py-2 hover:text-accent">
                  <LogOut className="h-4 w-4" /> Sign out
                </button>
              </>
            ) : (
              <>
                <Link href="/student-login" className="block text-sm py-2 hover:text-accent" onClick={() => setMobileMenuOpen(false)}>Student Login</Link>
                <Link href="/teacher-login" className="block text-sm py-2 hover:text-accent" onClick={() => setMobileMenuOpen(false)}>Teacher Login</Link>
                <Link href="/admin-login" className="block text-sm py-2 hover:text-accent" onClick={() => setMobileMenuOpen(false)}>Admin Login</Link>
                <Link href="/student-apply" className="block text-sm py-2 text-accent font-medium" onClick={() => setMobileMenuOpen(false)}>Apply Now</Link>
              </>
            )}
          </div>
        </div>
      )}
    </header>
  );
}
