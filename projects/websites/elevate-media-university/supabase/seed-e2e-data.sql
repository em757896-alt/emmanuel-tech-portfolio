-- E2E test data: course, enrollment, teacher assignment, HOD flag.
-- Creates one course (CS101) in the Computer Science department, assigns Sarah
-- Jones as its unit lecturer + HOD, and enrolls John Doe (EM20261001).
-- Run once in the Supabase SQL editor (idempotent - safe to re-run).

insert into public.courses (id, name, code, description, credits, "departmentId", semester, year)
values (
  'seed-cs101',
  'Introduction to Programming',
  'CS101',
  'Programming logic, variables, control structures, and basic data structures using Python.',
  3,
  'mry6izv1wvf6kf16',
  1,
  2026
)
on conflict (id) do nothing;

insert into public.course_enrollments (id, "studentId", "courseId", "enrolledAt")
values ('seed-enroll-jd', 'mry6j1mv9zdz3blk', 'seed-cs101', now())
on conflict (id) do nothing;

insert into public.course_assignments (id, "teacherId", "courseId", semester, year)
values ('seed-assign-sj', 'mry6j0nwemy9cb8f', 'seed-cs101', 1, 2026)
on conflict (id) do nothing;

update public.teachers set "isHod" = true where id = 'mry6j0nwemy9cb8f';
