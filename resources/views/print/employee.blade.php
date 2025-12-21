@extends('print.layout')

@section('title', 'Employee_Info_' . ($employee['employee_no'] ?? 'N/A') . '_' . date('Y-m-d'))

@section('content')
<div class="document-title">Employee Information</div>

{{-- Employee Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Employee No:</span>
            <span class="info-value">{{ $employee['employee_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value">{{ $employee['name'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Department:</span>
            <span class="info-value">{{ $employee['dname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Employee Type:</span>
            <span class="info-value">{{ $employee['etname'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">CNIC:</span>
            <span class="info-value">{{ $employee['cnic'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact No:</span>
            <span class="info-value">{{ $employee['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Emergency No:</span>
            <span class="info-value">{{ $employee['phone2'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                @if($employee['employee_status'] ?? false)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </span>
        </div>
    </div>
</div>

{{-- Employment Details --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Joining Date:</span>
            <span class="info-value">{{ $employee['joining_date'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Salary:</span>
            <span class="info-value">{{ isset($employee['salary']) ? 'Rs. ' . number_format($employee['salary']) : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">City:</span>
            <span class="info-value">{{ $employee['cname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Designation:</span>
            <span class="info-value">{{ $employee['designation'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Short Name:</span>
            <span class="info-value">{{ $employee['sname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Father Name:</span>
            <span class="info-value">{{ $employee['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Attendance ID:</span>
            <span class="info-value">{{ $employee['attendance_id'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Address Information --}}
<div class="info-section avoid-break">
    <h3>Address</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {{ $employee['address'] ?? 'N/A' }}
    </div>
</div>

{{-- Description --}}
@if(isset($employee['description']) && !empty($employee['description']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($employee['description']))) !!}
    </div>
</div>
@endif

{{-- Employee Images --}}
@if(isset($image) && $image->count() > 0)
<div class="avoid-break">
    <h3>Employee Images</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        @foreach($image as $img)
        <div style="border: 1px solid #ddd; padding: 5px; text-align: center;">
            <img src="{{ asset('storage/employees/' . $img->image_name) }}" 
                 alt="Employee Image" 
                 style="max-width: 150px; max-height: 150px; object-fit: cover;"
                 onerror="this.style.display='none'">
            <div style="font-size: 10px; margin-top: 5px;">{{ $img->image_name }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
