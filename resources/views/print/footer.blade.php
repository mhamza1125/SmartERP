{{--
    $company is injected by App\Http\View\Composers\PrintComposer for all print.* views.
--}}
@if($company && $company->footer_text)
    <div class="doc-footer__line">{!! $company->footer_text !!}</div>
@endif
