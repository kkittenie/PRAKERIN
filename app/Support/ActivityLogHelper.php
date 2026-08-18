<?php

namespace App\Support;

use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ActivityLogHelper
{
    /**
     * Catat exception ke Activity Log dan kembalikan respon error (atau rethrow jika validation/http response exception).
     *
     * @param Throwable $e
     * @param string|null $customMessage
     * @return JsonResponse|RedirectResponse
     * @throws Throwable
     */
    public static function handleException(Throwable $e, ?string $customMessage = null)
    {
        if ($e instanceof ValidationException || $e instanceof HttpResponseException) {
            throw $e;
        }

        $user = auth()->user() ?? auth('member')->user();

        $log = activity();
        if ($user) {
            $log->causedBy($user);
        }

        $log->withProperties([
            'error_message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'input' => request()->except(['password', 'password_confirmation', 'pin', 'token', '_token']),
        ])->log($customMessage ?? ('Error: ' . $e->getMessage()));

        $message = 'Terjadi kesalahan: ' . $e->getMessage();

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        }

        return back()->with('error', $message);
    }
}
