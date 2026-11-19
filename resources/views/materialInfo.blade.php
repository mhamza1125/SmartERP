@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Material Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('material.print', $material['material_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('material') }}" class="btn btn-primary">Back</a>
                @can('edit', App\Models\Material::class)<a href="{{ route('material.edit', $material['material_id']) }}" class="btn btn-primary">Edit</a>@endcan
@can('delete', App\Models\Material::class)@include('deleteButton', ['url' => route('material.destroy', $material['material_id']), 'check' => route('material.deleteCheck', $material['material_id']), 'label' => 'Material '.$material['material_no'], 'class' => 'btn btn-danger'])@endcan
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <table class="table">
                <tbody>
                  <tr>
                    <td><b>Material No: </b> {{$material['material_no']}}</td>
                    <td><b>Material Name: </b> {{$material['name']}}</td>
                    <td><b>Material Unit: </b> {{$material['uname']}}</td>
                  </tr>
                  <tr>
                    <td><b>Material Type: </b> {{$material['mtname']}}</td>
                    <td><b>Current Vendor: </b> {{$material['vendor_no']}} - {{$material['fname']}}</td>
                    <td><b>Current Price (Pkr): </b> {{$material['cprice']}}</td>
                  </tr>
                  <tr>
                    <td><b>Current Stock: </b> {{ number_format($currentStock ?? 0) }} {{$material['uname']}}</td>
                    <td><b>Opening Stock: </b> {{ number_format($material['quantity'] ?? 0) }} {{$material['uname']}}</td>
                    <td><b>Current Stock Value (Pkr): </b> {{ isset($material['cprice']) ? number_format(($currentStock ?? 0) * $material['cprice'], 2) : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td colspan="3"><b>Material Location (In Store): </b> {{$material['location']}}</td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      <div class="row">
                        <div class="col-md-1"><b>Details: </b></div>
                        <div class="col-md-11">
                          @php echo $material['description'] @endphp
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="row">
              <div class="col-md-12">
                <h5>Vendor Price Comparison
                  @if(auth()->user()->can('access', App\Models\Purchase::class) || auth()->user()->can('reports_access', App\Models\User::class))
                    <a href="{{ route('purchase.ledger', ['item' => 'material:'.$material['material_id']]) }}" class="btn btn-sm btn-outline-primary float-right" target="_blank">Purchase Ledger</a>
                  @endif
                </h5>
                @include('purchaseVendorComparison', ['comparison' => $vendorComparison, 'unit' => $material['uname']])
                <h5>Price History</h5>
                @if($priceHistory->count())
                  <div class="table-responsive">
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Date</th>
                          <th>Source</th>
                          <th>Purchase No</th>
                          <th>Job No</th>
                          <th>Vendor</th>
                          <th class="text-right">QTY</th>
                          <th class="text-right">Price</th>
                          <th class="text-right">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php
                          $sourceLabels = [
                            'opening' => ['Opening Price', 'secondary'],
                            'edit' => ['Price Edited', 'warning'],
                            'purchase' => ['Purchase', 'success'],
                            'process' => ['Processing', 'info'],
                          ];
                        @endphp
                        @foreach($priceHistory as $item)
                          @php [$sourceLabel, $sourceClass] = $sourceLabels[$item->source] ?? [ucfirst($item->source), 'light']; @endphp
                          <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ formatDate($item->date) }}</td>
                            <td><span class="badge badge-{{ $sourceClass }}">{{ $sourceLabel }}</span></td>
                            <td>
                              @if($item->purchase_id)
                                <a href="{{ $item->source === 'process' ? route('mprocess.show', $item->purchase_id) : route('purchase.show', $item->purchase_id) }}" target="_blank">{{ $item->purchase_no }}</a>
                              @else
                                -
                              @endif
                            </td>
                            <td>{{ $item->job_no ?? '-' }}</td>
                            <td>{{ $item->vendor_no }}{{ $item->fname ? ' - '.$item->fname : '' }}</td>
                            <td class="text-right">{{ $item->quantity !== null ? number_format($item->quantity, 2).' '.$material['uname'] : '-' }}</td>
                            <td class="text-right">{{ number_format($item->price, 2) }}</td>
                            <td class="text-right">{{ $item->total !== null ? number_format($item->total, 2) : '-' }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @else
                  <blockquote> No Price History </blockquote>
                @endif
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                @if($image->count())
                  <h5>Images</h5>
                  <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                    @foreach($image as $item)
                      <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                        <a href="{{ URL::asset('resources/material/'. $item->image) }}">
                          <img class="img-responsive thumbnail" src="{{ URL::asset('resources/material/'. $item->image) }}" alt="">
                        </a>
                        @can('edit', App\Models\Material::class)
                        <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'material'])}}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                        </form>
                        @endcan
                      </div>
                    @endforeach
                  </div>
                @else
                  <blockquote> No Images </blockquote>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection