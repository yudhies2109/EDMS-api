<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicEmployeeResource;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PublicEmployeeController extends Controller
{
    #[OA\Get(
        path: '/api/public/employees', operationId: 'listPublicEmployees', tags: ['Employee Public'],
        summary: 'Daftar dan cari Employee tanpa login',
        description: 'Hanya menampilkan id, name, department, position. Pencarian tidak membedakan huruf besar/kecil. Hasil kosong tetap 200 dengan data berupa array kosong.',
        security: [],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Cari berdasarkan nama, departemen, atau jabatan. Tidak mencari email/telepon.', schema: new OA\Schema(type: 'string', maxLength: 100, nullable: true), example: 'Budi'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar public dengan pagination.', content: new OA\JsonContent(ref: '#/components/schemas/PublicEmployeeList')),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
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

    #[OA\Get(
        path: '/api/public/employees/{id}', operationId: 'showPublicEmployee', tags: ['Employee Public'],
        summary: 'Detail Employee tanpa login',
        description: 'Respons tidak menyertakan email, nomor telepon, atau timestamp Employee.',
        security: [],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID dari tabel employees.', schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail public Employee.', content: new OA\JsonContent(ref: '#/components/schemas/PublicEmployeeResponse')),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
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
