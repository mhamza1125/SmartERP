@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{$customer['fname']}} {{$customer['lname']}}</h4>
            <div class="card-header-action">
              <a href="{{ route('customer.edit', $customer['customer_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Customer No: </b> {{$customer['customer_no']}}</td>
                  <td colspan="2"><b>Customer Name: </b> {{$customer['fname']}} {{$customer['lname']}}</td>
                </tr>
                <tr>
                  <td><b>Email: </b> {{$customer['email']}}</td>
                  <td><b>Contact: </b> {{$customer['phone']}}</td>
                  <td><b>Fax No: </b> {{$customer['fax']}}</td>
                </tr>
                <tr>
                  <td colspan="3"><b>Address: </b> {{$customer['address']}}</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $customer['description'] @endphp
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