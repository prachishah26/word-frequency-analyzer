<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait ApiResponseTrait
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, $message = 'Request was successful', $statusCode = Response::HTTP_OK)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($message, $statusCode = Response::HTTP_BAD_REQUEST, $data = null)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Return a validation error response.
     *
     * @param \Illuminate\Support\MessageBag $errors
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function validationErrorResponse($errors, $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        Log::info('error');
        return response()->json([
            'status'  => 'error',
            'message' => 'Validation errors occurred.',
            'errors'  => $errors,
        ], $statusCode);
    }

    /**
     * Return a 404 not found response.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function notFoundResponse($message = 'Resource not found', $statusCode = Response::HTTP_NOT_FOUND)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Return an internal server error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function internalServerErrorResponse($message = 'Internal server error', $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $statusCode);
    }
}
