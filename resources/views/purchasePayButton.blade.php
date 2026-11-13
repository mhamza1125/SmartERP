{{-- Pay button for a purchase (purchase / receive pages): opens the payment form with the vendor
     and this purchase preselected; a contractor's purchase opens the contractor payment form. --}}
@if((int) $vendorType === 1)
  @can('create', App\Models\Transaction::class)<a href="{{ route('transaction.addCPayment', ['payee_id' => $vendorId]) }}" class="btn btn-primary">Pay</a>@endcan
@else
  @can('create', App\Models\Transaction::class)<a href="{{ route('transaction.addVPayment', ['payee_id' => $vendorId, 'purchase_id' => $purchaseId]) }}" class="btn btn-primary">Pay</a>@endcan
@endif
