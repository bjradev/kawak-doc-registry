# KAWAK – Document Registry

CRUD de documentos con **código consecutivo por (Tipo, Proceso)** y recálculo seguro al editar. Arquitectura **MVC**, principios **SOLID** (SRP + DI), **Twig** para vistas, **Eloquent** como ORM y **CSRF/XSS** mitigado.

## Stack

- **PHP 8.1+**
- **Slim 4** (router/middleware)
- **Twig 3** (vistas, auto-escape)
- **Eloquent 11** (ORM standalone)
- **MySQL 8** (utf8mb4)
- **slim/csrf** (protección CSRF)
- **PHPUnit** / **PHPCS** / **PHPStan**

## Arquitectura

```
app/
  Controllers/
  Services/
  Repositories/
  Models/
  Middlewares/
  Views/              # Twig (presentación)
  Routes/web.php      # Definición de rutas
config/               # database.php
db/
  ddl/init.sql    # Tablas, FKs, índices
  dml/seed.sql    # Catálogos + vista VW_DOC_DOCUMENTO
public/
  index.php           # Front controller
tests/
  Unit/               # Unit tests (servicios)
  Feature/            # Feature tests (flujo)
```

## Instalación

1. **Sube MySQL**

   ```bash
   docker compose up -d
   ```

2. **Crear base (si hace falta)**

   ```bash
   docker exec -i kawak-mysql mysql -uroot -proot \
     -e "CREATE DATABASE IF NOT EXISTS kawak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. **Cargar DDL y DML**

   ```bash
   docker exec -i kawak-mysql mysql -ukawak -pkawak kawak < db/ddl/init.sql
   docker exec -i kawak-mysql mysql -ukawak -pkawak kawak < db/dml/seed.sql
   ```

4. **Dependencias PHP**

   ```bash
   composer install
   cp .env.example .env
   ```

   Edita `.env` si es necesario:

   ```
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=kawak
   DB_USER=kawak
   DB_PASS=kawak
   APP_DEBUG=true

   DB_USERNAME=kawak
   DB_PASSWORD=kawak
   ```

## Ejecución

```bash
composer start
```

Navega a: [http://localhost:8080](http://localhost:8080)

---

## Iniciar sesión

    Usuario: kawak
    Contraseña: kawak
    Puedes sobreescribir con variables de entorno:
        APP_USER, APP_PASSWORD

---

## Licencia

Este proyecto se publica bajo la licencia MIT.

Copyright (c) 2025 Bryant Reyes Consulta el archivo LICENSE para más detalles.
