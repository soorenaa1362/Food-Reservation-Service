<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HisApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        // توکن مورد انتظار (از .env بخون)
        $expectedToken = config('services.his.api_token'); // یا env('HIS_API_TOKEN')

        if (! $token || $token !== $expectedToken) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized - Invalid or missing token',
            ], 401);
        }

        // می‌تونی IP هم چک کنی (اختیاری ولی مفید)
        // $allowedIps = explode(',', env('HIS_ALLOWED_IPS', ''));
        // if (! in_array($request->ip(), $allowedIps)) {
        //     return response()->json(['message' => 'Unauthorized IP'], 403);
        // }

        return $next($request);
    }
}