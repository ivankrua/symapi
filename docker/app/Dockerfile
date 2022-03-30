FROM php:7.4-fpm-alpine
LABEL maintainer="Ivan Nazarenko <fop.nazarenko.ivan@gmail.com>"
# args
ARG TZ=UTC
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG APP_ENV=dev
# envs
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
ENV COMPOSER_HOME=/usr/local/share/composer
ENV APP_ENV=${APP_ENV}
# install base app
RUN apk add --no-cache nginx bash mc nano su-exec supervisor icu-dev libxslt-dev libzip-dev \
    freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql zip bcmath intl xsl sockets
# install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# add config
ADD ./php.ini /usr/local/etc/php/php.ini
ADD ./www.conf /usr/local/etc/php-fpm.d/www.conf
ADD ./supervisord.conf /etc/supervisord.conf
ADD ./nginx/default.conf /etc/nginx/conf.d/default.conf
ADD ./nginx/nginx.conf /etc/nginx/nginx.conf
ADD ./cron /etc/crontabs/root
# create user
RUN addgroup -g ${GROUP_ID} dev \
  && adduser  -u ${USER_ID} -G dev -h /home/dev -D dev \
  && chmod u+s /sbin/su-exec \
  && mkdir -p /app && chown -R dev:dev /app && chmod -R  777 /var/log \
  && mkdir -p /usr/local/share/composer && chmod 777 /usr/local/share/composer \
  && mkdir -p /app/storage && chmod 777 /app/storage \
  && touch /run/nginx.pid \
  && chown dev:dev /run/nginx.pid \
  && chown -R dev:dev /var/lib/nginx \
  && chown -R dev:dev /var/log/nginx
# others
WORKDIR /app
EXPOSE 80 9000
USER dev
CMD [ "su-exec","root", "/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
