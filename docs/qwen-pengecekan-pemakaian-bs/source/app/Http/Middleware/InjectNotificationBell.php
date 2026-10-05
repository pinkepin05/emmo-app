<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectNotificationBell
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Suntikkan lonceng hanya jika: user login + respons HTML + belum ada lonceng
        if (
            auth()->check() &&
            $response instanceof \Illuminate\Http\Response &&
            str_contains((string) $response->headers->get('Content-Type'), 'text/html') &&
            str_contains($response->getContent(), '</body>') &&
            !str_contains($response->getContent(), 'id="notifBell"') // 🎯 cegah lonceng ganda
        ) {
            $bell = view('partials.notification-bell')->render();
            $response->setContent(
                str_replace('</body>', $bell . '</body>', $response->getContent())
            );
        }

        return $response;
    }
}