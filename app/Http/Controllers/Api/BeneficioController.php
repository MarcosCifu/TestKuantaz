<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BeneficioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API de Prueba Técnica Kuantaz",
 *      description="Documentación de la API para la prueba técnica de Kuantaz sobre beneficios.",
 *      @OA\Contact(
 *          email="tu_email@ejemplo.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Servidor Principal de la API"
 * )
 */
class BeneficioController extends Controller
{
    protected BeneficioService $beneficioService;

    public function __construct(BeneficioService $beneficioService)
    {
        $this->beneficioService = $beneficioService;
    }

    /**
     * @OA\Get(
     *      path="/api/beneficios-procesados",
     *      operationId="obtenerBeneficiosProcesados",
     *      tags={"Beneficios"},
     *      summary="Obtiene los beneficios procesados, agrupados y filtrados por año.",
     *      description="Devuelve una lista de beneficios agrupados por año, con totales y fichas asociadas, filtrados por montos y ordenados de mayor a menor año.",
     *      @OA\Response(
     *          response=200,
     *          description="Operación exitosa",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="code", type="integer", example=200),
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="year", type="integer", example=2023),
     *                      @OA\Property(property="num", type="integer", example=5),
     *                      @OA\Property(property="monto_total_año", type="integer", example=150000),
     *                      @OA\Property(
     *                          property="beneficios",
     *                          type="array",
     *                          @OA\Items(
     *                              type="object",
     *                              @OA\Property(property="id_programa", type="integer", example=147),
     *                              @OA\Property(property="monto", type="integer", example=40000),
     *                              @OA\Property(property="fecha_recepcion", type="string", format="date", example="09/11/2023"),
     *                              @OA\Property(property="fecha", type="string", format="date", example="2023-11-09"),
     *                              @OA\Property(property="ano", type="string", example="2023"),
     *                              @OA\Property(property="view", type="boolean", example=true),
     *                              @OA\Property(
     *                                  property="ficha",
     *                                  type="object",
     *                                  @OA\Property(property="id", type="integer", example=922),
     *                                  @OA\Property(property="nombre", type="string", example="Emprende"),
     *                                  @OA\Property(property="id_programa", type="integer", example=147),
     *                                  @OA\Property(property="url", type="string", example="emprende"),
     *                                  @OA\Property(property="categoria", type="string", example="trabajo"),
     *                                  @OA\Property(property="descripcion", type="string", example="Fondos concursables para nuevos negocios")
     *                              )
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error interno del servidor",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="code", type="integer", example=500),
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Ocurrió un error al procesar los beneficios.")
     *          )
     *      )
     * )
     */
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