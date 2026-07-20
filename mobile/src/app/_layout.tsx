import '../global.css';

import { QueryClientProvider } from '@tanstack/react-query';
import { router, Stack } from 'expo-router';
import { useEffect } from 'react';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { SafeAreaProvider } from 'react-native-safe-area-context';

import { HeaderBack } from '@/components/ui';
import { setUnauthorizedHandler } from '@/lib/api';
import { queryClient } from '@/lib/query';
import { useAuthStore } from '@/stores/auth';
import { ThemeProvider } from '@/theme/provider';

// Anchor: /kvkk gibi rotalara doğrudan giriş (reload/restore/deep-link) sonrası
// index yığının altına oturur; native geri kendiliğinden çalışır.
export const unstable_settings = {
  initialRouteName: 'index',
};

export default function RootLayout() {
  const hydrate = useAuthStore((s) => s.hydrate);

  useEffect(() => {
    void hydrate();
    setUnauthorizedHandler(() => {
      // 401'de sunucuya tekrar istek atılmaz; yalnızca yerel oturum temizlenir.
      // Aksi halde geçersiz token'la logout POST'u yine 401 döner ve istek döngüsü oluşur.
      void useAuthStore.getState().clearSession();
      router.replace('/(auth)/login');
    });
  }, [hydrate]);

  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
      <SafeAreaProvider>
        <QueryClientProvider client={queryClient}>
          <ThemeProvider>
            <Stack screenOptions={{ headerShown: false }}>
              <Stack.Screen
                name="kvkk"
                options={{ headerShown: true, title: 'KVKK Aydınlatma Metni', headerLeft: () => <HeaderBack /> }}
              />
              <Stack.Screen
                name="edit-profile"
                options={{ headerShown: true, title: 'Bilgilerimi Düzenle', headerLeft: () => <HeaderBack /> }}
              />
              <Stack.Screen
                name="change-password"
                options={{ headerShown: true, title: 'Şifre Değiştir', headerLeft: () => <HeaderBack /> }}
              />
            </Stack>
          </ThemeProvider>
        </QueryClientProvider>
      </SafeAreaProvider>
    </GestureHandlerRootView>
  );
}
