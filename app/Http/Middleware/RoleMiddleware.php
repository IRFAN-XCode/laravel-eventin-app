<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Ambil token dari Header
        $header = $request->header('Authorization');
        
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak disediakan atau format salah.'
            ], 401);
        }

        $token = substr($header, 7);

        try {
            // 2. Decode token
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET_KEY'), 'HS256'));
            
            if (!in_array($decoded->role, $roles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak! Anda tidak memiliki hak akses untuk halaman ini.'
                ], 403);
            }

            $request->attributes->add(['auth_user' => $decoded]);

            return $next($request);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau telah kedaluwarsa.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
