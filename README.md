# Plantilla proyectos

Plantilla para proyectos desarrollados con **Symfony** y **Angular**.

## 🛠️ Instalación

Pasos detallados para configurar el entorno local:

### Backend

```bash
# Configurar variables de entorno
cp .env.example .env

# Generar los contenedores y los levanta
make build
make up
```

Dentro del contenedor php, al que podemos acceder mediante `make goto` y posterirmente `php`, ejecutamos los siguientes comandos:

```bash
# Instalamos dependencias
composer i

# Generar los pares de claves RSA
php bin/console lexik:jwt:generate-keypair

# Crear base de datos
php bin/console doctrine:database:create

# Aplica la migración
php bin/console doctrine:migrations:migrate --no-interaction

# Carga fixtures
php bin/console doctrine:fixtures:load --no-interaction
```

### Frontend

```bash
cd frontend/
npm i
ng server
```

## 🚀 Inicio Rápido

### Backend

Lenvantar los contenedores del back

```bash
make up
```

### Frontend

Inicializar la web en modo desarrollador

```bash
cd frontend/
ng server
```

## 🌐 Accesos a los Servicios

Una vez levantados los entornos, estos son los puntos de acceso locales:

| Servicio            | URL / Host                                                       | Puerto | Descripción            |
| ------------------- | ---------------------------------------------------------------- | ------ | ---------------------- |
| **Frontend**        | [http://localhost:4200](http://localhost:4200)                   | `4200` | Angular                |
| **Front > Backend** | [http://localhost:4200/api/test](http://localhost:4200/api/test) | `4200` | Symfony API            |
| **Backend**         | [http://localhost:8080](http://localhost:8080)                   | `8080` | Symfony API            |
| **Swagger**         | [http://localhost:8080/api/doc](http://localhost:8080/api/doc)   | `8080` | Swagger                |
| **Mailpit**         | [http://localhost:8025](http://localhost:8025)                   | `8025` | Servidor SMTP local    |
| **Base de Datos**   | `localhost`                                                      | `5432` | PostgreSQL             |

>El proxy `proxy.conf.json` solo funciona en desarrollo con el servidor de Angular. En producción, será el propio servidor web quien haga de pasarela enviando las peticiones del frontend al backend.

