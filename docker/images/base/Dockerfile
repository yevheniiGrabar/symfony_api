FROM ubuntu

ARG SYSTEM_TZ

RUN ln -snf /usr/share/zoneinfo/$SYSTEM_TZ /etc/localtime && echo $SYSTEM_TZ > /etc/timezone

RUN set -xe \
    && apt-get update \
    && apt -y install software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get -y install \
        git \
        php7.4 \
        php7.4-dev \
        php7.4-bcmath \
        php7.4-common \
        php7.4-curl \
        php7.4-dom \
        php7.4-gd \
        php7.4-imagick \
        php7.4-mbstring \
        php7.4-mysql \
        php7.4-mysqli \
        php7.4-mysqlnd \
        php7.4-pdo \
        php7.4-tidy \
        php7.4-xml \
        php7.4-xdebug \
        php7.4-zip \
        php7.4-intl \
        libapache2-mod-php7.4 \
        php-pear \
    && apt-get -y upgrade

RUN set -xe \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer
