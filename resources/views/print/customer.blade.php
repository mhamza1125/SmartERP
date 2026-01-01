@extends('print.layout')

@section('title', 'Customer_Info_' . ($customer['customer_no'] ?? 'N/A') . '_' . date('d-m-Y'))

@section('content')
<div class="document-title">Customer Information</div>

{{-- Customer Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer No:</span>
            <span class="info-value">{{ $customer['customer_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $customer['fname'] ?? 'N/A' }} {{ $customer['lname'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Company:</span>
            <span class="info-value">{{ $customer['company'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Country:</span>
            <span class="info-value">{{ $customer['coname'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $customer['email'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $customer['phone'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fax:</span>
            <span class="info-value">{{ $customer['fax'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                @if(isset($customer['status']) && $customer['status'])
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </span>
        </div>
    </div>
</div>

{{-- Address Information --}}
<div class="info-section avoid-break">
    <h3>Address Information</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {{ $customer['address'] ?? 'N/A' }}
    </div>
</div>

{{-- Customer Description --}}
@if(isset($customer['description']) && !empty($customer['description']))
<div class="info-section avoid-break">
    <h3>Customer Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($customer['description']))) !!}
    </div>
</div>
@endif

{{-- Financial Information --}}
@if(isset($customer['fi_no']) || isset($customer['rex_no']) || isset($customer['ntn']))
<div class="info-section avoid-break">
    <h3>Financial Information</h3>
    <table class="print-table">
        <tbody>
            @if(isset($customer['fi_no']) && !empty($customer['fi_no']))
            <tr>
                <td style="width: 30%; font-weight: bold;">FI Number:</td>
                <td>{{ $customer['fi_no'] }}</td>
            </tr>
            @endif
            @if(isset($customer['rex_no']) && !empty($customer['rex_no']))
            <tr>
                <td style="width: 30%; font-weight: bold;">REX Number:</td>
                <td>{{ $customer['rex_no'] }}</td>
            </tr>
            @endif
            @if(isset($customer['ntn']) && !empty($customer['ntn']))
            <tr>
                <td style="width: 30%; font-weight: bold;">NTN:</td>
                <td>{{ $customer['ntn'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Bank Information --}}
@if(isset($customer['bank_name']) || isset($customer['account_title']) || isset($customer['account_no']))
<div class="info-section avoid-break">
    <h3>Banking Information</h3>
    <table class="print-table">
        <tbody>
            @if(isset($customer['bank_name']) && !empty($customer['bank_name']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Bank Name:</td>
                <td>{{ $customer['bank_name'] }}</td>
            </tr>
            @endif
            @if(isset($customer['account_title']) && !empty($customer['account_title']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Account Title:</td>
                <td>{{ $customer['account_title'] }}</td>
            </tr>
            @endif
            @if(isset($customer['account_no']) && !empty($customer['account_no']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Account Number:</td>
                <td>{{ $customer['account_no'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Contact Person Information --}}
@if(isset($customer['contact_person']) || isset($customer['contact_phone']))
<div class="info-section avoid-break">
    <h3>Contact Person</h3>
    <table class="print-table">
        <tbody>
            @if(isset($customer['contact_person']) && !empty($customer['contact_person']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Contact Person:</td>
                <td>{{ $customer['contact_person'] }}</td>
            </tr>
            @endif
            @if(isset($customer['contact_phone']) && !empty($customer['contact_phone']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Contact Phone:</td>
                <td>{{ $customer['contact_phone'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

@endsection
