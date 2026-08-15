import { NextResponse } from "next/server";
import { auth } from "@/lib/auth";

export default auth((req) => {
  const { pathname } = req.nextUrl;
  const session = req.auth;

  if (pathname.startsWith("/api/auth") || pathname.startsWith("/api/register") || pathname.startsWith("/api/setup") || pathname.startsWith("/api/teachers")) {
    return NextResponse.next();
  }

  const publicRoutes = ["/", "/admin-login", "/register", "/courses", "/achievements", "/student-login", "/student-apply", "/teacher-login", "/teacher-apply", "/forgot-password", "/teacher-forgot-password", "/reset-password", "/staff-reset-password", "/verification-sent", "/reset-link-sent", "/about", "/admission", "/students", "/academics", "/research", "/library"];
  const isPublic = publicRoutes.some((route) => pathname === route || pathname.startsWith(`${route}/`) || pathname.startsWith("/api/"));

  if (!session && !isPublic) {
    return NextResponse.redirect(new URL("/admin-login", req.url));
  }

  if (session) {
    const role = (session.user as { role: string })?.role;
    const isHod = (session.user as { hod?: boolean })?.hod === true;

    if (pathname === "/admin-login" || pathname === "/register" || pathname === "/student-login" || pathname === "/teacher-login") {
      if (role === "ADMIN") return NextResponse.redirect(new URL("/admin", req.url));
      if (role === "TEACHER") return NextResponse.redirect(new URL(isHod ? "/teacher/hod" : "/teacher", req.url));
      if (role === "STUDENT") return NextResponse.redirect(new URL("/dashboard", req.url));
    }

    if (pathname.startsWith("/admin") && role !== "ADMIN") {
      return NextResponse.redirect(new URL("/admin-login", req.url));
    }

    if (pathname.startsWith("/teacher/hod") && role !== "ADMIN" && !isHod) {
      return NextResponse.redirect(new URL("/teacher", req.url));
    }

    if (pathname.startsWith("/teacher") && role !== "TEACHER" && role !== "ADMIN") {
      return NextResponse.redirect(new URL("/admin-login", req.url));
    }

    if (pathname.startsWith("/dashboard") && role !== "STUDENT" && role !== "ADMIN") {
      return NextResponse.redirect(new URL("/admin-login", req.url));
    }
  }

  return NextResponse.next();
});

export const config = {
  matcher: ["/((?!_next/static|_next/image|favicon.ico|public).*)"],
};
