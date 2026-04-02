-- =====================================================
-- SISTEMA DE EVALUACION DE COMUNICACION
-- Catalogos e instrumento base del diagnostico
-- =====================================================

INSERT IGNORE INTO `evalcom_procesos` (`codigo_proceso`, `nombre_proceso`, `descripcion_proceso`, `orden_proceso`)
VALUES
    ('PDI.04.01', 'Estrategia de comunicaciones', 'Planificacion, estructura y priorizacion de la comunicacion institucional.', 1),
    ('PDI.04.02', 'Administracion de medios y canales', 'Uso y optimizacion de medios, herramientas y canales institucionales.', 2),
    ('PDI.04.03', 'Gestion de imagen y reputacion institucional', 'Analisis de posicionamiento, reputacion y representacion publica.', 3),
    ('PDI.04.04', 'Administracion de la marca institucional', 'Uso de identidad visual, mensajes y consistencia institucional.', 4),
    ('PDI.04.05', 'Cobertura de eventos y relaciones publicas', 'Cobertura, vocerias y relacion con medios y actores externos.', 5),
    ('PDI.04.06', 'Gestion de contenidos', 'Pertinencia, claridad y alineacion de los contenidos institucionales.', 6);

INSERT IGNORE INTO `evalcom_escalas` (`codigo_esc`, `nombre_esc`, `descripcion_esc`)
VALUES
    ('DEBIL_EXCELENTE_5', 'Escala debil a excelente', 'Escala de 1 a 5 para auditorias y analisis internos.'),
    ('IMPORTANCIA_5', 'Escala de importancia', 'Escala de percepcion de importancia del trabajo institucional.'),
    ('CALIDAD_5', 'Escala de calidad', 'Escala para valorar la calidad de productos de comunicacion.');

INSERT IGNORE INTO `evalcom_escala_opciones` (`serial_esc`, `codigo_opc`, `valor_opc`, `etiqueta_opc`, `orden_opc`)
VALUES
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N1', 1.0000, 'Debil', 1),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N2', 2.0000, 'Nivel 2', 2),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N3', 3.0000, 'Bien', 3),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N4', 4.0000, 'Nivel 4', 4),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'N5', 5.0000, 'Excelente', 5),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'MUY_IMPORTANTE', 5.0000, 'Muy importante', 1),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'IMPORTANTE', 4.0000, 'Importante', 2),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'POCO_IMPORTANTE', 2.0000, 'Poco importante', 3),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'NADA_IMPORTANTE', 1.0000, 'Nada importante', 4),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5'), 'SIN_OPINION', NULL, 'No tengo opinion', 5),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'MUY_BAJA', 1.0000, 'Muy baja', 1),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'BAJA', 2.0000, 'Baja', 2),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'CORRECTA', 3.0000, 'Correcta', 3),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'BUENA', 4.0000, 'Buena', 4),
    ((SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5'), 'MUY_BUENA', 5.0000, 'Muy buena', 5);

INSERT IGNORE INTO `evalcom_instrumentos` (`codigo_ins`, `nombre_ins`, `descripcion_ins`, `tipo_ins`, `audiencia_ins`, `requiere_participante`, `orden_ins`)
VALUES
    ('PERSONAL_INTERNO', 'Encuesta o entrevista a directivos y personal', 'Instrumento para recoger percepciones internas sobre discurso, procesos y desafios de la comunicacion institucional.', 'ENTREVISTA', 'INTERNA', 'SI', 1),
    ('PUBLICOS_EXTERNOS', 'Encuesta o entrevista a organizaciones aliadas o publicos externos', 'Instrumento para recoger percepciones externas sobre clima institucional, imagen, relacion y participacion.', 'ENTREVISTA', 'EXTERNA', 'SI', 2),
    ('ESTRUCTURA_ORGANIZACIONAL', 'Estructura organizacional', 'Auditoria interna del proceso PDI.04.01.', 'AUDITORIA', 'INSTITUCIONAL', 'NO', 3),
    ('MAPEO_PUBLICOS', 'Mapeo y caracterizacion de publicos', 'Registro estructurado de publicos internos, externos y mixtos.', 'MAPEO', 'INSTITUCIONAL', 'NO', 4),
    ('COMUNICACION_ESTRATEGICA', 'Analisis de la comunicacion estrategica', 'Auditoria interna del plan y de la planificacion anual de comunicacion.', 'AUDITORIA', 'INSTITUCIONAL', 'NO', 5),
    ('AUDITORIA_HERRAMIENTAS', 'Auditoria de herramientas y espacios de comunicacion existentes', 'Inventario y analisis de plataformas, herramientas y espacios de comunicacion.', 'INVENTARIO', 'INSTITUCIONAL', 'NO', 6),
    ('ARCHIVOS_MEDIATICOS', 'Archivos mediaticos', 'Registro y analisis de apariciones en medios y canales digitales.', 'INVENTARIO', 'INSTITUCIONAL', 'NO', 7),
    ('IMAGEN_POSICIONAMIENTO', 'Analisis de la imagen y posicionamiento institucional', 'Auditoria de imagen, identidad institucional y administracion de marca.', 'AUDITORIA', 'INSTITUCIONAL', 'NO', 8),
    ('ARCHIVOS_INSTITUCIONALES', 'Archivos de la propia institucion', 'Registro y valoracion de publicaciones, videos y otros contenidos institucionales.', 'ANALISIS', 'INSTITUCIONAL', 'NO', 9);

INSERT IGNORE INTO `evalcom_secciones` (`serial_ins`, `codigo_sec`, `titulo_sec`, `descripcion_sec`, `tipo_sec`, `orden_sec`)
VALUES
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PERSONAL_INTERNO'), 'DISCURSO_INSTITUCIONAL', 'Discurso institucional', 'Preguntas abiertas para personal interno.', 'CUESTIONARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PERSONAL_INTERNO'), 'PROCESOS_COMUNICACIONALES', 'Vivencia de los procesos comunicacionales internos y externos', 'Preguntas sobre coordinacion, canales, inclusion y necesidades de los publicos.', 'CUESTIONARIO', 2),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PERSONAL_INTERNO'), 'FORTALEZAS_DESAFIOS', 'Fortalezas y desafios', 'Identificacion de fortalezas, desafios y necesidades de aprendizaje.', 'CUESTIONARIO', 3),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'CLIMA_INSTITUCIONAL', 'Clima institucional', 'Preguntas abiertas para participantes externos.', 'CUESTIONARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'PERCEPCION_INSTITUCION', 'Percepcion de la institucion', 'Percepcion externa de la imagen, el valor y la comparacion institucional.', 'CUESTIONARIO', 2),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'RELACIONES_PUBLICAS', 'Relaciones publicas', 'Percepcion de escucha, informacion y trato institucional.', 'CUESTIONARIO', 3),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'PUBLICOS_EXTERNOS'), 'COMUNICACION_PARTICIPACION', 'Comunicacion y participacion', 'Medios preferidos, participacion futura y calidad percibida de los productos.', 'CUESTIONARIO', 4),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'ESTRUCTURA_ORGANIZACIONAL'), 'ESTRUCTURA_ORG', 'Estructura organizacional', 'Auditoria del flujo de informacion, toma de decisiones y responsabilidades.', 'ESCALA', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'MAPEO_PUBLICOS'), 'MAPEO_PUBLICOS_BASE', 'Mapeo y caracterizacion de publicos', 'La captura detallada se registra en evalcom_publicos_mapeo.', 'MAPEO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'COMUNICACION_ESTRATEGICA'), 'ANALISIS_COM_ESTRATEGICA', 'Analisis de la comunicacion estrategica', 'Auditoria del plan, metas e indicadores de comunicacion.', 'ESCALA', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'AUDITORIA_HERRAMIENTAS'), 'AUDITORIA_HERRAMIENTAS_BASE', 'Auditoria de herramientas y espacios', 'La captura detallada se registra en evalcom_herramientas_auditoria.', 'INVENTARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'ARCHIVOS_MEDIATICOS'), 'ARCHIVOS_MEDIATICOS_BASE', 'Archivos mediaticos', 'La captura detallada se registra en evalcom_archivos_mediaticos.', 'INVENTARIO', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'IMAGEN_POSICIONAMIENTO'), 'IMAGEN_POSICIONAMIENTO_BASE', 'Analisis de la imagen y posicionamiento institucional', 'Auditoria de imagen, marca, identidad y mensajes.', 'ESCALA', 1),
    ((SELECT `serial_ins` FROM `evalcom_instrumentos` WHERE `codigo_ins` = 'ARCHIVOS_INSTITUCIONALES'), 'ARCHIVOS_INSTITUCIONALES_BASE', 'Archivos de la propia institucion', 'Registro de recursos institucionales y valoracion de cobertura, marca y contenido.', 'ESCALA', 1);

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
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_01', 'En sus palabras, como describiria lo que hace la institucion?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_02', 'Segun usted, cuales son los principales valores que orientan el trabajo de la institucion?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_03', 'En su trabajo, se involucra en la formulacion o difusion de mensajes sociales, educativos o politicos?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_04', 'Cuales son los mensajes principales o esloganes especificos elaborados o difundidos desde su programa o equipo?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'DISCURSO_INSTITUCIONAL'), 'DI_05', 'Segun usted, como se relacionan estos mensajes con las prioridades institucionales?', 'TEXTO', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_01', 'Sus proyectos o tareas involucran en alguna medida campanas de comunicacion o componentes comunicacionales como publicaciones, afiches o eventos publicos?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_02', 'Coordina estas actividades o productos con la persona o las personas responsables de la comunicacion en la institucion?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_03', 'Cual es su rol en la definicion, elaboracion o ejecucion de estas actividades o productos?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_04', 'Participa en alguno de los siguientes productos o actividades?', 'MULTIPLE_OPCION', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_05', 'A que publico suele dirigir sus mensajes de manera prioritaria?', 'MULTIPLE_OPCION', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_06', 'Que normativas institucionales conoce usted que se aplican a las comunicaciones?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_07', 'Que nivel de conocimiento considera tener respecto de las actividades llevadas a cabo por las otras areas de la institucion?', 'TEXTO', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_08', 'Tienen reuniones de equipo periodicas en su equipo o area? Con que frecuencia?', 'TEXTO', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_09', 'Tienen reuniones institucionales periodicas? Con que frecuencia?', 'TEXTO', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_10', 'Existen espacios informales para intercambiar como almuerzos, cumpleanos o actividades de consolidacion de equipo? Cuales?', 'TEXTO', 10),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_11', 'Como se asegura la inclusion de un enfoque de genero y de derechos en sus comunicaciones?', 'TEXTO', 11),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PROCESOS_COMUNICACIONALES'), 'PC_12', 'Considera que las comunicaciones responden a las necesidades especificas de los publicos objetivos? Como?', 'TEXTO', 12),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_01', 'Que dificultades o desafios identifica en relacion con las comunicaciones en su trabajo, su area o la institucion en general?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_02', 'Que fortalezas identifica en su equipo para las comunicaciones?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_03', 'Puede identificar cambios o desafios en el contexto externo que impactan la comunicacion interna o externa de su institucion?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'FORTALEZAS_DESAFIOS'), 'FD_04', 'Si pudiera profundizar alguna dimension tecnica del trabajo en comunicacion, cual seria y que le gustaria aprender?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'CLIMA_INSTITUCIONAL'), 'CI_01', 'En que actividades se involucro usted con la institucion?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'CLIMA_INSTITUCIONAL'), 'CI_02', 'Como se entero de que existia esta actividad o servicio y por que medio lo vio o escucho?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'CLIMA_INSTITUCIONAL'), 'CI_03', 'Generalmente, como se comunica con la institucion y su personal?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_01', 'En sus palabras, como describiria lo que hace la institucion de manera principal?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_02', 'Que le parece lo mas caracteristico de su trabajo con la institucion o de sus servicios?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_03', 'Segun su opinion, que aspectos del trabajo de la institucion seria interesante resaltar y por que?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_04', 'Puede compartir un lema que segun usted representa la institucion?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_05', 'Para usted, el trabajo que hace la institucion es...', 'UNICA_OPCION', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_06', 'Por que?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_07', 'Con que otra institucion que conoce la podria comparar en materia de servicios y apoyo que brinda?', 'TEXTO', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_08', 'Cuales cree que son los principales aspectos positivos o fortalezas de la institucion?', 'TEXTO', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'PERCEPCION_INSTITUCION'), 'PE_09', 'Cuales cree que son los principales desafios de la institucion?', 'TEXTO', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_01', 'Que le gusta de su relacion con el personal de la institucion?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_02', 'Que mejoraria?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_03', 'Diria que la institucion escucha a las personas con quienes colabora y a sus organizaciones?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_04', 'Considera que la institucion les informa regularmente de los avances, impactos o decisiones relacionadas con las actividades y con la institucion?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_05', 'Que imagen difunde la institucion acerca de las y los estudiantes?', 'TEXTO', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'RELACIONES_PUBLICAS'), 'RP_06', 'Considera que las comunicaciones de la institucion responden a las necesidades especificas de sus publicos objetivos? Como?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_01', 'Le gustaria estar mas al tanto de las actividades de la institucion? Por que?', 'TEXTO', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_02', 'Que formas o medios de comunicacion serian los mas eficaces para que tenga conocimiento del trabajo que realiza la institucion?', 'TEXTO', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_03', 'Como quisiera participar en el trabajo que realiza la institucion en los proximos tres anos y por que?', 'TEXTO', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_04', 'Que productos de comunicacion de la institucion recuerda haber visto o escuchado ultimamente?', 'TEXTO', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_05', 'Evalua usted su calidad como:', 'UNICA_OPCION', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_06', 'Cual es el recuerdo principal o el mensaje que le ha dejado el producto de comunicacion?', 'TEXTO', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'COMUNICACION_PARTICIPACION'), 'CP_07', 'Ha recomendado la institucion alguna vez a alguien? Lo haria de nuevo, para que apoyo o servicio y por que?', 'TEXTO', 7);

UPDATE `evalcom_preguntas`
SET `serial_esc` = (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'IMPORTANCIA_5')
WHERE `codigo_pre` = 'PE_05';

UPDATE `evalcom_preguntas`
SET `serial_esc` = (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'CALIDAD_5')
WHERE `codigo_pre` = 'CP_05';

INSERT IGNORE INTO `evalcom_preguntas` (`serial_sec`, `codigo_pre`, `enunciado_pre`, `tipo_respuesta`, `serial_esc`, `permite_observacion`, `orden_pre`)
VALUES
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_01', 'Existe un organigrama organizacional que detalla las responsabilidades y esta disponible para consulta?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_02', 'Como se toman las decisiones?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_03', 'Quien participa en los procesos de toma de decision?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_04', 'Que mecanismos permiten esta participacion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_05', 'Como esta dividida la organizacion en departamentos, equipos de trabajo o programas?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_06', 'Como fluye la informacion entre las areas y entre las personas en la organizacion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_07', 'Los miembros del equipo estan informados de los avances en proyectos o actividades institucionales de manera regular?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_08', 'Que herramientas o espacios de comunicacion son utilizados para actualizaciones y comunicacion interna?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_09', 'Faltan algunas herramientas o seria posible optimizar las que ya estan vigentes?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_10', 'La institucion cuenta con una o un responsable de comunicacion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 10),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_11', 'Cuales son las funciones actuales de la persona o las personas responsables de la comunicacion y si responden a las necesidades?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 11),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ESTRUCTURA_ORG'), 'EO_12', 'Que estrategias recomiendan para adecuar mas estas funciones a las necesidades?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 12),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_01', 'Cuenta con un plan estrategico de comunicacion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_02', 'Toma en cuenta las relaciones con los medios de comunicacion y la comunicacion digital?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_03', 'Toma este plan en cuenta las necesidades especificas de cada publico objetivo?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ANALISIS_COM_ESTRATEGICA'), 'CE_04', 'Hay metas especificas o indicadores de comunicacion en la planificacion anual?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_01', 'Cual es el proyecto basico de la institucion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_02', 'Su mision y vision estan bien definidas, son entendibles y reconocidas por quienes integran la institucion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_03', 'Cuales son los valores de la organizacion y si reflejan bien su identidad y cultura organizacional?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_04', 'Se entiende de manera clara sus servicios y especialidades?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_05', 'El equipo conoce la mision y vision de la organizacion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_06', 'La organizacion proyecta una marca institucional definida y clara?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_07', 'Como estan representados los varones y como las mujeres en el uso de imagenes y mensajes de la organizacion?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_08', 'Es posible identificar de manera clara y rapida la identidad de la organizacion en sus productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'IMAGEN_POSICIONAMIENTO_BASE'), 'IP_09', 'Cuales son los mensajes emitidos por la institucion y si son mensajes inclusivos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_01', 'Esta utilizando su logo de manera uniforme en todos los productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 1),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_02', 'Se aprecia consistencia en la manera de presentarse como colores, imagenes utilizadas y tipos de fotos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 2),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_03', 'Es posible reconocer rapidamente la identidad de la institucion en los productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 3),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_04', 'Es posible reconocer rapidamente la identidad de la Compania de Jesus en los productos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 4),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_05', 'Se ha incorporado un lenguaje inclusivo con enfoque de derechos?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 5),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_06', 'Los contenidos son pertinentes para sus publicos objetivos y estan desarrollados de manera clara?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 6),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_07', 'Los contenidos reflejan la espiritualidad y valores ignacianos como servicio, justicia, interioridad y liderazgo?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 7),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_08', 'Los contenidos muestran excelencia academica y vida en el aula, y promueven metodologias activas, creatividad, tecnologia o procesos formativos relevantes?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 8),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_09', 'El producto esta alineado con los ejes de la Red Jesuita de Educacion y el proyecto educativo institucional?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 9),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_10', 'El producto genera un impacto positivo en la comunidad educativa?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 10),
    ((SELECT `serial_sec` FROM `evalcom_secciones` WHERE `codigo_sec` = 'ARCHIVOS_INSTITUCIONALES_BASE'), 'AI_11', 'Se cumple con la proteccion de datos y autorizaciones de imagen?', 'NUMERICA_ESCALA', (SELECT `serial_esc` FROM `evalcom_escalas` WHERE `codigo_esc` = 'DEBIL_EXCELENTE_5'), 'SI', 11);

INSERT IGNORE INTO `evalcom_pregunta_opciones` (`serial_pre`, `codigo_opc`, `etiqueta_opc`, `orden_opc`)
VALUES
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'PUBLICACIONES', 'Publicaciones', 1),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'MESAS_INCIDENCIA', 'Trabajo en mesas de incidencia', 2),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'CONFERENCIAS_PRENSA', 'Conferencias de prensa', 3),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'NOTAS_PRENSA', 'Notas de prensa', 4),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'EVENTOS_PUBLICOS', 'Eventos publicos', 5),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'INTERNET', 'Internet y redes sociales', 6),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'CAMPANAS_SOCIALES', 'Campanas sociales', 7),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_04'), 'DOCUMENTALES_VIDEOS', 'Documentales y videos', 8),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'PADRES_MADRES', 'Padres y madres de familia', 1),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'JOVENES', 'Jovenes', 2),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'NINAS_NINOS', 'Ninas y ninos', 3),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'POBLACIONES_INDIGENAS', 'Poblaciones indigenas', 4),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'EMPRESAS', 'Empresas', 5),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'ONG', 'ONG', 6),
    ((SELECT `serial_pre` FROM `evalcom_preguntas` WHERE `codigo_pre` = 'PC_05'), 'OTRAS', 'Otras', 7);
