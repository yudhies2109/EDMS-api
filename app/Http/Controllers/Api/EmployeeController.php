<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Employee::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($validated['search'] ?? '');

        $employees = Employee::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%')
                        ->orWhereLike('department', '%'.$search.'%')
                        ->orWhereLike('position', '%'.$search.'%');
                });
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return EmployeeResource::collection($employees)
            ->additional([
                'message' => 'Daftar karyawan berhasil diambil.',
                'errors' => null,
            ])
            ->response();
    }

    public function show(Employee $employee): JsonResponse
    {
        Gate::authorize('view', $employee);

        return (new EmployeeResource($employee))
            ->additional([
                'message' => 'Detail karyawan berhasil diambil.',
                'errors' => null,
            ])
            ->response();
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());

        return (new EmployeeResource($employee))
            ->additional([
                'message' => 'Karyawan berhasil ditambahkan.',
                'errors' => null,
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        return (new EmployeeResource($employee))
            ->additional([
                'message' => 'Karyawan berhasil diperbarui.',
                'errors' => null,
            ])
            ->response();
    }

    public function destroy(Employee $employee): JsonResponse
    {
        Gate::authorize('delete', $employee);

        $employee->delete();

        return response()->json([
            'message' => 'Karyawan berhasil dihapus.',
            'data' => null,
            'errors' => null,
        ]);
    }
}
