# KentTalep — Mobil (Vatandaş Uygulaması)

Expo SDK 57 (React Native 0.86, Expo Router) tabanlı vatandaş uygulaması.
NativeWind v4 + TanStack Query + Zustand + React Hook Form/Zod +
expo-secure-store. Tema ve harita `GET /api/settings`'ten (white-label)
beslenir; ikonlar Lucide'dir.

Faz 3A kapsamı: tema/token sistemi, auth ekranları (giriş, kayıt, şifre
sıfırlama), sekme kabuğu ve Profil. Taleplerim listesi/harita ve talep
oluşturma Faz 3B'de gelir.

## Kurulum

```bash
cd mobile
npm install
cp .env.example .env   # EXPO_PUBLIC_API_URL değerini ortamınıza göre ayarlayın
npm start              # Expo dev server (a: Android, i: iOS, w: web)
```

Backend'in çalışıyor olması gerekir (bkz. kök README). API taban adresi:
- Android emülatör: `http://10.0.2.2:8000/api`
- iOS simülatör: `http://127.0.0.1:8000/api`
- Fiziksel cihaz: `http://<bilgisayar-LAN-IP>:8000/api`

## Komutlar

```bash
npm start          # Expo geliştirme sunucusu
npm run typecheck  # tsc --noEmit
npm run lint       # expo lint
npm test           # jest (jest-expo + @testing-library/react-native)
```
