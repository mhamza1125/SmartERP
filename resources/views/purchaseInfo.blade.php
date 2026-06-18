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
              <div class="dropdown">
                <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                  <i class="fas fa-print"></i> Print
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ isset($process) ? route('mprocess.print', $purchase['purchase_id']) : route('purchase.print', $purchase['purchase_id']) }}" target="_blank">
                    <i class="fas fa-file-alt"></i> {{ isset($process) ? 'Process Material' : 'Purchase Order' }}
                  </a>
                  @if($count >= 1)
                    <span class="dropdown-header pdd-header">Receiving Records</span>
                    @for($i=1; $i<=$count; $i++)
                      <a class="dropdown-item" href="{{ route('receive.print', $receiveTimes[$i-1]['receive_id']) }}" target="_blank">
                        <i class="fas fa-file-alt"></i> {{ $receiveTimes[$i-1]['receive_no'] }}
                      </a>
                    @endfor
                  @endif
                  @if($count2 >= 1)
                    <span class="dropdown-header pdd-header">Return Records</span>
                    @for($i=1; $i<=$count2; $i++)
                      <a class="dropdown-item" href="{{ route('return.print', $returnTimes[$i-1]['return_id']) }}" target="_blank">
                        <i class="fas fa-file-alt"></i> {{ $returnTimes[$i-1]['return_no'] }}
                      </a>
                    @endfor
                  @endif
                </div>
              </div>
              <div class="btn-group">
                <a href="{{ route('purchase') }}" class="btn btn-primary">Back</a>
                @if(!$purchase['has_received'])
                  @if(isset($process))
                    <a href="{{ route('mprocess.edit', $purchase['purchase_id']) }}" class="btn btn-primary">Edit</a>
                  @else
                    <a href="{{ route($purchase['purchase_type'] == 'material' ? 'purchase.edit' : 'productPurchase.edit', $purchase['purchase_id']) }}" class="btn btn-primary">Edit</a>
                  @endif
                @endif
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
                    <tr><td><b>{{ isset($process) ? 'Processing' : 'Purchase' }} Date:</b> {{\Carbon\Carbon::parse($purchase['purchase_date'])->format('d-m-Y')}}</td></tr>
                    <tr><td><b>Required Date:</b> {{\Carbon\Carbon::parse($purchase['require_date'])->format('d-m-Y')}}</td></tr>
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
                          <th>Material / Product</th>
                          <th>Units / Size</th>
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
                              <td>
                                @if($purchase['purchase_type'] == 'material')
                                  {{ $item->material_no ?? '' }}
                                @else
                                  {{ $item->article_no ?? '' }} - {{ $item->sname ?? '' }}
                                @endif
                              </td>
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
                              <td>{{$item->pmaterial_no}} - {{$item->pname}}</td>
                              <td>{{$item->material_no}} - {{$item->name}}</td>
                              <td>{{number_format($item->before_qty)}} {{$item->phname}}</td>
                              <td>{{number_format($item->quantity)}} {{$item->hname}}</td>
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
                          <th>Material / Product</th>
                          <th>Units / Size</th>
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
                              <td>
                                @if($purchase['purchase_type'] == 'material')
                                  {{ $item->material_no ?? '' }} - {{ $item->name ?? '' }}
                                @else
                                  {{ $item->article_no ?? '' }} - {{ $item->sname ?? '' }}
                                @endif
                              </td>
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
                          <th>Material / Product</th>
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
                              <td>{{number_format($item->credit)}}</td>
                              <td>{{\Carbon\Carbon::parse($item->transaction_date)->format('d-m-Y')}}</td>
                              <td><a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a></td>
                            </tr>
                            @php $total2 += $item->credit ; @endphp
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
                              <th>Material / Product</th>
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
                                  <td>
                                    @if($purchase['purchase_type'] == 'material')
                                      {{ $item->material_no ?? '' }} - {{ $item->name ?? '' }}
                                    @else
                                      {{ $item->article_no ?? '' }} - {{ $item->sname ?? '' }}
                                    @endif
                                  </td>
                                  <td>{{$item->hname}}</td>
                                  <td>{{$item->rqty}}</td>
                                  <td>{{$item->pending_qty}}</td>
                                  <td>{{$item->approved_qty}}</td>
                                  <td>{{$item->rejected_qty}}</td>
                                  {{-- <td>@if($item->inspection_status == 1) Pending
                                    @elseif($item->inspection_status == 2) Approved
                                    @else Rejected @endif</td> --}}
                                  <td>{{\Carbon\Carbon::parse($item->inspection_date)->format('d-m-Y')}}</td>
                                </tr>
                              @endif
                            @endforeach
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>Sr.</th>
                              <th>Material / Product</th>
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
                              <th>Material / Product</th>
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
                                  <td>
                                    @if($purchase['purchase_type'] == 'material')
                                      {{ $item->material_no ?? '' }} - {{ $item->name ?? '' }}
                                    @else
                                      {{ $item->article_no ?? '' }} - {{ $item->sname ?? '' }}
                                    @endif
                                  </td>
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
                              <th>Material / Product</th>
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

@if($purchaseItem->count())
@foreach($purchaseItem as $purchase)
    <div class="modal fade" id="exampleModal{{$purchase->purchase_item_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
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
                    <th colspan="6">
                      @if($purchase['purchase_type'] == 'material')  
                        {{$purchase->material_no}} - {{$purchase->name}}
                      @else
                        {{$purchase->name}} - {{ $item->sname }} - {{$item->hname}}
                      @endif
                      | Order Qty: {{$purchase->quantity}}
                    </th>
                  </tr>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Receive No / Return No</th>
                    <th>Receive Qty</th>
                    <th>Return Qty</th>
                    <th>Balance</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $sr = 1;
                    $balance = $purchase->quantity;
                    $transactions = collect();

                    foreach ($receiveAll as $receive) {
                      if ($purchase->purchase_item_id == $receive->purchase_item_id) {
                        $transactions->push([
                          'type' => 'receive',
                          'no' => $receive->receive_no,
                          'qty' => $receive->rqty,
                          'created_at' => $receive->created_at
                        ]);
                      }
                    }

                    foreach ($returnAll as $return) {
                      if ($purchase->purchase_item_id == $return->purchase_item_id) {
                        $transactions->push([
                          'type' => 'return',
                          'no' => $return->return_no,
                          'qty' => $return->rqty,
                          'created_at' => $return->created_at
                        ]);
                      }
                    }

                    $sortedTransactions = $transactions->sortBy('created_at');
                  @endphp

                  @foreach($sortedTransactions as $transaction)
                    <tr>
                      <td>{{$sr++}}</td>
                      <td>{{ date('d-m-Y', strtotime($transaction['created_at'])) }}</td>
                      <td>{{$transaction['no']}}</td>
                      <td>{{$transaction['type'] == 'receive' ? $transaction['qty'] : ''}}</td>
                      <td>{{$transaction['type'] == 'return' ? $transaction['qty'] : ''}}</td>
                      <td>
                        @php
                          if ($transaction['type'] == 'receive') {
                            $balance -= $transaction['qty'];
                          } else {
                            $balance += $transaction['qty'];
                          }
                        @endphp
                        {{$balance}}
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th></th>
                    <th>Order Qty: {{$purchase->quantity}}</th>
                    <th>Received: {{$sortedTransactions->where('type', 'receive')->sum('qty')}}</th>
                    <th>Returned: {{$sortedTransactions->where('type', 'return')->sum('qty')}}</th>
                    <th>Remaining: {{$balance}}</th>
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
@endsection