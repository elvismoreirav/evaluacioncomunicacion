START TRANSACTION;

UPDATE `evalcom_procesos`
SET
    `nombre_proceso` = CASE `codigo_proceso`
        WHEN 'PDI.04.02' THEN 'Administración de medios y canales'
        WHEN 'PDI.04.03' THEN 'Gestión de imagen y reputación institucional'
        WHEN 'PDI.04.04' THEN 'Administración de la marca institucional'
        WHEN 'PDI.04.05' THEN 'Cobertura de eventos y relaciones públicas'
        WHEN 'PDI.04.06' THEN 'Gestión de contenidos'
        ELSE `nombre_proceso`
    END,
    `descripcion_proceso` = CASE `codigo_proceso`
        WHEN 'PDI.04.01' THEN 'Planificación, estructura y priorización de la comunicación institucional.'
        WHEN 'PDI.04.02' THEN 'Uso y optimización de medios, herramientas y canales institucionales.'
        WHEN 'PDI.04.03' THEN 'Análisis de posicionamiento, reputación y representación pública.'
        WHEN 'PDI.04.05' THEN 'Cobertura, vocerías y relación con medios y actores externos.'
        WHEN 'PDI.04.06' THEN 'Pertinencia, claridad y alineación de los contenidos institucionales.'
        ELSE `descripcion_proceso`
    END
WHERE `codigo_proceso` IN ('PDI.04.01', 'PDI.04.02', 'PDI.04.03', 'PDI.04.04', 'PDI.04.05', 'PDI.04.06');

UPDATE `evalcom_escalas`
SET
    `nombre_esc` = CASE `codigo_esc`
        WHEN 'DEBIL_EXCELENTE_5' THEN 'Escala débil a excelente'
        ELSE `nombre_esc`
    END,
    `descripcion_esc` = CASE `codigo_esc`
        WHEN 'DEBIL_EXCELENTE_5' THEN 'Escala de 1 a 5 para auditorías y análisis internos.'
        WHEN 'IMPORTANCIA_5' THEN 'Escala de percepción de importancia del trabajo institucional.'
        WHEN 'CALIDAD_5' THEN 'Escala para valorar la calidad de productos de comunicación.'
        ELSE `descripcion_esc`
    END
WHERE `codigo_esc` IN ('DEBIL_EXCELENTE_5', 'IMPORTANCIA_5', 'CALIDAD_5');

UPDATE `evalcom_escala_opciones` eo
INNER JOIN `evalcom_escalas` e ON e.`serial_esc` = eo.`serial_esc`
SET eo.`etiqueta_opc` = CASE eo.`codigo_opc`
    WHEN 'N1' THEN 'Débil'
    WHEN 'SIN_OPINION' THEN 'No tengo opinión'
    ELSE eo.`etiqueta_opc`
END
WHERE (e.`codigo_esc` = 'DEBIL_EXCELENTE_5' AND eo.`codigo_opc` = 'N1')
   OR (e.`codigo_esc` = 'IMPORTANCIA_5' AND eo.`codigo_opc` = 'SIN_OPINION');

UPDATE `evalcom_instrumentos`
SET
    `nombre_ins` = CASE `codigo_ins`
        WHEN 'PUBLICOS_EXTERNOS' THEN 'Encuesta o entrevista a organizaciones aliadas o públicos externos'
        WHEN 'COMUNICACION_ESTRATEGICA' THEN 'Análisis de la comunicación estratégica'
        WHEN 'AUDITORIA_HERRAMIENTAS' THEN 'Auditoría de herramientas y espacios de comunicación existentes'
        WHEN 'ARCHIVOS_MEDIATICOS' THEN 'Archivos mediáticos'
        WHEN 'IMAGEN_POSICIONAMIENTO' THEN 'Análisis de la imagen y posicionamiento institucional'
        WHEN 'ARCHIVOS_INSTITUCIONALES' THEN 'Archivos de la propia institución'
        ELSE `nombre_ins`
    END,
    `descripcion_ins` = CASE `codigo_ins`
        WHEN 'PERSONAL_INTERNO' THEN 'Instrumento para recoger percepciones internas sobre discurso, procesos y desafíos de la comunicación institucional.'
        WHEN 'PUBLICOS_EXTERNOS' THEN 'Instrumento para recoger percepciones externas sobre clima institucional, imagen, relación y participación.'
        WHEN 'ESTRUCTURA_ORGANIZACIONAL' THEN 'Auditoría interna del proceso PDI.04.01.'
        WHEN 'MAPEO_PUBLICOS' THEN 'Registro estructurado de públicos internos, externos y mixtos.'
        WHEN 'COMUNICACION_ESTRATEGICA' THEN 'Auditoría interna del plan y de la planificación anual de comunicación.'
        WHEN 'AUDITORIA_HERRAMIENTAS' THEN 'Inventario y análisis de plataformas, herramientas y espacios de comunicación.'
        WHEN 'ARCHIVOS_MEDIATICOS' THEN 'Registro y análisis de apariciones en medios y canales digitales.'
        WHEN 'IMAGEN_POSICIONAMIENTO' THEN 'Auditoría de imagen, identidad institucional y administración de marca.'
        WHEN 'ARCHIVOS_INSTITUCIONALES' THEN 'Registro y valoración de publicaciones, videos y otros contenidos institucionales.'
        ELSE `descripcion_ins`
    END
WHERE `codigo_ins` IN (
    'PERSONAL_INTERNO', 'PUBLICOS_EXTERNOS', 'ESTRUCTURA_ORGANIZACIONAL', 'MAPEO_PUBLICOS',
    'COMUNICACION_ESTRATEGICA', 'AUDITORIA_HERRAMIENTAS', 'ARCHIVOS_MEDIATICOS',
    'IMAGEN_POSICIONAMIENTO', 'ARCHIVOS_INSTITUCIONALES'
);

UPDATE `evalcom_secciones`
SET
    `titulo_sec` = CASE `codigo_sec`
        WHEN 'FORTALEZAS_DESAFIOS' THEN 'Fortalezas y desafíos'
        WHEN 'PERCEPCION_INSTITUCION' THEN 'Percepción de la institución'
        WHEN 'RELACIONES_PUBLICAS' THEN 'Relaciones públicas'
        WHEN 'COMUNICACION_PARTICIPACION' THEN 'Comunicación y participación'
        WHEN 'MAPEO_PUBLICOS_BASE' THEN 'Mapeo y caracterización de públicos'
        WHEN 'ANALISIS_COM_ESTRATEGICA' THEN 'Análisis de la comunicación estratégica'
        WHEN 'AUDITORIA_HERRAMIENTAS_BASE' THEN 'Auditoría de herramientas y espacios'
        WHEN 'ARCHIVOS_MEDIATICOS_BASE' THEN 'Archivos mediáticos'
        WHEN 'IMAGEN_POSICIONAMIENTO_BASE' THEN 'Análisis de la imagen y posicionamiento institucional'
        WHEN 'ARCHIVOS_INSTITUCIONALES_BASE' THEN 'Archivos de la propia institución'
        ELSE `titulo_sec`
    END,
    `descripcion_sec` = CASE `codigo_sec`
        WHEN 'PROCESOS_COMUNICACIONALES' THEN 'Preguntas sobre coordinación, canales, inclusión y necesidades de los públicos.'
        WHEN 'FORTALEZAS_DESAFIOS' THEN 'Identificación de fortalezas, desafíos y necesidades de aprendizaje.'
        WHEN 'PERCEPCION_INSTITUCION' THEN 'Percepción externa de la imagen, el valor y la comparación institucional.'
        WHEN 'RELACIONES_PUBLICAS' THEN 'Percepción de escucha, información y trato institucional.'
        WHEN 'COMUNICACION_PARTICIPACION' THEN 'Medios preferidos, participación futura y calidad percibida de los productos.'
        WHEN 'ESTRUCTURA_ORG' THEN 'Auditoría del flujo de información, toma de decisiones y responsabilidades.'
        WHEN 'ANALISIS_COM_ESTRATEGICA' THEN 'Auditoría del plan, metas e indicadores de comunicación.'
        WHEN 'IMAGEN_POSICIONAMIENTO_BASE' THEN 'Auditoría de imagen, marca, identidad y mensajes.'
        WHEN 'ARCHIVOS_INSTITUCIONALES_BASE' THEN 'Registro de recursos institucionales y valoración de cobertura, marca y contenido.'
        ELSE `descripcion_sec`
    END
WHERE `codigo_sec` IN (
    'PROCESOS_COMUNICACIONALES', 'FORTALEZAS_DESAFIOS', 'PERCEPCION_INSTITUCION',
    'RELACIONES_PUBLICAS', 'COMUNICACION_PARTICIPACION', 'ESTRUCTURA_ORG',
    'MAPEO_PUBLICOS_BASE', 'ANALISIS_COM_ESTRATEGICA', 'AUDITORIA_HERRAMIENTAS_BASE',
    'ARCHIVOS_MEDIATICOS_BASE', 'IMAGEN_POSICIONAMIENTO_BASE', 'ARCHIVOS_INSTITUCIONALES_BASE'
);

UPDATE `evalcom_preguntas`
SET `enunciado_pre` = CASE `codigo_pre`
    WHEN 'DI_01' THEN '¿En sus palabras, cómo describiría lo que hace la institución?'
    WHEN 'DI_02' THEN 'Según usted, ¿cuáles son los principales valores que orientan el trabajo de la institución?'
    WHEN 'DI_03' THEN 'En su trabajo, ¿se involucra en la formulación o difusión de mensajes sociales, educativos o políticos?'
    WHEN 'DI_04' THEN '¿Cuáles son los mensajes principales o eslóganes específicos elaborados o difundidos desde su programa o equipo?'
    WHEN 'DI_05' THEN 'Según usted, ¿cómo se relacionan estos mensajes con las prioridades institucionales?'
    WHEN 'PC_01' THEN '¿Sus proyectos o tareas involucran en alguna medida campañas de comunicación o componentes comunicacionales como publicaciones, afiches o eventos públicos?'
    WHEN 'PC_02' THEN '¿Coordina estas actividades o productos con la persona o las personas responsables de la comunicación en la institución?'
    WHEN 'PC_03' THEN '¿Cuál es su rol en la definición, elaboración o ejecución de estas actividades o productos?'
    WHEN 'PC_04' THEN '¿Participa en alguno de los siguientes productos o actividades?'
    WHEN 'PC_05' THEN '¿A qué público suele dirigir sus mensajes de manera prioritaria?'
    WHEN 'PC_06' THEN '¿Qué normativas institucionales conoce usted que se aplican a las comunicaciones?'
    WHEN 'PC_07' THEN '¿Qué nivel de conocimiento considera tener respecto de las actividades llevadas a cabo por las otras áreas de la institución?'
    WHEN 'PC_08' THEN '¿Tienen reuniones de equipo periódicas en su equipo o área? ¿Con qué frecuencia?'
    WHEN 'PC_09' THEN '¿Tienen reuniones institucionales periódicas? ¿Con qué frecuencia?'
    WHEN 'PC_10' THEN '¿Existen espacios informales para intercambiar, como almuerzos, cumpleaños o actividades de consolidación de equipo? ¿Cuáles?'
    WHEN 'PC_11' THEN '¿Cómo se asegura la inclusión de un enfoque de género y de derechos en sus comunicaciones?'
    WHEN 'PC_12' THEN '¿Considera que las comunicaciones responden a las necesidades específicas de los públicos objetivos? ¿Cómo?'
    WHEN 'FD_01' THEN '¿Qué dificultades o desafíos identifica en relación con las comunicaciones en su trabajo, su área o la institución en general?'
    WHEN 'FD_02' THEN '¿Qué fortalezas identifica en su equipo para las comunicaciones?'
    WHEN 'FD_03' THEN '¿Puede identificar cambios o desafíos en el contexto externo que impactan la comunicación interna o externa de su institución?'
    WHEN 'FD_04' THEN 'Si pudiera profundizar alguna dimensión técnica del trabajo en comunicación, ¿cuál sería y qué le gustaría aprender?'
    WHEN 'CI_01' THEN '¿En qué actividades se involucró usted con la institución?'
    WHEN 'CI_02' THEN '¿Cómo se enteró de que existía esta actividad o servicio y por qué medio lo vio o escuchó?'
    WHEN 'CI_03' THEN 'Generalmente, ¿cómo se comunica con la institución y su personal?'
    WHEN 'PE_01' THEN 'En sus palabras, ¿cómo describiría lo que hace la institución de manera principal?'
    WHEN 'PE_02' THEN '¿Qué le parece lo más característico de su trabajo con la institución o de sus servicios?'
    WHEN 'PE_03' THEN 'Según su opinión, ¿qué aspectos del trabajo de la institución sería interesante resaltar y por qué?'
    WHEN 'PE_04' THEN '¿Puede compartir un lema que según usted representa la institución?'
    WHEN 'PE_05' THEN 'Para usted, el trabajo que hace la institución es...'
    WHEN 'PE_06' THEN '¿Por qué?'
    WHEN 'PE_07' THEN '¿Con qué otra institución que conoce la podría comparar en materia de servicios y apoyo que brinda?'
    WHEN 'PE_08' THEN '¿Cuáles cree que son los principales aspectos positivos o fortalezas de la institución?'
    WHEN 'PE_09' THEN '¿Cuáles cree que son los principales desafíos de la institución?'
    WHEN 'RP_01' THEN '¿Qué le gusta de su relación con el personal de la institución?'
    WHEN 'RP_02' THEN '¿Qué mejoraría?'
    WHEN 'RP_03' THEN '¿Diría que la institución escucha a las personas con quienes colabora y a sus organizaciones?'
    WHEN 'RP_04' THEN '¿Considera que la institución les informa regularmente de los avances, impactos o decisiones relacionadas con las actividades y con la institución?'
    WHEN 'RP_05' THEN '¿Qué imagen difunde la institución acerca de las y los estudiantes?'
    WHEN 'RP_06' THEN '¿Considera que las comunicaciones de la institución responden a las necesidades específicas de sus públicos objetivos? ¿Cómo?'
    WHEN 'CP_01' THEN '¿Le gustaría estar más al tanto de las actividades de la institución? ¿Por qué?'
    WHEN 'CP_02' THEN '¿Qué formas o medios de comunicación serían los más eficaces para que tenga conocimiento del trabajo que realiza la institución?'
    WHEN 'CP_03' THEN '¿Cómo quisiera participar en el trabajo que realiza la institución en los próximos tres años y por qué?'
    WHEN 'CP_04' THEN '¿Qué productos de comunicación de la institución recuerda haber visto o escuchado últimamente?'
    WHEN 'CP_05' THEN 'Evalúa usted su calidad como:'
    WHEN 'CP_06' THEN '¿Cuál es el recuerdo principal o el mensaje que le ha dejado el producto de comunicación?'
    WHEN 'CP_07' THEN '¿Ha recomendado la institución alguna vez a alguien? ¿Lo haría de nuevo, para qué apoyo o servicio y por qué?'
    WHEN 'EO_01' THEN '¿Existe un organigrama organizacional que detalla las responsabilidades y está disponible para consulta?'
    WHEN 'EO_02' THEN '¿Cómo se toman las decisiones?'
    WHEN 'EO_03' THEN '¿Quién participa en los procesos de toma de decisión?'
    WHEN 'EO_04' THEN '¿Qué mecanismos permiten esta participación?'
    WHEN 'EO_05' THEN '¿Cómo está dividida la organización en departamentos, equipos de trabajo o programas?'
    WHEN 'EO_06' THEN '¿Cómo fluye la información entre las áreas y entre las personas en la organización?'
    WHEN 'EO_07' THEN '¿Los miembros del equipo están informados de los avances en proyectos o actividades institucionales de manera regular?'
    WHEN 'EO_08' THEN '¿Qué herramientas o espacios de comunicación son utilizados para actualizaciones y comunicación interna?'
    WHEN 'EO_09' THEN '¿Faltan algunas herramientas o sería posible optimizar las que ya están vigentes?'
    WHEN 'EO_10' THEN '¿La institución cuenta con una o un responsable de comunicación?'
    WHEN 'EO_11' THEN '¿Cuáles son las funciones actuales de la persona o las personas responsables de la comunicación y si responden a las necesidades?'
    WHEN 'EO_12' THEN '¿Qué estrategias recomiendan para adecuar más estas funciones a las necesidades?'
    WHEN 'CE_01' THEN '¿Cuenta con un plan estratégico de comunicación?'
    WHEN 'CE_02' THEN '¿Toma en cuenta las relaciones con los medios de comunicación y la comunicación digital?'
    WHEN 'CE_03' THEN '¿Toma este plan en cuenta las necesidades específicas de cada público objetivo?'
    WHEN 'CE_04' THEN '¿Hay metas específicas o indicadores de comunicación en la planificación anual?'
    WHEN 'IP_01' THEN '¿Cuál es el proyecto básico de la institución?'
    WHEN 'IP_02' THEN '¿Su misión y visión están bien definidas, son entendibles y reconocidas por quienes integran la institución?'
    WHEN 'IP_03' THEN '¿Cuáles son los valores de la organización y si reflejan bien su identidad y cultura organizacional?'
    WHEN 'IP_04' THEN '¿Se entiende de manera clara sus servicios y especialidades?'
    WHEN 'IP_05' THEN '¿El equipo conoce la misión y visión de la organización?'
    WHEN 'IP_06' THEN '¿La organización proyecta una marca institucional definida y clara?'
    WHEN 'IP_07' THEN '¿Cómo están representados los varones y cómo las mujeres en el uso de imágenes y mensajes de la organización?'
    WHEN 'IP_08' THEN '¿Es posible identificar de manera clara y rápida la identidad de la organización en sus productos?'
    WHEN 'IP_09' THEN '¿Cuáles son los mensajes emitidos por la institución y si son mensajes inclusivos?'
    WHEN 'AI_01' THEN '¿Está utilizando su logo de manera uniforme en todos los productos?'
    WHEN 'AI_02' THEN '¿Se aprecia consistencia en la manera de presentarse, como colores, imágenes utilizadas y tipos de fotos?'
    WHEN 'AI_03' THEN '¿Es posible reconocer rápidamente la identidad de la institución en los productos?'
    WHEN 'AI_04' THEN '¿Es posible reconocer rápidamente la identidad de la Compañía de Jesús en los productos?'
    WHEN 'AI_05' THEN '¿Se ha incorporado un lenguaje inclusivo con enfoque de derechos?'
    WHEN 'AI_06' THEN '¿Los contenidos son pertinentes para sus públicos objetivos y están desarrollados de manera clara?'
    WHEN 'AI_07' THEN '¿Los contenidos reflejan la espiritualidad y valores ignacianos como servicio, justicia, interioridad y liderazgo?'
    WHEN 'AI_08' THEN '¿Los contenidos muestran excelencia académica y vida en el aula, y promueven metodologías activas, creatividad, tecnología o procesos formativos relevantes?'
    WHEN 'AI_09' THEN '¿El producto está alineado con los ejes de la Red Jesuita de Educación y el proyecto educativo institucional?'
    WHEN 'AI_10' THEN '¿El producto genera un impacto positivo en la comunidad educativa?'
    WHEN 'AI_11' THEN '¿Se cumple con la protección de datos y autorizaciones de imagen?'
    ELSE `enunciado_pre`
END
WHERE `codigo_pre` IN (
    'DI_01', 'DI_02', 'DI_03', 'DI_04', 'DI_05', 'PC_01', 'PC_02', 'PC_03', 'PC_04',
    'PC_05', 'PC_06', 'PC_07', 'PC_08', 'PC_09', 'PC_10', 'PC_11', 'PC_12', 'FD_01',
    'FD_02', 'FD_03', 'FD_04', 'CI_01', 'CI_02', 'CI_03', 'PE_01', 'PE_02', 'PE_03',
    'PE_04', 'PE_05', 'PE_06', 'PE_07', 'PE_08', 'PE_09', 'RP_01', 'RP_02', 'RP_03', 'RP_04',
    'RP_05', 'RP_06', 'CP_01', 'CP_02', 'CP_03', 'CP_04', 'CP_05', 'CP_06', 'CP_07',
    'EO_01', 'EO_02', 'EO_03', 'EO_04', 'EO_05', 'EO_06', 'EO_07', 'EO_08', 'EO_09',
    'EO_10', 'EO_11', 'EO_12', 'CE_01', 'CE_02', 'CE_03', 'CE_04', 'IP_01', 'IP_02',
    'IP_03', 'IP_04', 'IP_05', 'IP_06', 'IP_07', 'IP_08', 'IP_09', 'AI_01', 'AI_02',
    'AI_03', 'AI_04', 'AI_05', 'AI_06', 'AI_07', 'AI_08', 'AI_09', 'AI_10', 'AI_11'
);

UPDATE `evalcom_pregunta_opciones` po
INNER JOIN `evalcom_preguntas` p ON p.`serial_pre` = po.`serial_pre`
SET po.`etiqueta_opc` = CASE po.`codigo_opc`
    WHEN 'EVENTOS_PUBLICOS' THEN 'Eventos públicos'
    WHEN 'CAMPANAS_SOCIALES' THEN 'Campañas sociales'
    WHEN 'JOVENES' THEN 'Jóvenes'
    WHEN 'NINAS_NINOS' THEN 'Niñas y niños'
    WHEN 'POBLACIONES_INDIGENAS' THEN 'Poblaciones indígenas'
    ELSE po.`etiqueta_opc`
END
WHERE (p.`codigo_pre` = 'PC_04' AND po.`codigo_opc` IN ('EVENTOS_PUBLICOS', 'CAMPANAS_SOCIALES'))
   OR (p.`codigo_pre` = 'PC_05' AND po.`codigo_opc` IN ('JOVENES', 'NINAS_NINOS', 'POBLACIONES_INDIGENAS'));

COMMIT;
