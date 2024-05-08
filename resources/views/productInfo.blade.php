@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('product.edit', $product['product_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <ul class="nav nav-tabs" id="productInfoTab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="general-info-tab" data-toggle="tab" href="#generalInfo" role="tab" aria-controls="generalInfo" aria-selected="true">General Info</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="product-material-tab" data-toggle="tab" href="#productMaterial" role="tab" aria-controls="productMaterial" aria-selected="false">Packing / Material</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="product-costing-tab" data-toggle="tab" href="#productCosting" role="tab" aria-controls="productCosting" aria-selected="false">Costing</a>
                  </li>
                </ul>
                
                <div class="tab-content" id="productInfoTabContent">
                  {{-- General Info --}}
                  <div class="tab-pane fade show active" id="generalInfo" role="tabpanel" aria-labelledby="general-info-tab">      
                    <table class="table">
                      <tbody>
                        <tr>
                          <td><b>Article No: </b> {{$product['article_no']}}</td>
                          <td><b>Product Name: </b> {{$product['name']}}</td>
                          <td><b>Unit: </b> {{$product['hname']}}</td>
                        </tr>
                        <tr>
                          <td><b>Category: </b> {{$product['cname']}}</td>
                          <td><b>Sizes: </b> @foreach($size as $item) {{$item->name}}, @endforeach </td>
                          <td><b>Current Status: </b> @if($product['product_status']) 
                            <span class="badge badge-success">Active</span> @else 
                            <span class="badge badge-danger">Inactive</span> @endif</td>
                        </tr>
                        <tr>
                          <td colspan="3">
                            <div class="row">
                              <div class="col-md-1"><b>Details: </b></div>
                              <div class="col-md-11">
                                @php echo $product['description'] @endphp
                              </div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    @if($stage->count())
                      <h5>Product Stages</h5>
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Stage</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($stage->count())
                            @foreach($stage as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$item->name}}</td>                      
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    @else
                      <blockquote> No Materials </blockquote>
                    @endif
  
                    @if($material->count())
                      <h5>Product Materials</h5>
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Material No</th>
                            <th>Material Name</th>
                            <th>Unit</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($material->count())
                            @foreach($material as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$item->material_no}}</td>
                                <td>{{$item->name}}</td>                      
                                <td>{{$item->uname}}</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    @else
                      <blockquote> No Materials </blockquote>
                    @endif
  
                    @if($image->count())
                      <h5>Images</h5>
                      <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                        @foreach($image as $item)
                          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <a href="{{ URL::asset('resources/product/'. $item->image) }}">
                              <img class="img-responsive thumbnail" src="{{ URL::asset('resources/product/'. $item->image) }}" alt="">
                            </a>
                            <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'product'])}}" method="POST">
                              @csrf
                              <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                            </form>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <blockquote> No Images </blockquote>
                    @endif
        
                    @if($file->count())
                      <h5 class="mt-4">Files / Attachments</h5>
                      <div class="row">
                        @foreach($file as $item)
                          <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
                            <div class="card card-primary">
                              <div class="card-header">
                                <h4>{{($item->file_title)? $item->file_title:'Untitled File'}}</h4>
                              </div>
                              <div class="card-body">
                                <div class="row">
                                  <div class="col-md-6">
                                    <a href="{{ asset('resources/product_file/' . $item->image) }}" target="_blank" class="btn btn-info">View</a>
                                  </div>
                                  <div class="col-md-6">
                                    <form action="{{ route('image.delete', ['id' => $item->image_id, 'dir' => 'product_file']) }}" method="POST">
                                      @csrf
                                      <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <blockquote> No Files / Attachments </blockquote>
                    @endif
                  </div>
                  
                  {{-- Product Packing / Material --}}
                  <div class="tab-pane fade" id="productMaterial" role="tabpanel" aria-labelledby="product-material-tab">
                    <div class="row">
                      @if(!$countMaterial > 0)
                        <div class="col-md-12">
                          <h5 class="mt-2">Product Material
                            <a href="{{ route('productMaterial.add', $product['product_id']) }}" class="btn btn-primary float-right rounded-pill" target="_blank">Add</a>
                          </h5>
                        </div>
                      @endif
                      <div class="col-12 col-sm-12 col-md-2">
                        <ul class="nav nav-pills flex-column" id="materialTab" role="tablist">
                          @if($countMaterial > 0)
                            @for($i=1; $i<=$countMaterial; $i++)
                              <li class="nav-item">
                                <a class="nav-link {{($i==1)? 'active':''}}" id="tabm-{{ $i }}" data-toggle="tab" href="#tab-contentm-{{ $i }}" role="tab" aria-controls="tab-contentm-{{ $i }}" aria-selected="false">Size - {{$totalMaterial[$i-1]['name']}}</a>
                              </li>
                            @endfor
                          @endif
                        </ul>
                      </div>
                      <div class="col-12 col-sm-12 col-md-10">
                        <div class="tab-content no-padding" id="materialTabContent">
                          @if($countMaterial > 0)
                            @for($i=1; $i<=$countMaterial; $i++)
                            @php $loopIndex = 1; @endphp
                              <div class="tab-pane fade {{($i==1)? 'show active':''}}" id="tab-contentm-{{ $i }}" role="tabpanel" aria-labelledby="tabm-{{ $i }}">
                                @foreach($totalMaterial as $item)
                                  @if(($totalMaterial[$i-1]['product_type_id'] ?? null) === $item->product_type_id)
                                  <h5 class="mt-2">Product Packing
                                    <a href="{{ route('productMaterial.edit', $item->product_type_id) }}" class="btn btn-primary float-right rounded-pill" target="_blank">Edit</a>
                                  </h5>
                                  <table class="table table-sm">
                                    <tbody>
                                      @php $item2 = $getMaterial->where('material_type_id', '61')->where('product_type_id', $totalMaterial[$i-1]['product_type_id'])->first(); @endphp
                                      @if($item2)
                                        <tr>
                                          <td><b>Article No: </b> {{$item->article_no}}</td>
                                          <td><b>Product Name: </b> {{$item->pname}}</td>
                                          <td><b>Size: </b> {{$item->name}}</td>
                                        </tr>
                                        <tr>
                                          <td><b>Box No: </b> {{$item2->material_no}}</td>
                                          <td><b>Box Name: </b> {{$item2->name}}</td>
                                          <td><b>Quantity in Box: </b> {{1/$item2->quantity}} {{$item2->hname}}</td>
                                        </tr>
                                        <tr>
                                          <td colspan="3">
                                            <div class="row">
                                              <div class="col-md-1"><b>Details: </b></div>
                                              <div class="col-md-11">
                                                @php echo $item->description @endphp
                                              </div>
                                            </div>
                                          </td>
                                        </tr>
                                      @endif
                                    </tbody>
                                  </table>
                                  @endif
                                @endforeach
                                
                                <h5>Product Material</h5>
                                <table class="table table-sm table-striped">
                                  <thead>
                                    <tr>
                                      <th>Sr.</th>
                                      <th>Material No</th>
                                      <th>Material Name</th>
                                      <th>Quantity</th>
                                      <th>Units</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($getMaterial as $item)
                                      @if(($totalMaterial[$i-1]['product_type_id'] ?? null) === $item->product_type_id)
                                        @unless($item->material_type_id == '61')
                                          <tr>
                                              <td>{{$loopIndex++}}</td>
                                              <td>{{$item->material_no}}</td>
                                              <td>{{$item->name}}</td>
                                              <td>{{$item->quantity}}</td>
                                              <td>{{$item->hname}}</td>
                                            </tr>
                                          @endunless
                                        @endif
                                    @endforeach
                                  </tbody>
                                  <tfoot>
                                    <tr>
                                      <th>Sr.</th>
                                      <th>Material No</th>
                                      <th>Material Name</th>
                                      <th>Quantity</th>
                                      <th>Units</th>
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
  
                  {{-- Product Costing --}}
                  <div class="tab-pane fade" id="productCosting" role="tabpanel" aria-labelledby="product-costing-tab">
                    <div class="row">
                      <div class="col-md-12">
                        <h5 class="mt-2">Product Costing
                          <a href="{{ route('productCost.edit', $product['product_id']) }}" class="btn btn-primary float-right rounded-pill" target="_blank">Edit</a>
                        </h5>
                        <table class="table table-sm table-striped">
                          <thead>
                            <tr>
                              <th>Sr.</th>
                              <th>Employee / Vendor</th>
                              <th>Cost Head</th>
                              <th>Price</th>
                            </tr>
                          </thead>
                          <tbody>
                            @if($pcost->count())
                              @foreach($pcost as $item)
                                <tr>
                                  <td>{{$loop->index + 1}}</td>
                                  <td>{{$item->employee_no ?? $item->vendor_no}}{{$item->name ? ' - '.$item->name : 'General Cost'}}</td>                    
                                  <td>{{$item->hname}}</td>
                                  <td>{{$item->amount}}</td>
                                </tr>
                              @endforeach
                            @endif
                          </tbody>
                          <tfoot>
                            <tr>
                              <th>Sr.</th>
                              <th>Employee / Vendor</th>
                              <th>Cost Head</th>
                              <th>Price</th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

