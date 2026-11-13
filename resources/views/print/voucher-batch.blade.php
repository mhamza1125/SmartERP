@extends('print.layout-voucher')

{{-- Several half-page documents of one kind in one print ($sheets: rendered with sheetOnly) --}}
@section('title', $batchTitle ?? 'Vouchers')

@push('styles')
<style>
    @media screen { .half-sheet + .half-sheet { margin-top: 14px; } }
</style>
@endpush
