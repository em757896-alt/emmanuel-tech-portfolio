-- Verification & release workflow: faculties, teacher email login prep,
-- two-stage (unit lecturer + HOD) verification with accountability, release locking.
-- Run once in the Supabase SQL editor (idempotent - safe to re-run).

-- 1) Faculties (faculty -> departments -> courses)
create table if not exists public.faculties (
  id text primary key,
  name text not null unique,
  code text not null unique,
  description text
);
alter table public.faculties add column if not exists "hodTeacherId" text;

alter table public.departments add column if not exists "facultyId" text;
alter table public.teachers add column if not exists "facultyId" text;

-- 2) Uploads (POE / attendance / research) - two-stage verification + accountability.
--    lecturerVerifiedByRole = 'LECTURER' (the actual unit lecturer) or 'HOD' (HOD acted on behalf).
alter table public.poe_documents add column if not exists "lecturerVerifiedBy" text;
alter table public.poe_documents add column if not exists "lecturerVerifiedByRole" text;
alter table public.poe_documents add column if not exists "hodVerifiedBy" text;

alter table public.attendance_records add column if not exists "lecturerVerifiedBy" text;
alter table public.attendance_records add column if not exists "lecturerVerifiedByRole" text;
alter table public.attendance_records add column if not exists "hodVerifiedBy" text;

alter table public.research add column if not exists "courseId" text;
alter table public.research add column if not exists "lecturerApproved" boolean not null default false;
alter table public.research add column if not exists "lecturerVerifiedBy" text;
alter table public.research add column if not exists "lecturerVerifiedByRole" text;
alter table public.research add column if not exists "hodApproved" boolean not null default false;
alter table public.research add column if not exists "hodVerifiedBy" text;
alter table public.research add column if not exists "rejected" boolean not null default false;
alter table public.research add column if not exists "rejectionReason" text;

-- 3) Releases (results / exams / assignments) - visible to students once "released",
--    but downloads stay locked until BOTH lecturer + HOD have verified.
alter table public.result_documents add column if not exists "released" boolean not null default false;
alter table public.result_documents add column if not exists "lecturerApproved" boolean not null default false;
alter table public.result_documents add column if not exists "lecturerVerifiedBy" text;
alter table public.result_documents add column if not exists "lecturerVerifiedByRole" text;
alter table public.result_documents add column if not exists "hodApproved" boolean not null default false;
alter table public.result_documents add column if not exists "hodVerifiedBy" text;
alter table public.result_documents add column if not exists "status" text not null default 'PENDING';

alter table public.assignments add column if not exists "released" boolean not null default false;
alter table public.assignments add column if not exists "lecturerApproved" boolean not null default false;
alter table public.assignments add column if not exists "lecturerVerifiedBy" text;
alter table public.assignments add column if not exists "lecturerVerifiedByRole" text;
alter table public.assignments add column if not exists "hodApproved" boolean not null default false;
alter table public.assignments add column if not exists "hodVerifiedBy" text;
alter table public.assignments add column if not exists "status" text not null default 'PENDING';

alter table public.exams add column if not exists "fileUrl" text;
alter table public.exams add column if not exists "fileName" text;
alter table public.exams add column if not exists "released" boolean not null default false;
alter table public.exams add column if not exists "lecturerApproved" boolean not null default false;
alter table public.exams add column if not exists "lecturerVerifiedBy" text;
alter table public.exams add column if not exists "lecturerVerifiedByRole" text;
alter table public.exams add column if not exists "hodApproved" boolean not null default false;
alter table public.exams add column if not exists "hodVerifiedBy" text;
alter table public.exams add column if not exists "status" text not null default 'PENDING';
