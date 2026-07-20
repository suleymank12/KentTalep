# KentTalep Tasarım İlkeleri

Bu belge bağlayıcıdır. Faz 3 (vatandaş mobil) ve Faz 4 (admin panel)
ekranları, onaylı tasarım brief'i olmadan kodlanmaz.

## Kimlik ve white-label
- Ürün hiçbir belediyenin/kurumun rengine kilitlenmez. Belediye adı, logo,
  ana renk ve harita merkezi kurulum ayarlarından (settings) gelir.
- Tüm renkler semantic token üzerinden kullanılır (web: CSS variable,
  mobil: NativeWind theme). Bileşen içinde sabit hex yasak.
- Varsayılan (demo) tema: nötr, kurumsal bir sivil mavi/teal paleti.
- Kütüphanelerin varsayılan görünümü (ör. shadcn default) olduğu gibi
  kullanılmaz; tema token'ları projeye özel tanımlanır.

## Erişilebilirlik (kamu satışında argüman)
- Hedef: WCAG 2.2 AA. Kontrast ≥ 4.5:1, dokunma hedefi ≥ 44×44 px,
  taban yazı boyutu 16 px, sistem font ölçeklemesine saygı.
- Vatandaş uygulaması her yaş grubuna hitap eder: sade Türkçe,
  ikon + etiket birlikte, tek elle kullanılabilir yerleşim.

## Vatandaş uygulaması desenleri
- Talep oluşturma en fazla 3 adım: (1) konum, (2) kategori + fotoğraf,
  (3) açıklama + gönder.
- Fotoğraf-önce akış; durum takibi kargo-takip tarzı dikey zaman çizelgesi.

## Admin panel desenleri
- Dashboard satış vitrinidir: canlı harita (pin + yoğunluk), durum hunisi,
  kategori dağılımı, ortalama çözüm süresi kartları.
- Veri-yoğun ama sakin: az renk, net hiyerarşi, okunabilir tablolar.

## Tipografi ve içerik
- Yalnızca açık lisanslı ve tam Türkçe glif destekli fontlar kullanılır
  (varsayılan: Inter).
- Lorem ipsum yasak; tüm demo içerik gerçekçi Türkçedir (kategori, adres,
  isim örnekleri dahil).

## Responsive
- Mobil: flex tabanlı yerleşim, sabit kapsayıcı genişliği yok; 360dp
  telefondan tablete kırılmadan çalışır; tablette içerik en fazla 480dp
  sütunda ortalanır; safe-area zorunludur.
- Web (admin): masaüstü önceliklidir ancak 768px tablete kadar
  responsive'dir; tablolar dar ekranda yatay kaydırma ile kullanılabilir
  kalır.

## İkon ve amblem
- İkon seti Lucide'dir (web: lucide-react, mobil: lucide-react-native).
  Yapay zekâ ile üretilmiş ikon/amblem/illüstrasyon kullanılamaz.
- Varsayılan marka tipografik wordmark'tır; sahte belediye arması
  çizilmez. Gerçek belediye amblemi kurulum ayarlarından yüklenir.
