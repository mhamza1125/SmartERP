@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Purchas Info</h4>
            <div class="card-header-action">
              <a href="{{ route('purchase.edit', $purchase['purchase_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>purchase No: </b> {{$purchase['purchase_no']}}</td>
                  <td colspan="2"><b>purchase Name: </b> {{$purchase['fname']}} {{$purchase['lname']}}</td>
                </tr>
                <tr>
                  <td><b>Email: </b> {{$purchase['email']}}</td>
                  <td><b>Contact: </b> {{$purchase['phone']}}</td>
                  <td><b>Fax No: </b> {{$purchase['fax']}}</td>
                </tr>
                <tr>
                  <td colspan="3"><b>Address: </b> {{$purchase['address']}}</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $purchase['description'] @endphp
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection