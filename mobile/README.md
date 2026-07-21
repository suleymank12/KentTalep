# KentTalep — Mobil (Vatandaş Uygulaması)

Expo SDK 57 (React Native 0.86, Expo Router) tabanlı vatandaş uygulaması.
NativeWind v4 + TanStack Query + Zustand + React Hook Form/Zod +
expo-secure-store. Tema ve harita `GET /api/settings`'ten (white-label)
beslenir; ikonlar Lucide'dir.

Faz 3A: tema/token sistemi, auth ekranları, sekme kabuğu ve Profil.
Faz 3B: Taleplerim (liste ⇄ MapLibre harita), talep detayı + zaman çizelgesi,
dev client geçişi. Talep oluşturma akışı Faz 3C'de gelir.

## Kurulum

Geliştirme artık **dev client** ile yapılır (Expo Go KULLANILMAZ; MapLibre native
modülü ve ileride push bildirimleri özel derleme gerektirir — bkz. ADR-25).

```bash
cd mobile
npm install
cp .env.example .env         # EXPO_PUBLIC_API_URL değerini ortamınıza göre ayarlayın
npx expo run:android         # İLK kurulum: dev client'ı derler (ilk build 10-20 dk)
```

Sonraki günlük akış:

```bash
npm start                    # Metro'yu başlatır; kurulu dev client'a bağlanır
```

İkonlar koddan üretilir (AI görsel yasağı — ADR-24):

```bash
npm run icons                # assets/images altındaki ikon/splash/favicon PNG'lerini üretir
```

Backend'in çalışıyor olması gerekir (bkz. kök README). API taban adresi:
- Android emülatör: `http://10.0.2.2:8000/api`
- iOS simülatör: `http://127.0.0.1:8000/api`
- Fiziksel cihaz: `http://<bilgisayar-LAN-IP>:8000/api`

## Komutlar

```bash
npm start          # Metro (dev client'a bağlanır)
npm run typecheck  # tsc --noEmit
npm run lint       # expo lint
npm test           # jest (jest-expo + @testing-library/react-native)
npm run icons      # uygulama ikonlarını koddan üret (sharp)
```
