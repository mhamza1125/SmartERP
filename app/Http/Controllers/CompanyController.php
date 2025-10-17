<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Settings;
use App\Http\Requests\CompanyRequest;
use App\Repositories\CompanyRepository;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->middleware(['auth', 'all']);
        $this->companyRepository = $companyRepository;
    }

    public function index()
    {
        $this->authorize('access', Settings::class);
        $company = $this->companyRepository->all();

        return view('company', [
            'company' => $company,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Settings::class);
        return view('addCompany');
    }

    public function store(CompanyRequest $request)
    {
        $this->authorize('create', Settings::class);
        $validatedData = $request->validated();
        $this->companyRepository->store($validatedData);

        return redirect()->route('company.add')->with('success', 'Company Record Inserted Successfully');
    }

    public function show(Company $company)
    {
        $this->authorize('show', Settings::class);
        return view('companyInfo', [
            'company' => $company,
        ]);
    }

    public function edit(Company $company)
    {
        $this->authorize('edit', Settings::class);
        return view('editCompany', [
            'company' => $company,
        ]);
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $this->authorize('edit', Settings::class);
        $validatedData = $request->validated();

        // Handle logo file upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoName = time() . '_' . $logoFile->getClientOriginalName();
            $logoPath = $logoFile->storeAs('logos', $logoName, 'public');
            $validatedData['logo_path'] = 'storage/' . $logoPath;

            // Remove the logo file from validated data since we're storing the path
            unset($validatedData['logo']);
        }

        $this->companyRepository->update($company->id, $validatedData);

        return redirect()->route('company.edit', $company->id)->with('success', 'Company Record Updated Successfully');
    }

    public function destroy(Company $company)
    {
        $this->authorize('delete', Settings::class);
        $this->companyRepository->delete($company->id);

        return redirect()->route('company')->with('success', 'Company Record Deleted Successfully');
    }

    /**
     * Get company data for AJAX requests (for print functionality)
     */
    public function getCompanyData()
    {
        $company = $this->companyRepository->first();

        return response()->json([
            'name' => $company->name ?? 'Sajjadson Lab Equipment',
            'address' => $company->address ?? 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan',
            'phone' => $company->phone ?? '+92 52 357 3727',
            'email' => $company->email ?? 'info@sajjadsonlab.com',
            'website' => $company->website ?? 'sajjadsonlab.com',
            'logo_path' => $company->logo_path ?? 'assets/print-logo.png',
            'footer_text' => $company->footer_text ?? 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan',
        ]);
    }
}
