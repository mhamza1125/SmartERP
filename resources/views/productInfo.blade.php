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
              <a href="{{ route('product.edit', $product['product_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body row">
            <div class="col-md-12">
              <ul class="nav nav-tabs" id="productInfoTab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="general-info-tab" data-toggle="tab" href="#generalInfo" role="tab" aria-controls="generalInfo" aria-selected="true">General Info</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="product-material-tab" data-toggle="tab" href="#productMaterial" role="tab" aria-controls="productMaterial" aria-selected="false">Product Material</a>
                </li>              
                <li class="nav-item">
                  <a class="nav-link" id="product-costing-tab" data-toggle="tab" href="#productCosting" role="tab" aria-controls="productCosting" aria-selected="false">Product Costing</a>
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
                               
                {{-- Product Material --}}
                <div class="tab-pane fade" id="productMaterial" role="tabpanel" aria-labelledby="product-material-tab">
                  <div class="row">
                    <div class="col-12 col-sm-12 col-md-2">
                      <ul class="nav nav-pills flex-column" id="materialTab" role="tablist">
                        <li class="nav-item">
                          <a class="nav-link active" id="material-info-tab" data-toggle="tab" href="#materialInfo" role="tab" aria-controls="materialInfo" aria-selected="true">Material Info</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" id="additional-material-tab" data-toggle="tab" href="#additionalMaterial" role="tab" aria-controls="additionalMaterial" aria-selected="false">Additional Material</a>
                        </li>
                      </ul>
                    </div>
                    <div class="col-12 col-sm-12 col-md-10">
                      <div class="tab-content no-padding" id="materialTabContent">
                        {{-- Material Info --}}
                        <div class="tab-pane fade show active" id="materialInfo" role="tabpanel" aria-labelledby="material-info-tab">
                          <table class="table table-sm table-striped">
                            <thead>
                              <tr>
                                <th>Sr.</th>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                              </tr>
                            </thead>
                            <tbody>
                              {{-- @if($receiveSum->isEmpty()) --}}
                                <tr>
                                  <td valign="top" colspan="7" class="dataTables_empty text-center">No data available in table</td>
                                </tr>
                              {{-- @endif --}}
                              
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>Sr.</th>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                        {{-- Additional Material --}}
                        <div class="tab-pane fade" id="additionalMaterial" role="tabpanel" aria-labelledby="additional-material-tab">
                          <table class="table table-sm table-striped">
                            <thead>
                              <tr>
                                <th>Sr.</th>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                              </tr>
                            </thead>
                            <tbody>
                              {{-- @if($receiveSum->isEmpty()) --}}
                                <tr>
                                  <td valign="top" colspan="7" class="dataTables_empty text-center">No data available in table</td>
                                </tr>
                              {{-- @endif --}}
                              
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>Sr.</th>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>


                {{-- Product Costing --}}
                <div class="tab-pane fade" id="productCosting" role="tabpanel" aria-labelledby="product-costing-tab">
                  <div class="row">
                    <div class="col-12 col-sm-12 col-md-2">
                      <ul class="nav nav-pills flex-column" id="costingTab" role="tablist">
                        <li class="nav-item">
                          <a class="nav-link active" id="costing-info-tab" data-toggle="tab" href="#costingInfo" role="tab" aria-controls="costingInfo" aria-selected="true">Costing Info</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" id="additional-costing-tab" data-toggle="tab" href="#additionalCosting" role="tab" aria-controls="additionalCosting" aria-selected="false">Additional Costing</a>
                        </li>
                      </ul>
                    </div>
                    <div class="col-12 col-sm-12 col-md-10">
                      <div class="tab-content no-padding" id="costingTabContent">
                        {{-- Costing Info --}}
                        <div class="tab-pane fade show active" id="costingInfo" role="tabpanel" aria-labelledby="costing-info-tab">
                          <table class="table table-sm table-striped">
                            <thead>
                              <tr>
                                <th>Sr.</th>
                                <th>Costing Head</th>
                                <th>Amount</th>
                              </tr>
                            </thead>
                            <tbody>
                              {{-- @if($receiveSum->isEmpty()) --}}
                                <tr>
                                  <td valign="top" colspan="7" class="dataTables_empty text-center">No data available in table</td>
                                </tr>
                              {{-- @endif --}}
                              
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>Sr.</th>
                                <th>Costing Head</th>
                                <th>Amount</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                        {{-- Additional Costing --}}
                        <div class="tab-pane fade" id="additionalCosting" role="tabpanel" aria-labelledby="additional-costing-tab">
                          <table class="table table-sm table-striped">
                            <thead>
                              <tr>
                                <th>Sr.</th>
                                <th>Costing Head</th>
                                <th>Amount</th>
                              </tr>
                            </thead>
                            <tbody>
                              {{-- @if($receiveSum->isEmpty()) --}}
                                <tr>
                                  <td valign="top" colspan="7" class="dataTables_empty text-center">No data available in table</td>
                                </tr>
                              {{-- @endif --}}
                              
                            </tbody>
                            <tfoot>
                              <tr>
                                <th>Sr.</th>
                                <th>Costing Head</th>
                                <th>Amount</th>
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
  </div>
</section>
@endsection

