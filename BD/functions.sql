-- Active: 1747091012288@@127.0.0.1@3306@test
DROP FUNCTION atrasado;
CREATE DEFINER=`root`@`localhost` FUNCTION `atrasado`(fecha DATE, id_del_articulo INT) RETURNS tinyint(4)
    READS SQL DATA
BEGIN
    DECLARE fecha_de_publicacion DATE;

    SELECT fecha_publicacion INTO fecha_de_publicacion
    FROM articulo
    WHERE id_articulo = id_del_articulo
    LIMIT 1;

    IF fecha < fecha_de_publicacion THEN
        RETURN 0;
    ELSE
        RETURN 1;
    END IF;
END