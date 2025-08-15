<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\AlertHelper;
use App\Services\SchoolService;
use App\Http\Requests\School\SchoolAddRequest;
use App\Http\Requests\School\SchoolEditRequest;
use App\Http\Requests\School\SchoolListRequest;

class SchoolController extends Controller
{
    protected $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SchoolListRequest $request)
    {
        $validated = $request->validated();
        $schools = $this->schoolService->getSchools($validated);

        return view('admin.pages.school.index', [
            'schools' => $schools,
            'pagination' => $schools->links()->render(),
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
        return view('admin.pages.school.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SchoolAddRequest $request)
    {
        try {
            $school = $this->schoolService->createSchool($request->validated(), Auth::id());
            AlertHelper::success('Sekolah berhasil ditambahkan!');
            return redirect()->route('admin.school.index');
        } catch (\Exception $e) {
            Log::error("Error creating school: " . $e->getMessage());
            AlertHelper::error('Gagal menambahkan sekolah: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $school = $this->schoolService->findSchoolById($id);
        if (!$school) {
            AlertHelper::error('Sekolah tidak ditemukan.');
            return redirect()->route('admin.school.index');
        }
        return view('admin.pages.school.show', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $school = $this->schoolService->findSchoolById($id);
        if (!$school) {
            AlertHelper::error('Sekolah tidak ditemukan.');
            return redirect()->route('admin.school.index');
        }
        return view('admin.pages.school.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SchoolEditRequest $request, string $id)
    {
        try {
            $school = $this->schoolService->updateSchool($id, $request->validated(), Auth::id());
            AlertHelper::success('Sekolah berhasil diperbarui!');
            return redirect()->route('admin.school.index');
        } catch (\Exception $e) {
            Log::error("Error updating school: " . $e->getMessage());
            AlertHelper::error('Gagal memperbarui sekolah: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->schoolService->deleteSchool($id);
            AlertHelper::success('Sekolah berhasil dihapus!');
            return redirect()->route('admin.school.index');
        } catch (\Exception $e) {
            Log::error("Error deleting school: " . $e->getMessage());
            AlertHelper::error('Gagal menghapus sekolah: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
