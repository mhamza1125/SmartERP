@extends('index')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Company Information</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('company') }}">Company</a></div>
                <div class="breadcrumb-item">Company Info</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $company->name }}</h4>
                            <div class="card-header-action">
                                <a href="{{ route('company.edit', $company->id) }}" class="btn btn-warning">Edit</a>
                                <a href="{{ route('company') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Company Name:</strong></td>
                                            <td>{{ $company->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>CEO:</strong></td>
                                            <td>{{ $company->ceo }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NTN:</strong></td>
                                            <td>{{ $company->ntn ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $company->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Fax:</strong></td>
                                            <td>{{ $company->fax ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $company->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Website:</strong></td>
                                            <td>{{ $company->website ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Address:</strong></td>
                                            <td>{{ $company->address ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>City:</strong></td>
                                            <td>{{ $company->city ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Country:</strong></td>
                                            <td>{{ $company->country ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>ZIP Code:</strong></td>
                                            <td>{{ $company->zip ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Logo:</strong></td>
                                            <td>{{ $company->logo ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Logo Path:</strong></td>
                                            <td>{{ $company->logo_path ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            @if($company->footer_text)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Footer Text</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            {{ $company->footer_text }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
