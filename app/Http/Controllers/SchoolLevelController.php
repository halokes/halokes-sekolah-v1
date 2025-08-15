<?php

namespace App\Http\Controllers;

use App\Models\SchoolLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\AlertHelper;
use App\Services\SchoolLevelService;
use App\Http\Requests\SchoolLevel\SchoolLevelAddRequest;
use App\Http\Requests\SchoolLevel\SchoolLevelEditRequest;
use App\Http\Requests\SchoolLevel\SchoolLevelListRequest;

class SchoolLevelController extends Controller
{
    protected $schoolLevelService;

    public function __construct(SchoolLevelService $schoolLevelService)
    {
        $this->schoolLevelService = $schoolLevelService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SchoolLevelListRequest $request)
    {
        $validated = $request->validated();
        $schoolLevels = $this->schoolLevelService->getSchoolLevels($validated);

        return view('admin.pages.school_level.index', [
            'schoolLevels' => $schoolLevels,
            'pagination' => $schoolLevels->links()->render(),
            'search' => $validated['search'] ?? '',
            'perPage' => $validated['per_page'] ?? config('constant.CRUD.PER_PAGE'),
            'sortBy' => $validated['sort_by'] ?? config('constant.CRUD.SORT_BY'),
            'sortOrder' => $validated['sort_order'] ?? 'asc',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.school_level.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SchoolLevelAddRequest $request)
    {
        try {
            $schoolLevel = $this->schoolLevelService->createSchoolLevel($request->validated(), Auth::id());
            AlertHelper::success('Tingkat sekolah berhasil ditambahkan!');
            return redirect()->route('admin.school_level.index');
        } catch (\Exception $e) {
            Log::error("Error creating school level: " . $e->getMessage());
            AlertHelper::error('Gagal menambahkan tingkat sekolah: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schoolLevel = $this->schoolLevelService->findSchoolLevelById($id);
        if (!$schoolLevel) {
            AlertHelper::error('Tingkat sekolah tidak ditemukan.');
            return redirect()->route('admin.school_level.index');
        }
        return view('admin.pages.school_level.show', compact('schoolLevel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schoolLevel = $this->schoolLevelService->findSchoolLevelById($id);
        if (!$schoolLevel) {
            AlertHelper::error('Tingkat sekolah tidak ditemukan.');
            return redirect()->route('admin.school_level.index');
        }
        return view('admin.pages.school_level.edit', compact('schoolLevel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SchoolLevelEditRequest $request, string $id)
    {
        try {
            $schoolLevel = $this->schoolLevelService->updateSchoolLevel($id, $request->validated(), Auth::id());
            AlertHelper::success('Tingkat sekolah berhasil diperbarui!');
            return redirect()->route('admin.school_level.index');
        } catch (\Exception $e) {
            Log::error("Error updating school level: " . $e->getMessage());
            AlertHelper::error('Gagal memperbarui tingkat sekolah: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->schoolLevelService->deleteSchoolLevel($id);
            AlertHelper::success('Tingkat sekolah berhasil dihapus!');
            return redirect()->route('admin.school_level.index');
        } catch (\Exception $e) {
            Log::error("Error deleting school level: " . $e->getMessage());
            AlertHelper::error('Gagal menghapus tingkat sekolah: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
