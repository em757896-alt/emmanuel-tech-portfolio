import Link from "next/link";
import { Mail, Phone, MessageCircle } from "lucide-react";

export function SiteFooter() {
  return (
    <footer className="bg-foreground text-background">
      <div className="container mx-auto px-4 py-12">
        <div className="grid md:grid-cols-2 lg:grid-cols-5 gap-8 mb-8">
          {/* Brand */}
          <div>
            <div className="flex items-center gap-2 font-bold text-2xl mb-3">
              <div className="h-8 w-8 rounded-lg bg-accent flex items-center justify-center text-primary text-xs font-bold">
                EM
              </div>
              Elevate Media University
            </div>
            <p className="text-sm text-background/60 leading-relaxed">
              Empowering the next generation of professionals through innovative education, 
              cutting-edge technology, and a commitment to academic excellence.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="font-semibold text-sm uppercase tracking-wider mb-3 text-background/80">Quick Links</h3>
            <ul className="space-y-2">
              <li><Link href="/" className="text-sm text-background/60 hover:text-accent transition-colors">Home</Link></li>
              <li><Link href="/about" className="text-sm text-background/60 hover:text-accent transition-colors">About Us</Link></li>
              <li><Link href="/courses" className="text-sm text-background/60 hover:text-accent transition-colors">Courses</Link></li>
              <li><Link href="/achievements" className="text-sm text-background/60 hover:text-accent transition-colors">Achievements</Link></li>
              <li><Link href="/research" className="text-sm text-background/60 hover:text-accent transition-colors">Research</Link></li>
              <li><Link href="/library" className="text-sm text-background/60 hover:text-accent transition-colors">Library</Link></li>
              <li><Link href="/student-apply" className="text-sm text-background/60 hover:text-accent transition-colors">Apply Now</Link></li>
            </ul>
          </div>

          {/* Academics */}
          <div>
            <h3 className="font-semibold text-sm uppercase tracking-wider mb-3 text-background/80">Academics</h3>
            <ul className="space-y-2">
              <li><Link href="/academics/programmes" className="text-sm text-background/60 hover:text-accent transition-colors">Programmes</Link></li>
              <li><Link href="/academics/schools" className="text-sm text-background/60 hover:text-accent transition-colors">Schools & Faculties</Link></li>
              <li><Link href="/academics/calendar" className="text-sm text-background/60 hover:text-accent transition-colors">Academic Calendar</Link></li>
              <li><Link href="/academics/examinations" className="text-sm text-background/60 hover:text-accent transition-colors">Examinations</Link></li>
              <li><Link href="/academics/e-learning" className="text-sm text-background/60 hover:text-accent transition-colors">E-Learning</Link></li>
            </ul>
          </div>

          {/* Admissions */}
          <div>
            <h3 className="font-semibold text-sm uppercase tracking-wider mb-3 text-background/80">Admissions</h3>
            <ul className="space-y-2">
              <li><Link href="/student-apply" className="text-sm text-background/60 hover:text-accent transition-colors">Student Application</Link></li>
              <li><Link href="/teacher-apply" className="text-sm text-background/60 hover:text-accent transition-colors">Teacher Application</Link></li>
              <li><Link href="/admission/undergraduate" className="text-sm text-background/60 hover:text-accent transition-colors">Undergraduate</Link></li>
              <li><Link href="/admission/postgraduate" className="text-sm text-background/60 hover:text-accent transition-colors">Postgraduate</Link></li>
              <li><Link href="/admission/fees" className="text-sm text-background/60 hover:text-accent transition-colors">Fees Structure</Link></li>
              <li><Link href="/student-login" className="text-sm text-background/60 hover:text-accent transition-colors">Student Portal</Link></li>
              <li><Link href="/teacher-login" className="text-sm text-background/60 hover:text-accent transition-colors">Teacher Portal</Link></li>
              <li><Link href="/admin-login" className="text-sm text-background/60 hover:text-accent transition-colors">Admin Portal</Link></li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="font-semibold text-sm uppercase tracking-wider mb-3 text-background/80">Contact Us</h3>
            <ul className="space-y-3">
              <li>
                <a 
                  href="https://wa.me/254775333673" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  className="flex items-center gap-2 text-sm text-background/60 hover:text-accent transition-colors"
                >
                  <MessageCircle className="h-4 w-4 flex-shrink-0" />
                  WhatsApp: +254 775 333 673
                </a>
              </li>
              <li>
                <a href="tel:+254111275630" className="flex items-center gap-2 text-sm text-background/60 hover:text-accent transition-colors">
                  <Phone className="h-4 w-4 flex-shrink-0" />
                  Call: +254 111 275 630
                </a>
              </li>
              <li>
                <a href="mailto:em757896@gmail.com" className="flex items-center gap-2 text-sm text-background/60 hover:text-accent transition-colors">
                  <Mail className="h-4 w-4 flex-shrink-0" />
                  em757896@gmail.com
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div className="border-t border-background/10 pt-6">
          <div className="flex flex-col md:flex-row justify-between items-center gap-2">
            <p className="text-xs text-background/40">
              &copy; {new Date().getFullYear()} Elevate Media University. All rights reserved.
            </p>
            <p className="text-xs text-background/40">
              Website created by{" "}
              <a 
                href="https://wa.me/254775333673" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-accent hover:underline"
              >
                Elevate Media Productions
              </a>
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
}
