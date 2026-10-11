{{--
    $company and $printFooter are injected by App\Http\View\Composers\PrintComposer for all print.* views.
    Line 1: company address. Line 2 (below a divider): contacts spread left / center / right.
--}}
@if(!empty($printFooter['address']))
    <div class="doc-footer__address">{{ $printFooter['address'] }}</div>
@endif
@if(!empty($printFooter['contacts']))
    <div class="doc-footer__contacts">
        @foreach($printFooter['contacts'] as $contact)
            <span>{{ $contact }}</span>
        @endforeach
    </div>
@endif
