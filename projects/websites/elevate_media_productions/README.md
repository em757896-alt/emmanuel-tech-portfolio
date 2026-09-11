# Elevate Media Productions

The official brand platform for **Elevate Media Productions** — a digital studio building web applications, mobile apps and community platforms.

## Tech Stack

- **SvelteKit 2** (Svelte 5 runes) — fast, compiled framework
- **Tailwind CSS 4** — utility-first styling with custom design tokens
- **Supabase** — PostgreSQL database, Auth, Realtime (optional; site works with seeded data)
- **lucide-svelte** — icon set
- **Vercel** — deployment via `@sveltejs/adapter-auto`

## Features

- ✨ Animated landing page with glass-morphism design system
- 🔐 Full authentication (Login, Signup, Forgot password) — ready to connect to Supabase
- 💬 Developer forum with categories, threads, replies and voting
- 📝 Blog with markdown-style articles
- 🗂️ Portfolio gallery with live demos and source links
- 📊 Admin dashboard (projects, blog, forum moderation, users)
- 🌗 Dark / light mode
- 📱 Fully responsive

## Getting Started

```bash
npm install
npm run dev
```

Open [http://localhost:5173](http://localhost:5173).

## Connecting Supabase

1. Create a project at [supabase.com](https://supabase.com)
2. Copy `.env.example` to `.env`
3. Add your `PUBLIC_SUPABASE_URL` and `PUBLIC_SUPABASE_ANON_KEY`
4. Run the schema SQL (see `supabase/schema.sql`) in the Supabase SQL editor

Without Supabase keys, the site runs on seeded data so every feature is browsable.

## Deploying to Vercel

```bash
npm i -g vercel
vercel
```

The `@sveltejs/adapter-auto` adapter detects Vercel automatically.

## Project Structure

```
src/
├── lib/
│   ├── components/    # UI primitives, layout, sections
│   ├── data/          # Seed data (projects, blog, forum)
│   ├── stores/        # Svelte stores (theme, session)
│   └── config.ts      # Site-wide defaults
├── routes/            # App pages
│   ├── about/         # About us
│   ├── services/      # Services
│   ├── portfolio/     # Project gallery
│   ├── skills/        # Tech stack
│   ├── blog/          # Blog
│   ├── forum/         # Developer community
│   ├── auth/          # Auth pages
│   ├── dashboard/     # Admin panel
│   └── contact/       # Contact form
└── app.css            # Design system (Tailwind v4 theme)
```