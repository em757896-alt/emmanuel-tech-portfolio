-- Elevate Media Productions — Supabase schema
-- Run this in the Supabase SQL Editor after creating a project.

-- ============================================================
-- PROFILES (extends auth.users)
-- ============================================================
create table if not exists public.profiles (
  id uuid primary key references auth.users (id) on delete cascade,
  username text unique,
  full_name text,
  avatar_url text,
  bio text,
  role text not null default 'member' check (role in ('admin', 'moderator', 'member')),
  created_at timestamptz not null default now()
);

alter table public.profiles enable row level security;

create policy "Public profiles are viewable by everyone"
  on public.profiles for select
  using (true);

create policy "Users can update their own profile"
  on public.profiles for update
  using (auth.uid() = id);

-- Auto-create a profile when a user signs up
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer set search_path = public
as $$
begin
  insert into public.profiles (id, full_name, username)
  values (
    new.id,
    coalesce(new.raw_user_meta_data ->> 'full_name', ''),
    coalesce(new.raw_user_meta_data ->> 'username', split_part(new.email, '@', 1))
  )
  on conflict (id) do nothing;
  return new;
end;
$$;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute procedure public.handle_new_user();

-- ============================================================
-- PROJECTS
-- ============================================================
create table if not exists public.projects (
  id uuid primary key default gen_random_uuid(),
  title text not null,
  slug text not null unique,
  description text not null,
  long_description text,
  image_url text,
  demo_url text,
  github_url text,
  tech_tags text[] not null default '{}',
  category text not null default 'Web Application',
  featured boolean not null default false,
  "order" integer not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

alter table public.projects enable row level security;

create policy "Projects are viewable by everyone"
  on public.projects for select using (true);

create policy "Only admins can insert projects"
  on public.projects for insert
  with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

create policy "Only admins can update projects"
  on public.projects for update
  using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

create policy "Only admins can delete projects"
  on public.projects for delete
  using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- BLOG POSTS
-- ============================================================
create table if not exists public.posts (
  id uuid primary key default gen_random_uuid(),
  title text not null,
  slug text not null unique,
  content text not null,
  excerpt text,
  cover_image text,
  author_id uuid references public.profiles (id) on delete set null,
  category text not null default 'Web Development',
  tags text[] not null default '{}',
  reading_time integer not null default 3,
  published boolean not null default false,
  published_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

alter table public.posts enable row level security;

create policy "Published posts are viewable by everyone"
  on public.posts for select
  using (published = true OR exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

create policy "Admin can manage posts"
  on public.posts for all
  using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
  with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- FORUM CATEGORIES
-- ============================================================
create table if not exists public.forum_categories (
  id uuid primary key default gen_random_uuid(),
  name text not null unique,
  slug text not null unique,
  description text,
  icon text not null default 'code',
  color text not null default '#6366f1',
  thread_count integer not null default 0,
  post_count integer not null default 0,
  "order" integer not null default 0
);

alter table public.forum_categories enable row level security;

create policy "Categories are viewable by everyone"
  on public.forum_categories for select using (true);

create policy "Admin can manage categories"
  on public.forum_categories for all
  using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
  with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- FORUM THREADS
-- ============================================================
create table if not exists public.forum_threads (
  id uuid primary key default gen_random_uuid(),
  title text not null,
  slug text not null unique,
  content text not null,
  author_id uuid references public.profiles (id) on delete cascade not null,
  category_id uuid references public.forum_categories (id) on delete cascade not null,
  pinned boolean not null default false,
  locked boolean not null default false,
  upvotes integer not null default 0,
  reply_count integer not null default 0,
  last_reply_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

alter table public.forum_threads enable row level security;

create policy "Threads are viewable by everyone"
  on public.forum_threads for select using (true);

create policy "Authenticated users can post threads"
  on public.forum_threads for insert
  with check (auth.uid() = author_id);

create policy "Authors and admins can update threads"
  on public.forum_threads for update
  using (auth.uid() = author_id OR exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

create policy "Authors and admins can delete threads"
  on public.forum_threads for delete
  using (auth.uid() = author_id OR exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- FORUM REPLIES
-- ============================================================
create table if not exists public.forum_replies (
  id uuid primary key default gen_random_uuid(),
  content text not null,
  thread_id uuid references public.forum_threads (id) on delete cascade not null,
  author_id uuid references public.profiles (id) on delete cascade not null,
  parent_id uuid references public.forum_replies (id) on delete cascade,
  upvotes integer not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

alter table public.forum_replies enable row level security;

create policy "Replies are viewable by everyone"
  on public.forum_replies for select using (true);

create policy "Authenticated users can post replies"
  on public.forum_replies for insert
  with check (auth.uid() = author_id);

create policy "Authors and admins can update replies"
  on public.forum_replies for update
  using (auth.uid() = author_id OR exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

create policy "Authors and admins can delete replies"
  on public.forum_replies for delete
  using (auth.uid() = author_id OR exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- FORUM VOTES
-- ============================================================
create table if not exists public.forum_votes (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references public.profiles (id) on delete cascade not null,
  thread_id uuid references public.forum_threads (id) on delete cascade,
  reply_id uuid references public.forum_replies (id) on delete cascade,
  vote_type integer not null default 1 check (vote_type in (-1, 1)),
  created_at timestamptz not null default now(),
  unique (user_id, thread_id),
  unique (user_id, reply_id)
);

alter table public.forum_votes enable row level security;

create policy "Users can view votes"
  on public.forum_votes for select using (true);

create policy "Users can vote"
  on public.forum_votes for insert
  with check (auth.uid() = user_id);

-- ============================================================
-- NEWSLETTER SUBSCRIBERS
-- ============================================================
create table if not exists public.subscribers (
  id uuid primary key default gen_random_uuid(),
  email text not null unique,
  name text,
  verified boolean not null default false,
  unsubscribed boolean not null default false,
  created_at timestamptz not null default now()
);

alter table public.subscribers enable row level security;

create policy "Anyone can subscribe"
  on public.subscribers for insert
  with check (true);

create policy "Only admins can view subscribers"
  on public.subscribers for select
  using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- CONTACT MESSAGES
-- ============================================================
create table if not exists public.messages (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  email text not null,
  subject text not null,
  message text not null,
  read boolean not null default false,
  replied boolean not null default false,
  created_at timestamptz not null default now()
);

alter table public.messages enable row level security;

create policy "Anyone can send a message"
  on public.messages for insert
  with check (true);

create policy "Only admins can view messages"
  on public.messages for select
  using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- TESTIMONIALS
-- ============================================================
create table if not exists public.testimonials (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  role text,
  company text,
  content text not null,
  avatar_url text,
  rating integer not null default 5 check (rating between 1 and 5),
  featured boolean not null default false,
  created_at timestamptz not null default now()
);

alter table public.testimonials enable row level security;

create policy "Testimonials are viewable by everyone"
  on public.testimonials for select using (true);

create policy "Admin can manage testimonials"
  on public.testimonials for all
  using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
  with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- ============================================================
-- SEED DATA
-- ============================================================
insert into public.forum_categories (name, slug, description, icon, color, thread_count, post_count, "order") values
  ('Announcements', 'announcements', 'Official Elevate Media news, product launches and community updates.', 'megaphone', '#6366f1', 0, 0, 1),
  ('Projects & Portfolio', 'projects-portfolio', 'Showcase your projects, request reviews or discover work from the community.', 'rocket', '#ec4899', 0, 0, 2),
  ('Web Development', 'web-development', 'Svelte, Next.js, React, TypeScript, CSS — all things building for the web.', 'code', '#14b8a6', 0, 0, 3),
  ('Mobile & Native', 'mobile-native', 'Kotlin, Compose, React Native and everything that ships to a pocket.', 'smartphone', '#8b5cf6', 0, 0, 4),
  ('Backend & Databases', 'backend-databases', 'Supabase, PostgreSQL, APIs, auth and serverless architecture.', 'database', '#f59e0b', 0, 0, 5),
  ('Show & Tell', 'show-and-tell', 'Bragging rights section. Share screenshots, demos and wins.', 'trophy', '#22c55e', 0, 0, 6)
on conflict (slug) do nothing;