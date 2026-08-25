-- ============================================================
-- منصة نجيب — Row Level Security policies
-- ============================================================

-- Role helpers
CREATE OR REPLACE FUNCTION public.current_user_role()
RETURNS public.user_role
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT role FROM public.users WHERE id = auth.uid()
$$;

CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1 FROM public.users
        WHERE id = auth.uid() AND role = 'admin'
    )
$$;

CREATE OR REPLACE FUNCTION public.is_teacher()
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1 FROM public.users
        WHERE id = auth.uid() AND role = 'teacher'
    )
$$;

CREATE OR REPLACE FUNCTION public.is_student()
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1 FROM public.users
        WHERE id = auth.uid() AND role = 'student'
    )
$$;

CREATE OR REPLACE FUNCTION public.owns_course(p_course_id UUID)
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1 FROM public.courses
        WHERE id = p_course_id AND teacher_id = auth.uid()
    )
$$;

CREATE OR REPLACE FUNCTION public.can_access_lesson(p_lesson_id UUID)
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1
        FROM public.lessons l
        JOIN public.units u ON u.id = l.unit_id
        JOIN public.courses c ON c.id = u.course_id
        WHERE l.id = p_lesson_id
          AND (
              public.is_admin()
              OR c.teacher_id = auth.uid()
              OR (c.status = 'live' AND l.is_free_preview = true)
              OR (c.status = 'live' AND c.is_free = true)
              OR public.is_enrolled(auth.uid(), c.id)
          )
    )
$$;

-- Enable RLS on all tables
ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.teacher_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.student_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.courses ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.units ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.lessons ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.subscription_packages ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.subscription_requests ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.enrollments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.comments ENABLE ROW LEVEL SECURITY;

-- Force RLS even for table owners (Supabase service_role bypasses RLS)
ALTER TABLE public.users FORCE ROW LEVEL SECURITY;
ALTER TABLE public.teacher_profiles FORCE ROW LEVEL SECURITY;
ALTER TABLE public.student_profiles FORCE ROW LEVEL SECURITY;
ALTER TABLE public.courses FORCE ROW LEVEL SECURITY;
ALTER TABLE public.units FORCE ROW LEVEL SECURITY;
ALTER TABLE public.lessons FORCE ROW LEVEL SECURITY;
ALTER TABLE public.subscription_packages FORCE ROW LEVEL SECURITY;
ALTER TABLE public.subscription_requests FORCE ROW LEVEL SECURITY;
ALTER TABLE public.enrollments FORCE ROW LEVEL SECURITY;
ALTER TABLE public.comments FORCE ROW LEVEL SECURITY;

-- ============================================================
-- users
-- ============================================================
CREATE POLICY users_select_own ON public.users
    FOR SELECT TO authenticated
    USING (id = auth.uid() OR public.is_admin());

CREATE POLICY users_select_public_teacher_names ON public.users
    FOR SELECT TO authenticated
    USING (
        role = 'teacher'
        AND EXISTS (
            SELECT 1 FROM public.teacher_profiles tp
            WHERE tp.user_id = users.id AND tp.is_verified = true
        )
    );

CREATE POLICY users_update_own ON public.users
    FOR UPDATE TO authenticated
    USING (id = auth.uid() OR public.is_admin())
    WITH CHECK (id = auth.uid() OR public.is_admin());

CREATE POLICY users_insert_admin ON public.users
    FOR INSERT TO authenticated
    WITH CHECK (public.is_admin());

-- ============================================================
-- teacher_profiles
-- ============================================================
CREATE POLICY teacher_profiles_select_own ON public.teacher_profiles
    FOR SELECT TO authenticated
    USING (user_id = auth.uid() OR public.is_admin());

CREATE POLICY teacher_profiles_select_verified ON public.teacher_profiles
    FOR SELECT TO authenticated
    USING (is_verified = true);

CREATE POLICY teacher_profiles_insert_own ON public.teacher_profiles
    FOR INSERT TO authenticated
    WITH CHECK (
        user_id = auth.uid()
        AND public.is_teacher()
    );

CREATE POLICY teacher_profiles_update_own ON public.teacher_profiles
    FOR UPDATE TO authenticated
    USING (user_id = auth.uid() OR public.is_admin())
    WITH CHECK (user_id = auth.uid() OR public.is_admin());

-- ============================================================
-- student_profiles
-- ============================================================
CREATE POLICY student_profiles_select_own ON public.student_profiles
    FOR SELECT TO authenticated
    USING (user_id = auth.uid() OR public.is_admin());

CREATE POLICY student_profiles_select_by_teacher ON public.student_profiles
    FOR SELECT TO authenticated
    USING (
        public.is_teacher()
        AND (
            EXISTS (
                SELECT 1 FROM public.enrollments e
                JOIN public.courses c ON c.id = e.course_id
                WHERE e.student_id = student_profiles.user_id
                  AND c.teacher_id = auth.uid()
            )
            OR EXISTS (
                SELECT 1 FROM public.subscription_requests sr
                JOIN public.courses c ON c.id = sr.course_id
                WHERE sr.student_id = student_profiles.user_id
                  AND c.teacher_id = auth.uid()
            )
        )
    );

CREATE POLICY student_profiles_insert_own ON public.student_profiles
    FOR INSERT TO authenticated
    WITH CHECK (
        user_id = auth.uid()
        AND public.is_student()
    );

CREATE POLICY student_profiles_update_own ON public.student_profiles
    FOR UPDATE TO authenticated
    USING (user_id = auth.uid() OR public.is_admin())
    WITH CHECK (user_id = auth.uid() OR public.is_admin());

-- ============================================================
-- courses
-- ============================================================
CREATE POLICY courses_select_live ON public.courses
    FOR SELECT TO authenticated
    USING (status = 'live');

CREATE POLICY courses_select_own ON public.courses
    FOR SELECT TO authenticated
    USING (teacher_id = auth.uid() OR public.is_admin());

CREATE POLICY courses_select_enrolled ON public.courses
    FOR SELECT TO authenticated
    USING (public.is_enrolled(auth.uid(), id));

CREATE POLICY courses_insert_teacher ON public.courses
    FOR INSERT TO authenticated
    WITH CHECK (
        teacher_id = auth.uid()
        AND (public.is_teacher() OR public.is_admin())
    );

CREATE POLICY courses_update_own ON public.courses
    FOR UPDATE TO authenticated
    USING (teacher_id = auth.uid() OR public.is_admin())
    WITH CHECK (teacher_id = auth.uid() OR public.is_admin());

CREATE POLICY courses_delete_own ON public.courses
    FOR DELETE TO authenticated
    USING (teacher_id = auth.uid() OR public.is_admin());

-- ============================================================
-- units
-- ============================================================
CREATE POLICY units_select_live ON public.units
    FOR SELECT TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM public.courses c
            WHERE c.id = units.course_id AND c.status = 'live'
        )
    );

CREATE POLICY units_select_own ON public.units
    FOR SELECT TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin());

CREATE POLICY units_select_enrolled ON public.units
    FOR SELECT TO authenticated
    USING (public.is_enrolled(auth.uid(), course_id));

CREATE POLICY units_insert_own ON public.units
    FOR INSERT TO authenticated
    WITH CHECK (public.owns_course(course_id) OR public.is_admin());

CREATE POLICY units_update_own ON public.units
    FOR UPDATE TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin())
    WITH CHECK (public.owns_course(course_id) OR public.is_admin());

CREATE POLICY units_delete_own ON public.units
    FOR DELETE TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin());

-- ============================================================
-- lessons
-- ============================================================
CREATE POLICY lessons_select_live_preview ON public.lessons
    FOR SELECT TO authenticated
    USING (
        is_free_preview = true
        AND EXISTS (
            SELECT 1 FROM public.units u
            JOIN public.courses c ON c.id = u.course_id
            WHERE u.id = lessons.unit_id AND c.status = 'live'
        )
    );

CREATE POLICY lessons_select_live_free_course ON public.lessons
    FOR SELECT TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM public.units u
            JOIN public.courses c ON c.id = u.course_id
            WHERE u.id = lessons.unit_id
              AND c.status = 'live'
              AND c.is_free = true
        )
    );

CREATE POLICY lessons_select_own ON public.lessons
    FOR SELECT TO authenticated
    USING (
        public.unit_teacher_id(unit_id) = auth.uid()
        OR public.is_admin()
    );

CREATE POLICY lessons_select_enrolled ON public.lessons
    FOR SELECT TO authenticated
    USING (
        public.is_enrolled(auth.uid(), (
            SELECT u.course_id FROM public.units u WHERE u.id = lessons.unit_id
        ))
    );

CREATE POLICY lessons_insert_own ON public.lessons
    FOR INSERT TO authenticated
    WITH CHECK (
        public.unit_teacher_id(unit_id) = auth.uid()
        OR public.is_admin()
    );

CREATE POLICY lessons_update_own ON public.lessons
    FOR UPDATE TO authenticated
    USING (
        public.unit_teacher_id(unit_id) = auth.uid()
        OR public.is_admin()
    )
    WITH CHECK (
        public.unit_teacher_id(unit_id) = auth.uid()
        OR public.is_admin()
    );

CREATE POLICY lessons_delete_own ON public.lessons
    FOR DELETE TO authenticated
    USING (
        public.unit_teacher_id(unit_id) = auth.uid()
        OR public.is_admin()
    );

-- ============================================================
-- subscription_packages
-- ============================================================
CREATE POLICY subscription_packages_select_live ON public.subscription_packages
    FOR SELECT TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM public.courses c
            WHERE c.id = subscription_packages.course_id AND c.status = 'live'
        )
    );

CREATE POLICY subscription_packages_select_own ON public.subscription_packages
    FOR SELECT TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin());

CREATE POLICY subscription_packages_insert_own ON public.subscription_packages
    FOR INSERT TO authenticated
    WITH CHECK (public.owns_course(course_id) OR public.is_admin());

CREATE POLICY subscription_packages_update_own ON public.subscription_packages
    FOR UPDATE TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin())
    WITH CHECK (public.owns_course(course_id) OR public.is_admin());

CREATE POLICY subscription_packages_delete_own ON public.subscription_packages
    FOR DELETE TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin());

-- ============================================================
-- subscription_requests  ← الطالب يرى اشتراكاته فقط
-- ============================================================
CREATE POLICY subscription_requests_select_own_student ON public.subscription_requests
    FOR SELECT TO authenticated
    USING (student_id = auth.uid());

CREATE POLICY subscription_requests_select_own_teacher ON public.subscription_requests
    FOR SELECT TO authenticated
    USING (public.owns_course(course_id));

CREATE POLICY subscription_requests_select_admin ON public.subscription_requests
    FOR SELECT TO authenticated
    USING (public.is_admin());

CREATE POLICY subscription_requests_insert_student ON public.subscription_requests
    FOR INSERT TO authenticated
    WITH CHECK (
        student_id = auth.uid()
        AND public.is_student()
        AND status = 'pending'
    );

CREATE POLICY subscription_requests_update_teacher ON public.subscription_requests
    FOR UPDATE TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin())
    WITH CHECK (public.owns_course(course_id) OR public.is_admin());

-- ============================================================
-- enrollments  ← الطالب يرى التحاقاته فقط
-- ============================================================
CREATE POLICY enrollments_select_own_student ON public.enrollments
    FOR SELECT TO authenticated
    USING (student_id = auth.uid());

CREATE POLICY enrollments_select_own_teacher ON public.enrollments
    FOR SELECT TO authenticated
    USING (public.owns_course(course_id));

CREATE POLICY enrollments_select_admin ON public.enrollments
    FOR SELECT TO authenticated
    USING (public.is_admin());

CREATE POLICY enrollments_insert_teacher_or_admin ON public.enrollments
    FOR INSERT TO authenticated
    WITH CHECK (
        public.is_admin()
        OR public.owns_course(course_id)
    );

CREATE POLICY enrollments_update_teacher_or_admin ON public.enrollments
    FOR UPDATE TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin())
    WITH CHECK (public.owns_course(course_id) OR public.is_admin());

CREATE POLICY enrollments_delete_teacher_or_admin ON public.enrollments
    FOR DELETE TO authenticated
    USING (public.owns_course(course_id) OR public.is_admin());

-- ============================================================
-- comments
-- ============================================================
CREATE POLICY comments_select_accessible ON public.comments
    FOR SELECT TO authenticated
    USING (public.can_access_lesson(lesson_id));

CREATE POLICY comments_insert_own ON public.comments
    FOR INSERT TO authenticated
    WITH CHECK (
        user_id = auth.uid()
        AND public.can_access_lesson(lesson_id)
    );

CREATE POLICY comments_update_own ON public.comments
    FOR UPDATE TO authenticated
    USING (user_id = auth.uid() OR public.is_admin())
    WITH CHECK (user_id = auth.uid() OR public.is_admin());

CREATE POLICY comments_delete_own ON public.comments
    FOR DELETE TO authenticated
    USING (
        user_id = auth.uid()
        OR public.is_admin()
        OR public.lesson_teacher_id(lesson_id) = auth.uid()
    );
