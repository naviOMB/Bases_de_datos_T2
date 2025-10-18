-- Active: 1747091012288@@127.0.0.1@3306@test
--OBTENER TODOS LOS ARTICULOS
DROP PROCEDURE obtener_articulos_all;
CREATE DEFINER=`root`@`localhost` PROCEDURE `obtener_articulos_all`(IN nombre_autor CHAR(10))
BEGIN
	SELECT * FROM todos_los_articulos;
END

-- OBTENER TODOS LOS ARTICULOS DE CIERTO AUTOR
DROP PROCEDURE obtener_articulos_all_autor;
CREATE DEFINER=`root`@`localhost` PROCEDURE `obtener_articulos_all_autor`(IN nombre_autor CHAR(10))
BEGIN
	SELECT * FROM todos_los_articulos
	WHERE Autores LIKE CONCAT('%', nombre_autor, '%');
END

--OBTENER ARTICULOS REVISADOS EN LOS QUE HAYA PARTICIPADO CIERTO AUTOR
DROP PROCEDURE articulos_revisados_autor;
CREATE DEFINER=`root`@`localhost` PROCEDURE `articulos_revisados_autor`(IN nombre_autor CHAR(10))
BEGIN
	SELECT * FROM mis_articulos_revisados
	WHERE Autores LIKE CONCAT('%', nombre_autor, '%');
END

--AGREGAR ARTICULO A BD (NOMBRE,RESUMEN)
DROP PROCEDURE agregar_articulo;
CREATE DEFINER=`root`@`localhost` PROCEDURE `agregar_articulo`(IN nombre VARCHAR(100),IN resumen VARCHAR(200))
BEGIN
	INSERT INTO articulo (nombre_articulo,resumen_articulo)
	VALUES (nombre,resumen);
END

--AGREGAR DETALLE_PUBLICACION A BD (RUT,ID_ARTICULO,FECHA_SUBIDA,ES_CONTACTO) 
--Para agregar a la bd el registro se debe conocer id_articulo de antemano (usar last_insert_id() despues de agregar articulo)
DROP PROCEDURE agregar_detalle_publicacion;
CREATE DEFINER=`root`@`localhost` PROCEDURE `agregar_detalle_publicacion`(IN rut_miembro char(10),IN id_articulo INT,IN contacto TINYINT)
BEGIN
	INSERT INTO detalle_publicacion(rut_miembro,id_articulo,fecha_subida,es_contacto)
	VALUES (rut_miembro,id_articulo,CURDATE(),contacto);
END

DELIMITER $$

--ASIGNAR 3 REVISORES AL ARTICULO RECIEN SUBIDO
DROP PROCEDURE asignar_revisores;
CREATE DEFINER=`root`@`localhost` PROCEDURE `asignar_revisores`(IN id_articulo_param INT)
BEGIN
    INSERT INTO detalle_revision (rut_miembro, id_articulo)
    SELECT miembro.rut_miembro, id_articulo_param
    FROM miembro
    WHERE miembro.rut_miembro NOT IN (
        SELECT detalle_publicacion.rut_miembro
        FROM detalle_publicacion
        WHERE detalle_publicacion.id_articulo = id_articulo_param
    ) AND miembro.es_revisor = 1
    ORDER BY RAND()
    LIMIT 3;
END