-- Active: 1747091012288@@127.0.0.1@3306@test
-- ESTABLECER FECHA DE PUBLICACION
DROP TRIGGER set_fecha_publicacion;
CREATE DEFINER=`root`@`localhost` TRIGGER `set_fecha_publicacion` BEFORE INSERT ON `detalle_publicacion` FOR EACH ROW 
BEGIN
UPDATE articulo
	SET articulo.fecha_publicacion = DATE_ADD(NEW.fecha_subida, INTERVAL 7 DAY)
	WHERE articulo.id_articulo = NEW.id_articulo;
END;

-- MODIFICAR FECHA DE SUBIDA AL ACTUALIZAR REGISTRO
DROP TRIGGER set_fecha_subida;
CREATE DEFINER=`root`@`localhost` TRIGGER `set_fecha_subida` BEFORE UPDATE ON `detalle_publicacion` FOR EACH ROW SET NEW.fecha_subida = CURDATE();