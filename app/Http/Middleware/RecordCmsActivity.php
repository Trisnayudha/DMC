<?php

namespace App\Http\Middleware;

use App\Support\CmsAudit;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RecordCmsActivity
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $sessionAction = $request->routeIs('cms.login.submit', 'cms.login.otp.verify', 'cms.logout');
        if (!$sessionAction && !in_array('cms_auth', $route->middleware(), true)) {
            return $next($request);
        }

        $user = $request->routeIs('cms.login.submit', 'cms.login.otp.verify') ? null : $request->user('cms');
        if (!$user && !$sessionAction) {
            return $next($request);
        }
        $menu = CmsAudit::menu($request);
        $entry = [
            'menu' => $menu,
            'action' => CmsAudit::action($request, $menu),
            // The URI template also identifies unnamed routes without exposing their parameters.
            'route' => $route->getName() && !Str::endsWith($route->getName(), '.') ? $route->getName() : $route->uri(),
            'method' => $request->method(),
            'fields' => CmsAudit::fields($request),
            'record_ids' => CmsAudit::recordIds($request),
            'occurred_at' => now(),
        ];
        if ($user) {
            $entry['cms_user_id'] = $user->getAuthIdentifier();
            $entry['admin_name'] = (string) $user->name;
        }

        // Persist the attempt before the controller runs, even if it exits early or rolls back.
        $log = $user ? CmsAudit::record($entry) : null;
        if ($log && $request->isMethod('GET') && !$request->ajax() && !$request->wantsJson()) {
            $request->attributes->set('cms_audit_page', (string) Str::uuid());
        }
        $response = $next($request);
        if ($request->routeIs('cms.login.submit', 'cms.login.otp.verify')) {
            $user = $request->user('cms');
            if ($user) {
                $entry['cms_user_id'] = $user->getAuthIdentifier();
                $entry['admin_name'] = (string) $user->name;
            }
        }
        if ($user) {
            $entry['http_status'] = $response->getStatusCode();
            $entry['outcome'] = $user->is_active ? $this->outcome($request, $response) : 'denied';
            if ($response->isSuccessful() && $request->attributes->get('cms_audit_rendered')) {
                $entry['page_key'] = $request->attributes->get('cms_audit_page');
            }
            CmsAudit::record($entry, $log);
        }

        return $response;
    }

    private function outcome(Request $request, Response $response): string
    {
        $status = $response->getStatusCode();
        if (in_array($status, [401, 403, 419], true)) {
            return 'denied';
        }
        $flashed = $request->session()->get('_flash.new', []);
        if (($response->exception ?? null) instanceof ValidationException || $status === 422
            || ($response->isRedirect() && in_array('errors', $flashed, true))) {
            return 'invalid';
        }
        if ($response->isClientError() || $response->isServerError()
            || ($response->isRedirect() && in_array('error', $flashed, true))) {
            return 'failed';
        }
        if ($response instanceof JsonResponse && is_array($response->original)) {
            $result = $response->original['status'] ?? null;
            if ($result === 'error' || (is_numeric($result) && $result >= 400)) {
                return 'failed';
            }
            foreach (['success', 'status', 'ok'] as $flag) {
                if (($response->original[$flag] ?? true) === false) {
                    return 'failed';
                }
            }
        }
        if ($response->isRedirect() && in_array('success', $flashed, true)) {
            return 'completed';
        }
        return $response->isRedirection() ? 'redirect' : 'completed';
    }
}
