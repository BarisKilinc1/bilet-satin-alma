# PHP + Apache tabanlı imaj kullan
FROM php:8.2-apache

# Çalışma dizini ayarla
WORKDIR /var/www/html

# Proje dosyalarını container içine kopyala
COPY . /var/www/html

# Gerekli sistem paketlerini kur (SQLite derlemesi için)
RUN apt-get update && apt-get install -y libsqlite3-dev

# PHP modüllerini yükle
RUN docker-php-ext-install pdo pdo_sqlite

# Apache'nin rewrite modülünü aktif et
RUN a2enmod rewrite

# PHP session klasörünü oluştur ve izin ver
RUN mkdir -p /var/lib/php/sessions && chmod -R 777 /var/lib/php/sessions

# Port 80'i aç
EXPOSE 80

# Container başlatıldığında Apache'yi çalıştır
CMD ["apache2-foreground"]
