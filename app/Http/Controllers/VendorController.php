<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRequest;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\VendorRepository;
use App\Repositories\TransactionRepository;

class VendorController extends Controller
{
    protected $headRepository;
    protected $imageRepository;
    protected $vendorRepository;
    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        VendorRepository $vendorRepository, 
        TransactionRepository $transactionRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->vendorRepository = $vendorRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index(){
        $vendor = $this->vendorRepository->all();
        return view('vendor', [
            'vendor' => $vendor,
        ]); 
    }

    public function create(){
        $vendorType = $this->headRepository->get('11');
        $city = $this->headRepository->get('8');
        $vendor = $this->vendorRepository->all();
        $count = $this->vendorRepository->refNo();
        return view('addVendor', [
            'city' => $city,
            'count' => $count,
            'vendor' => $vendor,
            'vendorType' => $vendorType,
        ]);
    }

    public function store(VendorRequest $request){
        $validatedData = $request->validated();
        $getId = $this->vendorRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'vendor', 'vendors', $getId);        
            }
        }
        return redirect()->route('vendor.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $vendor = $this->vendorRepository->get($id);
        $image = $this->imageRepository->image('vendors', $id);
        return view('vendorInfo', [
            'vendor' => $vendor,
            'image' => $image,
        ]);
    }

    public function detail($id){
        $vendor = $this->vendorRepository->get($id);
        $detail = $this->transactionRepository->vDetail($id);
        $totalCredit = $detail->sum('credit');
        $totalDebit = $detail->sum('debit');
        $balance = $totalCredit - $totalDebit;
        return view('vendorDetail', [
            'vendor' => $vendor,
            'detail' => $detail,
            'balance' => $balance,
        ]);
    }
    
    public function edit(Vendor $id){        
        $vendorType = $this->headRepository->get('11');
        $city = $this->headRepository->get('8');
        return view('editVendor', [
            'city' => $city,
            'vendor' => $id,
            'vendorType' => $vendorType,
        ]);
    }

    public function update(Request $request, $id){
        $getId = $this->vendorRepository->update($id, $request->input());      
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'vendor', 'vendors', $getId);        
            }
        }
        return redirect()->route('vendor.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Vendor $vendor){}
}
