FROM debian:jessie
MAINTAINER Charles Sanquer <csanquer@kilix.fr>

RUN apt-get update && apt-get install -y \
    apt-transport-https \
    apt-utils \
    curl \
    wget

RUN apt-get update && apt-get install -y \
    zip \
    build-essential \
    acl \
    less \
    git

RUN apt-get install -y \
    imagemagick \
    php5-cli \
    php5-dev \
    php-pear \
    php5-curl \
    php5-gd \
    php5-imagick \
    php5-intl \
    php5-mcrypt \
    php5-sqlite \
    php5-xsl \
    php5-xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    curl -sS https://phar.phpunit.de/phpunit.phar -k -o /usr/local/bin/phpunit && \
    chmod 755 /usr/local/bin/phpunit && \
    wget -q http://get.sensiolabs.org/php-cs-fixer.phar -O /usr/local/bin/php-cs-fixer && \
    chmod a+x /usr/local/bin/php-cs-fixer

COPY config/cli/php.ini /etc/php5/cli/php.ini
COPY config/xdebug.sh /root/xdebug.sh
RUN bash /root/xdebug.sh

COPY docker-entrypoint.sh /entrypoint.sh

RUN chmod a+x /entrypoint.sh && chown root:root /entrypoint.sh

VOLUME /srv/api-core-bundle
WORKDIR /srv/api-core-bundle

ENTRYPOINT ["/entrypoint.sh"]

CMD ["/usr/sbin/php5"]
