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
use OpenApi\Attributes as OA;

class EmployeeController extends Controller
{
    #[OA\Get(
        path: '/api/employees', operationId: 'listEmployees', tags: ['Employee Private'],
        summary: 'Daftar dan cari Employee — Admin / Karyawan',
        description: 'Memerlukan token. Menampilkan data lengkap termasuk email dan telepon. Pencarian tidak membedakan huruf besar/kecil. Hasil kosong tetap 200.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Cari nama, email, departemen, atau jabatan.', schema: new OA\Schema(type: 'string', maxLength: 100, nullable: true), example: 'Budi'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar Employee dengan pagination.', content: new OA\JsonContent(ref: '#/components/schemas/EmployeeList')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
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

    #[OA\Get(
        path: '/api/employees/{employee}', operationId: 'showEmployee', tags: ['Employee Private'],
        summary: 'Detail lengkap Employee — Admin / Karyawan',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/EmployeeId')],
        responses: [
            new OA\Response(response: 200, description: 'Detail lengkap Employee.', content: new OA\JsonContent(ref: '#/components/schemas/EmployeeResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
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

    #[OA\Post(
        path: '/api/employees', operationId: 'createEmployee', tags: ['Employee Private'],
        summary: 'Tambah Employee — khusus Admin',
        description: 'Role karyawan ditolak dengan 403. Email harus unik. Telepon opsional. ID dan timestamp otomatis dibuat server. Simpan data.id untuk mencoba detail/edit/hapus.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/EmployeeInput')),
        responses: [
            new OA\Response(response: 201, description: 'Karyawan berhasil ditambahkan.', content: new OA\JsonContent(ref: '#/components/schemas/EmployeeResponse', example: [
                'message' => 'Karyawan berhasil ditambahkan.', 'data' => ['id' => 1, 'name' => 'Budi Santoso', 'email' => 'budi@example.com', 'phone' => '081234567890', 'department' => 'Teknologi Informasi', 'position' => 'Staff', 'created_at' => '2026-10-21T04:00:00.000000Z', 'updated_at' => '2026-10-21T04:00:00.000000Z'], 'errors' => null,
            ])),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
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

    #[OA\Put(
        path: '/api/employees/{employee}', operationId: 'replaceEmployee', tags: ['Employee Private'],
        summary: 'Edit semua kolom wajib — khusus Admin',
        description: 'Wajib mengirim name, email, department, position. Telepon opsional; jika tidak dikirim, nilai lama tetap tersimpan. Email Employee ini boleh dipertahankan; email Employee lain ditolak dengan 422.',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/EmployeeId')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/EmployeeInput')),
        responses: [
            new OA\Response(response: 200, description: 'Karyawan berhasil diperbarui.', content: new OA\JsonContent(ref: '#/components/schemas/EmployeeResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    #[OA\Patch(
        path: '/api/employees/{employee}', operationId: 'patchEmployee', tags: ['Employee Private'],
        summary: 'Edit sebagian kolom — khusus Admin',
        description: 'Kirim hanya kolom yang ingin diubah. Kolom wajib yang dikirim tidak boleh kosong. phone boleh null. Kolom yang tidak dikirim tetap dipertahankan.',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/EmployeeId')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/EmployeeFields', example: ['position' => 'Supervisor', 'phone' => null])),
        responses: [
            new OA\Response(response: 200, description: 'Karyawan berhasil diperbarui.', content: new OA\JsonContent(ref: '#/components/schemas/EmployeeResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
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

    #[OA\Delete(
        path: '/api/employees/{employee}', operationId: 'deleteEmployee', tags: ['Employee Private'],
        summary: 'Hapus Employee — khusus Admin',
        description: 'Tidak membutuhkan body. Data dihapus permanen. Setelah berhasil, detail ID ini menghasilkan 404. Gunakan data uji.',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/EmployeeId')],
        responses: [
            new OA\Response(response: 200, description: 'Karyawan berhasil dihapus.', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['message' => 'Karyawan berhasil dihapus.', 'data' => null, 'errors' => null])),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
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
