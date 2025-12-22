@php
    // Fetch company data from database
    $company = \App\Models\Company::first();
@endphp
{{-- Contact Information --}}
<div class="footer-center">
    @if($company && $company->footer_text)
        <div>{{ $company->footer_text }}</div>
    @endif
</div>
