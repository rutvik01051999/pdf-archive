<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleLargeUploads
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Increase PHP limits for large file uploads
        if (ini_get('upload_max_filesize') < '100M') {
            ini_set('upload_max_filesize', '100M');
        }
        
        if (ini_get('post_max_size') < '100M') {
            ini_set('post_max_size', '100M');
        }
        
        if (ini_get('max_execution_time') < 300) {
            ini_set('max_execution_time', 300);
        }
        
        if (ini_get('max_input_time') < 300) {
            ini_set('max_input_time', 300);
        }
        
        if (ini_get('memory_limit') < '256M') {
            ini_set('memory_limit', '256M');
        }

        return $next($request);
    }
}