# apps/admin — Filament panels, plain PHP dev server (no Octane, see README.md
# section on why the admin panel stays a separate, non-long-running process).
# Same bind-mount-everything approach as apps/backend's Dockerfile — see the
# comment there.
FROM php:8.4-cli-alpine

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/bin/

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    bcmath \
    intl \
    zip \
    opcache \
    gd

RUN apk add --no-cache nodejs npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/apps/admin

EXPOSE 8001

ENTRYPOINT ["entrypoint"]
# Not `php artisan serve` — its ServeCommand spawns the built-in server as a
# subprocess with a curated environment that drops Docker-injected env vars
# (confirmed: getenv('DB_CONNECTION') is empty inside it, even though it's
# set correctly for artisan/tinker). Driving PHP's built-in server directly
# with Laravel's own router script gets identical routing behavior without
# losing the container's environment.
CMD ["php", "-S", "0.0.0.0:8001", "-t", "public", "vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"]
