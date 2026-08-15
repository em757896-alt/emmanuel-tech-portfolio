-- Email verification column for students (adds safely; column is a no-op for
-- the login flow if it already exists). Run once in the Supabase SQL editor.
alter table public.students add column if not exists "emailVerified" boolean not null default false;
