import {
  Inter_400Regular,
  Inter_500Medium,
  Inter_600SemiBold,
  Inter_700Bold,
  useFonts,
} from '@expo-google-fonts/inter';
import { useQuery } from '@tanstack/react-query';
import * as SplashScreen from 'expo-splash-screen';
import { useEffect, type ReactNode } from 'react';
import { View } from 'react-native';

import { api } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import { useSettingsStore } from '@/stores/settings';
import { themeVars } from '@/theme/vars';

void SplashScreen.preventAutoHideAsync();

/**
 * Fontlar, /api/settings ve auth hydrate tamamlanana kadar splash'i tutar;
 * primary rengini NativeWind CSS değişkeni olarak köke uygular.
 */
export function ThemeProvider({ children }: { children: ReactNode }) {
  const [fontsLoaded] = useFonts({
    Inter_400Regular,
    Inter_500Medium,
    Inter_600SemiBold,
    Inter_700Bold,
  });

  const applySettings = useSettingsStore((s) => s.apply);
  const primaryColor = useSettingsStore((s) => s.primaryColor);
  const authStatus = useAuthStore((s) => s.status);

  const settings = useQuery({
    queryKey: ['settings'],
    queryFn: async (): Promise<Record<string, string | null>> => (await api.get('/settings')).data,
  });

  useEffect(() => {
    if (settings.isSuccess) {
      applySettings(settings.data);
    }
  }, [settings.isSuccess, settings.data, applySettings]);

  // Ayarlar geldiğinde ya da (backend erişilemezse) hata verdiğinde devam et.
  const ready = fontsLoaded && (settings.isSuccess || settings.isError) && authStatus !== 'loading';

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
