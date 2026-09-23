<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Supabase\WebhookLog;

class LogAdminActionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log successful modifications
        if (in_array($request->method(), ["POST", "PUT", "PATCH", "DELETE"]) && $response->isSuccessful() || $response->isRedirection()) {
            
            // Ignore login/logout
            if (str_contains($request->url(), "login") || str_contains($request->url(), "logout")) {
                return $response;
            }

            $payload = $request->except(["_token", "password", "_method"]);
            $action = "admin." . strtolower(str_replace("/", ".", trim(str_replace(url("/dainely-admin-panel"), "", $request->url()), "/")));
            if(empty($action) || $action === "admin.") {
                $action = "admin.action";
            }

            try {
                WebhookLog::create([
                    "source" => "Admin Panel",
                    "event_type" => $action . "." . strtolower($request->method()),
                    "payload" => [
                        "url" => $request->url(),
                        "method" => $request->method(),
                        "data" => $payload,
                        "ip" => $request->ip()
                    ],
                    "status" => "processed",
                    "processed_at" => now(),
                ]);
            } catch (\Throwable $e) {
                // Fail silently if webhook log fails
            }
        }

        return $response;
    }
}

