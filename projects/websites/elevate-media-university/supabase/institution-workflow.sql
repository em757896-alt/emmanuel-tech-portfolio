-- Institution workflow: HOD designation, POE approvals, result documents,
-- daily attendance sign-in + approvals.
-- Run once in the Supabase SQL editor (idempotent - safe to re-run).

-- 1) Teachers: HOD flag (admin designates the department HOD)
alter table public.teachers add column if not exists "isHod" boolean not null default false;

-- 2) POE documents: approval workflow + source
--    status: PENDING / PARTIALLY_APPROVED / APPROVED
--    source: UPLOAD (student manual, must be approved) / RESULT (from verified results)
alter table public.poe_documents add column if not exists "status" text not null default 'PENDING';
alter table public.poe_documents add column if not exists "lecturerApproved" boolean not null default false;
alter table public.poe_documents add column if not exists "hodApproved" boolean not null default false;
alter table public.poe_documents add column if not exists "approvedAt" timestamptz;
alter table public.poe_documents add column if not exists "source" text not null default 'UPLOAD';
alter table public.poe_documents add column if not exists "resultDocumentId" text;
alter table public.poe_documents add column if not exists "courseId" text;

-- 3) Result documents: verified results uploaded by the unit lecturer / HOD.
--    These are the source of truth that students can "Save as POE".
create table if not exists public.result_documents (
  id text primary key,
  "studentId" text not null references public.students(id) on delete cascade,
  "courseId" text references public.courses(id) on delete set null,
  "teacherId" text references public.teachers(id) on delete set null,
  semester int,
  year int,
  title text not null,
  description text,
  grade text,
  "fileUrl" text not null,
  "fileName" text not null,
  "fileType" text,
  "fileSize" int default 0,
  "uploadedAt" timestamptz not null default now()
);

-- 4) Attendance records: daily sign-in + approval workflow.
--    DAILY      -> approved by unit lecturer + HOD
--    CORRECTION -> approved by unit lecturer + Admin
--    sessionId made nullable (QR sessions remain for legacy flow).
alter table public.attendance_records add column if not exists "courseId" text;
alter table public.attendance_records add column if not exists "recordDate" date;
alter table public.attendance_records add column if not exists "lecturerApproved" boolean not null default false;
alter table public.attendance_records add column if not exists "hodApproved" boolean not null default false;
alter table public.attendance_records add column if not exists "adminApproved" boolean not null default false;
alter table public.attendance_records add column if not exists "type" text not null default 'DAILY';
alter table public.attendance_records alter column "sessionId" drop not null;
