{{-- Bank account picked on the invoice modal ($bankDetails from BankRepository::printDetails()) --}}
@if(!empty($bankDetails))
<div class="avoid-break" style="margin-top: 14px;">
    <div class="table-title">Bank Account Details</div>
    <table class="print-table">
        <tbody>
            @foreach([
                'Bank Name' => $bankDetails['bank_name'] ?? null,
                'Account Title' => $bankDetails['account_title'] ?? null,
                'Account Number' => $bankDetails['account'] ?? null,
                'IBAN' => $bankDetails['iban'] ?? null,
                'SWIFT Code' => $bankDetails['swift_code'] ?? null,
                'Branch Code' => $bankDetails['branch_code'] ?? null,
                'Bank Address' => $bankDetails['address'] ?? null,
            ] as $label => $value)
                @if(trim((string) $value) !== '')
                <tr>
                    <td style="width: 28%"><strong>{{ $label }}:</strong></td>
                    <td>{{ $value }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif
