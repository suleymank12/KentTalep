# KentTalep

Belediyeler için konum tabanlı hasar/talep yönetim sistemi. Vatandaş mobil
uygulamadan konum + kategori + fotoğraf ile talep açar; yönetici web panelden
talebi personele atar; personel sahada çözer ve kanıt fotoğrafı yükler;
vatandaş süreci bildirimlerle takip eder.

Belediye başına ayrı kurulum (white-label, multi-tenant değil). Belediye adı,
logo, ana renk ve harita merkezi kurulum ayarlarından gelir.

Repo: https://github.com/suleymank12/KentTalep.git

## Bileşenler
- `backend/`   → Laravel 13 REST API (PHP 8.4, PostgreSQL 17 + PostGIS)
- `mobile/`    → React Native (Expo) — vatandaş ve personel akışları (Faz 3)
- `admin-web/` → React (Vite) yönetici paneli (Faz 4)

## Belgeler
- Proje özeti: [docs/proje.md](docs/proje.md)
- Teknik kararlar: [docs/kararlar.md](docs/kararlar.md)
- Tasarım ilkeleri: [docs/tasarim-ilkeleri.md](docs/tasarim-ilkeleri.md)
- Çalışma kuralları: [CLAUDE.md](CLAUDE.md)

## Yerel kurulum

Gereksinimler: Docker Desktop, PHP 8.4, Composer.

```bash
# 1. Kök ortam dosyasını oluştur (Docker servisleri için)
cp .env.example .env

# 2. Altyapıyı başlat (PostgreSQL+PostGIS, MinIO, Mailpit)
docker compose up -d

# 3. Backend ortam dosyasını oluştur
cd backend
cp .env.example .env

# 4. PHP bağımlılıklarını kur
composer install

# 5. Uygulama anahtarını üret
php artisan key:generate

# 6. Veritabanı şemasını uygula (PostgreSQL — kenttalep)
php artisan migrate

# 7. Testleri koştur (PostgreSQL — kenttalep_test)
php artisan test
```

## Servis portları
| Servis   | Port(lar)      | Amaç                          |
|----------|----------------|-------------------------------|
| db       | 5432           | PostgreSQL 17 + PostGIS       |
| minio    | 9000 / 9001    | Obje deposu / konsol          |
| mailpit  | 1025 / 8025    | SMTP / web arayüzü            |
