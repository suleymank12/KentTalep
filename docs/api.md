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
