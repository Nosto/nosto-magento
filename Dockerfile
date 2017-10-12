FROM ubuntu:14.04

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
RUN         apt-get update && \
            apt-get -y install unzip && \
            apt-get -y install wget && \
            apt-get -y install libfreetype6-dev && \
            apt-get -y install libjpeg-dev && \
            apt-get -y install libmcrypt-dev && \
            apt-get -y install libreadline-dev && \
            apt-get -y install libpng-dev && \
            apt-get -y install libicu-dev && \
            apt-get -y install mysql-client && \
            apt-get -y install libmcrypt-dev && \
            apt-get -y install libxml2-dev && \
            apt-get -y install libxslt1-dev && \
            apt-get -y install vim && \
            apt-get -y install nano && \
            apt-get -y install git && \
            apt-get -y install nano && \
            apt-get -y install tree && \
            apt-get -y install curl && \
            apt-get -y install software-properties-common && \
            apt-get -y install language-pack-en-base && \
            apt-get -y install supervisor

# Add the custom PHP repository to install the PHP modules. In order to use the
# command to add a repo, the package software-properties-common must be already
# installed
RUN        add-apt-repository ppa:ondrej/php

# Install Apache, MySQL and all the required development and prod PHP modules
RUN        apt-get update && \
           apt-get -y install apache2 && \
           apt-get -y install php7.0 && \
           apt-get -y install mysql-client-core-5.6 && \
           apt-get -y install mysql-server-core-5.6 && \
           apt-get -y install mysql-server-5.6 && \
           apt-get -y install php7.0-dev && \
           apt-get -y install php7.0-gd && \
           apt-get -y install php7.0-mcrypt && \
           apt-get -y install php7.0-intl && \
           apt-get -y install php7.0-xsl && \
           apt-get -y install php7.0-zip && \
           apt-get -y install php7.0-bcmath && \
           apt-get -y install php7.0-curl && \
           apt-get -y install php7.0-mbstring && \
           apt-get -y install php7.0-mysql && \
           apt-get -y install php-ast && \
           apt-get -y install php7.0-soap && \
           a2enmod rewrite && phpenmod ast soap && \
           a2dissite 000-default.conf

RUN        php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php && \
           php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
           php -r "unlink('composer-setup.php');"

RUN        groupadd -r plugins -g 113 && \
           useradd -ms /bin/bash -u 113 -r -g plugins plugins && \
           usermod -a -G www-data plugins

USER       plugins
ENTRYPOINT ["bash"]
