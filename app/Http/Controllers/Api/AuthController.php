<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        operationId: 'login',
        tags: ['Autentikasi'],
        summary: 'Login Admin atau Karyawan',
        description: 'Menghasilkan token Sanctum yang berlaku selama 8 jam.',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'admin@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'password-akun-anda'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login berhasil. Salin data.token, lalu masukkan ke tombol Authorize.',
                content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Email atau password salah.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse', example: [
                    'message' => 'Email atau password salah.', 'data' => null, 'errors' => null,
                ])
            ),
            new OA\Response(
                response: 422,
                description: 'Email/password tidak valid atau tidak diisi.',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse', example: [
                    'message' => 'Data yang diberikan tidak valid.', 'data' => null,
                    'errors' => ['email' => ['The email field is required.']],
                ])
            ),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data yang diberikan tidak valid.',
                'data' => null,
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $validator->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        $token = $user->createToken(
            'api-token',
            ['*'],
            now()->addHours(8),
        )->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
            'errors' => null,
        ], 200);
    }

    #[OA\Get(
        path: '/api/user', operationId: 'getProfile', tags: ['Autentikasi'],
        summary: 'Lihat akun yang sedang login',
        description: 'Mengembalikan profil akun berdasarkan token. Tabel users berbeda dari tabel employees.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Profil berhasil diambil.', content: new OA\JsonContent(ref: '#/components/schemas/ProfileResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Profil berhasil diambil.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'errors' => null,
        ]);
    }

    #[OA\Post(
        path: '/api/logout', operationId: 'logout', tags: ['Autentikasi'],
        summary: 'Logout dan cabut token saat ini',
        description: 'Tidak membutuhkan body. Token yang dikirim dicabut; token lain milik akun tetap berlaku. Setelah logout, token ini akan ditolak dengan 401.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logout berhasil.', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
            'data' => null,
            'errors' => null,
        ], 200);
    }
}
