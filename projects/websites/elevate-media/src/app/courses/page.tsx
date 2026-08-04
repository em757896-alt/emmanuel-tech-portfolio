"use client";

import { useState } from "react";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import {
  ArrowLeft, ArrowRight, Clock, BookOpen, Award, ChevronRight, CheckCircle,
} from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { Navbar } from "@/components/layout/Navbar";
import { faculties } from "@/lib/courseCatalog";

export default function CoursesPage() {
  const [selectedFaculty, setSelectedFaculty] = useState<string | null>(null);
  const [selectedDept, setSelectedDept] = useState<string | null>(null);

  const activeFaculty = faculties.find((f) => f.id === selectedFaculty);
  const activeDept = activeFaculty?.departments.find((d) => d.id === selectedDept);

  return (
    <div className="min-h-screen bg-background">
      <Navbar />
      {/* Header */}
      <section className="bg-primary text-primary-foreground py-16">
        <div className="container mx-auto px-4">
          <div className="max-w-3xl">
            <h2 className="text-4xl md:text-5xl font-extrabold text-accent mb-1 tracking-tight">Elevate Media University</h2>
            <p className="text-sm text-primary-foreground/70 mb-4">Empowering the next generation of professionals through innovative education</p>
            {selectedFaculty || selectedDept ? (
              <button onClick={() => { if (selectedDept) { setSelectedDept(null); } else { setSelectedFaculty(null); setSelectedDept(null); } }} className="flex items-center gap-2 text-sm text-primary-foreground/70 hover:text-accent transition-colors mb-4">
                <ArrowLeft className="h-4 w-4" /> {selectedDept ? activeFaculty?.name : "All Faculties"}
              </button>
            ) : null}
            <h1 className="text-3xl md:text-4xl font-bold mb-4">
              {activeDept ? activeDept.name : activeFaculty ? activeFaculty.name : "Courses Offered"}
            </h1>
            <p className="text-primary-foreground/80 text-lg">
              {activeDept
                ? `${activeDept.degree} — ${activeDept.courses.length} courses`
                : activeFaculty
                  ? activeFaculty.description
                  : "Explore our faculties and discover programs designed to build real-world skills for your career."}
            </p>
          </div>
        </div>
      </section>

      <div className="container mx-auto px-4 py-12">
        {/* Level 1: Faculties */}
        {!selectedFaculty && (
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            {faculties.map((f) => (
              <button key={f.id} onClick={() => setSelectedFaculty(f.id)} className="text-left">
                <Card className="hover:shadow-lg transition-all duration-300 h-full">
                  <CardContent className="p-5">
                    <div className="flex items-start gap-3">
                      <div className={`h-12 w-12 rounded-lg ${f.color} flex items-center justify-center flex-shrink-0`}>
                        <f.icon className="h-6 w-6" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <h3 className="text-sm font-semibold mb-1 leading-snug">{f.name}</h3>
                        <p className="text-xs text-muted-foreground mb-2">{f.departments.length} department{f.departments.length > 1 ? "s" : ""}</p>
                        <span className="text-xs text-primary font-medium flex items-center gap-1">
                          View programs <ChevronRight className="h-3 w-3" />
                        </span>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </button>
            ))}
          </div>
        )}

        {/* Level 2: Departments in faculty */}
        {selectedFaculty && !selectedDept && (
          <div className="grid md:grid-cols-2 gap-5">
            {activeFaculty?.departments.map((dept) => (
              <button key={dept.id} onClick={() => setSelectedDept(dept.id)} className="text-left">
                <Card className="hover:shadow-lg transition-all duration-300 h-full">
                  <CardContent className="p-5">
                    <h3 className="text-base font-semibold mb-1">{dept.name}</h3>
                    <p className="text-sm text-muted-foreground mb-1">{dept.degree}</p>
                    <p className="text-xs text-muted-foreground mb-2">{dept.courses.length} courses</p>
                    <span className="text-sm text-primary font-medium flex items-center gap-1">
                      View courses <ChevronRight className="h-4 w-4" />
                    </span>
                  </CardContent>
                </Card>
              </button>
            ))}
          </div>
        )}

        {/* Level 3: Courses */}
        {activeDept && (
          <div>
            <button onClick={() => setSelectedDept(null)} className="flex items-center gap-2 text-sm text-muted-foreground hover:text-primary transition-colors mb-6">
              <ArrowLeft className="h-4 w-4" /> Back to departments
            </button>
            <p className="text-sm text-muted-foreground mb-6">{activeDept.degree}</p>
            <div className="space-y-4">
              {activeDept.courses.map((course, idx) => (
                <Card key={course.code} className="hover:shadow-md transition-all duration-300">
                  <CardContent className="p-5">
                    <div className="flex flex-col md:flex-row md:items-start gap-3">
                      <div className="h-9 w-9 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <span className="text-xs font-bold text-primary">{idx + 1}</span>
                      </div>
                      <div className="flex-1">
                        <div className="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                          <h3 className="text-base font-semibold">{course.title}</h3>
                          <span className="text-xs font-mono bg-muted px-2 py-0.5 rounded w-fit">{course.code}</span>
                        </div>
                        <p className="text-sm text-muted-foreground mb-2">{course.description}</p>
                        <div className="flex flex-wrap gap-3 text-xs text-muted-foreground mb-2">
                          <span className="flex items-center gap-1"><Clock className="h-3 w-3" /> {course.duration}</span>
                          <span className="flex items-center gap-1"><BookOpen className="h-3 w-3" /> {course.credits} Credits</span>
                        </div>
                        <div className="flex items-center gap-3 mb-2">
                          <Link href="/student-apply" className="inline-flex items-center gap-1 text-sm font-semibold text-primary bg-accent/20 hover:bg-accent/40 px-3 py-1 rounded-full transition-colors">
                            Apply Now <ArrowRight className="h-3 w-3" />
                          </Link>
                        </div>
                        <div>
                          <p className="text-xs font-medium mb-1.5 flex items-center gap-1"><Award className="h-3 w-3" /> Skills & Knowledge</p>
                          <div className="flex flex-wrap gap-1.5">
                            {course.skills.map((skill) => (
                              <span key={skill} className="inline-flex items-center gap-1 text-xs bg-primary/5 text-primary px-2 py-0.5 rounded-full">
                                <CheckCircle className="h-3 w-3" /> {skill}
                              </span>
                            ))}
                          </div>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* CTA */}
      <section className="py-12 bg-muted/50">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-2xl font-bold mb-3">Ready to Start Your Journey?</h2>
          <p className="text-muted-foreground mb-6 max-w-lg mx-auto">
            Join Elevate Media University today and take the first step toward your academic and professional goals.
          </p>
          <div className="flex gap-4 justify-center">
            <Link href="/student-apply">
              <Button size="lg" className="bg-accent text-primary hover:bg-accent/90">Apply Now</Button>
            </Link>
            <Link href="/">
              <Button size="lg" variant="outline">Back to Home</Button>
            </Link>
          </div>
        </div>
      </section>

      <SiteFooter />
    </div>
  );
}
