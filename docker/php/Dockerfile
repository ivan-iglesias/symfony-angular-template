FROM php:8.5-fpm-alpine

# Argumentos para recibir el ID del usuario de tu máquina local
ARG USER_ID=1000
ARG GROUP_ID=1000

# Instalación de dependencias del sistema
# - shadow nos permite usar usermod/groupmod.
# - $PHPIZE_DEPS temporalmente para poder compilar extensiones de PECL
RUN apk add --no-cache \
    icu-dev \
    libpq-dev \
    git \
    unzip \
    tzdata \
    shadow \
    $PHPIZE_DEPS

# Configuramos la zona horaria a nivel de sistema y PHP
ENV TZ=Europe/Madrid
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN echo "date.timezone = Europe/Madrid" > /usr/local/etc/php/conf.d/00-docker-php-ext-timezone.ini \
    && chmod 644 /usr/local/etc/php/conf.d/00-docker-php-ext-timezone.ini

# Instalamos extensiones de PHP
RUN docker-php-ext-install intl pdo pdo_pgsql

# Instalamos y habilitamos la extensión de Redis vía PECL
RUN pecl install redis && docker-php-ext-enable redis

# Creamos un usuario con el mismo ID que tu usuario local
# Esto evita que los archivos creados pertenezcan a root
RUN addgroup -g ${GROUP_ID} devuser && \
    adduser -D -u ${USER_ID} -G devuser devuser

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuramos el directorio de trabajo
WORKDIR /var/www/html

# Cambiamos al usuario que acabamos de crear
USER devuser
