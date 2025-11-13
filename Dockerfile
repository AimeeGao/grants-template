# ==============================================================
# GRANTS-TEMPLATE Local Dockerfile
# Does NOT include OpenShift-specific logic or frontend build steps.
# ==============================================================
FROM php:8.3-apache

# Install system dependencies and PHP extensionsphp
RUN apt-get update && apt-get install -y \
    git nano curl zip unzip libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd \
# Enable Apache module (mod_rewrite)
  && a2enmod rewrite \
# Apache - Hide version
  && sed -i -e 's/^ServerTokens OS$/ServerTokens Prod/g' \
        -e 's/^ServerSignature On$/ServerSignature Off/g' \
        /etc/apache2/conf-available/security.conf 
#install npm
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
&& NODE_MAJOR=22 \
&& echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
&& apt-get update \
&& apt-get install -y nodejs

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
# Cleanup
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy Apache virtual host configuration
COPY apache2/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html/

# Copy application files
COPY . .

# Install Laravel dependencies
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && composer install --no-interaction

# Expose the web port
EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
