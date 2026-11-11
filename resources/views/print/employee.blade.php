@extends('print.layout')

@section('title', 'Employee_Info_' . ($employee['employee_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Employee Information')
@section('doc_no', $employee['employee_no'] ?? '')

@section('content')
{{-- Employee Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Employee No</span>
            <span class="meta-value">{{ $employee['employee_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Name</span>
            <span class="meta-value">{{ $employee['name'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Department</span>
            <span class="meta-value">{{ $employee['dname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Employee Type</span>
            <span class="meta-value">{{ $employee['etname'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">CNIC</span>
            <span class="meta-value">{{ $employee['cnic'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Contact No</span>
            <span class="meta-value">{{ $employee['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Emergency No</span>
            <span class="meta-value">{{ $employee['phone2'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Status</span>
            <span class="meta-value">
                @if($employee['employee_status'] ?? false)
                    <span class="status-badge status-badge-success">Active</span>
                @else
                    <span class="status-badge status-badge-danger">Inactive</span>
                @endif
            </span>
        </div>
    </div>
</div>

{{-- Employment Details --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Joining Date</span>
            <span class="meta-value">{{ formatDate($employee['joining_date'] ?? null, 'N/A') }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Salary</span>
            <span class="meta-value">{{ isset($employee['salary']) ? 'Rs. ' . number_format($employee['salary']) : 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">City</span>
            <span class="meta-value">{{ $employee['cname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Designation</span>
            <span class="meta-value">{{ $employee['designation'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Short Name</span>
            <span class="meta-value">{{ $employee['sname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Father Name</span>
            <span class="meta-value">{{ $employee['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Attendance ID</span>
            <span class="meta-value">{{ $employee['attendance_id'] ?? 'N/A' }}</span>
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
