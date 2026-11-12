FROM composer:2 AS composer

FROM debian:trixie-slim
LABEL maintainer="me"
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get -q update && \
    apt-get -qy install --no-install-recommends php-cli php-curl php-xml php-mysql php-sqlite3 php-mbstring \
        libapache2-mod-php apache2 ca-certificates unzip && \
    rm -rf /var/lib/apt/lists/* && \
    rm /etc/apache2/sites-enabled/* && \
    a2enmod rewrite deflate && \
    ln -sf /usr/share/zoneinfo/Etc/GMT /etc/localtime && echo Etc/GMT > /etc/timezone && \
    ln -sf /dev/stdout /var/log/apache2/access.log && \
    ln -sf /dev/stderr /var/log/apache2/error.log

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY ./provisioning/apache-host /etc/apache2/sites-enabled/default.conf
COPY . /srv/example-crm-integration
WORKDIR /srv/example-crm-integration
RUN /usr/local/bin/composer -n install --no-dev --optimize-autoloader

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
EXPOSE 80
