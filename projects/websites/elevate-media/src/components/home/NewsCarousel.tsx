"use client";

import Link from "next/link";
import { useState, useEffect, useCallback } from "react";
import { ChevronLeft, ChevronRight, Newspaper, CalendarDays, ArrowRight, BookOpen } from "lucide-react";

type NewsItem = {
  title: string;
  category: string;
  date: string;
  summary: string;
  image: string;
  link: string;
};

const news: NewsItem[] = [
  {
    title: "Applications for the 2026/2027 Academic Year Are Now Open",
    category: "Admissions",
    date: "August 2026",
    summary:
      "Join Elevate Media University for the next intake. Explore our undergraduate, postgraduate and online programmes and begin your journey today.",
    image:
      "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1600&q=80&auto=format&fit=crop",
    link: "/admission/undergraduate",
  },
  {
    title: "Elevate Launches a State-of-the-Art Digital Media & Innovation Lab",
    category: "Campus News",
    date: "July 2026",
    summary:
      "Our new media lab features studios, editing suites and virtual production tools that will power the next generation of creatives and storytellers.",
    image:
      "https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1600&q=80&auto=format&fit=crop",
    link: "/library/media-desk",
  },
  {
    title: "Students Win Top Prize at the National Innovation Challenge",
    category: "Achievements",
    date: "July 2026",
    summary:
      "A team of Elevate students impressed judges with an AI-powered learning platform, taking home first place among universities from across the country.",
    image:
      "https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1600&q=80&auto=format&fit=crop",
    link: "/achievements",
  },
  {
    title: "Expanded Scholarship & Bursary Programme Announced",
    category: "Students",
    date: "June 2026",
    summary:
      "More students will access quality education through merit and need-based support. Applications open at the start of the academic year.",
    image:
      "https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=1600&q=80&auto=format&fit=crop",
    link: "/students/scholarships",
  },
  {
    title: "New Research Partnerships Signed with Global Universities",
    category: "Research",
    date: "June 2026",
    summary:
      "Elevate Media University deepens international collaboration, opening new exchange, joint-research and innovation opportunities for staff and students.",
    image:
      "https://images.unsplash.com/photo-1531482615713-2afd69097998?w=1600&q=80&auto=format&fit=crop",
    link: "/research/collaborations",
  },
  {
    title: "Graduation Ceremony Celebrates the Class of 2026",
    category: "Graduation",
    date: "May 2026",
    summary:
      "Families and friends gathered to honour our latest graduates — a proud moment for our community and a new beginning for our alumni.",
    image:
      "https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1600&q=80&auto=format&fit=crop",
    link: "/students/graduation",
  },
];

const categoryColors: Record<string, string> = {
  Admissions: "bg-blue-100 text-blue-700",
  "Campus News": "bg-green-100 text-green-700",
  Achievements: "bg-amber-100 text-amber-700",
  Students: "bg-purple-100 text-purple-700",
  Research: "bg-rose-100 text-rose-700",
  Graduation: "bg-teal-100 text-teal-700",
};

export function NewsCarousel() {
  const [active, setActive] = useState(0);
  const [paused, setPaused] = useState(false);

  const next = useCallback(() => setActive((a) => (a + 1) % news.length), []);
  const prev = useCallback(() => setActive((a) => (a - 1 + news.length) % news.length), []);

  useEffect(() => {
    if (paused) return;
    const id = setInterval(next, 4500);
    return () => clearInterval(id);
  }, [paused, next]);

  const item = news[active];

  return (
    <>
      {/* News & Publications hero slider */}
      <section className="py-16 bg-background">
        <div className="container mx-auto px-4">
          <div className="text-center mb-10">
            <div className="inline-flex items-center gap-2 text-accent font-semibold uppercase tracking-wider text-sm mb-2">
              <Newspaper className="h-4 w-4" /> Stay Informed
            </div>
            <h2 className="text-3xl md:text-4xl font-bold mb-3">News &amp; Publications</h2>
            <p className="text-muted-foreground max-w-2xl mx-auto">
              The latest updates, announcements and colourful stories from across our campus community.
            </p>
          </div>

          {/* Rotating feature slide */}
          <div
            className="relative overflow-hidden rounded-2xl shadow-xl h-[420px] md:h-[480px]"
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
          >
            <img
              key={item.image}
              src={item.image}
              alt={item.title}
              className="absolute inset-0 h-full w-full object-cover animate-fade-in"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent" />

            <div className="absolute bottom-0 left-0 right-0 p-6 md:p-10">
              <div className="flex flex-wrap items-center gap-3 mb-3">
                <span className={`rounded-full px-3 py-1 text-xs font-semibold ${categoryColors[item.category] || "bg-white text-black"}`}>
                  {item.category}
                </span>
                <span className="inline-flex items-center gap-1 text-xs text-white/80">
                  <CalendarDays className="h-3.5 w-3.5" /> {item.date}
                </span>
              </div>
              <h3 className="text-2xl md:text-4xl font-bold text-white mb-3 max-w-3xl leading-tight">
                {item.title}
              </h3>
              <p className="text-white/85 text-sm md:text-base max-w-2xl mb-5">{item.summary}</p>
              <Link
                href={item.link}
                className="inline-flex items-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-primary hover:bg-accent/90 transition-colors"
              >
                Read More <ArrowRight className="h-4 w-4" />
              </Link>
            </div>

            {/* Controls */}
            <button
              onClick={prev}
              aria-label="Previous slide"
              className="absolute left-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-white hover:bg-white/40 transition-colors"
            >
              <ChevronLeft className="h-5 w-5" />
            </button>
            <button
              onClick={next}
              aria-label="Next slide"
              className="absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-white hover:bg-white/40 transition-colors"
            >
              <ChevronRight className="h-5 w-5" />
            </button>

            {/* Dots */}
            <div className="absolute top-4 right-4 flex items-center gap-2">
              {news.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setActive(i)}
                  aria-label={`Go to slide ${i + 1}`}
                  className={`h-2.5 rounded-full transition-all ${i === active ? "w-8 bg-accent" : "w-2.5 bg-white/50 hover:bg-white/80"}`}
                />
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Latest publications grid */}
      <section className="py-14 bg-muted/40">
        <div className="container mx-auto px-4">
          <div className="flex items-end justify-between mb-8">
            <div>
              <div className="inline-flex items-center gap-2 text-accent font-semibold uppercase tracking-wider text-sm mb-2">
                <BookOpen className="h-4 w-4" /> Publications
              </div>
              <h2 className="text-2xl md:text-3xl font-bold">Latest From Campus</h2>
            </div>
            <Link href="/library/news" className="text-sm font-semibold text-accent hover:underline hidden sm:inline-flex items-center gap-1">
              View All <ArrowRight className="h-4 w-4" />
            </Link>
          </div>

          <div className="grid md:grid-cols-3 gap-6">
            {news.slice(0, 3).map((n) => (
              <Link
                key={n.title}
                href={n.link}
                className="group rounded-xl bg-white border shadow-sm overflow-hidden hover:shadow-lg transition-shadow"
              >
                <div className="relative h-44 overflow-hidden">
                  <img
                    src={n.image}
                    alt={n.title}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                  />
                  <span className={`absolute top-3 left-3 rounded-full px-3 py-1 text-xs font-semibold ${categoryColors[n.category] || "bg-white text-black"}`}>
                    {n.category}
                  </span>
                </div>
                <div className="p-5">
                  <p className="text-xs text-muted-foreground mb-2">{n.date}</p>
                  <h3 className="font-semibold text-lg mb-2 leading-snug group-hover:text-accent transition-colors">
                    {n.title}
                  </h3>
                  <p className="text-sm text-muted-foreground line-clamp-2">{n.summary}</p>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
