<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BeneficioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeneficioController extends Controller
{
    protected BeneficioService $beneficioService;

    public function __construct(BeneficioService $beneficioService)
    {
        $this->beneficioService = $beneficioService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $datosProcesados = $this->beneficioService->obtenerBeneficiosProcesados();
            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => $datosProcesados
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => $e->getCode() ?: 500,
                'success' => false,
                'message' => 'Ocurrió un error al procesar los beneficios.',
            ], $e->getCode() ?: 500);
        }
    }
} 