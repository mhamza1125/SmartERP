{{--
    $company is injected by App\Http\View\Composers\PrintComposer
    for all print.* views. No DB query needed here.
--}}
<div class="company-logo-section">
    @if($company && $company->logo_path && file_exists(public_path($company->logo_path)))
        <img src="{{ asset($company->logo_path) }}" alt="{{ $company->name ?? 'Company Logo' }}" class="company-logo">
    @elseif($company && $company->logo && file_exists(public_path('storage/' . $company->logo)))
        <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name ?? 'Company Logo' }}" class="company-logo">
    @else
        <img src="{{ asset('assets/print-logo.png') }}" alt="Company Logo" class="company-logo">
    @endif
</div>
