-- =====================================================
-- SISTEMA DE EVALUACION DE COMUNICACION
-- Catalogos e instrumento base del diagnostico
-- =====================================================

INSERT IGNORE INTO `evalcom_procesos` (`codigo_proceso`, `nombre_proceso`, `descripcion_proceso`, `orden_proceso`)
VALUES
    ('PDI.04.01', 'Estrategia de comunicaciones', 'Planificación, estructura y priorización de la comunicación institucional.', 1),
    ('PDI.04.02', 'Administración de medios y canales', 'Uso y optimización de medios, herramientas y canales institucionales.', 2),
    ('PDI.04.03', 'Gestión de imagen y reputación institucional', 'Análisis de posicionamiento, reputación y representación pública.', 3),
    ('PDI.04.04', 'Administración de la marca institucional', 'Uso de identidad visual, mensajes y consistencia institucional.', 4),
    ('PDI.04.05', 'Cobertura de eventos y relaciones públicas', 'Cobertura, vocerías y relación con medios y actores externos.', 5),
    ('PDI.04.06', 'Gestión de contenidos', 'Pertinencia, claridad y alineación de los contenidos institucionales.', 6);

INSERT IGNORE INTO `evalcom_escalas` (`codigo_esc`, `nombre_esc`, `descripcion_esc`)
VALUES
    ('DEBIL_EXCELENTE_5', 'Escala débil a excelente', 'Escala de 1 a 5 para auditorías y análisis internos.'),
    ('IMPORTANCIA_5', 'Escala de importancia', 'Escala de percepción de importancia del trabajo institucional.'),
    ('CALIDAD_5', 'Escala de calidad', 'Escala para valorar la calidad de productos de comunicación.');

INSERT IGNORE INTO `evalcom_escala_opciones` (`serial_esc`, `codigo_opc`, `valor_opc`, `etiqueta_opc`, `orden_opc`)
VALUES
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N1', 1.0000, 'Débil', 1),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N2', 2.0000, 'Nivel 2', 2),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N3', 3.0000, 'Bien', 3),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N4', 4.0000, 'Nivel 4', 4),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N5', 5.0000, 'Excelente', 5),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'MUY_IMPORTANTE', 5.0000, 'Muy importante', 1),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'IMPORTANTE', 4.0000, 'Importante', 2),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'POCO_IMPORTANTE', 2.0000, 'Poco importante', 3),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'NADA_IMPORTANTE', 1.0000, 'Nada importante', 4),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'SIN_OPINION', NULL, 'No tengo opinión', 5),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'MUY_BAJA', 1.0000, 'Muy baja', 1),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'BAJA', 2.0000, 'Baja', 2),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'CORRECTA', 3.0000, 'Correcta', 3),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'BUENA', 4.0000, 'Buena', 4),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'MUY_BUENA', 5.0000, 'Muy buena', 5);

INSERT IGNORE INTO `evalcom_instrumentos` (`codigo_ins`, `nombre_ins`, `descripcion_ins`, `tipo_ins`, `audiencia_ins`, `requiere_participante`, `orden_ins`)
VALUES
    ('PERSONAL_INTERNO', 'Encuesta o entrevista a directivos y personal', 'Instrumento para recoger percepciones internas sobre discurso, procesos y desafíos de la comunicación institucional.', 'ENTREVISTA', 'INTERNA', 'SI', 1),
    ('PUBLICOS_EXTERNOS', 'Encuesta o entrevista a organizaciones aliadas o públicos externos', 'Instrumento para recoger percepciones externas sobre clima institucional, imagen, relación y participación.', 'ENTREVISTA', 'EXTERNA', 'SI', 2),
    ('ESTRUCTURA_ORGANIZACIONAL', 'Estructura organizacional', 'Auditoría interna del proceso PDI.04.01.', 'AUDITORIA', 'INSTITUCIONAL', 'NO', 3),
    ('MAPEO_PUBLICOS', 'Mapeo y caracterización de públicos', 'Registro estructurado de públicos internos, externos y mixtos.', 'MAPEO', 'INSTITUCIONAL', 'NO', 4),
    ('COMUNICACION_ESTRATEGICA', 'Análisis de la comunicación estratégica', 'Auditoría interna del plan y de la planificación anual de comunicación.', 'AUDITORIA', 'INSTITUCIONAL', 'NO', 5),
    ('AUDITORIA_HERRAMIENTAS', 'Auditoría de herramientas y espacios de comunicación existentes', 'Inventario y análisis de plataformas, herramientas y espacios de comunicación.', 'INVENTARIO', 'INSTITUCIONAL', 'NO', 6),
    ('ARCHIVOS_MEDIATICOS', 'Archivos mediáticos', 'Registro y análisis de apariciones en medios y canales digitales.', 'INVENTARIO', 'INSTITUCIONAL', 'NO', 7),
    ('IMAGEN_POSICIONAMIENTO', 'Análisis de la imagen y posicionamiento institucional', 'Auditoría de imagen, identidad institucional y administración de marca.', 'AUDITORIA', 'INSTITUCIONAL', 'NO', 8),
    ('ARCHIVOS_INSTITUCIONALES', 'Archivos de la propia institución', 'Registro y valoración de publicaciones, videos y otros contenidos institucionales.', 'ANALISIS', 'INSTITUCIONAL', 'NO', 9);

INSERT IGNORE INTO `evalcom_secciones` (`serial_ins`, `codigo_sec`, `titulo_sec`, `descripcion_sec`, `tipo_sec`, `orden_sec`)
VALUES
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PERSONAL_INTERNO'), 'DISCURSO_INSTITUCIONAL', 'Discurso institucional', 'Preguntas abiertas para personal interno.', 'CUESTIONARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PERSONAL_INTERNO'), 'PROCESOS_COMUNICACIONALES', 'Vivencia de los procesos comunicacionales internos y externos', 'Preguntas sobre coordinación, canales, inclusión y necesidades de los públicos.', 'CUESTIONARIO', 2),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PERSONAL_INTERNO'), 'FORTALEZAS_DESAFIOS', 'Fortalezas y desafíos', 'Identificación de fortalezas, desafíos y necesidades de aprendizaje.', 'CUESTIONARIO', 3),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'CLIMA_INSTITUCIONAL', 'Clima institucional', 'Preguntas abiertas para participantes externos.', 'CUESTIONARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'PERCEPCION_INSTITUCION', 'Percepción de la institución', 'Percepción externa de la imagen, el valor y la comparación institucional.', 'CUESTIONARIO', 2),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'RELACIONES_PUBLICAS', 'Relaciones públicas', 'Percepción de escucha, información y trato institucional.', 'CUESTIONARIO', 3),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'COMUNICACION_PARTICIPACION', 'Comunicación y participación', 'Medios preferidos, participación futura y calidad percibida de los productos.', 'CUESTIONARIO', 4),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'ESTRUCTURA_ORGANIZACIONAL'), 'ESTRUCTURA_ORG', 'Estructura organizacional', 'Auditoría del flujo de información, toma de decisiones y responsabilidades.', 'ESCALA', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'MAPEO_PUBLICOS'), 'MAPEO_PUBLICOS_BASE', 'Mapeo y caracterización de públicos', 'La captura detallada se registra en evalcom_publicos_mapeo.', 'MAPEO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'COMUNICACION_ESTRATEGICA'), 'ANALISIS_COM_ESTRATEGICA', 'Análisis de la comunicación estratégica', 'Auditoría del plan, metas e indicadores de comunicación.', 'ESCALA', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'AUDITORIA_HERRAMIENTAS'), 'AUDITORIA_HERRAMIENTAS_BASE', 'Auditoría de herramientas y espacios', 'La captura detallada se registra en evalcom_herramientas_auditoria.', 'INVENTARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'ARCHIVOS_MEDIATICOS'), 'ARCHIVOS_MEDIATICOS_BASE', 'Archivos mediáticos', 'La captura detallada se registra en evalcom_archivos_mediaticos.', 'INVENTARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'IMAGEN_POSICIONAMIENTO'), 'IMAGEN_POSICIONAMIENTO_BASE', 'Análisis de la imagen y posicionamiento institucional', 'Auditoría de imagen, marca, identidad y mensajes.', 'ESCALA', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'ARCHIVOS_INSTITUCIONALES'), 'ARCHIVOS_INSTITUCIONALES_BASE', 'Archivos de la propia institución', 'Registro de recursos institucionales y valoración de cobertura, marca y contenido.', 'ESCALA', 1);

UPDATE `evalcom_secciones`
SET `codigo_proceso` = 'PDI.04.01',
    `serial_esc` = (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5')
WHERE `codigo_sec` IN ('ESTRUCTURA_ORG', 'ANALISIS_COM_ESTRATEGICA');

UPDATE `evalcom_secciones`
SET `codigo_proceso` = 'PDI.04.01'
WHERE `codigo_sec` = 'MAPEO_PUBLICOS_BASE';

UPDATE `evalcom_secciones`
SET `codigo_proceso` = 'PDI.04.02'
WHERE `codigo_sec` = 'AUDITORIA_HERRAMIENTAS_BASE';

UPDATE `evalcom_secciones`
SET `codigo_proceso` = 'PDI.04.03'
WHERE `codigo_sec` = 'ARCHIVOS_MEDIATICOS_BASE';

UPDATE `evalcom_secciones`
SET `codigo_proceso` = 'PDI.04.03',
    `serial_esc` = (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5')
WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE';

UPDATE `evalcom_secciones`
SET `codigo_proceso` = 'PDI.04.05',
    `serial_esc` = (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5')
WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE';

INSERT IGNORE INTO `evalcom_seccion_procesos` (`serial_sec`, `codigo_proceso`, `orden_relacion`)
VALUES
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'PDI.04.01', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'MAPEO_PUBLICOS_BASE'), 'PDI.04.01', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'PDI.04.01', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'AUDITORIA_HERRAMIENTAS_BASE'), 'PDI.04.02', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_MEDIATICOS_BASE'), 'PDI.04.03', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'PDI.04.03', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'PDI.04.04', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'PDI.04.05', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'PDI.04.06', 2);

INSERT IGNORE INTO `evalcom_preguntas` (`serial_sec`, `codigo_pre`, `enunciado_pre`, `tipo_respuesta`, `orden_pre`)
VALUES
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_01', '¿En sus palabras, cómo describiría lo que hace la institución?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_02', 'Según usted, ¿cuáles son los principales valores que orientan el trabajo de la institución?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_03', 'En su trabajo, ¿se involucra en la formulación o difusión de mensajes sociales, educativos o políticos?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_04', '¿Cuáles son los mensajes principales o eslóganes específicos elaborados o difundidos desde su programa o equipo?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_05', 'Según usted, ¿cómo se relacionan estos mensajes con las prioridades institucionales?', 'TEXTO', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_01', '¿Sus proyectos o tareas involucran en alguna medida campañas de comunicación o componentes comunicacionales como publicaciones, afiches o eventos públicos?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_02', '¿Coordina estas actividades o productos con la persona o las personas responsables de la comunicación en la institución?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_03', '¿Cuál es su rol en la definición, elaboración o ejecución de estas actividades o productos?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_04', '¿Participa en alguno de los siguientes productos o actividades?', 'MULTIPLE_OPCION', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_05', '¿A qué público suele dirigir sus mensajes de manera prioritaria?', 'MULTIPLE_OPCION', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_06', '¿Qué normativas institucionales conoce usted que se aplican a las comunicaciones?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_07', '¿Qué nivel de conocimiento considera tener respecto de las actividades llevadas a cabo por las otras áreas de la institución?', 'TEXTO', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_08', '¿Tienen reuniones de equipo periódicas en su equipo o área? ¿Con qué frecuencia?', 'TEXTO', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_09', '¿Tienen reuniones institucionales periódicas? ¿Con qué frecuencia?', 'TEXTO', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_10', '¿Existen espacios informales para intercambiar, como almuerzos, cumpleaños o actividades de consolidación de equipo? ¿Cuáles?', 'TEXTO', 10),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_11', '¿Cómo se asegura la inclusión de un enfoque de género y de derechos en sus comunicaciones?', 'TEXTO', 11),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_12', '¿Considera que las comunicaciones responden a las necesidades específicas de los públicos objetivos? ¿Cómo?', 'TEXTO', 12),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_01', '¿Qué dificultades o desafíos identifica en relación con las comunicaciones en su trabajo, su área o la institución en general?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_02', '¿Qué fortalezas identifica en su equipo para las comunicaciones?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_03', '¿Puede identificar cambios o desafíos en el contexto externo que impactan la comunicación interna o externa de su institución?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_04', 'Si pudiera profundizar alguna dimensión técnica del trabajo en comunicación, ¿cuál sería y qué le gustaría aprender?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'CLIMA_INSTITUCIONAL'), 'CI_01', '¿En qué actividades se involucró usted con la institución?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'CLIMA_INSTITUCIONAL'), 'CI_02', '¿Cómo se enteró de que existía esta actividad o servicio y por qué medio lo vio o escuchó?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'CLIMA_INSTITUCIONAL'), 'CI_03', 'Generalmente, ¿cómo se comunica con la institución y su personal?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_01', 'En sus palabras, ¿cómo describiría lo que hace la institución de manera principal?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_02', '¿Qué le parece lo más característico de su trabajo con la institución o de sus servicios?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_03', 'Según su opinión, ¿qué aspectos del trabajo de la institución sería interesante resaltar y por qué?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_04', '¿Puede compartir un lema que según usted representa la institución?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_05', 'Para usted, el trabajo que hace la institución es...', 'UNICA_OPCION', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_06', '¿Por qué?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_07', '¿Con qué otra institución que conoce la podría comparar en materia de servicios y apoyo que brinda?', 'TEXTO', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_08', '¿Cuáles cree que son los principales aspectos positivos o fortalezas de la institución?', 'TEXTO', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_09', '¿Cuáles cree que son los principales desafíos de la institución?', 'TEXTO', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_01', '¿Qué le gusta de su relación con el personal de la institución?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_02', '¿Qué mejoraría?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_03', '¿Diría que la institución escucha a las personas con quienes colabora y a sus organizaciones?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_04', '¿Considera que la institución les informa regularmente de los avances, impactos o decisiones relacionadas con las actividades y con la institución?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_05', '¿Qué imagen difunde la institución acerca de las y los estudiantes?', 'TEXTO', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_06', '¿Considera que las comunicaciones de la institución responden a las necesidades específicas de sus públicos objetivos? ¿Cómo?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_01', '¿Le gustaría estar más al tanto de las actividades de la institución? ¿Por qué?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_02', '¿Qué formas o medios de comunicación serían los más eficaces para que tenga conocimiento del trabajo que realiza la institución?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_03', '¿Cómo quisiera participar en el trabajo que realiza la institución en los próximos tres años y por qué?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_04', '¿Qué productos de comunicación de la institución recuerda haber visto o escuchado últimamente?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_05', 'Evalúa usted su calidad como:', 'UNICA_OPCION', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_06', '¿Cuál es el recuerdo principal o el mensaje que le ha dejado el producto de comunicación?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_07', '¿Ha recomendado la institución alguna vez a alguien? ¿Lo haría de nuevo, para qué apoyo o servicio y por qué?', 'TEXTO', 7);

UPDATE `evalcom_preguntas`
SET `serial_esc` = (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5')
WHERE `codigo_pre` = 'PE_05';

UPDATE `evalcom_preguntas`
SET `serial_esc` = (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5')
WHERE `codigo_pre` = 'CP_05';

INSERT IGNORE INTO `evalcom_preguntas` (`serial_sec`, `codigo_pre`, `enunciado_pre`, `tipo_respuesta`, `serial_esc`, `permite_observacion`, `orden_pre`)
VALUES
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_01', '¿Existe un organigrama organizacional que detalla las responsabilidades y está disponible para consulta?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_02', '¿Cómo se toman las decisiones?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_03', '¿Quién participa en los procesos de toma de decisión?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_04', '¿Qué mecanismos permiten esta participación?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_05', '¿Cómo está dividida la organización en departamentos, equipos de trabajo o programas?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_06', '¿Cómo fluye la información entre las áreas y entre las personas en la organización?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_07', '¿Los miembros del equipo están informados de los avances en proyectos o actividades institucionales de manera regular?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_08', '¿Qué herramientas o espacios de comunicación son utilizados para actualizaciones y comunicación interna?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_09', '¿Faltan algunas herramientas o sería posible optimizar las que ya están vigentes?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_10', '¿La institución cuenta con una o un responsable de comunicación?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 10),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_11', '¿Cuáles son las funciones actuales de la persona o las personas responsables de la comunicación y si responden a las necesidades?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 11),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_12', '¿Qué estrategias recomiendan para adecuar más estas funciones a las necesidades?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 12),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_01', '¿Cuenta con un plan estratégico de comunicación?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_02', '¿Toma en cuenta las relaciones con los medios de comunicación y la comunicación digital?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_03', '¿Toma este plan en cuenta las necesidades específicas de cada público objetivo?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_04', '¿Hay metas específicas o indicadores de comunicación en la planificación anual?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_01', '¿Cuál es el proyecto básico de la institución?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_02', '¿Su misión y visión están bien definidas, son entendibles y reconocidas por quienes integran la institución?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_03', '¿Cuáles son los valores de la organización y si reflejan bien su identidad y cultura organizacional?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_04', '¿Se entiende de manera clara sus servicios y especialidades?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_05', '¿El equipo conoce la misión y visión de la organización?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_06', '¿La organización proyecta una marca institucional definida y clara?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_07', '¿Cómo están representados los varones y cómo las mujeres en el uso de imágenes y mensajes de la organización?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_08', '¿Es posible identificar de manera clara y rápida la identidad de la organización en sus productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_09', '¿Cuáles son los mensajes emitidos por la institución y si son mensajes inclusivos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_01', '¿Está utilizando su logo de manera uniforme en todos los productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_02', '¿Se aprecia consistencia en la manera de presentarse, como colores, imágenes utilizadas y tipos de fotos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_03', '¿Es posible reconocer rápidamente la identidad de la institución en los productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_04', '¿Es posible reconocer rápidamente la identidad de la Compañía de Jesús en los productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_05', '¿Se ha incorporado un lenguaje inclusivo con enfoque de derechos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_06', '¿Los contenidos son pertinentes para sus públicos objetivos y están desarrollados de manera clara?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_07', '¿Los contenidos reflejan la espiritualidad y valores ignacianos como servicio, justicia, interioridad y liderazgo?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_08', '¿Los contenidos muestran excelencia académica y vida en el aula, y promueven metodologías activas, creatividad, tecnología o procesos formativos relevantes?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_09', '¿El producto está alineado con los ejes de la Red Jesuita de Educación y el proyecto educativo institucional?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_10', '¿El producto genera un impacto positivo en la comunidad educativa?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 10),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_11', '¿Se cumple con la protección de datos y autorizaciones de imagen?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 11);

INSERT IGNORE INTO `evalcom_pregunta_opciones` (`serial_pre`, `codigo_opc`, `etiqueta_opc`, `orden_opc`)
VALUES
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'PUBLICACIONES', 'Publicaciones', 1),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'MESAS_INCIDENCIA', 'Trabajo en mesas de incidencia', 2),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'CONFERENCIAS_PRENSA', 'Conferencias de prensa', 3),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'NOTAS_PRENSA', 'Notas de prensa', 4),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'EVENTOS_PUBLICOS', 'Eventos públicos', 5),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'INTERNET', 'Internet y redes sociales', 6),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'CAMPANAS_SOCIALES', 'Campañas sociales', 7),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'DOCUMENTALES_VIDEOS', 'Documentales y videos', 8),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'PADRES_MADRES', 'Padres y madres de familia', 1),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'JOVENES', 'Jóvenes', 2),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'NINAS_NINOS', 'Niñas y niños', 3),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'POBLACIONES_INDIGENAS', 'Poblaciones indígenas', 4),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'EMPRESAS', 'Empresas', 5),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'ONG', 'ONG', 6),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'OTRAS', 'Otras', 7);
