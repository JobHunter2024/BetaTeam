<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable; // Import the Throwable interface
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class GlobalExceptionHandler extends ExceptionHandler
{
    protected $dontReport = [
        // Add any exceptions you want to ignore
    ];

    public function render($request, Throwable $exception) // Change Exception to Throwable
    {
        // Check if the exception is an instance of InvalidArgumentException
        if ($exception instanceof \InvalidArgumentException) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        // Call the parent render method for other exceptions
        return parent::render($request, $exception);
    }
}