<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'PublicEmployee',
            type: 'object',
            required: ['id', 'name', 'department', 'position'],
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                new OA\Property(property: 'department', type: 'string', example: 'Teknologi Informasi'),
                new OA\Property(property: 'position', type: 'string', example: 'Staff'),
            ]
        ),
        new OA\Schema(
            schema: 'Employee',
            allOf: [
                new OA\Schema(ref: '#/components/schemas/PublicEmployee'),
                new OA\Schema(
                    type: 'object',
                    required: ['email', 'phone', 'created_at', 'updated_at'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '081234567890'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
                    ]
                ),
            ]
        ),
        new OA\Schema(
            schema: 'EmployeeFields',
            description: 'Kolom yang dikirim harus valid. Nomor telepon boleh null. Email harus unik pada tabel employees; saat edit, email Employee itu sendiri boleh dipertahankan.',
            type: 'object',
            properties: [
                new OA\Property(property: 'name', type: 'string', minLength: 1, maxLength: 255, example: 'Budi Santoso'),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'budi@example.com'),
                new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '081234567890'),
                new OA\Property(property: 'department', type: 'string', minLength: 1, maxLength: 255, example: 'Teknologi Informasi'),
                new OA\Property(property: 'position', type: 'string', minLength: 1, maxLength: 255, example: 'Staff'),
            ]
        ),
        new OA\Schema(
            schema: 'EmployeeInput',
            required: ['name', 'email', 'department', 'position'],
            allOf: [new OA\Schema(ref: '#/components/schemas/EmployeeFields')]
        ),
        new OA\Schema(
            schema: 'UserProfile',
            type: 'object',
            required: ['id', 'name', 'email', 'role'],
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 2),
                new OA\Property(property: 'name', type: 'string', example: 'Admin'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                new OA\Property(property: 'role', type: 'string', enum: ['admin', 'karyawan'], example: 'admin'),
            ]
        ),
        new OA\Schema(
            schema: 'LoginResponse',
            type: 'object',
            required: ['message', 'data', 'errors'],
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Login berhasil.'),
                new OA\Property(
                    property: 'data', type: 'object', required: ['token', 'token_type', 'user'],
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'TOKEN_DARI_SERVER'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'user', ref: '#/components/schemas/UserProfile'),
                    ]
                ),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ]
        ),
        new OA\Schema(
            schema: 'ProfileResponse',
            type: 'object',
            required: ['message', 'data', 'errors'],
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Profil berhasil diambil.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/UserProfile'),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ]
        ),
        new OA\Schema(
            schema: 'MessageResponse',
            type: 'object',
            required: ['message', 'data', 'errors'],
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Logout berhasil.'),
                new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ]
        ),
        new OA\Schema(
            schema: 'ErrorResponse',
            type: 'object',
            required: ['message', 'data', 'errors'],
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                new OA\Property(
                    property: 'errors', type: 'object', nullable: true, example: null,
                    additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string'))
                ),
            ]
        ),
        new OA\Schema(
            schema: 'PaginationLinks',
            type: 'object',
            required: ['first', 'last', 'prev', 'next'],
            properties: [
                new OA\Property(property: 'first', type: 'string'),
                new OA\Property(property: 'last', type: 'string'),
                new OA\Property(property: 'prev', type: 'string', nullable: true),
                new OA\Property(property: 'next', type: 'string', nullable: true),
            ]
        ),
        new OA\Schema(
            schema: 'PaginationMeta',
            type: 'object',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
                new OA\Property(property: 'to', type: 'integer', nullable: true, example: 10),
                new OA\Property(property: 'last_page', type: 'integer', example: 2),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 12),
                new OA\Property(property: 'path', type: 'string', example: '/api/employees'),
                new OA\Property(
                    property: 'links', type: 'array',
                    items: new OA\Items(type: 'object', properties: [
                        new OA\Property(property: 'url', type: 'string', nullable: true),
                        new OA\Property(property: 'label', type: 'string', example: '1'),
                        new OA\Property(property: 'active', type: 'boolean', example: true),
                    ])
                ),
            ]
        ),
        new OA\Schema(
            schema: 'PublicEmployeeResponse',
            type: 'object',
            required: ['message', 'data', 'errors'],
            properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/PublicEmployee'),
                new OA\Property(property: 'message', type: 'string', example: 'Detail karyawan berhasil diambil.'),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ]
        ),
        new OA\Schema(
            schema: 'EmployeeResponse',
            type: 'object',
            required: ['message', 'data', 'errors'],
            properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/Employee'),
                new OA\Property(property: 'message', type: 'string', example: 'Detail karyawan berhasil diambil.'),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ]
        ),
        new OA\Schema(
            schema: 'PublicEmployeeList',
            type: 'object',
            required: ['message', 'data', 'errors', 'links', 'meta'],
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PublicEmployee')),
                new OA\Property(property: 'message', type: 'string', example: 'Daftar karyawan berhasil diambil.'),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
                new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
            ]
        ),
        new OA\Schema(
            schema: 'EmployeeList',
            type: 'object',
            required: ['message', 'data', 'errors', 'links', 'meta'],
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Employee')),
                new OA\Property(property: 'message', type: 'string', example: 'Daftar karyawan berhasil diambil.'),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
                new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
                new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
            ]
        ),
    ],
    responses: [
        new OA\Response(
            response: 'Unauthorized',
            description: 'Token tidak ada, tidak valid, sudah dicabut, atau kedaluwarsa.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse', example: [
                'message' => 'Unauthenticated.', 'data' => null, 'errors' => null,
            ])
        ),
        new OA\Response(
            response: 'Forbidden',
            description: 'Role pengguna tidak diizinkan melakukan tindakan ini.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse', example: [
                'message' => 'This action is unauthorized.', 'data' => null, 'errors' => null,
            ])
        ),
        new OA\Response(
            response: 'NotFound',
            description: 'Employee dengan ID tersebut tidak ditemukan.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse', example: [
                'message' => 'Karyawan tidak ditemukan.', 'data' => null, 'errors' => null,
            ])
        ),
        new OA\Response(
            response: 'ValidationError',
            description: 'Input tidak valid. Detail per kolom tersedia pada errors; teks pesan mengikuti bahasa server.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse', example: [
                'message' => 'The email field is required.', 'data' => null,
                'errors' => ['email' => ['The email field is required.']],
            ])
        ),
    ],
    parameters: [
        new OA\Parameter(
            parameter: 'Page', name: 'page', in: 'query', required: false,
            description: 'Nomor halaman. Setiap halaman memuat maksimal 10 Employee.',
            schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)
        ),
        new OA\Parameter(
            parameter: 'EmployeeId', name: 'employee', in: 'path', required: true,
            description: 'ID Employee pada tabel employees, bukan ID akun users.',
            schema: new OA\Schema(type: 'integer', minimum: 1), example: 1
        ),
    ],
)]
class ApiSchemas {}
