<?php

namespace App\Domain\Common\Helpers;

use App\Exceptions\ErrorCode;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as FResponse;

trait ApiResponse
{
    protected $statusCode;
    protected $message;

    /**
     * @param int $statusCode
     * @return $this
     */
    private function setStatus(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param null|string|array $data
     * @param array $header
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    private function respond($data, $header = [])
    {
        return Response::json($data, $this->statusCode, $header);
    }

    /**
     * @param null|string|array $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function success($data = '', int $statusCode = FResponse::HTTP_OK)
    {
        return $this->setStatus($statusCode)->respond([
            'error_code' => 0,
            'message' => 'ok',
            'data' => $data
        ]);
    }

    /**
     * @param string|array $message
     * @param int $errorCode
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function failed($message = '', int $errorCode = ErrorCode::DEFAULT, int $statusCode = FResponse::HTTP_OK)
    {
        return $this->setStatus($statusCode)->respond([
            'error_code' => $errorCode,
            'message' => $message ?: 'failure'
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function internalServerError()
    {
        return $this->failed(ErrorCode::ERROR_MSG[ErrorCode::INTERNAL_SERVER_ERROR], ErrorCode::INTERNAL_SERVER_ERROR, FResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
