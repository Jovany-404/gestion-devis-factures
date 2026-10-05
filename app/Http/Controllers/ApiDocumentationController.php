<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiDocumentationController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        return response()->file(base_path('docs/openapi.yaml'), [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }
}
