-- ## 1) Nuevas entradas para detalle_publicacion
-- (para artículos 4 al 8, usando distintos autores y fechas)
INSERT INTO detalle_publicacion (rut_miembro, id_articulo, fecha_subida, es_contacto) VALUES
  ('80794585-6', 4, '2024-05-08', 0),  -- Miguela Cañas Sastre publica el artículo 4
  ('2747174-7', 5, '2024-05-16', 1),  -- Edu Pintor Rico, es contacto, artículo 5
  ('36538940-6', 6, '2024-05-18', 0), -- Paulino Doménech Vélez, artículo 6
  ('85597492-1', 7, '2024-05-20', 1), -- Guadalupe Iniesta Acedo, es contacto, artículo 7
  ('66120111-2', 8, '2024-05-22', 0); -- Rosaura Mayte Vélez Olivares, artículo 8

-- ## 2) Ejemplos de revisiones (sempre +8 días después de la subida)
INSERT INTO detalle_revision (
    rut_miembro, id_articulo, fecha_revision,
    calidad_tecnica, originalidad, valoracion_global,
    argumento_valoracion, comentario_revision
) VALUES
  -- Revisión del artículo 1 (subido 12/05 → revisado 21/05)
  ('4222702-7', 1, '2024-05-21', 8, 7, 8,
     'Estructura clara y bien fundamentada.', 'Profundizar en la sección de resultados.'),
  ('80794585-6', 1, '2024-05-21', 7, 8, 7,
     'Muy original, pero faltan referencias recientes.', ''),
  -- Revisión del artículo 2 (subido 10/05 → revisado 18/05)
  ('7585982-7', 2, '2024-05-18', 9, 8, 9,
     'Excelente revisión técnica y análisis de datos.', ''),
  ('4222702-7', 2, '2024-05-18', 8, 7, 8,
     'Buena metodología, pero la discusión es breve.', 'Agregar comparaciones con otros estudios.'),
  -- Revisión del artículo 3 (subido 14/05 → revisado 22/05)
  ('80794585-6', 3, '2024-05-22', 7, 7, 8,
     'Redacción fluida, pero falta discusión de casos de uso.', ''),
  ('4222702-7', 3, '2024-05-22', 8, 8, 9,
     'Muy completo y bien organizado.', 'Revisar formato de tablas para mayor claridad.');

-- ## 3) Marcar como “revisado” los artículos que ya tienen al menos una revisión
UPDATE articulo
  SET revisado = 1
 WHERE id_articulo IN (1,2,3);



--- maximo

-- 1) Insertar el nuevo artículo (revisado = 0)
INSERT INTO articulo (
  nombre_articulo,
  resumen_articulo,
  fecha_publicacion,
  revisado
) VALUES (
  'Impacto de la robótica en la educación',
  'Este artículo analiza cómo la robótica está transformando los métodos de enseñanza y aprendizaje en la educación moderna.',
  CURDATE(),
  0
);

-- 2) Capturar el ID recién generado
SET @nuevo_id = LAST_INSERT_ID();

-- 3) Asociar el artículo al autor (Maxi Hierro) en detalle_publicacion
INSERT INTO detalle_publicacion (
  rut_miembro,
  id_articulo,
  fecha_subida,
  es_contacto
) VALUES (
  '74819831-7',
  @nuevo_id,
  CURDATE(),
  1
);

-- 4) Opcional: asignar uno o varios tópicos al artículo
-- Descomenta y ajusta los IDs de tópico según necesites:
INSERT INTO detalle_articulo (id_articulo, id_topico) VALUES
(@nuevo_id, 3),
(@nuevo_id, 7);











 SELECT * FROM miembro