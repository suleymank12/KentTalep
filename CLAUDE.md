# KentTalep — Çalışma Kuralları

Bu belge bağlayıcıdır. Aşağıdaki kurallar her fazda geçerlidir.

- **K1:** Hiçbir dosya 400 satırı geçemez. Yaklaşan dosya bölünür.
- **K2:** TODO, "geçici çözüm", "sonra düzeltilecek" yasak. Kolayı yapma, doğrusunu yap.
- **K3:** Secret'lar yalnızca `.env`'de yaşar. Her yeni anahtar `.env.example`'a eklenir. `.env` asla commit edilmez.
- **K4:** HİÇBİR git komutu çalıştırma (init, add, commit, push dahil). Git'i kullanıcı terminalden yönetir. NO GIT PUSH.
- **K5:** Her iddia kanıtlanır. `pint --test` + `phpstan` + `php artisan test` çıktıları paylaşılmadan "tamamlandı" denmez.
- **K6 (Laravel'e özel):** Tüm PHP dosyalarında `declare(strict_types=1)` (Pint kuralıyla zorunlu). `Model::shouldBeStrict` aktif. Testler asla SQLite'a karşı koşmaz — yalnızca PostgreSQL+PostGIS. Controller'lar API Resource'suz response dönmez. Merge edilmiş migration bir daha düzenlenmez. Testlerde `Sanctum::actingAs` kullanılmaz; gerçek token için `tests/Pest.php`'deki `tokenFor()` yardımcısı kullanılır (`currentAccessToken()` bearer-PAT varsayımı).
- **K7 (Tasarım):** `docs/tasarim-ilkeleri.md` bağlayıcıdır. UI ekranları, kullanıcı onaylı tasarım brief'i olmadan kodlanmaz. Kütüphane varsayılan görünümleri (ör. shadcn default teması) olduğu gibi kullanılmaz.

## Yerel araçlar (bu geliştirme makinesi)
- PHP 8.4 + `composer.phar`: `C:\Users\suley\kenttalep-tools` (PHP ikilisi
  `php84\php.exe`). Git Bash PATH'inde `php` yok; komutlar bu tam yolla koşulur.
- DB host portu bu makinede **5433** (`docker-compose.override.yml` ile ezilir;
  commit edilen dosyalarda 5432 kalır).
- Node 22+.
- Android Studio + emülatör kurulu.

## Kaynak hiyerarşisi
Çalışma kuralları: `CLAUDE.md` · Teknik kararlar: `docs/kararlar.md` · Tasarım: `docs/tasarim-ilkeleri.md` · Proje özeti: `docs/proje.md`
