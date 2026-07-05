# KentTalep — Teknik Kararlar (ADR-lite)

Her madde: karar + kısa gerekçe.

- **ADR-01 — Laravel 13 + PHP 8.4:** Backend framework olarak Laravel 13, PHP
  8.4 üzerinde. Gerekçe: Laravel 11 destek sonu Mart 2026; güncel LTS-benzeri
  hat ve PHP 8.4 dil özellikleriyle uzun ömür.
- **ADR-02 — PostgreSQL 17 + PostGIS:** Veritabanı PostgreSQL 17, coğrafi
  sorgular için PostGIS eklentisi. Gerekçe: konum tabanlı ürün; yakınlık,
  alan içi filtreleme ve harita sorguları için birinci sınıf coğrafi destek.
- **ADR-03 — Belediye başına kurulum (multi-tenant değil):** Her belediye için
  ayrı kurulum/örnek. Gerekçe: veri izolasyonu, kamu satış modeli ve kurulumla
  teslim edilebilir ürün hedefi multi-tenant karmaşıklığından basit ve güvenli.
- **ADR-04 — Queue/Cache için database driver:** Varsayılan queue, cache ve
  session sürücüsü database. Gerekçe: ek altyapı (Redis) gerektirmeden çalışır;
  ihtiyaç halinde `.env` ile Redis'e geçilebilir.
- **ADR-05 — Realtime Faz 5'te Laravel Reverb:** Gerçek zamanlı bildirim/olay
  katmanı Laravel Reverb ile Faz 5'te eklenir. Gerekçe: WebSocket ihtiyacı
  personel akışı ve bildirimlerle birlikte doğar; erken bağımlılık gereksiz.
- **ADR-06 — Dosya depolama Flysystem (local → MinIO/S3):** Medya dosyaları
  Flysystem soyutlamasıyla; geliştirmede local disk, üretimde MinIO/S3.
  Gerekçe: tek arayüz, ortamlar arası taşınabilirlik, S3 uyumlu MinIO ile
  yerelde gerçekçi test.
- **ADR-07 — Push bildirim Expo Push Service:** Mobil push bildirimleri Expo
  Push Service üzerinden. Gerekçe: Expo tabanlı mobil istemciyle native uyum,
  FCM/APNs yönetimini basitleştirir.
- **ADR-08 — Harita MapLibre + OSM:** İstemci haritaları MapLibre GL ve
  OpenStreetMap kaynağı. Gerekçe: açık kaynak, lisans maliyeti yok, white-label
  ürün için tedarikçi kilidi olmadan tam kontrol.
- **ADR-09 — Monorepo:** backend, mobile ve admin-web tek repoda. Gerekçe: tek
  sürümleme, ortak dokümantasyon ve fazlar arası koordinasyonu kolaylaştırır.
- **ADR-10 — White-label tema mimarisi:** Marka, renk, logo ve harita merkezi
  kurulum ayarından (settings) beslenir; kodda sabitlenmez. Gerekçe: tek kod
  tabanının farklı belediyelere satılabilmesi ve hızlı markalaşma.
- **ADR-11 — Sanctum: cihaz başına token, 180 gün, günlük prune:** Kimlik
  doğrulama Laravel Sanctum ile; her giriş/kayıt ayrı bir token ve buna bağlı
  `user_devices` kaydı üretir. Token geçerliliği 180 gün
  (`SANCTUM_TOKEN_EXPIRATION_MINUTES=259200`), süresi dolanlar
  `sanctum:prune-expired` ile günlük temizlenir. Gerekçe: mobil istemcide uzun
  oturum, cihaz bazlı iptal ve push hedefleme; expired token birikimini önleme.
- **ADR-12 — spatie/laravel-permission, İngilizce tanımlayıcı + Türkçe etiket:**
  Roller (citizen, staff, manager, admin) `web` guard ile spatie üzerinden
  yönetilir; enum backed değerleri İngilizce, `label()` Türkçe döner. Gerekçe:
  kod/DB tarafında stabil, dile bağımsız tanımlayıcılar; kullanıcıya Türkçe
  sunum.
- **ADR-13 — Şifre sıfırlama 6 haneli e-posta kodu, kuyruksuz gönderim:**
  Sıfırlama linki yerine 15 dk geçerli 6 haneli kod, `password_reset_tokens`
  içinde hash'li saklanır; 60 sn tekrar sınırı vardır. Bildirim senkron (kuyruk
  yok) gönderilir. Gerekçe: mobil istemci odaklı akış (derin link gerektirmez);
  kullanıcı kodu hemen beklediğinden ve Faz 0'da queue worker garanti
  edilmediğinden senkron gönderim tercih edildi. Kod, 5 yanlış denemede
  geçersiz kılınır (kayıt silinir) ve kalan deneme hakkı yanıtta sızdırılmaz.
- **ADR-14 — laravel-lang ile Türkçe yerelleştirme:** `APP_LOCALE=tr`,
  `APP_FAKER_LOCALE=tr_TR`, fallback `en`. Doğrulama/kimlik mesajları
  `laravel-lang/common` ile `lang/tr` altına yayınlanır. Gerekçe: son kullanıcı
  ve demo içeriğin Türkçe olması; çevirilerin bakımını topluluk paketine
  devretmek.
