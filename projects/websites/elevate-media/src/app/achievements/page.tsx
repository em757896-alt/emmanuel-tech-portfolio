"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import {
  Award, Trophy, Star, Users, BookOpen, GraduationCap,
  Globe, TrendingUp, CheckCircle, Target, Heart, Shield,
  Clock, Zap, Laptop,
} from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { Navbar } from "@/components/layout/Navbar";
import { PageImages } from "@/components/layout/PageImages";

const achievements = [
  {
    icon: Trophy,
    title: "95% Graduate Employment Rate",
    description: "Our graduates are employed within 6 months of completion, working at leading tech companies and organizations worldwide.",
    color: "bg-amber-100 text-amber-600",
  },
  {
    icon: Globe,
    title: "International Recognition",
    description: "Accredited programs recognized across 30+ countries, opening doors to global career opportunities.",
    color: "bg-blue-100 text-blue-600",
  },
  {
    icon: Users,
    title: "500+ Active Students",
    description: "A vibrant community of learners from diverse backgrounds collaborating and innovating together.",
    color: "bg-green-100 text-green-600",
  },
  {
    icon: BookOpen,
    title: "50+ Industry-Relevant Courses",
    description: "Curriculum designed with industry partners to ensure you learn skills employers actually need.",
    color: "bg-purple-100 text-purple-600",
  },
  {
    icon: Award,
    title: "Award-Winning Faculty",
    description: "30+ experienced educators who are active researchers and industry practitioners in their fields.",
    color: "bg-rose-100 text-rose-600",
  },
  {
    icon: TrendingUp,
    title: "#1 in Student Satisfaction",
    description: "Consistently ranked highest in student satisfaction surveys for academic support and facilities.",
    color: "bg-cyan-100 text-cyan-600",
  },
];

const whyChooseUs = [
  {
    icon: Target,
    title: "Career-Focused Curriculum",
    description: "Every course is designed with direct input from industry professionals. You learn what matters in the real world, not just theory.",
  },
  {
    icon: Laptop,
    title: "Modern Learning Technology",
    description: "State-of-the-art labs, online learning platforms, QR-based attendance, and AI-powered student management system.",
  },
  {
    icon: Heart,
    title: "Personalized Student Support",
    description: "Dedicated academic advisors, career counseling, mental health support, and small class sizes ensure no student is left behind.",
  },
  {
    icon: Shield,
    title: "Proven Track Record",
    description: "Over a decade of producing industry-ready graduates. Our alumni lead teams at top companies across the globe.",
  },
  {
    icon: Zap,
    title: "Innovation & Research",
    description: "Active research programs, student-led innovation labs, and partnerships with leading research institutions.",
  },
  {
    icon: Users,
    title: "Global Alumni Network",
    description: "Connect with 2,000+ alumni working in 40+ countries. Mentorship, networking, and career opportunities for life.",
  },
];

const milestones = [
  { year: "2012", event: "Institution Founded" },
  { year: "2015", event: "First Accreditation Achieved" },
  { year: "2017", event: "1,000 Students Milestone" },
  { year: "2019", event: "Launched Online Learning Platform" },
  { year: "2021", event: "International Partnerships Established" },
  { year: "2023", event: "AI-Powered Student Management System Deployed" },
  { year: "2025", event: "500+ Active Students, 95% Employment Rate" },
];

export default function AchievementsPage() {
  return (
    <div className="min-h-screen bg-background">
      <Navbar />
      {/* Hero */}
      <section className="bg-primary text-primary-foreground py-20">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-4xl md:text-5xl font-extrabold text-accent mb-1 tracking-tight">Elevate Media University</h2>
          <p className="text-sm text-primary-foreground/70 mb-4">Empowering the next generation of professionals through innovative education</p>
          <div className="inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-2 mb-6">
            <Star className="h-4 w-4 text-accent" />
            <span className="text-sm font-medium">Our Impact</span>
          </div>
          <h1 className="text-3xl md:text-5xl font-bold mb-4">Achievements & Why Choose Us</h1>
          <p className="text-primary-foreground/80 text-lg max-w-2xl mx-auto">
            A decade of academic excellence, innovation, and producing industry-ready graduates.
          </p>
        </div>
      </section>

      {/* Featured image */}
      <section className="py-12">
        <div className="container mx-auto px-4">
          <div className="max-w-4xl mx-auto">
            <PageImages
              images={["https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?w=1600&q=80&auto=format&fit=crop"]}
              captions={["Celebrating a decade of academic excellence"]}
            />
          </div>
        </div>
      </section>

      {/* Achievements Grid */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-2xl font-bold text-center mb-10">Our Achievements</h2>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {achievements.map((item) => (
              <Card key={item.title} className="hover:shadow-lg transition-all duration-300">
                <CardContent className="p-6">
                  <div className={`h-12 w-12 rounded-lg ${item.color} flex items-center justify-center mb-4`}>
                    <item.icon className="h-6 w-6" />
                  </div>
                  <h3 className="text-lg font-semibold mb-2">{item.title}</h3>
                  <p className="text-sm text-muted-foreground">{item.description}</p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Timeline */}
      <section className="py-16 bg-muted/50">
        <div className="container mx-auto px-4">
          <h2 className="text-2xl font-bold text-center mb-10">Our Journey</h2>
          <div className="max-w-2xl mx-auto">
            {milestones.map((m, idx) => (
              <div key={m.year} className="flex gap-4 mb-6 last:mb-0">
                <div className="flex flex-col items-center">
                  <div className="h-10 w-10 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {m.year.slice(2)}
                  </div>
                  {idx < milestones.length - 1 && <div className="w-px flex-1 bg-border mt-2" />}
                </div>
                <div className="pb-6">
                  <p className="text-sm font-semibold text-primary">{m.year}</p>
                  <p className="text-sm text-muted-foreground">{m.event}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Why Choose Us */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-2xl font-bold text-center mb-4">Why Choose Elevate Media?</h2>
          <p className="text-center text-muted-foreground max-w-2xl mx-auto mb-10">
            We do not just teach. We transform students into professionals ready for the challenges of tomorrow.
          </p>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {whyChooseUs.map((item) => (
              <Card key={item.title} className="hover:shadow-lg transition-all duration-300 group">
                <CardContent className="p-6">
                  <div className="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                    <item.icon className="h-6 w-6 text-primary group-hover:text-white transition-colors" />
                  </div>
                  <h3 className="text-lg font-semibold mb-2">{item.title}</h3>
                  <p className="text-sm text-muted-foreground">{item.description}</p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Key Numbers */}
      <section className="py-16 bg-primary text-primary-foreground">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
              <p className="text-4xl font-bold text-accent">2,000+</p>
              <p className="text-sm text-primary-foreground/70 mt-1">Alumni Worldwide</p>
            </div>
            <div>
              <p className="text-4xl font-bold text-accent">95%</p>
              <p className="text-sm text-primary-foreground/70 mt-1">Employment Rate</p>
            </div>
            <div>
              <p className="text-4xl font-bold text-accent">40+</p>
              <p className="text-sm text-primary-foreground/70 mt-1">Countries</p>
            </div>
            <div>
              <p className="text-4xl font-bold text-accent">13</p>
              <p className="text-sm text-primary-foreground/70 mt-1">Years of Excellence</p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-16">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-2xl font-bold mb-3">Be Part of Our Success Story</h2>
          <p className="text-muted-foreground mb-6 max-w-lg mx-auto">
            Join a community that values excellence, innovation, and your personal growth.
          </p>
          <div className="flex gap-4 justify-center">
            <Link href="/register">
              <Button size="lg" className="bg-accent text-primary hover:bg-accent/90">Apply Now</Button>
            </Link>
            <Link href="/courses">
              <Button size="lg" variant="outline">Explore Courses</Button>
            </Link>
          </div>
        </div>
      </section>

      <SiteFooter />
    </div>
  );
}
