<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicEmployeeResource;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicEmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $employees = Employee::query()
            ->select(['id', 'name', 'department', 'position'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('department', '%'.$search.'%')
                        ->orWhereLike('position', '%'.$search.'%');
                });
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return PublicEmployeeResource::collection($employees)
            ->additional([
                'message' => 'Daftar karyawan berhasil diambil.',
                'errors' => null,
            ])
            ->response();
    }

    public function show(int $id): JsonResponse
    {
        $employee = Employee::query()
            ->select(['id', 'name', 'department', 'position'])
            ->find($id);

        if (! $employee) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        return (new PublicEmployeeResource($employee))
            ->additional([
                'message' => 'Detail karyawan berhasil diambil.',
                'errors' => null,
            ])
            ->response();
    }
}
