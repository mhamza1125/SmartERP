@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Info</h4>
            <div class="card-header-action">
              <a href="{{ route('order.edit', $order['order_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Order No: </b> {{$order['order_no']}}</td>
                  <td colspan="2"><b>Job No: </b> {{$order['job_no']}}</td>
                </tr>
                <tr>
                  <td><b>Email: </b> {{$order['email']}}</td>
                  <td><b>Contact: </b> {{$order['phone']}}</td>
                  <td><b>Fax No: </b> {{$order['fax']}}</td>
                </tr>
                <tr>
                  <td colspan="3"><b>Address: </b> {{$order['address']}}</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $order['description'] @endphp
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