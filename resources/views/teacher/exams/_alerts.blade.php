@if (session('status'))
    <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
@endif

@if (session('error'))
    <div class="nageeb-alert nageeb-alert--error mb-6">{{ session('error') }}</div>
@endif
