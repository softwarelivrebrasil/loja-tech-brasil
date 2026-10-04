# apps/backend — Laravel Octane (FrankenPHP), the long-running API process.
# Dev image: no app code or vendor baked in on purpose — docker-compose bind-mounts
# the whole repo over /var/www so packages/* (consumed via Composer path repos)
# resolve exactly like they do on the host. docker/entrypoint.sh installs
# composer/npm deps and builds assets on first boot, cached in named volumes.
FROM dunglas/frankenphp:1-php8.4-alpine

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    bcmath \
    intl \
    zip \
    opcache \
    pcntl \
    posix

RUN apk add --no-cache nodejs npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/apps/backend

EXPOSE 8000

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000", "--watch"]
