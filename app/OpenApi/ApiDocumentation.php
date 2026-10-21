<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'EDMS Employee API',
    version: '1.0.0',
    description: 'Login melalui POST /api/login, salin data.token, lalu klik Authorize dan masukkan token tanpa awalan Bearer. Admin dapat membaca, mencari, menambah, mengedit, dan menghapus Employee. Karyawan hanya dapat membaca dan mencari. Endpoint public tidak membutuhkan token. Token berlaku 8 jam; logout mencabut token yang digunakan. ID akun users berbeda dari ID Employee.'
)]
#[OA\Server(
    url: '/',
    description: 'Server aplikasi saat ini'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    description: 'Masukkan token dari respons login, tanpa awalan Bearer.'
)]
#[OA\Tag(name: 'Autentikasi', description: 'Login, profil akun, dan logout. Login tidak membutuhkan token.')]
#[OA\Tag(name: 'Employee Public', description: 'Tanpa token. Hanya id, name, department, position yang ditampilkan.')]
#[OA\Tag(name: 'Employee Private', description: 'Memerlukan token Sanctum. Baca/cari: Admin dan Karyawan. Tambah/edit/hapus: khusus Admin.')]
class ApiDocumentation {}
