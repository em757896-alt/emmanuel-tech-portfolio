import { NextResponse } from "next/server";
import { auth } from "@/lib/auth";

const PUBLIC_PAGES = [
  "/", "/admin-login", "/register", "/courses", "/achievements",
  "/student-login", "/student-apply", "/teacher-login", "/teacher-apply",
  "/forgot-password", "/teacher-forgot-password", "/reset-password",
  "/staff-reset-password", "/verification-sent", "/reset-link-sent",
  "/about", "/admission", "/students", "/academics", "/research",
  "/library", "/privacy", "/terms",
];

const PUBLIC_API_PREFIXES = [
  "/api/auth",
  "/api/register",
  "/api/setup",
  "/api/departments",
  "/api/courses",
  "/api/geo/countries",
  "/api/geo/cities",
];

function matches(pathname: string, route: string) {
  return pathname === route || pathname.startsWith(`${route}/`);
}

export default auth((req) => {
  const { pathname } = req.nextUrl;
  const session = req.auth;
  const role = (session?.user as { role?: string } | undefined)?.role;
  const isHod = (session?.user as { hod?: boolean } | undefined)?.hod === true;

  const isPublicPage = PUBLIC_PAGES.some((route) => matches(pathname, route));
  const isPublicApi =
    ((pathname === "/api/auth" || pathname.startsWith("/api/auth/")) && ["GET", "POST"].includes(req.method)) ||
    ((pathname === "/api/register" || pathname.startsWith("/api/register/")) && req.method === "POST") ||
    ((pathname === "/api/setup" || pathname.startsWith("/api/setup/")) && req.method === "POST") ||
    ["/api/departments", "/api/courses", "/api/geo/countries", "/api/geo/cities"].some((route) =>
      matches(pathname, route) && req.method === "GET"
    ) ||
    false;

  // API routes are not public by default. Only the explicitly listed
  // registration, login, setup-token, and public catalogue endpoints bypass auth.
  if (pathname.startsWith("/api/") && !isPublicApi && !session) {
    return NextResponse.json({ error: "Authentication required" }, { status: 401 });
  }

  if (!pathname.startsWith("/api/") && !session && !isPublicPage) {
    return NextResponse.redirect(new URL("/admin-login", req.url));
  }

  if (session) {
    // API namespaces carry the same role boundaries as their dashboard pages.
    if (pathname.startsWith("/api/admin/") && role !== "ADMIN") {
      return NextResponse.json({ error: "Forbidden" }, { status: 403 });
    }
    if (pathname.startsWith("/api/teacher/hod/") && role !== "ADMIN" && !isHod) {
      return NextResponse.json({ error: "Forbidden" }, { status: 403 });
    }
    if (pathname.startsWith("/api/teacher/") && role !== "TEACHER" && role !== "ADMIN") {
      return NextResponse.json({ error: "Forbidden" }, { status: 403 });
    }
    if (pathname.startsWith("/api/student/") && role !== "STUDENT" && role !== "ADMIN") {
      return NextResponse.json({ error: "Forbidden" }, { status: 403 });
    }

    if (["/admin-login", "/register", "/student-login", "/teacher-login"].some((route) => pathname === route)) {
      if (role === "ADMIN") return NextResponse.redirect(new URL("/admin", req.url));
      if (role === "TEACHER") return NextResponse.redirect(new URL(isHod ? "/teacher/hod" : "/teacher", req.url));
      if (role === "STUDENT") return NextResponse.redirect(new URL("/dashboard", req.url));
    }

    if (matches(pathname, "/admin") && role !== "ADMIN") {
      return pathname.startsWith("/api/") 
        ? NextResponse.json({ error: "Forbidden" }, { status: 403 })
        : NextResponse.redirect(new URL("/admin-login", req.url));
    }

    if (matches(pathname, "/teacher/hod") && role !== "ADMIN" && !isHod) {
      return pathname.startsWith("/api/")
        ? NextResponse.json({ error: "Forbidden" }, { status: 403 })
        : NextResponse.redirect(new URL("/teacher", req.url));
    }

    if (matches(pathname, "/teacher") && role !== "TEACHER" && role !== "ADMIN") {
      return pathname.startsWith("/api/")
        ? NextResponse.json({ error: "Forbidden" }, { status: 403 })
        : NextResponse.redirect(new URL("/admin-login", req.url));
    }

    if (matches(pathname, "/dashboard") && role !== "STUDENT" && role !== "ADMIN") {
      return pathname.startsWith("/api/")
        ? NextResponse.json({ error: "Forbidden" }, { status: 403 })
        : NextResponse.redirect(new URL("/admin-login", req.url));
    }
  }

  return NextResponse.next();
});

export const config = {
  matcher: ["/((?!_next/static|_next/image|favicon.ico|public).*)"],
};
