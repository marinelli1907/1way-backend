<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // You can add logging/reporting here if needed.
        });

        // JSON 404 response
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(
                    function_exists('responseFormatter')
                        ? responseFormatter(DEFAULT_404)
                        : [
                            'response_code' => 404,
                            'message' => 'Not Found',
                            'content' => null,
                            'errors' => [],
                        ],
                    404
                );
            }

            return null; // let Laravel handle non-JSON
        });

        // JSON response for other HttpExceptions (401/403/405/419/429/500, etc.)
        $this->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson()) {
                $status = $e->getStatusCode();

                return response()->json([
                    'response_code' => $status,
                    'message' => $e->getMessage() ?: $this->defaultHttpMessage($status),
                    'content' => null,
                    'errors' => [],
                ], $status);
            }

            return null; // let Laravel handle non-JSON
        });
    }

    /**
     * Fallback messages when HttpException has an empty message.
     */
    private function defaultHttpMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Page Expired',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Server Error',
            default => 'Error',
        };
    }
}
