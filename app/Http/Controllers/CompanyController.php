<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'all']);
    }

    public function index(){
        // Data Display
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        // Form for Insertion
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Insertion
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        // Similar to get
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        // Edit Form
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        // Edit
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // Delete
    }
}
