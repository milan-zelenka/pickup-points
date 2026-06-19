# Pickup Points Importer

Console app that imports carrier pickup points into MySQL. Currently supports **Balíkovna** (~20 MB XML feed).

## Requirements

- Docker + Docker Compose

## Setup

```bash
make up        # start containers (php, mysql, adminer)
make install   # copy .env, install deps, run migrations
```

## Import

```bash
make import
```

Streams the Balíkovna XML feed and upserts all pickup points into the database. Points that were present in a previous run but are missing from the current feed are automatically marked as `temporarily_unavailable`.

## Check the data

Adminer is available at:

```
http://localhost:8080/?server=mysql&username=admin&db=pickup_points
```

| Field    | Value           |
|----------|-----------------|
| Server   | `mysql`         |
| Username | `admin`         |
| Password | `admin`         |
| Database | `pickup_points` |
