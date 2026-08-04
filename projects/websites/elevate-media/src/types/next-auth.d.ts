import NextAuth from "next-auth";

declare module "next-auth" {
  interface User {
    role?: string;
    avatar?: string | null;
  }
  interface Session {
    user: {
      id: string;
      email: string;
      name: string | null;
      role: string;
      avatar: string | null;
      image?: string | null;
    };
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    role?: string;
    avatar?: string | null;
  }
}
