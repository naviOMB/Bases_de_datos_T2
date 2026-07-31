# Tarea 2 - Bases de Datos

## Descripción General

Para la implementación de la base de datos se utilizó **XAMPP**, el cual provee los servicios de **Apache** y **MySQL**.  
El proyecto contempla la gestión de miembros, artículos y revisiones, con un enfoque en el flujo de publicación, revisión y administración dentro de una organización académica.

---

## Implementación de la Base de Datos

- Tanto **autores** como **revisores** son miembros de la organización.  
  Los campos booleanos `es_autor` y `es_revisor` determinan su rol.
- Cada miembro posee un identificador único de tipo **RUT**, solicitado al momento del registro.
- La base de datos debe llamarse **`test`** para garantizar el correcto funcionamiento.
- Se recomienda crear una carpeta para el proyecto en la ruta: C:\xampp\htdocs (o la equivalente en el sistema utilizado).

---

## Funcionamiento General

### Casos de Uso

#### **5.1 - Subir artículo**
- Cuando un **autor** sube un artículo, este se marca con el estado **"Enviado"**.  
- Se contemplan las **pruebas de aceptación** indicadas en el enunciado de la tarea.

#### **5.2 - Acceso al artículo**
- Desde el envío, los **autores** disponen de **una semana** para modificar su artículo.  
- Pasado ese tiempo, el artículo cambia a estado **"En revisión"**.  
- Se seleccionan **3 revisores automáticamente** para evaluarlo.  
- Una vez que los tres revisores completan su evaluación, el artículo pasa a estado **"Revisado"**, quedando disponibles las observaciones.

#### **5.3 - Gestión de Revisores**
- El **Jefe de Comité** (miembro con `es_admin = 1`) puede:
- Visualizar todos los revisores de la organización.  
- Registrar nuevos revisores.  
- Promover autores al rol de revisor o remover dicho rol.
- Al eliminar un revisor:
- Se mantiene su posibilidad de ser autor.
- Se eliminan en cascada sus revisiones y especialidades asociadas.

#### **5.4 - Asignación de artículos a revisores**
- Los **3 revisores** se asignan automáticamente al momento de la publicación del artículo.  
- El **Jefe de Comité** puede **reasignar revisores manualmente**, priorizando especialización en al menos un tópico del artículo.

---

## Cuentas de Usuario

- Autores, revisores y el **Jefe de Comité** comparten un **sistema de cuentas único**.  
- El usuario puede elegir su **modo de operación** (autor, revisor o administrador) desde los ajustes.  
- Es posible **editar datos personales**, manteniendo la **unicidad de `userid` y `nombre`** en la base de datos.

---

## Búsqueda y Filtros

- Autores y revisores pueden buscar artículos relacionados con ellos.  
- El sistema incluye una **barra de búsqueda** y **filtros avanzados** para el administrador, de acuerdo al enunciado de la tarea.

---

## Operaciones CRUD

- Se implementan operaciones **Crear, Leer, Actualizar y Eliminar (CRUD)** para las principales entidades:
- **Miembro** (autor o revisor)
- **Artículo**
- Consideraciones importantes:
- Un autor **solo puede eliminar un artículo** si es **el único autor** del mismo.  
- Si existen múltiples autores, la eliminación no está permitida.

---

*UTFSM - Ingeniería Civil Telemática*
