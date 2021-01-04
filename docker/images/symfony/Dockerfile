FROM baseimg

WORKDIR /var/www/html

#CMD tail -f /dev/null

COPY composer.json ./composer.json
COPY composer.lock ./composer.lock
RUN composer install --no-scripts

COPY . ./

RUN /bin/echo -e '\
ErrorLog "/dev/stderr"\n\
CustomLog "/dev/stdout" common\n\
<VirtualHost *:80>\n\
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1\n\
    SetEnvIf Content-Type "(.*)" HTTP_CONTENT_TYPE=$1\n\
    SetEnvIf Accept "(.*)" HTTP_ACCEPT=$1\n\
    DocumentRoot "/var/www/html/public"\n\
    <Directory "/var/www/html/public">\n\
        AllowOverride None\n\
        Order Allow,Deny\n\
        Allow from All\n\
        <IfModule mod_rewrite.c>\n\
            Options -MultiViews\n\
	        Options FollowSymlinks\n\
            RewriteEngine On\n\
            RewriteCond %{REQUEST_FILENAME} !-f\n\
            RewriteRule ^(.*)$ index.php [QSA,L]\n\
        </IfModule>\n\
    </Directory>\n\
</VirtualHost>\n\
' > /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

RUN /bin/echo -e "#!/bin/bash\n\
chown -R www-data /var/www/html\n\
chmod -R 0777 /var/www/html/var/cache\n\
chmod -R 0777 /var/www/html/var/log\n\
exec apachectl -D FOREGROUND\n\
" > /usr/sbin/docker-entrypoint.sh && chmod +x /usr/sbin/docker-entrypoint.sh

RUN composer install

CMD /usr/sbin/docker-entrypoint.sh
