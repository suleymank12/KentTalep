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
