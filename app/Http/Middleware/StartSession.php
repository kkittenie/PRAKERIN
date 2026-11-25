<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as BaseStartSession;
use Symfony\Component\HttpFoundation\Response;

class StartSession extends BaseStartSession
{
    /**
     * Request polling background & DataTables GET yang bersifat read-only tidak
     * boleh menimpa `_previous.url` ataupun memakan/menimpa flash session
     * (`success`, `error`, `_old_input`, `errors`) milik navigasi/form utama.
     */
    protected function isReadOnlyBackgroundRequest(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        return $request->is('notifikasi/unread-json')
            || $request->is('mobile/notifikasi/unread-json')
            || $request->is('mobile/menu/sync')
            || $request->is('mobile/pesanan/*/status')
            || ($request->ajax() && $request->has('draw'));
    }

    protected function storeCurrentUrl(Request $request, $session)
    {
        if ($this->isReadOnlyBackgroundRequest($request)) {
            return;
        }

        parent::storeCurrentUrl($request, $session);
    }

    protected function addCookieToResponse(Response $response, $session)
    {
        if (request() && $this->isReadOnlyBackgroundRequest(request())) {
            return;
        }

        parent::addCookieToResponse($response, $session);
    }

    protected function saveSession($request)
    {
        if ($this->isReadOnlyBackgroundRequest($request)) {
            return;
        }

        parent::saveSession($request);
    }
}
