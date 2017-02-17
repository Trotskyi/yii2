FROM codemix/yii2-base:2.0.11.2-php7-apache
#FROM codemix/yii2-base:2.0.11.2-php7-fpm
#FROM codemix/yii2-base:2.0.11.2-hhvm


# Composer packages are installed first. This will only add packages
# that are not already in the yii2-base image.
COPY composer.json /var/www/html/
COPY composer.lock /var/www/html/
RUN composer self-update --no-progress && \
    composer install --no-progress

# Copy the working dir to the image's web root
COPY . /var/www/html

# The following directories are .dockerignored to not pollute the docker images
# with local logs and published assets from development. So we need to create
# empty dirs and set right permissions inside the container.
RUN mkdir runtime web/assets \
    && chown www-data:www-data runtime web/assets

# Expose everything under /var/www (vendor + html)
# This is only required for the nginx setup
VOLUME ["/var/www"]


FROM php:7

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini