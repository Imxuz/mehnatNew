<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        //
    }

    /**
     * API foydalanuvchi token yo‘q bo‘lsa
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.'
        ], 401);
    }

    /**
     * API validatsiya xatolari uchun JSON format
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Global xatolar uchun JSON
     */
// app/Exceptions/Handler.php
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $exception->errors(),
                ], 422);
            }
        }

        return parent::render($request, $exception);
    }
}
