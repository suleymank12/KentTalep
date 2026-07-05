# KentTalep — Backend

Laravel 13 REST API (PHP 8.4, PostgreSQL 17 + PostGIS). Vatandaş/personel/
yönetici/admin rolleriyle konum tabanlı hasar/talep yönetimi. Kimlik doğrulama
Laravel Sanctum (cihaz başına token) ile, yetkilendirme spatie/laravel-permission
ile sağlanır.

Depo genel bakışı ve altyapı için köke bakın: [`../README.md`](../README.md).

## Kurulum

Altyapı (PostgreSQL+PostGIS, MinIO, Mailpit) kökten Docker ile ayağa kalkar:

```bash
# repo kökünde
cp .env.example .env
docker compose up -d
```

Ardından backend:

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

Lokal geliştirme için örnek verileri (roller + her rolden bir kullanıcı) yükle:

```bash
php artisan migrate:fresh --seed
```

Production kurulumunda yalnız roller seed edilir ve ilk admin komutla açılır:

```bash
php artisan db:seed --class=RoleSeeder
php artisan kenttalep:admin
```

## Test

Testler yalnızca PostgreSQL + PostGIS (`kenttalep_test`) veritabanına karşı
koşar; SQLite kullanılmaz.

```bash
php artisan test
```

## Kalite araçları

```bash
vendor/bin/pint --test        # kod stili (Laravel preset + strict types)
vendor/bin/phpstan analyse    # statik analiz (level 8, baseline yok)
```

## Yararlı komutlar

```bash
php artisan route:list --path=api   # API rotalarını listele
php artisan kenttalep:admin         # admin kullanıcı oluştur/güncelle
```
