FROM debian:stretch
MAINTAINER me
ENV DEBIAN_FRONTEND noninteractive


RUN apt-get -qq update && apt-get -qy install eatmydata && \
    eatmydata -- apt-get -qy install apt-transport-https && apt-get clean && rm -Rf /var/lib/apt/lists

COPY provisioning/debsury.list /etc/apt/sources.list.d/debsury.list
COPY provisioning/debsury.gpg /etc/apt/trusted.gpg.d/debsury.gpg

RUN eatmydata -- apt-get -q update && \
    eatmydata -- apt-get -qy install php7.1-cli php7.1-curl php7.1-json php7.1-xml php7.1-mysql php7.1-sqlite php7.1-mbstring \
        libapache2-mod-php7.1 curl lsb-release ca-certificates unzip apache2 && \
    rm -rf /var/lib/apt/lists/* && \
    rm /etc/apache2/sites-enabled/* && \
    a2enmod rewrite deflate 

RUN curl -so /usr/local/bin/composer https://getcomposer.org/composer.phar  && chmod 755 /usr/local/bin/composer && \
    echo GMT > /etc/timezone && dpkg-reconfigure --frontend noninteractive tzdata && \
    ln -sf /dev/stdout /var/log/apache2/access.log \
    && ln -sf /dev/stderr /var/log/apache2/error.log

COPY ./provisioning/apache-host /etc/apache2/sites-enabled/default.conf
COPY . /srv/example-crm-integration
WORKDIR /srv/example-crm-integration
RUN /usr/local/bin/composer -n install 

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
EXPOSE 80
