"use client";

import Link from "next/link";
import { useSession } from "next-auth/react";
import { Button } from "@/components/ui/button";
import { GraduationCap, BookOpen, Users, Award, ArrowRight, Star } from "lucide-react";
import { Navbar } from "@/components/layout/Navbar";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { NewsCarousel } from "@/components/home/NewsCarousel";

const stats = [
  { label: "Students", value: "500+", icon: GraduationCap },
  { label: "Courses", value: "50+", icon: BookOpen },
  { label: "Faculty", value: "30+", icon: Users },
  { label: "Programs", value: "12", icon: Award },
];

export default function HomePage() {
  const { data: session } = useSession();

  const getDashboardLink = () => {
    if (!session) return "/admin-login";
    const role = (session.user as { role: string })?.role;
    if (role === "ADMIN") return "/admin";
    if (role === "TEACHER") return "/teacher";
    return "/dashboard";
  };

  return (
    <div className="min-h-screen">
      <Navbar />
      {/* Hero Section */}
      <section className="relative bg-primary text-primary-foreground overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-primary via-primary/90 to-primary/70" />
        <div className="absolute inset-0 opacity-10">
          <div className="absolute top-20 left-20 h-72 w-72 rounded-full bg-accent blur-3xl" />
          <div className="absolute bottom-20 right-20 h-96 w-96 rounded-full bg-accent blur-3xl" />
        </div>
        <div className="relative container mx-auto px-4 py-24 md:py-32">
          <div className="max-w-3xl mx-auto text-center">
            <h2 className="text-5xl md:text-7xl font-extrabold text-accent mb-3 tracking-tight">Elevate Media University</h2>
            <p className="text-lg text-primary-foreground/80 mb-8">Empowering the next generation of professionals through innovative education</p>
            <div className="inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-2 mb-6">
              <Star className="h-4 w-4 text-accent" />
              <span className="text-sm font-medium">Welcome to Elevate Media</span>
            </div>
            <h1 className="text-4xl md:text-6xl font-bold mb-6 leading-tight">
              Your Academic Journey{" "}
              <span className="text-accent">Starts Here</span>
            </h1>
            <p className="text-lg md:text-xl text-primary-foreground/80 mb-8 max-w-2xl mx-auto">
              A comprehensive student management platform for modern educational institutions.
              Manage courses, track progress, submit assignments, and connect with your academic community.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              {session ? (
                <Link href={getDashboardLink()}>
                  <Button size="lg" className="bg-accent text-primary hover:bg-accent/90 text-base px-8">
                    Go to Dashboard <ArrowRight className="h-5 w-5" />
                  </Button>
                </Link>
              ) : (
                <>
                  <Link href="/student-apply">
                    <Button size="lg" className="bg-accent text-primary hover:bg-accent/90 text-base px-8">
                      Get Started <ArrowRight className="h-5 w-5" />
                    </Button>
                  </Link>
                  <Link href="/admin-login">
                    <Button size="lg" className="bg-accent text-primary hover:bg-accent/90 text-base px-8">
                      Sign In
                    </Button>
                  </Link>
                </>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-12 bg-white border-b">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat) => (
              <div key={stat.label} className="text-center">
                <div className="inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 mb-3">
                  <stat.icon className="h-6 w-6 text-primary" />
                </div>
                <p className="text-3xl font-bold text-primary">{stat.value}</p>
                <p className="text-sm text-muted-foreground mt-1">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* News & Publications */}
      <NewsCarousel />

      <SiteFooter />
    </div>
  );
}
