import Link from "next/link";
import { ArrowRight, CheckCircle2, ChevronRight } from "lucide-react";
import { Navbar } from "@/components/layout/Navbar";
import { SiteFooter } from "@/components/layout/SiteFooter";
import { PageImages } from "@/components/layout/PageImages";

export interface InfoCard {
  title: string;
  text: string;
}

export interface InfoSection {
  heading: string;
  paragraphs?: string[];
  bullets?: string[];
  cards?: InfoCard[];
}

export interface InfoContent {
  slug: string;
  section: string;
  title: string;
  subtitle: string;
  description: string;
  images?: string[];
  captions?: string[];
  sections: InfoSection[];
  cta?: { label: string; href: string; note?: string };
}

export function InfoPage({ content }: { content: InfoContent }) {
  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navbar />
      {/* Hero */}
      <section className="bg-primary text-primary-foreground py-14">
        <div className="container mx-auto px-4">
          <nav className="flex items-center gap-1 text-xs text-primary-foreground/70 mb-3">
            <Link href="/" className="hover:text-accent transition-colors">Home</Link>
            <ChevronRight className="h-3 w-3" />
            <span className="text-accent">{content.section}</span>
            <ChevronRight className="h-3 w-3" />
            <span className="text-primary-foreground/90 font-medium">{content.title}</span>
          </nav>
          <h1 className="text-3xl md:text-5xl font-extrabold text-accent tracking-tight mb-3">{content.title}</h1>
          <p className="text-base md:text-lg text-primary-foreground/80 max-w-3xl">{content.subtitle}</p>
        </div>
      </section>

      {/* Content */}
      <div className="flex-1 container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto space-y-12">
          {content.images && content.images.length > 0 && (
            <PageImages images={content.images} captions={content.captions} />
          )}

          {content.sections.map((section) => (
            <section key={section.heading}>
              <h2 className="text-2xl md:text-3xl font-bold text-foreground mb-4 tracking-tight">{section.heading}</h2>
              {section.paragraphs?.map((p, i) => (
                <p key={i} className="text-muted-foreground leading-relaxed mb-4">{p}</p>
              ))}
              {section.bullets && (
                <ul className="space-y-2 mt-2">
                  {section.bullets.map((b, i) => (
                    <li key={i} className="flex items-start gap-2 text-muted-foreground">
                      <CheckCircle2 className="h-5 w-5 text-accent shrink-0 mt-0.5" />
                      <span className="leading-relaxed">{b}</span>
                    </li>
                  ))}
                </ul>
              )}
              {section.cards && (
                <div className="grid md:grid-cols-3 gap-4 mt-2">
                  {section.cards.map((card) => (
                    <div key={card.title} className="rounded-lg border bg-card p-5">
                      <h3 className="font-semibold text-foreground mb-2">{card.title}</h3>
                      <p className="text-sm text-muted-foreground leading-relaxed">{card.text}</p>
                    </div>
                  ))}
                </div>
              )}
            </section>
          ))}

          {content.cta && (
            <div className="rounded-xl bg-muted/50 border p-8 text-center">
              <h2 className="text-xl font-bold text-foreground mb-2">{content.cta.label}</h2>
              {content.cta.note && <p className="text-muted-foreground text-sm mb-4 max-w-xl mx-auto">{content.cta.note}</p>}
              <Link href={content.cta.href}>
                <span className="inline-flex items-center gap-2 rounded-md bg-accent text-primary px-6 py-3 font-semibold hover:bg-accent/90 transition-colors">
                  {content.cta.label} <ArrowRight className="h-4 w-4" />
                </span>
              </Link>
            </div>
          )}
        </div>
      </div>

      <SiteFooter />
    </div>
  );
}
