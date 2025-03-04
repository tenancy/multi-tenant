# Dockerfile
FROM php:8.4-cli

# Installiere System-Abhängigkeiten
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-install zip pdo_mysql

# Composer installieren
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis setzen
WORKDIR /var/www/html

# Projektdateien kopieren (optional; wenn du direkt via Volume mountest, ist dies evtl. nicht notwendig)
# COPY . /var/www/html

# Pakete installieren, Cache reduzieren
RUN composer global require "laravel/installer"

# Pfad zu Composer bin hinzufügen
ENV PATH="/root/.composer/vendor/bin:${PATH}"

# Standardkommando: Zeigt nur PHP-Version an (placeholder)
CMD ["php", "-v"]
