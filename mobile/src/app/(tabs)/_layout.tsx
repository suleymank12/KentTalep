import { Redirect, Tabs } from 'expo-router';
import { FileText, Plus, User } from 'lucide-react-native';

import { useAuthStore } from '@/stores/auth';
import { useSettingsStore } from '@/stores/settings';
import { colors } from '@/theme/colors';

export default function TabsLayout() {
  const status = useAuthStore((s) => s.status);
  const primary = useSettingsStore((s) => s.primaryColor);

  if (status !== 'authenticated') {
    return <Redirect href="/(auth)/login" />;
  }

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: primary,
        tabBarInactiveTintColor: colors.inkMuted,
      }}>
      <Tabs.Screen
        name="index"
        options={{ title: 'Taleplerim', tabBarIcon: ({ color, size }) => <FileText color={color} size={size} /> }}
      />
      <Tabs.Screen
        name="create"
        options={{ title: 'Yeni Talep', tabBarIcon: ({ color, size }) => <Plus color={color} size={size} /> }}
      />
      <Tabs.Screen
        name="profile"
        options={{ title: 'Profil', tabBarIcon: ({ color, size }) => <User color={color} size={size} /> }}
      />
    </Tabs>
  );
}
