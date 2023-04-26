FROM jassue/laravel:7.4
#FROM jassue/laravel:swoole-7.4

MAINTAINER JASSUE <jassue@163.com>

ENV WEB_DOCUMENT_ROOT=/app/public \
    WORKDIR=/app

WORKDIR $WORKDIR

# copy source code
COPY . .

RUN cp build/start.sh /start.sh \
    && chmod +x /start.sh \
    # cron
    && echo "* * * * * php $WORKDIR/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontabs/root \
    # worker
    && cp build/laravel.supervisor.conf /opt/docker/etc/supervisor.d/laravel.conf \
    # laravel-s(swoole) nginx conf
    #&& cp build/laravel-s.location-root.conf /opt/docker/etc/nginx/vhost.common.d/10-location-root.conf \
    #&& cp build/laravel-s.php.conf /opt/docker/etc/nginx/conf.d/10-php.conf \
    #&& cp build/laravel-s.location-php.conf /opt/docker/etc/nginx/vhost.common.d/10-php.conf \
    # clean
    && if [ -f .env ]; then rm .env; fi \
    && if [ -d .git ]; then rm -rf .git; fi \
    # other
    && chown -R application:application $WORKDIR

EXPOSE 80

VOLUME $WORKDIR/storage

ENTRYPOINT ["/start.sh"]

CMD ["supervisord"]
