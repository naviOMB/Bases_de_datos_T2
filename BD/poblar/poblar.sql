-- Active: 1747091012288@@127.0.0.1@3306@test
-- Tabla topico
LOAD DATA LOCAL INFILE  'C:/xampp/htdocs/Tarea/BD/poblar/topico.csv'
INTO TABLE topico
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(id_topico, nombre_topico);

-- Tabla articulo  CREAR ARTICULOS MANUALMENTE. MEJOR
--LOAD DATA LOCAL INFILE  'C:/xampp/htdocs/Tarea/BD/poblar/articulo.csv'
--INTO TABLE articulo
--FIELDS TERMINATED BY ',' 
--ENCLOSED BY '"'
--LINES TERMINATED BY '\n'
--IGNORE 1 LINES
--(id_articulo,nombre_articulo,resumen_articulo,revisado);

-- Tabla detalle_articulo
LOAD DATA LOCAL INFILE  'C:/xampp/htdocs/Tarea/BD/poblar/detalle_articulo.csv'
INTO TABLE detalle_articulo
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(id_articulo, id_topico);

-- Tabla miembro
LOAD DATA LOCAL INFILE  'C:/xampp/htdocs/Tarea/BD/poblar/miembro.csv'
INTO TABLE miembro
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(rut_miembro,nombre_miembro,email_miembro,userid_miembro,password_miembro,es_autor,es_revisor,es_admin);

-- Tabla detalle_topico
LOAD DATA LOCAL INFILE  'C:/xampp/htdocs/Tarea/BD/poblar/detalle_topico.csv'
INTO TABLE detalle_topico
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(rut_miembro,id_topico);


-- Tabla detalle_publicacion
--LOAD DATA LOCAL INFILE  'C:/xampp/htdocs/BD2/poblar/detalle_publicacion.csv'
--INTO TABLE detalle_publicacion
--FIELDS TERMINATED BY ',' 
--ENCLOSED BY '"'
--LINES TERMINATED BY '\n'
--IGNORE 1 LINES
--(rut_miembro,id_articulo,fecha_subida,es_contacto);