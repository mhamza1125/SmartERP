{{-- One people-ledger description: the first part as the main line, the details below in small
     text ("Payment · via Meezan Bank · Cheque 12" / "Purchase P2611003 received: Item x 10, ...") --}}
@php
  $descParts = preg_split('/ · |: /u', (string) $text, 2);
@endphp
<div>{{ $descParts[0] }}</div>
@if(!empty($descParts[1]))
  <small class="text-muted d-block">{{ $descParts[1] }}</small>
@endif
