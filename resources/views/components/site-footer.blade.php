<footer class="nageeb-footer">
    <div class="nageeb-container">
        <div class="nageeb-footer__grid">
            <div>
                <a href="{{ url('/') }}" class="nageeb-public-nav__brand">
                    <span class="nageeb-mark">ن</span>
                    <span>نجيب</span>
                </a>
                <p class="nageeb-type-body-sm nageeb-text-muted mt-4 max-w-xs leading-relaxed">
                    منصة تعليمية متكاملة تجمع الطلاب والمعلمين في تجربة تعليمية أكثر وضوحًا وفاعلية.
                </p>
            </div>
            <div>
                <p class="nageeb-type-label">المنصة</p>
                <div class="nageeb-footer__list">
                    <a href="{{ url('/') }}">الرئيسية</a>
                    <a href="{{ route('courses.index') }}">المواد التعليمية</a>
                    <a href="{{ url('/#teachers') }}">المعلمون</a>
                    <a href="{{ route('login') }}">تسجيل الدخول</a>
                </div>
            </div>
            <div>
                <p class="nageeb-type-label">للمعلمين</p>
                <div class="nageeb-footer__list">
                    @auth
                        @if (auth()->user()->isTeacher())
                            <a href="{{ route('teacher.dashboard') }}">لوحة التحكم</a>
                            <a href="{{ route('teacher.courses.index') }}">المواد</a>
                            <a href="{{ route('teacher.enrollments.index') }}">الطلاب</a>
                            <a href="{{ route('teacher.dashboard') }}">التحليلات</a>
                        @else
                            <a href="{{ route('register.teacher') }}">انضم كمعلّم</a>
                            <a href="{{ auth()->user()->dashboardRoute() }}">لوحتي</a>
                        @endif
                    @else
                        <a href="{{ route('register.teacher') }}">انضم كمعلّم</a>
                        <a href="{{ route('login') }}">لوحة التحكم</a>
                    @endauth
                </div>
            </div>
            <div>
                <p class="nageeb-type-label">للطلبة</p>
                <div class="nageeb-footer__list">
                    <a href="{{ route('register.student') }}">حساب طالب</a>
                    <a href="{{ route('courses.index') }}">استكشف المواد</a>
                    @auth
                        @if (auth()->user()->isStudent())
                            <a href="{{ route('student.exams.index') }}">الاختبارات</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
        <div class="nageeb-footer__legal">
            <p class="nageeb-type-caption">© {{ now()->year }} نجيب · منصة تعليمية عربية</p>
        </div>
    </div>
</footer>
