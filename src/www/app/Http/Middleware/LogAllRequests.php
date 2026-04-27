<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
 
use Illuminate\Support\Facades\Log as Logger;

class LogAllRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $data = $request->all();

         Logger::channel('custom')->info('Incoming Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            // 'headers' => $request->headers->all(),
              'payload_keys' => array_keys($data),
            'has_file_content' => !empty($data['file_content'])
        ]);

        return $next($request);
    }
}
