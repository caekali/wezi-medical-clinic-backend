<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Validator;

trait ApiResponse
{

    protected function successResponse($data, $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    protected function paginatedResponse(LengthAwarePaginator $paginator, $code = 200): JsonResponse

    {
        return response()->json([
            "data" => $paginator->items(),
            "meta" => [
                "page" => $paginator->currentPage(),
                "per_page" => $paginator->perPage(),
                "total" => $paginator->total(),
                "total_pages" => $paginator->lastPage(),

            ],
            "links" => [
                "first" => $paginator->url(1),
                "last" => $paginator->url($paginator->lastPage()),
                "next" => $paginator->url($paginator->nextPageUrl()),
                "prev" => $paginator->url($paginator->previousPageUrl()),
            ]
        ]);
    }

    protected function errorResponse($code, $message, $details = [], $status = 400): JsonResponse
    {
        $error = [
            "code" => $code,
            "message" => $message,
        ];

        if (!empty($details)) {
            $error["details"] = $details;
        }
        return response()->json($error, $status);

    }

    protected function validationErrorResponse(Validator $validator): JsonResponse
    {
        $details = [];
        foreach ($validator->errors()->messages() as $field => $messages) {
            foreach ($messages as $message) {
                $details[] = ["field" => $field, "message" => $message];
            }
        }
        return $this->errorResponse("VALIDATION_ERROR", $details, 422);
    }
}