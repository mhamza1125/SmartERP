{{--
    $company is injected by App\Http\View\Composers\PrintComposer for all print.* views.
--}}
<div class="doc-header__inner">
    @if($company && $company->logo_path && file_exists(public_path($company->logo_path)))
        <img src="{{ asset($company->logo_path) }}" alt="Logo" class="doc-header__logo">
    @elseif($company && $company->logo && file_exists(public_path('storage/' . $company->logo)))
        <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="doc-header__logo">
    @else
        <img src="{{ asset('assets/print-logo.png') }}" alt="Logo" class="doc-header__logo">
    @endif
</div>
<div class="doc-header__rule"></div>
