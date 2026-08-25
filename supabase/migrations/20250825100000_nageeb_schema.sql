-- ============================================================
-- منصة نجيب — Schema migration
-- ============================================================

-- Custom types (enums)
CREATE TYPE public.user_role AS ENUM ('admin', 'teacher', 'student');
CREATE TYPE public.student_region AS ENUM ('gaza', 'west_bank_abroad');
CREATE TYPE public.course_status AS ENUM ('draft', 'live', 'archived');
CREATE TYPE public.lesson_content_type AS ENUM ('uploaded_video', 'external_link');
CREATE TYPE public.subscription_request_status AS ENUM ('pending', 'approved', 'rejected');

-- ------------------------------------------------------------
-- 1) users — linked to Supabase Auth
-- ------------------------------------------------------------
CREATE TABLE public.users (
    id          UUID PRIMARY KEY REFERENCES auth.users (id) ON DELETE CASCADE,
    name        TEXT NOT NULL,
    email       TEXT NOT NULL UNIQUE,
    phone       TEXT,
    role        public.user_role NOT NULL DEFAULT 'student',
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_users_role ON public.users (role);
CREATE INDEX idx_users_email ON public.users (email);

-- Auto-create profile row when a user signs up via Supabase Auth
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    INSERT INTO public.users (id, name, email, role)
    VALUES (
        NEW.id,
        COALESCE(NEW.raw_user_meta_data ->> 'name', split_part(NEW.email, '@', 1)),
        NEW.email,
        COALESCE((NEW.raw_user_meta_data ->> 'role')::public.user_role, 'student')
    );
    RETURN NEW;
END;
$$;

CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW
    EXECUTE FUNCTION public.handle_new_user();

-- ------------------------------------------------------------
-- 2) teacher_profiles
-- ------------------------------------------------------------
CREATE TABLE public.teacher_profiles (
    user_id         UUID PRIMARY KEY REFERENCES public.users (id) ON DELETE CASCADE,
    bio             TEXT,
    avatar_url      TEXT,
    specialization  TEXT,
    is_verified     BOOLEAN NOT NULL DEFAULT false
);

-- ------------------------------------------------------------
-- 3) student_profiles
-- ------------------------------------------------------------
CREATE TABLE public.student_profiles (
    user_id      UUID PRIMARY KEY REFERENCES public.users (id) ON DELETE CASCADE,
    region       public.student_region NOT NULL DEFAULT 'gaza',
    grade_level  TEXT
);

-- ------------------------------------------------------------
-- 4) courses
-- ------------------------------------------------------------
CREATE TABLE public.courses (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    teacher_id        UUID NOT NULL REFERENCES public.users (id) ON DELETE RESTRICT,
    title_ar          TEXT NOT NULL,
    description_ar    TEXT,
    grade_level       TEXT,
    status            public.course_status NOT NULL DEFAULT 'draft',
    reference_price   NUMERIC(10, 2),
    cover_image_url   TEXT,
    is_free           BOOLEAN NOT NULL DEFAULT false,
    created_at        TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE OR REPLACE FUNCTION public.enforce_course_teacher_role()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM public.users u
        WHERE u.id = NEW.teacher_id AND u.role IN ('teacher', 'admin')
    ) THEN
        RAISE EXCEPTION 'teacher_id must reference a user with role teacher or admin';
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER courses_teacher_role_check
    BEFORE INSERT OR UPDATE OF teacher_id ON public.courses
    FOR EACH ROW
    EXECUTE FUNCTION public.enforce_course_teacher_role();

CREATE INDEX idx_courses_teacher_id ON public.courses (teacher_id);
CREATE INDEX idx_courses_status ON public.courses (status);
CREATE INDEX idx_courses_grade_level ON public.courses (grade_level);

-- ------------------------------------------------------------
-- 5) units
-- ------------------------------------------------------------
CREATE TABLE public.units (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    course_id    UUID NOT NULL REFERENCES public.courses (id) ON DELETE CASCADE,
    title        TEXT NOT NULL,
    order_index  INTEGER NOT NULL DEFAULT 0,

    CONSTRAINT units_course_order_unique UNIQUE (course_id, order_index)
);

CREATE INDEX idx_units_course_id ON public.units (course_id);

-- ------------------------------------------------------------
-- 6) lessons
-- ------------------------------------------------------------
CREATE TABLE public.lessons (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    unit_id           UUID NOT NULL REFERENCES public.units (id) ON DELETE CASCADE,
    title             TEXT NOT NULL,
    duration_minutes  INTEGER,
    content_type      public.lesson_content_type NOT NULL DEFAULT 'uploaded_video',
    video_url         TEXT,
    is_free_preview   BOOLEAN NOT NULL DEFAULT false,
    order_index       INTEGER NOT NULL DEFAULT 0,

    CONSTRAINT lessons_unit_order_unique UNIQUE (unit_id, order_index)
);

CREATE INDEX idx_lessons_unit_id ON public.lessons (unit_id);

-- ------------------------------------------------------------
-- 7) subscription_packages
-- ------------------------------------------------------------
CREATE TABLE public.subscription_packages (
    id                      UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    course_id               UUID NOT NULL REFERENCES public.courses (id) ON DELETE CASCADE,
    name                    TEXT NOT NULL,
    price_gaza              NUMERIC(10, 2) NOT NULL DEFAULT 0,
    price_west_bank_abroad  NUMERIC(10, 2) NOT NULL DEFAULT 0,
    duration_label          TEXT NOT NULL
);

CREATE INDEX idx_subscription_packages_course_id ON public.subscription_packages (course_id);

-- ------------------------------------------------------------
-- 8) subscription_requests
-- ------------------------------------------------------------
CREATE TABLE public.subscription_requests (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    student_id          UUID NOT NULL REFERENCES public.users (id) ON DELETE CASCADE,
    course_id           UUID NOT NULL REFERENCES public.courses (id) ON DELETE CASCADE,
    package_id          UUID NOT NULL REFERENCES public.subscription_packages (id) ON DELETE RESTRICT,
    receipt_image_url   TEXT,
    status              public.subscription_request_status NOT NULL DEFAULT 'pending',
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    reviewed_at         TIMESTAMPTZ
);

CREATE OR REPLACE FUNCTION public.enforce_subscription_request_package_course()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM public.subscription_packages sp
        WHERE sp.id = NEW.package_id AND sp.course_id = NEW.course_id
    ) THEN
        RAISE EXCEPTION 'package_id must belong to the specified course_id';
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER subscription_requests_package_course_check
    BEFORE INSERT OR UPDATE OF package_id, course_id ON public.subscription_requests
    FOR EACH ROW
    EXECUTE FUNCTION public.enforce_subscription_request_package_course();

CREATE INDEX idx_subscription_requests_student_id ON public.subscription_requests (student_id);
CREATE INDEX idx_subscription_requests_course_id ON public.subscription_requests (course_id);
CREATE INDEX idx_subscription_requests_status ON public.subscription_requests (status);

-- ------------------------------------------------------------
-- 9) enrollments
-- ------------------------------------------------------------
CREATE TABLE public.enrollments (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    student_id   UUID NOT NULL REFERENCES public.users (id) ON DELETE CASCADE,
    course_id    UUID NOT NULL REFERENCES public.courses (id) ON DELETE CASCADE,
    granted_by   UUID REFERENCES public.users (id) ON DELETE SET NULL,
    granted_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    expires_at   TIMESTAMPTZ,

    CONSTRAINT enrollments_student_course_unique UNIQUE (student_id, course_id)
);

CREATE INDEX idx_enrollments_student_id ON public.enrollments (student_id);
CREATE INDEX idx_enrollments_course_id ON public.enrollments (course_id);
CREATE INDEX idx_enrollments_expires_at ON public.enrollments (expires_at);

-- ------------------------------------------------------------
-- 10) comments
-- ------------------------------------------------------------
CREATE TABLE public.comments (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    lesson_id   UUID NOT NULL REFERENCES public.lessons (id) ON DELETE CASCADE,
    user_id     UUID NOT NULL REFERENCES public.users (id) ON DELETE CASCADE,
    message     TEXT NOT NULL,
    parent_id   UUID REFERENCES public.comments (id) ON DELETE CASCADE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_comments_lesson_id ON public.comments (lesson_id);
CREATE INDEX idx_comments_user_id ON public.comments (user_id);
CREATE INDEX idx_comments_parent_id ON public.comments (parent_id);

-- ------------------------------------------------------------
-- Helper: resolve course_id from a lesson (used by RLS)
-- ------------------------------------------------------------
CREATE OR REPLACE FUNCTION public.lesson_course_id(p_lesson_id UUID)
RETURNS UUID
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT u.course_id
    FROM public.lessons l
    JOIN public.units u ON u.id = l.unit_id
    WHERE l.id = p_lesson_id
$$;

CREATE OR REPLACE FUNCTION public.lesson_teacher_id(p_lesson_id UUID)
RETURNS UUID
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT c.teacher_id
    FROM public.lessons l
    JOIN public.units u ON u.id = l.unit_id
    JOIN public.courses c ON c.id = u.course_id
    WHERE l.id = p_lesson_id
$$;

CREATE OR REPLACE FUNCTION public.unit_teacher_id(p_unit_id UUID)
RETURNS UUID
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT c.teacher_id
    FROM public.units u
    JOIN public.courses c ON c.id = u.course_id
    WHERE u.id = p_unit_id
$$;

CREATE OR REPLACE FUNCTION public.is_enrolled(p_student_id UUID, p_course_id UUID)
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1
        FROM public.enrollments e
        WHERE e.student_id = p_student_id
          AND e.course_id = p_course_id
          AND (e.expires_at IS NULL OR e.expires_at > now())
    )
$$;
