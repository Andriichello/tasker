<?php

namespace App\Http;

use OpenApi\Annotations as OA;

/**
 * This class is needed solely as a place for common
 * OpenApi annotations.
 */
abstract class OpenApiAnnotations
{
    /**
     * @OA\Info(title="tasker", version="0.1"),
     *   @OA\Server(
     *     url="http://localhost:8000",
     *     description="Local server"
     *   )
     * ),
     *
     * @OA\SecurityScheme(
     *    securityScheme="bearerAuth",
     *    type="http",
     *    scheme="bearer"
     *  ),
     */

    /**
     * @OA\Schema(
     *   schema="DestroyResponse",
     *   description="Delete response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     */
}
