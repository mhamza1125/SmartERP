@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{ isset($process) ? 'Process Material' : 'Purchase' }} Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('purchase') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('purchase.edit', $purchase['purchase_id']) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('receive.add', $purchase['purchase_id']) }}" class="btn btn-primary">Receive</a>
                <a href="{{ route('transaction.addVPayment') }}" class="btn btn-primary">Pay</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Name:</b> {{$purchase['fname']}}</td></tr>
                    <tr><td><b>Phone:</b> {{$purchase['phone1']}}</td></tr>
                    <tr><td><b>Address:</b> {{$purchase['address']}}</td></tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>P.O.#:</b> {{$purchase['purchase_no']}}</td></tr>
                    <tr><td><b>Job.#:</b> {{($purchase['job_no'])? $purchase['job_no']:'Default Purchase'}}</td></tr>
                    <tr><td><b>{{ isset($process) ? 'Processing' : 'Purchase' }} Date:</b> {{$purchase['purchase_date']}}</td></tr>
                    <tr><td><b>Required Date:</b> {{$purchase['require_date']}}</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">Purchase</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="receive-tab" data-toggle="tab" href="#receive" role="tab" aria-controls="receive" aria-selected="false">All Record</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="payment-tab" data-toggle="tab" href="#payment" role="tab" aria-controls="payment" aria-selected="false">Payment Record</a>
                  </li>
                  @if($count >= 1)
                    @for($i=1; $i<=$count; $i++)
                      <li class="nav-item">
                        <a class="nav-link" id="tab-{{ $i }}" data-toggle="tab" href="#tab-content-{{ $i }}" role="tab" aria-controls="tab-content-{{ $i }}" aria-selected="false">{{$receiveTimes[$i-1]['receive_no']}}</a>
                      </li>
                    @endfor
                  @endif
                  @if($count2 >= 1)
                    @for($i=1; $i<=$count2; $i++)
                      <li class="nav-item">
                        <a class="nav-link" id="rtab-{{ $i }}" data-toggle="tab" href="#rtab-content-{{ $i }}" role="tab" aria-controls="rtab-content-{{ $i }}" aria-selected="false">{{$returnTimes[$i-1]['return_no']}}</a>
                      </li>
                    @endfor
                  @endif
                </ul>
                <div class="tab-content" id="myTabContent">
                  {{-- Purchase --}}
                  <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">      
                    @if(!isset($process))
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Material No</th>
                          <th>Material Name</th>
                          <th>Unit</th>
                          <th>Quantity</th>
                          <th>Rate</th>
                          <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($purchaseItem->count())
                          @foreach($purchaseItem as $item)
                            <tr>
                              <td>{{$loop->index + 1}}</td>
                              <td>{{$item->material_no}}</td>
                              <td>{{$item->name}}</td>
                              <td>{{$item->hname}}</td>
                              <td>{{number_format($item->quantity)}}</td>
                              <td>{{number_format($item->price)}}</td>
                              <td>{{number_format($item->quantity * $item->price)}}</td>
                            </tr>
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        @php $total = $purchaseItem->sum(function($item) {
                          return $item->quantity * $item->price;
                        }); @endphp
                        <tr>
                          <th colspan="3"></th>
                          <th colspan="2" class="text-center">Grand Total:</th>
                          <th>{{ number_format($total) }}</th>
                        </tr>
                      </tfoot>
                    </table>
                    @else
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Material A</th>
                          <th>Material B</th>
                          <th>Quantity A</th>
                          <th>Quantity B</th>
                          <th>Rate</th>
                          <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($purchaseItem->count())
                          @foreach($purchaseItem as $item)
                            <tr>
                              <td>{{$loop->index + 1}}</td>
                              <td>{{$item->material_no}} - {{$item->name}}</td>
                              <td>{{$item->pmaterial_no}} - {{$item->pname}}</td>
                              <td>{{number_format($item->before_qty)}} {{$item->hname}}</td>
                              <td>{{number_format($item->quantity)}} {{$item->phname}}</td>
                              <td>{{number_format($item->price)}}</td>
                              <td>{{number_format($item->quantity * $item->price)}}</td>
                            </tr>
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        @php $total = $purchaseItem->sum(function($item) {
                          return $item->quantity * $item->price;
                        }); @endphp
                        <tr>
                          <th colspan="3"></th>
                          <th colspan="2" class="text-center">Grand Total:</th>
                          <th>{{ number_format($total) }}</th>
                        </tr>
                      </tfoot>
                    </table>
                    @endif
                  </div>
                  {{-- Receive All --}}
                  <div class="tab-pane fade" id="receive" role="tabpanel" aria-labelledby="receive-tab">  
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Material No</th>
                          <th>Material Name</th>
                          <th>Units</th>
                          <th>Order Qty</th>
                          <th>Receive Qty</th>
                          <th>Return Qty</th>
                          <th>Remaining</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($receiveSum->isEmpty())
                          <tr>
                            <td valign="top" colspan="9" class="dataTables_empty text-center">No data available in table</td>
                          </tr>
                        @endif
                        @if($receiveSum->count())
                          @foreach($receiveSum as $item)
                            <tr>
                              <td>{{$loop->index + 1}}</td>
                              <td>{{$item->material_no}}</td>
                              <td>{{$item->name}}</td>
                              <td>{{$item->hname}}</td>
                              <td>{{$item->quantity}}</td>
                              <td>{{$item->rqty}}</td>
                              <td>{{ $item->rqty2 ?? '0' }}</td>
                              <td>{{$item->quantity - $item->rqty + $item->rqty2}}</td>
                              <td><button type="button" class="btn btn-icon btn-sm btn-info" data-toggle="modal" data-target="#exampleModal{{$item->purchase_item_id}}"><i class="fas fa-info-circle"></i></button></td>
                            </tr>
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>Sr.</th>
                          <th>Material No</th>
                          <th>Material Name</th>
                          <th>Units</th>
                          <th>Order Qty</th>
                          <th>Receive Qty</th>
                          <th>Return Qty</th>
                          <th>Remaining</th>
                          <th>Action</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  {{-- Paymetnt Record --}}
                  <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">  
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Amount Paid</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($transaction->isEmpty())
                          <tr>
                            <td valign="top" colspan="4" class="dataTables_empty text-center">No data available in table</td>
                          </tr>
                        @endif
                        @php $total2 = 0; @endphp
                        @if($transaction->count())
                          @foreach($transaction as $item)
                            <tr>
                              <td>{{$loop->index + 1}}</td>
                              <td>{{number_format($item->debit)}}</td>
                              <td>{{$item->transaction_date}}</td>
                              <td><a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a></td>
                            </tr>
                            @php $total2 += $item->debit ; @endphp
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th></th>
                          <th>Total: {{ number_format($total) }} | Paid: {{ number_format($total2) }}</th>
                          <th>Remaining: {{ number_format($total - $total2) }}</th>
                          <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  {{-- Receive Times --}}
                  @if($count >= 1)
                    @for($i=1; $i<=$count; $i++)  
                      @php $loopIndex = 1; @endphp
                      <div class="tab-pane fade" id="tab-content-{{ $i }}" role="tabpanel" aria-labelledby="tab-{{ $i }}">
                        <a href="{{ route('receive.edit', $receiveTimes[$i-1]['receive_id'] )}}" class="btn btn-primary rounded-pill pbtn" target="_blank">Edit</a>
                        <table class="table table-sm table-striped">
                          <thead>
                            <tr>
                              <th>Sr.</th>
                              <th>Material No</th>
                              <th>Material Name</th>
                              <th>Units</th>
                              <th>Receive Qty</th>
                              <th>Pending</th>
                              <th>Approved</th>
                              <th>Rejected</th>
                              {{-- <th>Inspection Status</th> --}}
                              <th>Inspection Date</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($receiveAll as $item)
                              @if($receiveTimes[$i-1]['receive_no'] == $item->receive_no)
                                <tr>
                                  <td>{{$loopIndex++}}</td>
                                  <td>{{$item->material_no}}</td>
                                  <td>{{$item->name}}</td>
                                  <td>{{$item->hname}}</td>
                                  <td>{{$item->rqty}}</td>
                                  <td>{{$item->pending_qty}}</td>
                                  <td>{{$item->approved_qty}}</td>
                                  <td>{{$item->rejected_qty}}</td>
                                  {{-- <td>@if($item->inspection_status == 1) Pending
                                    @elseif($item->inspection_status == 2) Approved
                                    @else Rejected @endif</td> --}}
                                  <td>{{$item->inspection_date}}</td>
                                </tr>
                              @endif
                            @endforeach
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>Sr.</th>
                              <th>Material No</th>
                              <th>Material Name</th>
                              <th>Units</th>
                              <th>Receive Qty</th>
                              <th>Pending</th>
                              <th>Approved</th>
                              <th>Rejected</th>
                              {{-- <th>Inspection Status</th> --}}
                              <th>Inspection Date</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    @endfor
                  @endif
                  {{-- Return Times --}}
                  @if($count2 >= 1)
                    @for($i=1; $i<=$count2; $i++)  
                      @php $loopIndex = 1; @endphp
                      <div class="tab-pane fade" id="rtab-content-{{ $i }}" role="tabpanel" aria-labelledby="rtab-{{ $i }}">
                        <a href="{{ route('return.edit', $returnTimes[$i-1]['return_id'] )}}" class="btn btn-primary rounded-pill pbtn" target="_blank">Edit</a>
                        <table class="table table-sm table-striped">
                          <thead>
                            <tr>
                              <th>Sr.</th>
                              <th>Material No</th>
                              <th>Material Name</th>
                              <th>Units</th>
                              <th>Return Qty</th>
                              <th>Remarks</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($returnAll as $item)
                              @if($returnTimes[$i-1]['return_no'] == $item->return_no)
                                <tr>
                                  <td>{{$loopIndex++}}</td>
                                  <td>{{$item->material_no}}</td>
                                  <td>{{$item->name}}</td>
                                  <td>{{$item->hname}}</td>
                                  <td>{{$item->rqty}}</td>
                                  <td>{{$item->remarks}}</td>
                                </tr>
                              @endif
                            @endforeach
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>Sr.</th>
                              <th>Material No</th>
                              <th>Material Name</th>
                              <th>Units</th>
                              <th>Return Qty</th>
                              <th>Remarks</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    @endfor
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Add this hidden div to hold the modal content for printing -->
<div id="printModalContent" style="display: none;"></div>

@if($purchaseItem->count())
@foreach($purchaseItem as $purchase)
    <div class="modal fade" id="exampleModal{{$purchase->purchase_item_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">{{ isset($process) ? 'Process Material' : 'Purchase Item' }} Detail</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th colspan="4">{{$purchase->material_no}} - {{$purchase->name}}</th>
                  </tr>
                  <tr>
                    <th>Sr.</th>
                    <th>Receive No</th>
                    <th>Quantity</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  @php $sr = 1; $total = 0; $total2 = 0; @endphp
                  @foreach($receiveAll as $receive)
                    @if($purchase->purchase_item_id == $receive->purchase_item_id)
                    <tr>
                      <td>{{$sr++}}</td>
                      <td>{{$receive->receive_no}}</td>
                      <td>{{$receive->rqty}}</td>
                      <td>{{ date('Y-m-d', strtotime($receive->created_at)) }}</td>
                      @php $total += $receive->rqty @endphp
                    </tr>
                    @endif
                  @endforeach
                  @foreach($returnAll as $return)
                    @if($purchase->purchase_item_id == $return->purchase_item_id)
                    <tr>
                      <td>{{$sr++}}</td>
                      <td>{{$return->return_no}}</td>
                      <td>{{$return->rqty}}</td>
                      <td>{{ date('Y-m-d', strtotime($return->created_at)) }}</td>
                      @php $total2 += $return->rqty @endphp
                    </tr>
                    @endif
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    <th>Order Qty: {{$purchase->quantity}}</th>
                    <th>Received: {{$total}} <br>
                        Returned: {{$total2}}</th>
                    <th>Remaining: {{$purchase->quantity - $total + $total2}}</th>
                  </tr>
                  <tr>
                    <th colspan="4">
                      <button class="btn btn-primary btn-print float-right" onclick="printPModal123('exampleModal{{$purchase->purchase_item_id}}')">Print</button>
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endif

<script>
function printPModal(modalId) {
    // Get the modal content by ID
    var modalContent = document.getElementById(modalId).innerHTML;

    // Set the modal content to the hidden div
    document.getElementById('printModalContent').innerHTML = modalContent;

    // Print just the content of the hidden div
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Print</title><link rel="stylesheet" href="' + window.location.origin + '/assets/css/app.min.css' + '"></head><body>');
    printWindow.document.write(document.getElementById('printModalContent').innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>
  </script>
@endsection