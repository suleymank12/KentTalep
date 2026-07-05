# KentTalep — Proje Özeti

## Ne yapıyoruz
Belediyeler için konum tabanlı hasar/talep yönetim sistemi. Vatandaş,
mobil uygulamadan konum + kategori + fotoğraf ile talep açar; yönetici
web panelden talebi personele atar; personel sahada çözer ve kanıt
fotoğrafı yükler; vatandaş süreci bildirimlerle takip eder.

## Ürün modeli
- Belediye başına ayrı kurulum (white-label). Multi-tenant DEĞİL.
- Belediye adı, logo, ana renk ve harita merkezi kurulum ayarlarından gelir.
- Hedef: kurulum dokümanıyla teslim edilebilir, satılabilir ürün.

## Bileşenler
- backend/   → Laravel 13 REST API (PHP 8.4, PostgreSQL 17 + PostGIS)
- mobile/    → React Native (Expo) — vatandaş ve personel akışları
- admin-web/ → React (Vite) yönetici paneli

## Roller
- Vatandaş: talep açar, yalnızca kendi taleplerini görür ve takip eder.
- Personel: yalnızca kendine atanan talepleri görür; çözer, fotoğraf
  yükler, kapatır.
- Yönetici: tüm talepleri görür, personele atar, raporlara erişir.
- Admin: yönetici yetkileri + kullanıcı yönetimi + kurulum ayarları.

## Talep yaşam döngüsü
pending → assigned → in_progress → resolved → closed
Ek durumlar: cancelled (vatandaş iptali), rejected (yönetici reddi).
Geçişler rol bazlı state machine ile sınırlıdır; her geçiş
ticket_status_logs tablosuna yazılır.

## Fazlar
- Faz 0: monorepo, Docker, Laravel 13, kalite araçları, CI
- Faz 1: Sanctum auth, roller, kullanıcı yönetimi, user_devices
- Faz 2: talep çekirdeği (CRUD, state machine, PostGIS konum, medya,
  demo seed verisi)
- Faz 3: vatandaş mobil uygulaması
- Faz 4: admin web paneli
- Faz 5: personel mobil akışı + bildirimler (Expo Push, Reverb)
- Faz 6: sertleştirme, production Docker, kurulum dokümanı

## Kaynak hiyerarşisi
Çalışma kuralları: CLAUDE.md · Teknik kararlar: docs/kararlar.md ·
Tasarım: docs/tasarim-ilkeleri.md
Bu üç belgeyle çelişen hiçbir varsayım yapılmaz.
