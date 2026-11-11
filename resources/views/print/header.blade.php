{{--
    Standard print header used by every print view (via print.layout / print.layout-voucher).
    Left: company logo. Middle: document title (@section('doc_title')). Right: phone, email, website.
    Record codes (@section('doc_no')) are not shown here; each print shows its code in the body.
    $company and $printContacts are injected by App\Http\View\Composers\PrintComposer.
--}}
<div class="doc-header__inner">
    <div class="doc-header__brand">
        <img src="{{ printLogoUrl($company) }}" alt="Logo" class="doc-header__logo">
    </div>

    <div class="doc-header__title">
        <div class="doc-header__title-main">@yield('doc_title')</div>
    </div>

    <div class="doc-header__contacts">
        @if(!empty($printContacts['phone']))
            <div><span class="doc-header__contact-label">Phone</span> {{ $printContacts['phone'] }}</div>
        @endif
        @if(!empty($printContacts['email']))
            <div><span class="doc-header__contact-label">Email</span> {{ $printContacts['email'] }}</div>
        @endif
        @if(!empty($printContacts['website']))
            <div><span class="doc-header__contact-label">Web</span> {{ $printContacts['website'] }}</div>
        @endif
    </div>
</div>
<div class="doc-header__rule"></div>
