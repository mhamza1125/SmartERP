@php
    // Fetch company data from database
    $company = \App\Models\Company::first();
@endphp

{{-- Company Address --}}
<div class="footer-left">
    @if($company && $company->address)
        <div><strong>Address:</strong></div>
        <div>{{ $company->address }}</div>
    @endif
    @if($company && $company->city)
        <div>{{ $company->city }}@if($company->country), {{ $company->country }}@endif</div>
    @endif
</div>

{{-- Contact Information --}}
<div class="footer-center">
    <div><strong>Contact Information</strong></div>
    @if($company && $company->phone)
        <div>Phone: {{ $company->phone }}</div>
    @endif
    @if($company && $company->email)
        <div>Email: {{ $company->email }}</div>
    @endif
    @if($company && $company->website)
        <div>Website: {{ $company->website }}</div>
    @endif
</div>

{{-- Footer Text --}}
<div class="footer-right">
    @if($company && $company->footer_text)
        <div>{{ $company->footer_text }}</div>
    @else
        <div><strong>Business Hours:</strong></div>
        <div>Mon-Fri: 9:00 AM - 6:00 PM</div>
    @endif
</div>
