{{--
    View button for one transaction row, used by every transaction listing
    (Transactions, Cash Book, Bank Ledger) so each type opens its own view page.
    Expects $item with transaction_id and transaction_to.
--}}
@php
  $viewRoutes = [
      'employee' => 'transaction.showEPayment',
      'vendor' => 'transaction.showVPayment',
      'contractor' => 'transaction.showCPayment',
      'expense' => 'transaction.showExpense',
      'customer' => 'transaction.showOPayment',
      'generalVoucher' => 'transaction.showGeneralVoucher',
      'transfer' => 'transaction.showTransfer',
      'brs' => 'transaction.showBRS',
      'admin' => 'transaction.showBRS', // bank opening balance
  ];
  $viewRoute = $viewRoutes[$item->transaction_to ?? ''] ?? null;
@endphp
@if($viewRoute)
  <a href="{{ route($viewRoute, $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
@can('delete', App\Models\Transaction::class)@include('deleteButton', ['url' => route('transaction.destroy', $item->transaction_id), 'check' => route('transaction.deleteCheck', $item->transaction_id), 'label' => transactionLabel($item->transaction_type).' #'.$item->transaction_id, 'class' => 'btn btn-danger btn-sm'])@endcan
@endif
