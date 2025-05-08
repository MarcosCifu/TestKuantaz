<?php

use App\Http\Controllers\Api\BeneficioController;

Route::get('/beneficios-procesados', [BeneficioController::class, 'index']); 