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
- **ADR-15 — Atomik talep numarası (ON CONFLICT ... RETURNING):** Yıl bazlı
  sıralı numara (`2026-000482`) `ticket_counters` üzerinde tek SQL ifadesiyle
  (`INSERT ... ON CONFLICT (year) DO UPDATE SET last_value = last_value + 1
  RETURNING`) üretilir. Gerekçe: oku-artır-yaz deseni eşzamanlı taleplerde lost
  update / çakışan numara üretir; atomik ifade yarış penceresi bırakmaz.
- **ADR-16 — State machine + foto-zorunlu resolve:** Durum geçişleri sabit bir
  geçiş tablosu (TicketTransitionMap) ve TicketStateMachine ile rol/sahiplik
  yetkileri altında yapılır; her geçiş loglanır. `in_progress → resolved` için
  en az bir "sonrası" fotoğraf zorunludur. Gerekçe: geçersiz geçişleri ve yetki
  ihlallerini tek noktada engellemek; kanıt fotoğrafı zorunluluğu ürünün saha
  değerini ve denetlenebilirliğini artırır.
- **ADR-17 — Medya: private disk + yetkili stream + el yapımı işlemci:**
  Görseller private diskte tutulur ve yalnız yetkili stream uç noktasından
  servis edilir (public URL yok). İşleme `intervention/image` ile elle yazılmış
  senkron bir servistir; `spatie/laravel-medialibrary` yerine tercih edildi.
  Gerekçe: şema üzerinde tam kontrol (before/after tipi, thumb, boyut),
  denetlenebilirlik, queue'suz senkron dönüşüm ve KVKK gereği EXIF/GPS'in
  yeniden encode ile kesin temizlenmesi. Ek güvenlik: gerçek MIME (finfo) ve
  piksel-bombası (≤40M piksel) kontrolleri. Resolve anından itibaren medya
  denetim kanıtıdır; yükleyen yalnız pending/assigned/in_progress durumlarında
  silebilir, istisnai silme (KVKK talebi, hatalı yükleme) yalnız admin
  yetkisindedir.
- **ADR-18 — Konum için clickbar/laravel-magellan (PostGIS):** Konum,
  Laravel'in native `geography(point, 4326)` kolonunda tutulur; Eloquent tarafı
  Magellan `Point` cast'i ile yönetilir. Yakınlık filtresi parametreli
  `ST_DWithin(..::geography, metre)` ile yazılır. Gerekçe: Magellan Laravel 13
  ile sorunsuz kuruldu; Point okuma/yazma ve coğrafi sorgular için olgun destek
  (fallback raw ifadeye gerek kalmadı).
- **ADR-19 — priority = triage + iptal penceresi:** Talep oluştururken öncelik
  istenmez; varsayılan `medium`'dur ve önceliklendirme yalnız yönetici/admin'e
  aittir. Vatandaş talebini yalnız `pending` veya `assigned` durumundayken iptal
  edebilir. Gerekçe: önceliklendirme kurumsal bir karardır (vatandaş
  şişiremez); iş başladıktan (in_progress) sonra iptal, harcanan saha emeğini
  boşa çıkaracağından kapatılır.
- **ADR-20 — Mobil stack (Expo SDK 57):** Vatandaş uygulaması Expo SDK 57
  (React Native 0.86) + Expo Router (dosya tabanlı) + NativeWind v4 +
  tailwindcss ^3.4 (v4 NativeWind ile uyumsuz) + TanStack Query (sunucu durumu)
  + Zustand (istemci durumu) + React Hook Form/Zod (form+doğrulama) +
  expo-secure-store (token). Gerekçe: yönetilen Expo iş akışı hızlı kurulum ve
  OTA imkânı; token/query/form için olgun, tip-güvenli, hafif kütüphaneler;
  token yalnız güvenli depoda tutulur.
- **ADR-21 — Mobil harita MapLibre RN:** Harita katmanı (Faz 3B) MapLibre React
  Native ile kurulur; tile URL settings'ten gelir. Gerekçe: Google Maps SDK
  anahtarı/kullanım faturası yok, web (MapLibre GL) ile ortak stack, tile
  kaynağının kurulumdan değiştirilebilmesi white-label modeline uygun.
- **ADR-22 — Settings ile runtime white-label tema:** Marka adı, ana renk ve
  harita varsayılanları `GET /api/settings` (auth'suz, whitelist'li, 5 dk
  cache) üzerinden gelir; mobilde primary rengi NativeWind CSS değişkeniyle
  (`--color-primary`) çalışma zamanında uygulanır. Gerekçe: tek kod tabanının
  belediyeye göre yeniden markalanması; yalnız whitelist anahtarların
  sızdırılması (iç ayarlar gizli kalır).
- **ADR-23 — Lucide ikon seti + AI-görsel yasağı + wordmark:** İkonlar Lucide
  (web: lucide-react, mobil: lucide-react-native); yapay zekâ ile üretilmiş
  ikon/amblem/illüstrasyon kullanılmaz. Varsayılan marka tipografik
  wordmark'tır; gerçek belediye arması kurulumda settings'ten yüklenir.
  Gerekçe: tutarlı, lisanslı, kod tabanlı görseller; sahte kurumsal amblem
  üretmekten kaçınma ve denetlenebilirlik.
