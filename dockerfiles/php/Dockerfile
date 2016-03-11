FROM kilix/php:5.6

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod a+x /entrypoint.sh && chown root:root /entrypoint.sh

VOLUME /srv/api-core-bundle
WORKDIR /srv/api-core-bundle

ENTRYPOINT ["/entrypoint.sh"]
