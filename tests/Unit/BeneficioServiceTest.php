<?php

namespace Tests\Unit;

use App\Services\BeneficioService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class BeneficioServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    #[Test]
    public function procesa_correctamente_los_beneficios_filtros_y_fichas()
    {
        Http::fake([
            config('externalapis.beneficios.url') => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id_programa' => 147,
                        'monto' => 40000,
                        'fecha_recepcion' => '09/11/2023',
                        'fecha' => '2023-11-09'
                    ],
                    [
                        'id_programa' => 147,
                        'monto' => 60000,
                        'fecha_recepcion' => '10/10/2023',
                        'fecha' => '2023-10-10'
                    ],
                    [
                        'id_programa' => 130,
                        'monto' => 10000,
                        'fecha_recepcion' => '08/06/2022',
                        'fecha' => '2022-06-08'
                    ],
                    [
                        'id_programa' => 999,
                        'monto' => 10000,
                        'fecha_recepcion' => '01/01/2023',
                        'fecha' => '2023-01-01'
                    ],
                    [
                        'id_programa' => 130,
                        'monto' => 10000,
                        'fecha_recepcion' => 'INVALID_DATE',
                        'fecha' => 'INVALID_DATE'
                    ],
                    [
                        'id_programa' => 146,
                        'monto' => 20000,
                        'fecha_recepcion' => '10/10/2023',
                        'fecha' => '2023-10-10'
                    ],
                ]
            ]),
            config('externalapis.filtros.url') => Http::response([
                'success' => true,
                'data' => [
                    ['id_programa' => 147, 'tramite' => 'Emprende', 'min' => 1000, 'max' => 50000, 'ficha_id' => 922],
                    ['id_programa' => 130, 'tramite' => 'Subsidio Único Familiar', 'min' => 5000, 'max' => 180000, 'ficha_id' => 2042],
                    ['id_programa' => 146, 'tramite' => 'Crece', 'min' => 0, 'max' => 30000, 'ficha_id' => 9999],
                ]
            ]),
            config('externalapis.fichas.url') => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 922, 'nombre' => 'Emprende', 'id_programa' => 147, 'url' => 'emprende', 'categoria' => 'trabajo'],
                    ['id' => 2042, 'nombre' => 'Subsidio Familiar (SUF)', 'id_programa' => 130, 'url' => 'subsidio_familiar_suf', 'categoria' => 'bonos'],
                ]
            ]),
        ]);

        $servicio = new BeneficioService();
        $resultado = $servicio->obtenerBeneficiosProcesados();

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $resultado2023 = $resultado[0];
        $this->assertEquals(2023, $resultado2023['year']);
        $this->assertEquals(1, $resultado2023['num']);
        $this->assertEquals(40000, $resultado2023['monto_total_año']);
        $this->assertCount(1, $resultado2023['beneficios']);
        $this->assertEquals(147, $resultado2023['beneficios'][0]['id_programa']);
        $this->assertEquals(40000, $resultado2023['beneficios'][0]['monto']);
        $this->assertEquals('2023', $resultado2023['beneficios'][0]['ano']);
        $this->assertEquals('Emprende', $resultado2023['beneficios'][0]['ficha']['nombre']);

        $resultado2022 = $resultado[1];
        $this->assertEquals(2022, $resultado2022['year']);
        $this->assertEquals(1, $resultado2022['num']);
        $this->assertEquals(10000, $resultado2022['monto_total_año']);
        $this->assertCount(1, $resultado2022['beneficios']);
        $this->assertEquals(130, $resultado2022['beneficios'][0]['id_programa']);
        $this->assertEquals(10000, $resultado2022['beneficios'][0]['monto']);
        $this->assertEquals('2022', $resultado2022['beneficios'][0]['ano']);
        $this->assertEquals('Subsidio Familiar (SUF)', $resultado2022['beneficios'][0]['ficha']['nombre']);

        Http::assertSentCount(3);
        Http::assertSent(function ($request) {
            return $request->url() == config('externalapis.beneficios.url');
        });
        Http::assertSent(function ($request) {
            return $request->url() == config('externalapis.filtros.url');
        });
        Http::assertSent(function ($request) {
            return $request->url() == config('externalapis.fichas.url');
        });
    }

    /** @test */
    #[Test]
    public function maneja_correctamente_respuestas_vacias_o_fallidas_de_apis_externas()
    {
        Http::fake([
            config('externalapis.beneficios.url') => Http::response(null, 500),
            config('externalapis.filtros.url') => Http::response(['data' => []]),
            config('externalapis.fichas.url') => Http::response(['data' => null]),
        ]);

        $servicio = new BeneficioService();
        $resultado = $servicio->obtenerBeneficiosProcesados();

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado, 'El resultado debería estar vacío si las APIs fallan o no devuelven datos.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
} 