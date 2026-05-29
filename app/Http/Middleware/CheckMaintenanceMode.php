<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if Maintenance Mode is enabled in settings
        $isMaintenance = Setting::getValue('maintenance_mode', '0');

        if ($isMaintenance === '1') {
            // 2. Allow Admin Panel, Login routes, and Assets even in maintenance
            if ($request->is('admin*') || 
                $request->is('login') || 
                $request->is('logout') || 
                $request->is('password/*') ||
                $request->is('register') || // Depending on needs
                $request->is('api/*') || // Usually api has separate maintenance logic
                $request->is('ui-physio/*') ||
                $request->is('storage/*')) {
                return $next($request);
            }

            // 3. Show maintenance view
            $message = Setting::getValue('maintenance_message', 'We are currently under maintenance. Please check back later.');
            
            return response()->view('errors.maintenance', [
                'message' => $message
            ], 503); // Service Unavailable status code
        }

        return $next($request);
    }
}
