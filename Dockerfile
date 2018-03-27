FROM debian:stretch-slim

ENV         LANGUAGE en_US.UTF-8
ENV         LANG en_US.UTF-8
ENV         TERM xterm
RUN         export LC_ALL=en_US.UTF-8

# Environment variables to force the extension to connect to a specified instance
ENV         NOSTO_SERVER_URL staging.nosto.com
ENV         NOSTO_API_BASE_URL https://staging-api.nosto.com
ENV         NOSTO_OAUTH_BASE_URL https://staging.nosto.com/oauth
ENV         NOSTO_WEB_HOOK_BASE_URL https://staging.nosto.com
ENV         NOSTO_IFRAME_ORIGIN_REGEXP .*

ENV         MYSQL_ENV_MYSQL_DATABASE magento2
ENV         MYSQL_ENV_MYSQL_USER root
ENV         MYSQL_ENV_MYSQL_ROOT root
ENV         COMPOSER_ALLOW_SUPERUSER 1
ENV         DEBIAN_FRONTEND noninteractive

MAINTAINER  Nosto "platforms@nosto.com"

# Install all core dependencies required for setting up Apache and PHP atleast
RUN         apt-get update && apt-get -y -q install unzip wget libfreetype6-dev libjpeg-dev \
            libmcrypt-dev libreadline-dev libpng-dev libicu-dev default-mysql-client \
            libmcrypt-dev libxml2-dev libxml2-utils libxslt1-dev vim nano git tree curl \
            supervisor ca-certificates && \
            apt-get -y clean

# Install Apache, MySQL and all the required development and prod PHP modules
RUN         apt-get -y -q install apache2 php7.0 default-mysql-client-core \
            default-mysql-server-core default-mysql-server php7.0-dev php7.0-gd \
            php7.0-mcrypt php7.0-intl php7.0-xsl php7.0-zip php7.0-bcmath \
            php7.0-curl php7.0-mbstring php7.0-mysql php-ast php7.0-soap && \
            apt-get -y clean

# Upgrade ast extension
RUN         apt-get -y -q install build-essential php-pear && \
            pecl install ast && \
            apt-get purge -y build-essential && \
            apt-get -y clean

RUN         a2enmod rewrite && phpenmod ast soap && \
            a2dissite 000-default.conf

RUN        php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php && \
           php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
           php -r "unlink('composer-setup.php');"

RUN        groupadd -r plugins -g 113 && \
           useradd -ms /bin/bash -u 113 -r -g plugins plugins && \
           usermod -a -G www-data plugins

USER       plugins
#ENTRYPOINT ["bash"]
