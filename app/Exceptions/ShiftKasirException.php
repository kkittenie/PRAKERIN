<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception khusus aturan bisnis shift kasir (mis. shift masih berjalan atau sudah ditutup).
 * Dipisahkan dari \RuntimeException agar error database (\PDOException / QueryException)
 * tidak tertelan dan tetap tercatat ke Activity Log.
 */
class ShiftKasirException extends Exception
{
}
