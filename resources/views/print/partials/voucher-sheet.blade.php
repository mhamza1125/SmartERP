{{-- One half-page voucher sheet (header + content of the current print view). Used by
     print.layout-voucher for a single document, and alone (sheetOnly) when several documents
     are printed together; the sheet then carries its own styles. --}}
@if(!empty($sheetOnly))
    @stack('styles')
@endif
<div class="half-sheet">
<header class="doc-header">
    @include('print.header')
</header>

<table class="page-frame">
    <thead><tr><td><div class="page-frame__header-space"></div></td></tr></thead>
    <tbody><tr><td>
        <main class="doc-body">
            <div class="voucher-container">
                @yield('content')
            </div>
        </main>
    </td></tr></tbody>
    <tfoot><tr><td><div class="page-frame__footer-space"></div></td></tr></tfoot>
</table>
</div>
