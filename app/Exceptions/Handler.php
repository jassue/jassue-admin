<?php

namespace App\Exceptions;

use App\Domain\Common\Helpers\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        BusinessException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $customReport = [
        AuthenticationException::class => ErrorCode::UNAUTHORIZED,
        ModelNotFoundException::class => ErrorCode::MODEL_NOT_FOUND,
        AuthorizationException::class => ErrorCode::FORBIDDEN,
        ValidationException::class => ErrorCode::UNPROCESSABLE_ENTITY,
        UnauthorizedHttpException::class => ErrorCode::UNAUTHORIZED,
        NotFoundHttpException::class => ErrorCode::HTTP_NOT_FOUND,
        MethodNotAllowedHttpException::class => ErrorCode::METHOD_NOT_ALLOWED,
        QueryException::class => ErrorCode::SQL_ERROR,
        TooManyRequestsHttpException::class => ErrorCode::TOO_MANY_REQUEST,
        BusinessException::class => ErrorCode::DEFAULT
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param Throwable $e
     * @return bool|mixed
     */
    public function shouldCustomReturn(Throwable $e)
    {
        foreach (array_keys($this->customReport) as $report) {
            if ($e instanceof $report) return $report;
        }
        return false;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->isJson()) {
            if ($report = $this->shouldCustomReturn($e)) {
                $errorCode = $this->customReport[$report];
                $errorMsg = ErrorCode::ErrorMsg[$errorCode] ?? $e->getMessage();

                if ($e instanceof ValidationException) {
                    return $this->failed(collect($e->errors())->first()[0], $errorCode);
                }

                if ($e instanceof BusinessException) {
                    return $this->failed($e->getMessage(), $e->getCode());
                }

                if ($e instanceof QueryException) {
                    return $this->failed(
                        env('APP_DEBUG') ? $e->getMessage() : $errorMsg,
                        $errorCode
                    );
                }

                return $this->failed($errorMsg, $errorCode);
            }

            return env('APP_DEBUG') ? parent::render($request, $e) : $this->internalServerError();
        }

        return parent::render($request, $e);
    }
}
