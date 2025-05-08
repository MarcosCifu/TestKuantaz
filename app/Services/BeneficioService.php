<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class BeneficioService
{
    protected string $beneficiosUrl;
    protected string $filtrosUrl;
    protected string $fichasUrl;

    public function __construct()
    {
        $this->beneficiosUrl = config('externalapis.beneficios.url');
        $this->filtrosUrl = config('externalapis.filtros.url');
        $this->fichasUrl = config('externalapis.fichas.url');
    }

    public function obtenerBeneficiosProcesados(): array
    {
        $beneficios = $this->obtenerDatosBeneficios();
        $filtros = $this->obtenerDatosFiltros()->keyBy('id_programa');
        $fichas = $this->obtenerDatosFichas()->keyBy('id');

        $beneficiosProcesados = $beneficios->map(function ($beneficio) use ($filtros, $fichas) {
            if (!is_array($beneficio) || !isset($beneficio['id_programa']) || !isset($beneficio['monto']) || !isset($beneficio['fecha'])) {
                return null;
            }

            $filtro = $filtros->get($beneficio['id_programa']);

            if (!$filtro) {
                return null;
            }

            if (!is_array($filtro) || !isset($filtro['min']) || !isset($filtro['max']) || !isset($filtro['ficha_id'])) {
                return null;
            }

            $montoBeneficio = $beneficio['monto'];
            if ($montoBeneficio < $filtro['min'] || $montoBeneficio > $filtro['max']) {
                return null;
            }

            $ficha = $fichas->get($filtro['ficha_id']);
            if (!$ficha) {
                return null;
            }
            if (!is_array($ficha)) {
                return null;
            }

            try {
                $year = Carbon::parse($beneficio['fecha'])->year;
                $anoStr = Carbon::parse($beneficio['fecha'])->format('Y');
            } catch (\Exception $e) {
                return null;
            }

            return [
                'id_programa' => $beneficio['id_programa'],
                'monto' => $beneficio['monto'],
                'fecha_recepcion' => $beneficio['fecha_recepcion'] ?? null,
                'fecha' => $beneficio['fecha'],
                'ano' => $anoStr,
                'view' => true,
                'ficha' => $ficha,
                '_year_for_grouping' => $year
            ];
        })->filter();

        $agrupadosPorAno = $beneficiosProcesados->groupBy('_year_for_grouping')
            ->map(function (Collection $beneficiosDelAno, $year) {
                $beneficiosFormateados = $beneficiosDelAno->map(function ($b) {
                    unset($b['_year_for_grouping']);
                    return $b;
                })->values()->all();

                return [
                    'year' => (int)$year,
                    'num' => $beneficiosDelAno->count(),
                    'monto_total_año' => $beneficiosDelAno->sum('monto'),
                    'beneficios' => $beneficiosFormateados,
                ];
            })
            ->sortByDesc('year')
            ->values();

        return $agrupadosPorAno->all();
    }

    private function obtenerDatosBeneficios(): Collection
    {
        $response = Http::get($this->beneficiosUrl);
        if ($response->failed()) {
            return collect([]);
        }
        return collect($response->json('data', []));
    }

    private function obtenerDatosFiltros(): Collection
    {
        $response = Http::get($this->filtrosUrl);
        if ($response->failed()) {
            return collect([]);
        }
        return collect($response->json('data', []));
    }

    private function obtenerDatosFichas(): Collection
    {
        $response = Http::get($this->fichasUrl);
        if ($response->failed()) {
            return collect([]);
        }
        return collect($response->json('data', []));
    }
} 