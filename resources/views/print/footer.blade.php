{{--
    $company is injected by App\Http\View\Composers\PrintComposer
    for all print.* views. No DB query needed here.
--}}
<div class="footer-center">
    @if($company && $company->footer_text)
        {!! $company->footer_text !!}
    @elseif($company && $company->email)
        {{ $company->email }}
    @endif
</div>
