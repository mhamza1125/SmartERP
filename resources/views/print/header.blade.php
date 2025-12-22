@php
    // Fetch company data from database
    $company = \App\Models\Company::first();
@endphp

{{-- Company Logo or Name --}}
<div class="company-logo-section">
    @if($company && $company->logo_path && file_exists(public_path($company->logo_path)))
        <img src="{{ asset($company->logo_path) }}" alt="Company Logo" class="company-logo"
             onerror="this.style.display='none'">
    @elseif($company && $company->logo && file_exists(public_path('storage/' . $company->logo)))
        <img src="{{ asset('storage/' . $company->logo) }}" alt="Company Logo" class="company-logo"
             onerror="this.style.display='none'">
    @endif
</div>
<img src="{{ asset('assets/print-logo.png') }}" alt="Company Logo" class="company-logo">

{{-- Company Information --}}
{{-- <div class="company-info">
    <div class="company-name">{{ $company->name ?? 'YOUR COMPANY NAME' }}</div>
    @if($company && $company->ntn)
        <div class="company-tagline">NTN: {{ $company->ntn }}</div>
    @endif
    @if($company && $company->address)
        <div class="company-reg">{{ $company->address }}</div>
    @endif
</div> --}}

{{-- Document Date/Time --}}
{{-- <div class="document-meta">
    <div style="font-size: 10px; text-align: right;">
        <div>Print Date: {{ date('d-M-Y') }}</div>
        <div>Print Time: {{ date('h:i A') }}</div>
    </div>
</div> --}}
