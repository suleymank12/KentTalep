import {
  Inter_400Regular,
  Inter_500Medium,
  Inter_600SemiBold,
  Inter_700Bold,
  useFonts,
} from '@expo-google-fonts/inter';
import { useQuery } from '@tanstack/react-query';
import * as SplashScreen from 'expo-splash-screen';
import { useEffect, useState, type ReactNode } from 'react';
import { View } from 'react-native';

import { api } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import { useSettingsStore } from '@/stores/settings';
import { themeVars } from '@/theme/vars';

void SplashScreen.preventAutoHideAsync();

// Ayarların çözülmesi için üst sınır: hem axios isteği hem de splash emniyet
// zamanlayıcısı bu süreyi kullanır. Kullanıcı hiçbir koşulda süresiz splash'te
// kalmaz — süre dolarsa varsayılan temayla açılır, ayarlar arka planda tazelenir.
const SETTINGS_TIMEOUT_MS = 5000;

/**
 * Fontlar, /api/settings ve auth hydrate tamamlanana kadar splash'i tutar;
 * primary rengini NativeWind CSS değişkeni olarak köke uygular. Ayarlar 5 sn
 * içinde gelmezse (zaman aşımı/hata) varsayılan temayla açılır ve TanStack
 * retry + refetchOnReconnect ile arka planda tazelenir.
 */
export function ThemeProvider({ children }: { children: ReactNode }) {
  const [fontsLoaded] = useFonts({
    Inter_400Regular,
    Inter_500Medium,
    Inter_600SemiBold,
    Inter_700Bold,
  });

  const [timedOut, setTimedOut] = useState(false);

  const applySettings = useSettingsStore((s) => s.apply);
  const primaryColor = useSettingsStore((s) => s.primaryColor);
  const authStatus = useAuthStore((s) => s.status);

  // retry global queryClient'tan (retry: 1) miras alınır; refetchOnReconnect ile
  // ağ dönünce arka planda tazelenir. Splash bunların hiçbirini beklemez (5 sn
  // emniyet zamanlayıcısı + isError yolu).
  const settings = useQuery({
    queryKey: ['settings'],
    queryFn: async (): Promise<Record<string, string | null>> =>
      (await api.get('/settings', { timeout: SETTINGS_TIMEOUT_MS })).data,
    refetchOnReconnect: true,
    staleTime: 5 * 60 * 1000,
  });

  useEffect(() => {
    if (settings.isSuccess) {
      applySettings(settings.data);
    }
  }, [settings.isSuccess, settings.data, applySettings]);

  // Emniyet kemeri: retry sürerken bile 5 sn sonunda splash mutlaka kalkar.
  useEffect(() => {
    const timer = setTimeout(() => setTimedOut(true), SETTINGS_TIMEOUT_MS);
    return () => clearTimeout(timer);
  }, []);

  // Ayarlar geldiğinde, hata verdiğinde ya da süre dolduğunda devam et.
  const settingsSettled = settings.isSuccess || settings.isError || timedOut;
  const ready = fontsLoaded && settingsSettled && authStatus !== 'loading';

  useEffect(() => {
    if (ready) {
      void SplashScreen.hideAsync();
    }
  }, [ready]);

  if (!ready) {
    return null;
  }

  return (
    <View style={themeVars(primaryColor)} className="flex-1">
      {children}
    </View>
  );
}
