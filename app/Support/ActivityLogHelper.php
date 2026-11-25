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

        $log = activity('error');
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

        // Constraint FK (kode SQLSTATE 23000, misal "Cannot delete or update
        // a parent row") aslinya cukup umum terjadi kalau user coba hapus
        // data yang masih dipakai di tabel lain. Daripada nampilin pesan SQL
        // mentah ke user, kasih pesan yang lebih manusiawi di sini sebagai
        // jaring pengaman umum — idealnya tiap controller udah ngecek duluan
        // sebelum delete, tapi ini buat jaga-jaga kalau ada yang kelewat.
        if ($e instanceof \Illuminate\Database\QueryException && $e->getCode() === '23000') {
            $message = 'Data tidak bisa dihapus/diubah karena masih terpakai di data lain. Hapus atau lepas dulu keterkaitannya sebelum mencoba lagi.';
        } else {
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        }

        $previousUrl = rtrim(url()->previous(), '/');
        $currentUrl = rtrim(request()->fullUrl(), '/');

        if ($previousUrl === '' || $previousUrl === $currentUrl) {
            abort(500, $message);
        }

        return back()
            ->withInput(request()->except(['password', 'password_confirmation', 'pin', 'token', '_token']))
            ->with('error', $message);
    }
}