# KentTalep API — Faz 1 (Kimlik Doğrulama ve Kullanıcı Yönetimi)

Tüm uç noktalar `api/` önekiyle ve JSON döner (ForceJsonResponse middleware).
Kimlik doğrulama Laravel Sanctum bearer token ile yapılır; yetkilendirme
spatie/laravel-permission rolleriyle sağlanır. Kimlik doğrulaması gereken tüm
rotalar `auth:sanctum` + `active` (pasif hesap 403) + `device.seen` (cihaz
last_seen güncelleme) middleware'lerinden geçer.

## Kimlik doğrulama

| Method | Path | Auth | Rol | Açıklama |
|--------|------|------|-----|----------|
| POST | /api/auth/register | Hayır | — | Vatandaş kaydı; citizen rolü, token + cihaz kaydı oluşturur (201) |
| POST | /api/auth/login | Hayır | — | Giriş; yeni token + cihaz döner. Hatalı bilgi 422, pasif hesap 403 |
| POST | /api/auth/logout | Evet | Tümü | Mevcut token'ı ve bağlı cihazı siler (204) |
| GET | /api/auth/me | Evet | Tümü | Oturumdaki kullanıcı (UserResource) |
| PATCH | /api/auth/device | Evet | Tümü | Push token'ı mevcut cihaza yazar (cihaz el değiştirme kuralıyla) |
| PATCH | /api/auth/password | Evet | Tümü | Şifre değiştirir; diğer tüm token'ları iptal eder (204) |
| POST | /api/auth/forgot-password | Hayır | — | Her durumda generic 200; kayıtlı+aktif ise 6 haneli e-posta kodu |
| POST | /api/auth/reset-password | Hayır | — | Kodu doğrular, şifreyi sıfırlar, tüm token/cihazları siler |

Hız sınırları: login 5/dk (IP+e-posta), register 5/dk (IP),
forgot-password 3/dk (IP+e-posta), reset-password 5/dk (IP). Aşımda 429.

## Kullanıcı yönetimi (yalnız admin)

| Method | Path | Auth | Rol | Açıklama |
|--------|------|------|-----|----------|
| GET | /api/users | Evet | Admin | Sayfalı liste; `role`, `search`, `per_page` filtreleri |
| POST | /api/users | Evet | Admin | Herhangi bir rolde kullanıcı oluşturur (201) |
| GET | /api/users/{user} | Evet | Admin | Kullanıcı detayı |
| PATCH | /api/users/{user} | Evet | Admin | name, phone, role, is_active günceller |
| DELETE | /api/users/{user} | Evet | Admin | Soft delete + token/cihaz temizliği (204) |

Koruma kuralları (422): admin kendi rolünü/aktifliğini değiştiremez, kendini
silemez; sistemdeki son aktif admin silinemez, pasifleştirilemez, rolü
düşürülemez. is_active=false yapılan kullanıcının tüm token/cihazları silinir.

## UserResource alanları
`id, name, email, phone, role (tek rol adı), is_active, created_at`

Yönetici (manager) Faz 2'den itibaren `GET /api/users`'ı yalnız `role=staff`
kapsamında görebilir (atama ekranı için); admin tümünü ve tüm rolleri filtreler.

# KentTalep API — Faz 2 (Talep Çekirdeği)

Tüm uç noktalar `auth:sanctum` + `active` + `device.seen` gerektirir.

## Kategoriler

| Method | Path | Rol | Açıklama |
|--------|------|-----|----------|
| GET | /api/categories | Tümü | Aktif kategoriler ağaç halinde (parent + children), sort_order |

## Talep CRUD

| Method | Path | Rol | Açıklama |
|--------|------|-----|----------|
| POST | /api/tickets | Vatandaş/Admin | Talep oluşturur (pending). priority İSTENMEZ (triage). throttle: 5/10dk (kullanıcı) |
| GET | /api/tickets | Tümü | Rol kapsamlı liste. Filtreler: `status`, `category_id`, `priority`, `q`, `near=lat,lng`+`radius_km`, `per_page` |
| GET | /api/tickets/{ticket} | Sahibi/Atanan/Yön./Admin | Detay (kategori, medya, atanan, sahip) |
| PATCH | /api/tickets/{ticket} | Sahibi (pending, title/desc) · Yön./Admin (category/priority, terminal değil) | Günceller |
| DELETE | /api/tickets/{ticket} | Admin | Soft delete |
| GET | /api/tickets/{ticket}/logs | Detay policy'si | Durum geçmişi (en yeni üstte) |

Kapsam: vatandaş kendi taleplerini, personel kendine atananları, yönetici/admin
tümünü görür.

## Durum geçişleri (state machine)

Hepsi `PATCH /api/tickets/{ticket}/{eylem}`; güncel TicketResource döner.

| Eylem | Geçiş | Yetki | Gereklilik |
|-------|-------|-------|------------|
| assign | pending→assigned, assigned→assigned | Yönetici/Admin | assigned_to (aktif personel) |
| start | assigned→in_progress | Atanan personel/Yön./Admin | — |
| resolve | in_progress→resolved | Atanan personel/Yön./Admin | ≥1 "sonrası" medya (yoksa 422) |
| close | resolved→closed | Sahibi/Yön./Admin | — |
| reopen | resolved→in_progress | Sahibi/Yön./Admin | note zorunlu |
| cancel | pending/assigned→cancelled | Sahibi/Admin | — |
| reject | pending→rejected | Yönetici/Admin | note zorunlu |

Terminal durumlar (closed, cancelled, rejected) çıkışsızdır. Yetkisiz rol 403,
tanımsız/geçersiz geçiş 422 döner. Her geçiş `ticket_status_logs`'a yazılır.

## Medya

| Method | Path | Rol | Açıklama |
|--------|------|-----|----------|
| POST | /api/tickets/{ticket}/media | before: sahibi/admin (terminal değil) · after: atanan/yön./admin (in_progress\|resolved) | Multipart file+type. Talep başına 10 medya |
| GET | /api/ticket-media/{media} | Talebi görebilen | Asıl görseli stream eder (private) |
| GET | /api/ticket-media/{media}/thumb | Talebi görebilen | Thumbnail stream eder |
| DELETE | /api/ticket-media/{media} | Yükleyen (pending\|assigned\|in_progress) · Admin (her durumda) | Kaydı ve dosyaları siler |

Görseller private diskte tutulur; public URL yoktur. İşleme senkrondur: gerçek
MIME (finfo), boyut ≤10MB, piksel ≤40M kontrolleri; EXIF orientation uygulanır
ve yeniden encode ile EXIF/GPS temizlenir (KVKK); uzun kenar 2560'a küçültülür,
480px thumbnail üretilir.
