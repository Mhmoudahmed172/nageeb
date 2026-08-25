-- ============================================================
-- منصة نجيب — RLS policy tests (pgTAP)
-- Run: supabase db test
-- ============================================================

BEGIN;

SELECT plan(28);

-- ------------------------------------------------------------
-- Test fixtures
-- ------------------------------------------------------------
CREATE TEMP TABLE test_ids AS
SELECT
    '11111111-1111-1111-1111-111111111111'::UUID AS admin_id,
    '22222222-2222-2222-2222-222222222222'::UUID AS teacher_a_id,
    '33333333-3333-3333-3333-333333333333'::UUID AS teacher_b_id,
    '44444444-4444-4444-4444-444444444444'::UUID AS student_a_id,
    '55555555-5555-5555-5555-555555555555'::UUID AS student_b_id;

-- auth.users (required FK for public.users)
INSERT INTO auth.users (id, email, encrypted_password, email_confirmed_at, raw_user_meta_data, created_at, updated_at)
SELECT admin_id, 'admin@nageeb.test', crypt('password', gen_salt('bf')), now(), '{"name":"Admin"}', now(), now() FROM test_ids
UNION ALL
SELECT teacher_a_id, 'teacher-a@nageeb.test', crypt('password', gen_salt('bf')), now(), '{"name":"Teacher A","role":"teacher"}', now(), now() FROM test_ids
UNION ALL
SELECT teacher_b_id, 'teacher-b@nageeb.test', crypt('password', gen_salt('bf')), now(), '{"name":"Teacher B","role":"teacher"}', now(), now() FROM test_ids
UNION ALL
SELECT student_a_id, 'student-a@nageeb.test', crypt('password', gen_salt('bf')), now(), '{"name":"Student A","role":"student"}', now(), now() FROM test_ids
UNION ALL
SELECT student_b_id, 'student-b@nageeb.test', crypt('password', gen_salt('bf')), now(), '{"name":"Student B","role":"student"}', now(), now() FROM test_ids;

-- Override auto-created roles from trigger
UPDATE public.users SET role = 'admin' WHERE id = (SELECT admin_id FROM test_ids);
UPDATE public.users SET role = 'teacher' WHERE id IN (
    SELECT teacher_a_id FROM test_ids UNION SELECT teacher_b_id FROM test_ids
);
UPDATE public.users SET role = 'student' WHERE id IN (
    SELECT student_a_id FROM test_ids UNION SELECT student_b_id FROM test_ids
);

INSERT INTO public.teacher_profiles (user_id, bio, specialization, is_verified)
SELECT teacher_a_id, 'Bio A', 'Math', true FROM test_ids
UNION ALL
SELECT teacher_b_id, 'Bio B', 'Physics', true FROM test_ids;

INSERT INTO public.student_profiles (user_id, region, grade_level)
SELECT student_a_id, 'gaza', '10' FROM test_ids
UNION ALL
SELECT student_b_id, 'west_bank_abroad', '11' FROM test_ids;

INSERT INTO public.courses (id, teacher_id, title_ar, status, is_free)
SELECT
    'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'::UUID,
    teacher_a_id,
    'دورة المعلم أ',
    'live',
    false
FROM test_ids
UNION ALL
SELECT
    'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'::UUID,
    teacher_b_id,
    'دورة المعلم ب',
    'draft',
    false
FROM test_ids;

INSERT INTO public.units (id, course_id, title, order_index)
VALUES
    ('cccccccc-cccc-cccc-cccc-cccccccccccc', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'الوحدة 1', 0),
    ('dddddddd-dddd-dddd-dddd-dddddddddddd', 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'الوحدة 1', 0);

INSERT INTO public.lessons (id, unit_id, title, is_free_preview, order_index)
VALUES
    ('eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee', 'cccccccc-cccc-cccc-cccc-cccccccccccc', 'درس معاينة', true, 0),
    ('ffffffff-ffff-ffff-ffff-ffffffffffff', 'cccccccc-cccc-cccc-cccc-cccccccccccc', 'درس مدفوع', false, 1);

INSERT INTO public.subscription_packages (id, course_id, name, price_gaza, price_west_bank_abroad, duration_label)
VALUES
    ('99999999-9999-9999-9999-999999999901', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'باقة شهر', 50, 70, '1 month');

INSERT INTO public.subscription_requests (id, student_id, course_id, package_id, status)
VALUES
    ('99999999-9999-9999-9999-999999999911', '44444444-4444-4444-4444-444444444444', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '99999999-9999-9999-9999-999999999901', 'pending'),
    ('99999999-9999-9999-9999-999999999912', '55555555-5555-5555-5555-555555555555', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '99999999-9999-9999-9999-999999999901', 'pending');

INSERT INTO public.enrollments (id, student_id, course_id, granted_by)
VALUES
    ('99999999-9999-9999-9999-999999999921', '44444444-4444-4444-4444-444444444444', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '22222222-2222-2222-2222-222222222222');

-- Helper: impersonate authenticated user
CREATE OR REPLACE FUNCTION pg_temp.set_user(p_user_id UUID)
RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
    PERFORM set_config('role', 'authenticated', true);
    PERFORM set_config('request.jwt.claim.sub', p_user_id::TEXT, true);
    PERFORM set_config('request.jwt.claim.role', 'authenticated', true);
END;
$$;

-- ============================================================
-- Teacher isolation: teacher A cannot see teacher B draft course
-- ============================================================
SELECT pg_temp.set_user('22222222-2222-2222-2222-222222222222');

SELECT is(
    (SELECT count(*)::INT FROM public.courses WHERE id = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
    0,
    'Teacher A cannot see Teacher B draft course'
);

SELECT is(
    (SELECT count(*)::INT FROM public.courses WHERE teacher_id = '22222222-2222-2222-2222-222222222222'),
    1,
    'Teacher A sees only own courses (including live)'
);

-- ============================================================
-- Teacher sees subscription requests for own courses only
-- ============================================================
SELECT is(
    (SELECT count(*)::INT FROM public.subscription_requests),
    2,
    'Teacher A sees both subscription requests for own live course'
);

SELECT pg_temp.set_user('33333333-3333-3333-3333-333333333333');

SELECT is(
    (SELECT count(*)::INT FROM public.subscription_requests),
    0,
    'Teacher B sees no subscription requests (not their course)'
);

-- ============================================================
-- Student isolation: each student sees only own subscriptions
-- ============================================================
SELECT pg_temp.set_user('44444444-4444-4444-4444-444444444444');

SELECT is(
    (SELECT count(*)::INT FROM public.subscription_requests),
    1,
    'Student A sees only own subscription request'
);

SELECT is(
    (SELECT student_id FROM public.subscription_requests LIMIT 1),
    '44444444-4444-4444-4444-444444444444'::UUID,
    'Student A subscription request belongs to Student A'
);

SELECT pg_temp.set_user('55555555-5555-5555-5555-555555555555');

SELECT is(
    (SELECT count(*)::INT FROM public.subscription_requests),
    1,
    'Student B sees only own subscription request'
);

SELECT is(
    (SELECT student_id FROM public.subscription_requests LIMIT 1),
    '55555555-5555-5555-5555-555555555555'::UUID,
    'Student B subscription request belongs to Student B'
);

-- ============================================================
-- Student isolation: each student sees only own enrollments
-- ============================================================
SELECT pg_temp.set_user('44444444-4444-4444-4444-444444444444');

SELECT is(
    (SELECT count(*)::INT FROM public.enrollments),
    1,
    'Student A sees only own enrollment'
);

SELECT pg_temp.set_user('55555555-5555-5555-5555-555555555555');

SELECT is(
    (SELECT count(*)::INT FROM public.enrollments),
    0,
    'Student B has no enrollments and sees none'
);

-- ============================================================
-- Student can browse live course catalog
-- ============================================================
SELECT pg_temp.set_user('44444444-4444-4444-4444-444444444444');

SELECT ok(
    EXISTS (SELECT 1 FROM public.courses WHERE id = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa' AND status = 'live'),
    'Student can see live courses in catalog'
);

SELECT ok(
    NOT EXISTS (SELECT 1 FROM public.courses WHERE id = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
    'Student cannot see draft courses of other teachers'
);

-- ============================================================
-- Lesson access: preview vs enrolled vs paid
-- ============================================================
SELECT is(
    (SELECT count(*)::INT FROM public.lessons WHERE id = 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee'),
    1,
    'Student sees free preview lesson on live course'
);

SELECT is(
    (SELECT count(*)::INT FROM public.lessons WHERE id = 'ffffffff-ffff-ffff-ffff-ffffffffffff'),
    1,
    'Enrolled student sees paid lesson'
);

-- Unenrolled student should not see paid lesson
SELECT pg_temp.set_user('55555555-5555-5555-5555-555555555555');

SELECT is(
    (SELECT count(*)::INT FROM public.lessons WHERE id = 'ffffffff-ffff-ffff-ffff-ffffffffffff'),
    0,
    'Non-enrolled student cannot see paid lesson'
);

-- ============================================================
-- Teacher manages own course content
-- ============================================================
SELECT pg_temp.set_user('22222222-2222-2222-2222-222222222222');

SELECT lives_ok(
    $$ INSERT INTO public.units (course_id, title, order_index)
       VALUES ('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'وحدة جديدة', 1) $$,
    'Teacher A can insert unit into own course'
);

SELECT throws_ok(
    $$ INSERT INTO public.units (course_id, title, order_index)
       VALUES ('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'وحدة مرفوضة', 1) $$,
    '42501',
    NULL,
    'Teacher A cannot insert unit into Teacher B course'
);

-- ============================================================
-- Admin full access
-- ============================================================
SELECT pg_temp.set_user('11111111-1111-1111-1111-111111111111');

SELECT is(
    (SELECT count(*)::INT FROM public.courses),
    2,
    'Admin sees all courses'
);

SELECT is(
    (SELECT count(*)::INT FROM public.subscription_requests),
    2,
    'Admin sees all subscription requests'
);

SELECT is(
    (SELECT count(*)::INT FROM public.enrollments),
    1,
    'Admin sees all enrollments'
);

-- ============================================================
-- RLS is enabled on all tables
-- ============================================================
SELECT ok(
    (SELECT bool_and(relrowsecurity) FROM pg_class WHERE relname = 'users' AND relnamespace = 'public'::regnamespace),
    'RLS enabled on users'
);

SELECT ok(
    (SELECT bool_and(relrowsecurity) FROM pg_class WHERE relname = 'courses' AND relnamespace = 'public'::regnamespace),
    'RLS enabled on courses'
);

SELECT ok(
    (SELECT bool_and(relrowsecurity) FROM pg_class WHERE relname = 'subscription_requests' AND relnamespace = 'public'::regnamespace),
    'RLS enabled on subscription_requests'
);

SELECT ok(
    (SELECT bool_and(relrowsecurity) FROM pg_class WHERE relname = 'enrollments' AND relnamespace = 'public'::regnamespace),
    'RLS enabled on enrollments'
);

SELECT ok(
    (SELECT bool_and(relrowsecurity) FROM pg_class
     WHERE relname IN ('teacher_profiles','student_profiles','units','lessons','subscription_packages','comments')
       AND relnamespace = 'public'::regnamespace),
    'RLS enabled on remaining tables'
);

SELECT * FROM finish();

ROLLBACK;
