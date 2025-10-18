--VIEW TODOS LOS ARTICULOS
DROP VIEW todos_los_articulos;

CREATE OR REPLACE VIEW todos_los_articulos AS
  SELECT
    a.id_articulo,
    a.nombre_articulo,
    a.resumen_articulo AS descripcion,
    GROUP_CONCAT(DISTINCT ma.nombre_miembro SEPARATOR ', ') AS Autores,
    GROUP_CONCAT(DISTINCT mr.nombre_miembro SEPARATOR ', ') AS Revisores,
    GROUP_CONCAT(DISTINCT t.nombre_topico SEPARATOR ', ')   AS Topicos,
    a.fecha_publicacion                                   AS Fecha_de_publicacion,
    a.revisado                                            AS Estado
  FROM articulo a
  LEFT JOIN detalle_publicacion dp ON a.id_articulo = dp.id_articulo
  LEFT JOIN miembro ma            ON dp.rut_miembro = ma.rut_miembro
  LEFT JOIN detalle_revision dr   ON a.id_articulo = dr.id_articulo
  LEFT JOIN miembro mr            ON dr.rut_miembro = mr.rut_miembro
  LEFT JOIN detalle_articulo da   ON a.id_articulo = da.id_articulo
  LEFT JOIN topico t              ON da.id_topico = t.id_topico
  GROUP BY a.id_articulo;


--VIEW ARTICULOS REVISADOS

DROP VIEW articulos_revisados

CREATE VIEW articulos_revisados AS 
SELECT 
articulo.nombre_articulo AS Titulo,
articulo.resumen_articulo AS Resumen, 
group_concat(distinct miembro_autores.nombre_miembro separator ', ') AS Autores,
group_concat(distinct miembro_revisores.nombre_miembro separator ', ') AS Revisores,
group_concat(distinct topico.nombre_topico separator ', ') AS Topicos 
FROM (((((articulo 
LEFT JOIN detalle_publicacion ON (articulo.id_articulo = detalle_publicacion.id_articulo)) 
LEFT JOIN miembro miembro_autores ON (detalle_publicacion.rut_miembro = miembro_autores.rut_miembro)) 
LEFT JOIN detalle_revision ON (articulo.id_articulo = detalle_revision.id_articulo)) 
LEFT JOIN miembro miembro_revisores ON (detalle_revision.rut_miembro = miembro_revisores.rut_miembro)) 
LEFT JOIN detalle_articulo ON (articulo.id_articulo = detalle_articulo.id_articulo)) 
LEFT JOIN topico ON (topico.id_topico = detalle_articulo.id_topico)
WHERE articulo.revisado=1
GROUP BY articulo.id_articulo;

--VIEW ARTICULOS REVISADOS

DROP VIEW articulos_por_revisar

CREATE VIEW articulos_por_revisar AS 
SELECT 
articulo.nombre_articulo AS Titulo,
articulo.resumen_articulo AS Resumen, 
group_concat(distinct miembro_autores.nombre_miembro separator ', ') AS Autores,
group_concat(distinct miembro_revisores.nombre_miembro separator ', ') AS Revisores,
group_concat(distinct topico.nombre_topico separator ', ') AS Topicos 
FROM (((((articulo 
LEFT JOIN detalle_publicacion ON (articulo.id_articulo = detalle_publicacion.id_articulo)) 
LEFT JOIN miembro miembro_autores ON (detalle_publicacion.rut_miembro = miembro_autores.rut_miembro)) 
LEFT JOIN detalle_revision ON (articulo.id_articulo = detalle_revision.id_articulo)) 
LEFT JOIN miembro miembro_revisores ON (detalle_revision.rut_miembro = miembro_revisores.rut_miembro)) 
LEFT JOIN detalle_articulo ON (articulo.id_articulo = detalle_articulo.id_articulo)) 
LEFT JOIN topico ON (topico.id_topico = detalle_articulo.id_topico)
WHERE articulo.revisado=0
GROUP BY articulo.id_articulo;