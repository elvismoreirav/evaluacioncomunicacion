<?php
/**
 * Bootstrap del sistema de diagnostico de comunicacion
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Mailer.php';
require_once __DIR__ . '/core/CommunicationEvaluation.php';

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$uploadDirs = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/documentos',
    __DIR__ . '/uploads/temp',
];

foreach ($uploadDirs as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}
