"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Loader2, Mail, Lock, User, Phone, Calendar, ChevronDown, GraduationCap, Globe } from "lucide-react";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { PageImages } from "@/components/layout/PageImages";
import { faculties } from "@/lib/courseCatalog";
import { modesOfLearning } from "@/lib/geoData";

type CountryOption = { code: string; name: string; flag: string };
type CityOption = { id: number; name: string };

function CitySelect({
  options,
  value,
  onChange,
  disabled,
  placeholder,
}: {
  options: CityOption[];
  value: string;
  onChange: (id: string) => void;
  disabled?: boolean;
  placeholder: string;
}) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");
  const selected = options.find((c) => c.id === Number(value));

  const filtered = query.trim() ? options.filter((c) => c.name.toLowerCase().includes(query.toLowerCase())) : options;

  return (
    <div className="relative">
      <button
        type="button"
        disabled={disabled}
        onClick={() => setOpen((o) => !o)}
        className="flex h-10 w-full items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
      >
        {selected ? (
          <span>{selected.name}</span>
        ) : (
          <span className="text-muted-foreground">{placeholder}</span>
        )}
        <ChevronDown className="ml-auto h-4 w-4 text-muted-foreground" />
      </button>
      {open && (
        <>
          <div className="fixed inset-0 z-20" onClick={() => setOpen(false)} />
          <div className="absolute z-30 mt-1 w-full rounded-md border bg-background shadow-lg">
            <div className="p-2">
              <input
                autoFocus
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Search city..."
                className="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
            </div>
            <div className="max-h-80 overflow-y-auto p-1">
              {filtered.length === 0 ? (
                <p className="px-2 py-1.5 text-sm text-muted-foreground">No cities found</p>
              ) : (
                filtered.map((c) => (
                  <button
                    key={c.id}
                    type="button"
                    onClick={() => {
                      onChange(String(c.id));
                      setOpen(false);
                      setQuery("");
                    }}
                    className="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-muted"
                  >
                    <span className="truncate">{c.name}</span>
                  </button>
                ))
              )}
            </div>
            {filtered.length > 50 && (
              <p className="border-t px-3 py-1.5 text-xs text-muted-foreground">{filtered.length} cities found - type to narrow</p>
            )}
          </div>
        </>
      )}
    </div>
  );
}

function CountrySelect({ options, value, onChange }: { options: CountryOption[]; value: string; onChange: (code: string) => void }) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");
  const selected = options.find((c) => c.code === value);

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        className="flex h-10 w-full items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
      >
        {selected ? (
          <>
            <img src={selected.flag} alt="" className="h-4 w-6 rounded-sm object-cover" />
            <span>{selected.name}</span>
          </>
        ) : (
          <span className="text-muted-foreground">Select your country</span>
        )}
        <ChevronDown className="ml-auto h-4 w-4 text-muted-foreground" />
      </button>
      {open && (
        <>
          <div className="fixed inset-0 z-20" onClick={() => setOpen(false)} />
          <div className="absolute z-30 mt-1 w-full rounded-md border bg-background shadow-lg">
            <div className="p-2">
              <input
                autoFocus
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Search country..."
                className="w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              />
            </div>
            <div className="max-h-60 overflow-y-auto p-1">
              {options
                .filter((c) => c.name.toLowerCase().includes(query.toLowerCase()))
                .map((c) => (
                  <button
                    key={c.code}
                    type="button"
                    onClick={() => {
                      onChange(c.code);
                      setOpen(false);
                      setQuery("");
                    }}
                    className="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-muted"
                  >
                    <img src={c.flag} alt="" className="h-4 w-6 rounded-sm object-cover" />
                    <span className="truncate">{c.name}</span>
                  </button>
                ))}
            </div>
          </div>
        </>
      )}
    </div>
  );
}

export default function StudentApplyPage() {
  const [submitted, setSubmitted] = useState(false);
  const [studentId, setStudentId] = useState("");
  const [countriesList, setCountriesList] = useState<CountryOption[]>([]);
  const [cities, setCities] = useState<CityOption[]>([]);
  const [formData, setFormData] = useState({
    firstName: "",
    lastName: "",
    email: "",
    password: "",
    phone: "",
    dateOfBirth: "",
    country: "",
    city: "",
    modeOfLearning: "",
    facultyId: "",
    departmentId: "",
    courseCode: "",
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    let ignore = false;
    fetch("/api/geo/countries")
      .then((r) => r.json())
      .then((d) => {
        if (!ignore) setCountriesList(d.countries || []);
      })
      .catch(() => {});
    return () => {
      ignore = true;
    };
  }, []);

  useEffect(() => {
    let ignore = false;
    setCities([]);
    if (!formData.country) return;
    fetch(`/api/geo/cities?country=${formData.country}`)
      .then((r) => r.json())
      .then((d) => {
        if (!ignore) setCities(d.cities || []);
      })
      .catch(() => {});
    return () => {
      ignore = true;
    };
  }, [formData.country]);

  const activeFaculty = faculties.find((f) => f.id === formData.facultyId);
  const activeDepartment = activeFaculty?.departments.find((d) => d.id === formData.departmentId);
  const activeCourse = activeDepartment?.courses.find((co) => co.code === formData.courseCode);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    if (!formData.city) {
      setError("Please select your city.");
      setLoading(false);
      return;
    }

    try {
      const res = await fetch("/api/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          firstName: formData.firstName,
          lastName: formData.lastName,
          email: formData.email,
          password: formData.password,
          phone: formData.phone,
          dateOfBirth: formData.dateOfBirth || undefined,
          country: countriesList.find((c) => c.code === formData.country)?.name || undefined,
          city: cities.find((c) => c.id === Number(formData.city))?.name || undefined,
          modeOfLearning: formData.modeOfLearning || undefined,
          departmentName: activeDepartment?.name,
          courseName: activeCourse?.title,
          courseCode: activeCourse?.code,
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Application failed");
        return;
      }

      setStudentId(data.studentId || "");
      setSubmitted(true);
    } catch {
      setError("An error occurred. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (submitted) {
      const params = new URLSearchParams({ email: formData.email });
      if (studentId) params.set("admNo", studentId);
      window.location.href = `/verification-sent?${params.toString()}`;
    }
  }, [submitted, studentId, formData.email]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    if (name === "facultyId") {
      setFormData({ ...formData, facultyId: value, departmentId: "", courseCode: "" });
    } else if (name === "departmentId") {
      setFormData({ ...formData, departmentId: value, courseCode: "" });
    } else if (name === "country") {
      setFormData({ ...formData, country: value, city: "" });
    } else {
      setFormData({ ...formData, [name]: value });
    }
  };

  if (submitted) {
    return (
      <div className="min-h-screen flex flex-col">
        <div className="flex-1 flex items-center justify-center bg-background px-4">
          <Card className="w-full max-w-md text-center">
            <CardContent className="p-8">
              <div className="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <Loader2 className="h-8 w-8 animate-spin text-primary" />
              </div>
              <h2 className="text-2xl font-bold mb-2">Redirecting...</h2>
              <p className="text-muted-foreground mb-6">Taking you to the confirmation page.</p>
            </CardContent>
          </Card>
        </div>
        <SiteFooter />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex flex-col items-center justify-center bg-background px-4 py-8">
        <div className="w-full max-w-lg">
          <PageImages
            images={["https://images.unsplash.com/photo-1758270704534-fd9715bffc0e?w=1600&q=80&auto=format&fit=crop"]}
            captions={["Your campus journey starts here"]}
          />
        </div>
        <Card className="w-full max-w-lg">
          <CardHeader className="text-center">
            <Link href="/" className="inline-flex items-center justify-center gap-2 font-bold text-3xl mb-2">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold text-sm">EM</div>
              Elevate Media University
            </Link>
            <CardTitle className="text-2xl">Student Application</CardTitle>
            <CardDescription>Apply as a student at Elevate Media University</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              {error && (
                <Alert variant="destructive">
                  <AlertDescription>{error}</AlertDescription>
                </Alert>
              )}
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="firstName">First Name *</Label>
                  <div className="relative">
                    <User className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input id="firstName" name="firstName" placeholder="John" value={formData.firstName} onChange={handleChange} className="pl-10" required />
                  </div>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="lastName">Last Name *</Label>
                  <Input id="lastName" name="lastName" placeholder="Doe" value={formData.lastName} onChange={handleChange} required />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Email Address *</Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input id="email" name="email" type="email" placeholder="you@example.com" value={formData.email} onChange={handleChange} className="pl-10" required />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Password *</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input id="password" name="password" type="password" placeholder="Min. 6 characters" value={formData.password} onChange={handleChange} className="pl-10" required minLength={6} />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="phone">Phone Number</Label>
                <div className="relative">
                  <Phone className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input id="phone" name="phone" placeholder="+254 700 000 000" value={formData.phone} onChange={handleChange} className="pl-10" />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="dateOfBirth">Date of Birth</Label>
                <div className="relative">
                  <Calendar className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input id="dateOfBirth" name="dateOfBirth" type="date" value={formData.dateOfBirth} onChange={handleChange} className="pl-10" />
                </div>
              </div>

              <div className="border-t pt-4">
                <p className="text-sm font-medium mb-3 flex items-center gap-2"><Globe className="h-4 w-4 text-primary" /> Location & Learning Mode</p>
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="country">Country *</Label>
                    <CountrySelect options={countriesList} value={formData.country} onChange={(code) => setFormData({ ...formData, country: code, city: "" })} />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="city">City *</Label>
                    <CitySelect
                      options={cities}
                      value={formData.city}
                      onChange={(id) => setFormData({ ...formData, city: id })}
                      disabled={!formData.country}
                      placeholder={formData.country ? "Select your city" : "Select country first"}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="modeOfLearning">Mode of Learning *</Label>
                    <Select name="modeOfLearning" value={formData.modeOfLearning} onChange={handleChange} required>
                      <option value="">Select mode of learning</option>
                      {modesOfLearning.map((mode) => (
                        <option key={mode} value={mode}>{mode}</option>
                      ))}
                    </Select>
                  </div>
                </div>
              </div>

              <div className="border-t pt-4">
                <p className="text-sm font-medium mb-3 flex items-center gap-2"><GraduationCap className="h-4 w-4 text-primary" /> Choose Your Program</p>
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="facultyId">Faculty *</Label>
                    <div className="flex items-center gap-2">
                      <Select name="facultyId" value={formData.facultyId} onChange={handleChange} className="flex-1" required>
                        <option value="">Select a faculty</option>
                        {faculties.map((f) => (
                          <option key={f.id} value={f.id}>{f.name}</option>
                        ))}
                      </Select>
                      {activeFaculty && (
                        <span className={`h-8 w-8 rounded-lg ${activeFaculty.color} flex items-center justify-center flex-shrink-0`}>
                          <activeFaculty.icon className="h-4 w-4" />
                        </span>
                      )}
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="departmentId">Department *</Label>
                    <Select name="departmentId" value={formData.departmentId} onChange={handleChange} disabled={!formData.facultyId} required>
                      <option value="">{formData.facultyId ? "Select a department" : "Select faculty first"}</option>
                      {(activeFaculty?.departments || []).map((d) => (
                        <option key={d.id} value={d.id}>{d.name}</option>
                      ))}
                    </Select>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="courseCode">Course *</Label>
                    <Select name="courseCode" value={formData.courseCode} onChange={handleChange} disabled={!formData.departmentId} required>
                      <option value="">{formData.departmentId ? "Select a course" : "Select department first"}</option>
                      {(activeDepartment?.courses || []).map((co) => (
                        <option key={co.code} value={co.code}>{co.title} ({co.code})</option>
                      ))}
                    </Select>
                  </div>
                </div>
              </div>

              <Button type="submit" className="w-full bg-accent text-primary hover:bg-accent/90" disabled={loading}>
                {loading ? (<><Loader2 className="h-4 w-4 animate-spin" /> Submitting Application...</>) : "Submit Application"}
              </Button>
            </form>
            <div className="mt-6 text-center text-sm">
              <span className="text-muted-foreground">Already have an account? </span>
              <Link href="/student-login" className="text-primary hover:underline font-medium">Student Login</Link>
            </div>
          </CardContent>
        </Card>
      </div>
      <SiteFooter />
    </div>
  );
}
