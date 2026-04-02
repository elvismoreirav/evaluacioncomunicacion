<?php
require_once __DIR__ . '/../bootstrap.php';

$evaluation = new CommunicationEvaluation();
$period = $evaluation->getActiveAcademicPeriod();
$instruments = $evaluation->getInstrumentCatalog();

json_response([
    'app' => APP_NAME,
    'version' => APP_VERSION,
    'periodo_activo' => $period,
    'instrumentos' => array_map(
        static fn(array $instrument): array => [
            'codigo' => $instrument['codigo_ins'],
            'nombre' => $instrument['nombre_ins'],
            'audiencia' => $instrument['audiencia_ins'],
        ],
        $instruments
    ),
]);
