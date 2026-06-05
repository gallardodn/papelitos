# Papelitos

## Requisitos

- Docker Desktop

## Levantar en local

```bash
docker compose up --build
```

Abrir:

- http://localhost:18080

## Persistencia de datos

- Carpeta local: `./data` (ignorada por git)
- Montaje dentro del contenedor: `/var/www/data`
