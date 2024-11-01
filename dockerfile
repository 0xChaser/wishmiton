FROM php:8.3.6-alpine AS base

LABEL maintainer="Florian ISAK"

USER root

SHELL ["/bin/sh", "-c"]

# RUN find / -name "php.ini" -type f 2> /dev/null

RUN echo 'cgi.force_redirect=0' >> /usr/local/etc/php/php.ini

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | sh
RUN apk add symfony-cli 

USER www-data

COPY --chown=www-data:www-data . /app/wishmiton/

USER root
RUN chown -R www-data: /app/wishmiton/

WORKDIR /app/wishmiton

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

USER www-data

RUN rm composer.lock
RUN composer install

FROM base AS development

USER www-data

CMD ["php", "-S" , "0.0.0.0:8000", "-t", "public"]
EXPOSE 8000