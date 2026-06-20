<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeadRequest;
use App\Models\Head;
use App\Repositories\HeadRepository;
use App\Repositories\HeadTypeRepository;
use Illuminate\Http\Request;

class HeadController extends Controller
{
    protected $headRepository;

    protected $headTypeRepository;

    public function __construct(
        HeadRepository $headRepository,
        HeadTypeRepository $headTypeRepository
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->headTypeRepository = $headTypeRepository;
    }

    public function index()
    {
        $this->authorize('access', Head::class);
        $head = $this->headRepository->all();
        $headType = $this->headTypeRepository->all();

        return view('head', [
            'head' => $head,
            'headType' => $headType,
        ]);
    }

    public function headType()
    {
        $this->authorize('access', Head::class);
        $headType = $this->headRepository->headType();

        return view('headType', [
            'headType' => $headType,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Head::class);
        $headType = $this->headTypeRepository->all();

        return view('addHead', [
            'headType' => $headType,
        ]);
    }

    public function store(HeadRequest $request)
    {
        $this->authorize('create', Head::class);
        $validatedData = $request->validated();
        $duplicate = $this->headRepository->duplicate($validatedData);
        if ($duplicate) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Head name already exists'], 422);
            }
            return redirect()->route('head.add')->with(['fails' => 'Head name already exists'])->withInput();
        }
        $headId = $this->headRepository->store($validatedData);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'head_id' => $headId, 'message' => 'Head created successfully']);
        }

        return redirect()->route('head.add')->with('success', 'Record Inserted Successfully');
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Head::class);
        $this->headRepository->update($id, $request->input());

        return redirect()->route('head')->with('success', 'Record Updated Successfully');
    }

    public function show(Head $head)
    {
    }

    public function edit(Head $head)
    {
    }

    public function destroy(Head $head)
    {
    }
}
