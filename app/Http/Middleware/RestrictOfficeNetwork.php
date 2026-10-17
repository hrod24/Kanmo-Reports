<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictOfficeNetwork
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        // Daftar IP WiFi kantor dari config
        $allowedIps = config(
            'office.allowed_ips',
            []
        );

        // IP yang terbaca oleh Laravel
        $clientIp = $request->ip();

        /*
        |--------------------------------------------------------------------------
        | Local Development
        |--------------------------------------------------------------------------
        | Izinkan localhost saat development.
        | Tidak berlaku pada production.
        */

        if (app()->environment('local')) {

            $allowedIps = array_merge(
                $allowedIps,
                [
                    '127.0.0.1',
                    '::1',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi IP
        |--------------------------------------------------------------------------
        */

        if (!in_array(
            $clientIp,
            $allowedIps,
            true
        )) {

            abort(
                403,
                'You can\'t access this page.'
            );
        }

        return $next($request);
    }
}
