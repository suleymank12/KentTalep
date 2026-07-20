# KentTalep — Mobil Tasarım Brief'i (Vatandaş Uygulaması)

Bu belge bağlayıcıdır (K7) ve docs/tasarim-ilkeleri.md ile birlikte okunur.

## Kapsam
- Faz 3A: tema/token sistemi, auth ekranları (giriş, kayıt, şifre
  sıfırlama), sekme kabuğu, Profil.
- Faz 3B: Taleplerim (liste⇄harita), 3 adımlı talep oluşturma, talep
  detayı + zaman çizelgesi. Mobil harita: MapLibre RN (anahtar ve
  kullanım faturası yok; tile URL settings'ten).

## Renk token'ları (varsayılan tema; primary settings'ten ezilebilir)
primary #0F766E · on-primary #FFFFFF · primary-press #115E59
ink #0F172A · ink-soft #475569 · ink-muted #94A3B8
surface #FFFFFF · surface-alt #F8FAFC · border #E2E8F0
danger #DC2626 · success #16A34A · warning #D97706
Durum rozetleri (metin/zemin): pending #92400E/#FEF3C7 ·
assigned #1E40AF/#DBEAFE · in_progress #3730A3/#E0E7FF ·
resolved #065F46/#D1FAE5 · closed #334155/#E2E8F0 ·
cancelled #475569/#F1F5F9 · rejected #991B1B/#FEE2E2
Kural: bileşen içinde sabit hex yasak; tüm renkler token üzerinden.

## Tipografi (Inter)
display 28/34 bold · title 22/28 semibold · heading 18/24 semibold ·
body 16/24 regular (taban) · caption 14/20 · small 12/16 (yalnız meta).
Sistem font ölçeklemesine saygı; 1.3× ölçekte yerleşim kırılmaz.

## Ölçüler
4pt grid · buton/input yüksekliği 52 (dokunma hedefi ≥44×44) · radius 12 ·
kart iç boşluğu 16 · ekran yatay boşluğu 16 · sekme çubuğu 64 + safe area.

## Responsive
- Yerleşimler flex tabanlı; kapsayıcılarda sabit genişlik yok.
- Referans aralık: 360×640dp küçük telefondan büyük telefona; tablette
  içerik en fazla 480dp genişlikte sütunda ortalanır.
- MVP dikey (portrait) kilitli; safe-area insets her ekranda uygulanır.
- Uzun metin kısaltılmaz, sarılır; numberOfLines yalnız liste kartlarında.

## Navigasyon
3 sekme: Taleplerim · Yeni Talep (ortada, belirgin) · Profil.
Harita ayrı sekme değildir; Taleplerim içinde liste⇄harita geçişidir (3B).

## İkon ve amblem
- İkon seti Lucide'dir (lucide-react-native). Kategori ikonları
  backend'deki Lucide adlarıyla eşleşir.
- Yapay zekâ ile üretilmiş ikon/amblem/illüstrasyon kullanılamaz. Boş
  durumlar ikon + metinle kurulur.
- Varsayılan marka tipografik wordmark'tır ("KentTalep", Inter semibold);
  sahte belediye arması çizilmez. Gerçek belediye amblemi kurulumda
  settings üzerinden yüklenir (Faz 4).
- Uygulama ikonu: primary zemin üzerinde basit geometrik "K" harfi.

## Ekran desenleri
- Form etiketleri placeholder değil, alan üstünde kalıcıdır; hata metni
  alan altında danger renginde gösterilir.
- Kayıt: KVKK aydınlatma onayı zorunlu checkbox (metne bağlantı verilir).
- Şifre sıfırlama: e-posta → 6 haneli kod + yeni şifre; 60 sn
  tekrar-gönder sayacı.
- Talep oluşturma (3B): 3 adım — (1) konum: merkez-pin deseni +
  "Konumumu Kullan"; (2) kategori + fotoğraf: birincil aksiyon büyük
  "Fotoğraf Çek", galeri ikincil, en fazla 5 fotoğraf; (3) açıklama +
  gönder. Gönderim sırası: önce talep, sonra fotoğraflar tek tek; kısmi
  hatada "x/y yüklendi — Tekrar Dene" ekranı.
- Başarı ekranı: büyük talep numarası.
- Detay (3B): durum rozeti + dikey zaman çizelgesi; resolved durumunda
  "Sorun çözüldü mü?" kartı (Onayla ve Kapat / Sorun Devam Ediyor → not
  zorunlu modal). İptal yalnız pending|assigned'da, onay sheet'i ile.
- Hatalar: 429 "Çok sık denediniz, lütfen biraz sonra tekrar deneyin.";
  401'de oturum temizlenir ve girişe dönülür; çevrimdışıysa banner +
  tekrar dene; konum izni yoksa haritadan elle seçim.
- Animasyonlar 150-200 ms; reduce-motion tercihine saygılıdır.
