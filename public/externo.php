<?php
require_once __DIR__ . '/../bootstrap.php';

redirect('evaluacion.php?instrumento=' . urlencode(CommunicationEvaluation::INSTRUMENT_EXTERNAL));
