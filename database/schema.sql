-- =====================================================
-- SISTEMA DE EVALUACION DE COMUNICACION
-- Esquema base inspirado en evaluacion180
-- =====================================================

CREATE TABLE IF NOT EXISTS `evalcom_admins` (
    `serial_admin` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nombre_completo` VARCHAR(200) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `rol` ENUM('SUPERADMIN', 'ADMIN', 'REVISOR') NOT NULL DEFAULT 'ADMIN',
    `activo` CHAR(2) NOT NULL DEFAULT 'SI',
    `ultimo_acceso` DATETIME NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_periodos` (
    `serial_cfg` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_per` INT NOT NULL UNIQUE,
    `fecha_inicio_diagnostico` DATETIME NULL,
    `fecha_fin_diagnostico` DATETIME NULL,
    `fecha_inicio_revision` DATETIME NULL,
    `fecha_fin_revision` DATETIME NULL,
    `estado_cfg` ENUM('BORRADOR', 'ACTIVO', 'CERRADO') NOT NULL DEFAULT 'BORRADOR',
    `observacion_cfg` TEXT NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_periodo (`serial_per`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_procesos` (
    `codigo_proceso` VARCHAR(20) PRIMARY KEY,
    `nombre_proceso` VARCHAR(160) NOT NULL,
    `descripcion_proceso` TEXT NULL,
    `orden_proceso` INT NOT NULL DEFAULT 1,
    `activo_proceso` CHAR(2) NOT NULL DEFAULT 'SI'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_participantes` (
    `serial_par` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_epl` INT NULL,
    `tipo_participante` ENUM('INTERNO', 'EXTERNO', 'MIXTO') NOT NULL DEFAULT 'INTERNO',
    `nombres_par` VARCHAR(120) NOT NULL,
    `apellidos_par` VARCHAR(120) NULL,
    `genero_par` ENUM('MUJER', 'VARON', 'NO_BINARIO', 'OTRO', 'PREFIERE_NO_DECIR') NULL,
    `cargo_par` VARCHAR(160) NULL,
    `organizacion_par` VARCHAR(200) NULL,
    `publico_par` VARCHAR(200) NULL,
    `email_par` VARCHAR(150) NULL,
    `telefono_par` VARCHAR(60) NULL,
    `activo_par` CHAR(2) NOT NULL DEFAULT 'SI',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_participante_tipo (`tipo_participante`, `activo_par`),
    INDEX idx_evalcom_participante_empleado (`serial_epl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_escalas` (
    `serial_esc` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_esc` VARCHAR(40) NOT NULL UNIQUE,
    `nombre_esc` VARCHAR(120) NOT NULL,
    `descripcion_esc` VARCHAR(255) NULL,
    `activo_esc` CHAR(2) NOT NULL DEFAULT 'SI',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_escala_opciones` (
    `serial_eco` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_esc` INT NOT NULL,
    `codigo_opc` VARCHAR(40) NOT NULL,
    `valor_opc` DECIMAL(10,4) NULL,
    `etiqueta_opc` VARCHAR(160) NOT NULL,
    `descripcion_opc` VARCHAR(255) NULL,
    `orden_opc` INT NOT NULL DEFAULT 1,
    `activo_opc` CHAR(2) NOT NULL DEFAULT 'SI',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_evalcom_escala_opcion (`serial_esc`, `codigo_opc`),
    UNIQUE KEY uk_evalcom_escala_orden (`serial_esc`, `orden_opc`),
    CONSTRAINT fk_evalcom_escala_opcion
        FOREIGN KEY (`serial_esc`) REFERENCES `evalcom_escalas`(`serial_esc`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_instrumentos` (
    `serial_ins` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_ins` VARCHAR(50) NOT NULL UNIQUE,
    `nombre_ins` VARCHAR(180) NOT NULL,
    `descripcion_ins` TEXT NULL,
    `tipo_ins` ENUM('ENTREVISTA', 'ENCUESTA', 'AUDITORIA', 'MAPEO', 'INVENTARIO', 'ANALISIS') NOT NULL DEFAULT 'ENCUESTA',
    `audiencia_ins` ENUM('INTERNA', 'EXTERNA', 'MIXTA', 'INSTITUCIONAL') NOT NULL DEFAULT 'INTERNA',
    `requiere_participante` CHAR(2) NOT NULL DEFAULT 'SI',
    `orden_ins` INT NOT NULL DEFAULT 1,
    `activo_ins` CHAR(2) NOT NULL DEFAULT 'SI',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_instrumento_tipo (`tipo_ins`, `activo_ins`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_secciones` (
    `serial_sec` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_ins` INT NOT NULL,
    `codigo_proceso` VARCHAR(20) NULL,
    `codigo_sec` VARCHAR(60) NOT NULL,
    `titulo_sec` VARCHAR(200) NOT NULL,
    `descripcion_sec` TEXT NULL,
    `tipo_sec` ENUM('CUESTIONARIO', 'ESCALA', 'MAPEO', 'INVENTARIO', 'ANALISIS') NOT NULL DEFAULT 'CUESTIONARIO',
    `serial_esc` INT NULL,
    `orden_sec` INT NOT NULL DEFAULT 1,
    `activo_sec` CHAR(2) NOT NULL DEFAULT 'SI',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_evalcom_seccion (`serial_ins`, `codigo_sec`),
    INDEX idx_evalcom_seccion_proceso (`codigo_proceso`, `orden_sec`),
    CONSTRAINT fk_evalcom_seccion_instrumento
        FOREIGN KEY (`serial_ins`) REFERENCES `evalcom_instrumentos`(`serial_ins`)
        ON DELETE CASCADE,
    CONSTRAINT fk_evalcom_seccion_proceso
        FOREIGN KEY (`codigo_proceso`) REFERENCES `evalcom_procesos`(`codigo_proceso`)
        ON DELETE SET NULL,
    CONSTRAINT fk_evalcom_seccion_escala
        FOREIGN KEY (`serial_esc`) REFERENCES `evalcom_escalas`(`serial_esc`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_seccion_procesos` (
    `serial_spr` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_sec` INT NOT NULL,
    `codigo_proceso` VARCHAR(20) NOT NULL,
    `orden_relacion` INT NOT NULL DEFAULT 1,
    UNIQUE KEY uk_evalcom_seccion_proceso (`serial_sec`, `codigo_proceso`),
    INDEX idx_evalcom_proceso_seccion (`codigo_proceso`, `orden_relacion`),
    CONSTRAINT fk_evalcom_seccion_procesos_seccion
        FOREIGN KEY (`serial_sec`) REFERENCES `evalcom_secciones`(`serial_sec`)
        ON DELETE CASCADE,
    CONSTRAINT fk_evalcom_seccion_procesos_proceso
        FOREIGN KEY (`codigo_proceso`) REFERENCES `evalcom_procesos`(`codigo_proceso`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_preguntas` (
    `serial_pre` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_sec` INT NOT NULL,
    `codigo_pre` VARCHAR(80) NOT NULL,
    `enunciado_pre` TEXT NOT NULL,
    `ayuda_pre` TEXT NULL,
    `tipo_respuesta` ENUM('TEXTO', 'NUMERICA_ESCALA', 'UNICA_OPCION', 'MULTIPLE_OPCION', 'BOOLEANO', 'NUMERO', 'FECHA') NOT NULL DEFAULT 'TEXTO',
    `serial_esc` INT NULL,
    `permite_observacion` CHAR(2) NOT NULL DEFAULT 'NO',
    `es_obligatoria` CHAR(2) NOT NULL DEFAULT 'SI',
    `orden_pre` INT NOT NULL DEFAULT 1,
    `activo_pre` CHAR(2) NOT NULL DEFAULT 'SI',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_evalcom_pregunta (`serial_sec`, `codigo_pre`),
    INDEX idx_evalcom_pregunta_seccion (`serial_sec`, `orden_pre`),
    CONSTRAINT fk_evalcom_pregunta_seccion
        FOREIGN KEY (`serial_sec`) REFERENCES `evalcom_secciones`(`serial_sec`)
        ON DELETE CASCADE,
    CONSTRAINT fk_evalcom_pregunta_escala
        FOREIGN KEY (`serial_esc`) REFERENCES `evalcom_escalas`(`serial_esc`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_pregunta_opciones` (
    `serial_pop` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_pre` INT NOT NULL,
    `codigo_opc` VARCHAR(40) NOT NULL,
    `etiqueta_opc` VARCHAR(160) NOT NULL,
    `valor_opc` VARCHAR(120) NULL,
    `orden_opc` INT NOT NULL DEFAULT 1,
    `activo_opc` CHAR(2) NOT NULL DEFAULT 'SI',
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_evalcom_pregunta_opcion (`serial_pre`, `codigo_opc`),
    UNIQUE KEY uk_evalcom_pregunta_opcion_orden (`serial_pre`, `orden_opc`),
    CONSTRAINT fk_evalcom_pregunta_opciones
        FOREIGN KEY (`serial_pre`) REFERENCES `evalcom_preguntas`(`serial_pre`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_evaluaciones` (
    `serial_eva` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_per` INT NOT NULL,
    `serial_ins` INT NOT NULL,
    `serial_par` INT NULL,
    `tipo_registro` ENUM('INDIVIDUAL', 'INSTITUCIONAL') NOT NULL DEFAULT 'INDIVIDUAL',
    `estado_eva` ENUM('BORRADOR', 'ENVIADA', 'REVISADA', 'CERRADA') NOT NULL DEFAULT 'BORRADOR',
    `titulo_referencia` VARCHAR(200) NULL,
    `puntaje_final` DECIMAL(10,4) NULL,
    `observacion_general` TEXT NULL,
    `fecha_inicio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_envio` DATETIME NULL,
    `fecha_revision` DATETIME NULL,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_eva_periodo (`serial_per`, `estado_eva`),
    INDEX idx_evalcom_eva_instrumento (`serial_ins`, `serial_per`),
    INDEX idx_evalcom_eva_participante (`serial_par`, `estado_eva`),
    CONSTRAINT fk_evalcom_eva_instrumento
        FOREIGN KEY (`serial_ins`) REFERENCES `evalcom_instrumentos`(`serial_ins`)
        ON DELETE RESTRICT,
    CONSTRAINT fk_evalcom_eva_participante
        FOREIGN KEY (`serial_par`) REFERENCES `evalcom_participantes`(`serial_par`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_respuestas` (
    `serial_res` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_eva` INT NOT NULL,
    `serial_pre` INT NOT NULL,
    `serial_eco` INT NULL,
    `serial_pop` INT NULL,
    `valor_texto` LONGTEXT NULL,
    `valor_numero` DECIMAL(10,4) NULL,
    `valor_booleano` CHAR(2) NULL,
    `valor_fecha` DATE NULL,
    `observacion` TEXT NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_evalcom_respuesta (`serial_eva`, `serial_pre`),
    CONSTRAINT fk_evalcom_respuesta_evaluacion
        FOREIGN KEY (`serial_eva`) REFERENCES `evalcom_evaluaciones`(`serial_eva`)
        ON DELETE CASCADE,
    CONSTRAINT fk_evalcom_respuesta_pregunta
        FOREIGN KEY (`serial_pre`) REFERENCES `evalcom_preguntas`(`serial_pre`)
        ON DELETE CASCADE,
    CONSTRAINT fk_evalcom_respuesta_escala_opcion
        FOREIGN KEY (`serial_eco`) REFERENCES `evalcom_escala_opciones`(`serial_eco`)
        ON DELETE SET NULL,
    CONSTRAINT fk_evalcom_respuesta_pregunta_opcion
        FOREIGN KEY (`serial_pop`) REFERENCES `evalcom_pregunta_opciones`(`serial_pop`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_respuesta_opciones` (
    `serial_rop` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_res` INT NOT NULL,
    `serial_pop` INT NOT NULL,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_evalcom_respuesta_multi (`serial_res`, `serial_pop`),
    CONSTRAINT fk_evalcom_multi_respuesta
        FOREIGN KEY (`serial_res`) REFERENCES `evalcom_respuestas`(`serial_res`)
        ON DELETE CASCADE,
    CONSTRAINT fk_evalcom_multi_opcion
        FOREIGN KEY (`serial_pop`) REFERENCES `evalcom_pregunta_opciones`(`serial_pop`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_publicos_mapeo` (
    `serial_pub` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_eva` INT NOT NULL,
    `tipo_publico` ENUM('INTERNO', 'EXTERNO', 'MIXTO') NOT NULL DEFAULT 'EXTERNO',
    `nombre_publico` VARCHAR(180) NOT NULL,
    `categoria_grupo` VARCHAR(160) NULL,
    `situacion_publico` ENUM('ALIADO', 'INDECISO', 'A_CONVENCER', 'OTRO') NULL,
    `fuente_informacion` VARCHAR(200) NULL,
    `influencia_directa` CHAR(2) NOT NULL DEFAULT 'NO',
    `estrategia_influencia` TEXT NULL,
    `necesidades_comunicacion` TEXT NULL,
    `intereses_valores_creencias` TEXT NULL,
    `medios_preferenciales` TEXT NULL,
    `cambio_buscado` TEXT NULL,
    `tono_lenguaje` TEXT NULL,
    `respuesta_necesidades` TEXT NULL,
    `mapa_empatia` LONGTEXT NULL,
    `orden_publico` INT NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_publico_evaluacion (`serial_eva`, `tipo_publico`, `orden_publico`),
    CONSTRAINT fk_evalcom_publico_evaluacion
        FOREIGN KEY (`serial_eva`) REFERENCES `evalcom_evaluaciones`(`serial_eva`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_herramientas_auditoria` (
    `serial_her` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_eva` INT NOT NULL,
    `plataforma_herramienta` VARCHAR(180) NOT NULL,
    `proposito_herramienta` TEXT NULL,
    `frecuencia_uso` VARCHAR(120) NULL,
    `personas_alcanzadas` VARCHAR(120) NULL,
    `area_responsable` VARCHAR(160) NULL,
    `observaciones_herramienta` TEXT NULL,
    `recomendaciones_herramienta` TEXT NULL,
    `orden_herramienta` INT NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_herramienta_eva (`serial_eva`, `orden_herramienta`),
    CONSTRAINT fk_evalcom_herramienta_evaluacion
        FOREIGN KEY (`serial_eva`) REFERENCES `evalcom_evaluaciones`(`serial_eva`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_archivos_mediaticos` (
    `serial_arm` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_eva` INT NOT NULL,
    `fecha_referencia` DATE NULL,
    `tipo_medio` VARCHAR(80) NULL,
    `nombre_medio` VARCHAR(180) NULL,
    `titulo_referencia` VARCHAR(220) NOT NULL,
    `url_referencia` VARCHAR(255) NULL,
    `representacion_institucion` TEXT NULL,
    `ejes_tematicos` TEXT NULL,
    `evaluacion_historica` TEXT NULL,
    `mejora_relaciones_publicas` TEXT NULL,
    `observacion_vocerias` TEXT NULL,
    `orden_archivo` INT NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_archivo_medio (`serial_eva`, `orden_archivo`),
    CONSTRAINT fk_evalcom_archivo_mediatico_evaluacion
        FOREIGN KEY (`serial_eva`) REFERENCES `evalcom_evaluaciones`(`serial_eva`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_archivos_institucionales` (
    `serial_ari` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_eva` INT NOT NULL,
    `tipo_recurso` VARCHAR(80) NOT NULL,
    `titulo_recurso` VARCHAR(220) NOT NULL,
    `fecha_recurso` DATE NULL,
    `publico_objetivo` VARCHAR(180) NULL,
    `descripcion_recurso` TEXT NULL,
    `url_recurso` VARCHAR(255) NULL,
    `observaciones_recurso` TEXT NULL,
    `orden_recurso` INT NOT NULL DEFAULT 1,
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evalcom_archivo_institucional (`serial_eva`, `orden_recurso`),
    CONSTRAINT fk_evalcom_archivo_institucional_evaluacion
        FOREIGN KEY (`serial_eva`) REFERENCES `evalcom_evaluaciones`(`serial_eva`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evalcom_log` (
    `serial_log` INT AUTO_INCREMENT PRIMARY KEY,
    `serial_per` INT NULL,
    `serial_eva` INT NULL,
    `serial_par` INT NULL,
    `serial_admin` INT NULL,
    `accion` VARCHAR(100) NOT NULL,
    `detalle` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_evalcom_log_periodo (`serial_per`),
    INDEX idx_evalcom_log_evaluacion (`serial_eva`),
    INDEX idx_evalcom_log_fecha (`fecha`),
    CONSTRAINT fk_evalcom_log_admin
        FOREIGN KEY (`serial_admin`) REFERENCES `evalcom_admins`(`serial_admin`)
        ON DELETE SET NULL,
    CONSTRAINT fk_evalcom_log_evaluacion
        FOREIGN KEY (`serial_eva`) REFERENCES `evalcom_evaluaciones`(`serial_eva`)
        ON DELETE SET NULL,
    CONSTRAINT fk_evalcom_log_participante
        FOREIGN KEY (`serial_par`) REFERENCES `evalcom_participantes`(`serial_par`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
