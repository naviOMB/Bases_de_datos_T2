-- Active: 1747091012288@@127.0.0.1@3306@phpmyadmin
CREATE DATABASE test;
USE test;


-- articulo
create table articulo (
    id_articulo int auto_increment primary key,
    nombre_articulo varchar(100) not null,
    resumen_articulo varchar(200) not null,
	fecha_publicacion date DEFAULT null,
    revisado tinyint(1) default 0 not null
);

-- miembro
create table miembro (
    rut_miembro char(10) primary key,
    nombre_miembro varchar(100) unique not null,
    email_miembro varchar(100) unique not null,
    userid_miembro varchar(50) unique not null,
    password_miembro varchar(50) not null,
    es_autor tinyint(1) not null,
    es_revisor tinyint(1) not null,
    es_admin tinyint(1) not null
);

-- topico
create table topico (
    id_topico int auto_increment primary key,
    nombre_topico varchar(100) not null
);

-- detalle_publicacion
create table detalle_publicacion (
    rut_miembro char(10) not null,
    id_articulo int not null,
    es_contacto tinyint(1) default 0,
	fecha_subida date DEFAULT NULL,
    primary key (id_articulo, rut_miembro),
    foreign key (rut_miembro) references miembro(rut_miembro),
    foreign key (id_articulo) references articulo(id_articulo)
);

-- detalle_topico
create table detalle_topico (
    rut_miembro char(10) not null,
    id_topico int not null,
    primary key (id_topico, rut_miembro),
    foreign key (rut_miembro) references miembro(rut_miembro),
    foreign key (id_topico) references topico(id_topico)
);

-- detalle_articulo
create table detalle_articulo (
    id_topico int not null,
    id_articulo int not null,
    primary key (id_articulo, id_topico),
    foreign key (id_topico) references topico(id_topico),
    foreign key (id_articulo) references articulo(id_articulo)
);

-- detalle_revision
create table detalle_revision (
    rut_miembro char(10) not null,
    id_articulo int not null,
    fecha_revision date null default null,
    calidad_tecnica int,
    originalidad int,
    valoracion_global int,
    argumento_valoracion varchar(500),
    comentario_revision varchar(100),
    primary key (id_articulo, rut_miembro),
    foreign key (rut_miembro) references miembro(rut_miembro),
    foreign key (id_articulo) references articulo(id_articulo)
);
