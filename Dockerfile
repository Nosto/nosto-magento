FROM debian:stretch-slim

MAINTAINER  Nosto "platforms@nosto.com"

ENV        DEBIAN_FRONTEND noninteractive

# Do not install suggested dependencies
RUN echo -n "APT::Install-Recommends \"false\";\nAPT::Install-Suggests \"false\";" \
            | tee /etc/apt/apt.conf

# Enable access to metadata and packages using https
RUN apt-get update && \
            apt-get -y -qq install apt-transport-https

# Setup locale
RUN apt-get update && \
            apt-get -y -qq upgrade && \
            apt-get -y -qq install apt-utils locales && \
            sed -i 's/^# *\(en_US.UTF-8\)/\1/' /etc/locale.gen && \
            ln -sf /etc/locale.alias /usr/share/locale/locale.alias && \
            locale-gen && \
            apt-get -y -qq clean

ENV         LANGUAGE en_US.UTF-8
ENV         LANG en_US.UTF-8
ENV         LC_ALL en_US.UTF-8
ENV         TERM xterm

# Environment variables to force the extension to connect to a specified instance
ENV         NOSTO_SERVER_URL connect.staging.nosto.com
ENV         NOSTO_API_BASE_URL https://api.staging.nosto.com
ENV         NOSTO_OAUTH_BASE_URL https://my.staging.nosto.com/oauth
ENV         NOSTO_WEB_HOOK_BASE_URL https://my.staging.nosto.com
ENV         NOSTO_IFRAME_ORIGIN_REGEXP .*

ENV         MYSQL_ENV_MYSQL_DATABASE magento
ENV         MYSQL_ENV_MYSQL_USER root
ENV         MYSQL_ENV_MYSQL_ROOT root
ENV         COMPOSER_ALLOW_SUPERUSER 1
ENV         DEBIAN_FRONTEND noninteractive

MAINTAINER  Nosto "platforms@nosto.com"

# Add php-7.1 Source List
RUN         apt-get -y -qq install lsb-release ca-certificates wget
RUN         wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN         sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
RUN         apt-get -y -qq update

# Install all core dependencies required for setting up Apache and PHP at least
RUN         apt-get -y -qq install unzip wget libfreetype6-dev libjpeg-dev \
            libmcrypt-dev libreadline-dev libpng-dev libicu-dev default-mysql-client \
            libmcrypt-dev libxml2-dev libxml2-utils libxslt1-dev vim nano git tree curl \
            supervisor ca-certificates && \
            apt-get -y -qq clean

# Install Apache, MySQL and all the required development and prod PHP modules
RUN         apt-get -y -qq install apache2 php7.1 php7.1-common default-mysql-client-core \
            default-mysql-server-core default-mysql-server php7.1-dev \
            php7.1-mcrypt php7.1-xsl php7.1-zip php7.1-bcmath php7.1-intl php7.1-gd \
            php7.1-curl php7.1-mbstring php7.1-mysql php7.1-soap php-xml php7.1-xml && \
            apt-get -y clean

# Upgrade AST extension
RUN         apt-get -y -qq install build-essential php-pear && \
            pecl install ast-0.1.6 && \
            apt-get purge -y build-essential && \
            apt-get -y clean

# Enable AST extension
RUN         echo "extension=ast.so" >> /etc/php/7.1/cli/php.ini

RUN         a2enmod rewrite && phpenmod soap && \
            a2dissite 000-default.conf


RUN        php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php && \
           php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
           php -r "unlink('composer-setup.php');"

# Set Permissions
RUN        groupadd -r plugins -g 113 && \
           useradd -ms /bin/bash -u 113 -r -g plugins plugins && \
           usermod -a -G www-data plugins


USER       plugins
#ENTRYPOINT ["bash"]
