import { router } from 'expo-router';
import { KeyRound, LogOut, ShieldCheck, UserPen } from 'lucide-react-native';
import type { ReactNode } from 'react';
import { Alert, Pressable, Text, View } from 'react-native';

import { Screen } from '@/components/ui';
import { ROLE_LABELS } from '@/lib/types';
import { useAuthStore } from '@/stores/auth';
import { colors } from '@/theme/colors';

function Row({
  icon,
  label,
  onPress,
  danger = false,
}: {
  icon: ReactNode;
  label: string;
  onPress: () => void;
  danger?: boolean;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      className="flex-row items-center gap-3 rounded-xl border border-border bg-surface p-4 active:bg-surface-alt">
      {icon}
      <Text className={`flex-1 text-base ${danger ? 'text-danger' : 'text-ink'}`}>{label}</Text>
    </Pressable>
  );
}

export default function ProfileScreen() {
  const user = useAuthStore((s) => s.user);
  const logout = useAuthStore((s) => s.logout);

  const confirmLogout = () => {
    Alert.alert('Çıkış Yap', 'Oturumu kapatmak istediğinize emin misiniz?', [
      { text: 'Vazgeç', style: 'cancel' },
      {
        text: 'Çıkış Yap',
        style: 'destructive',
        onPress: () => {
          void logout();
          router.replace('/(auth)/login');
        },
      },
    ]);
  };

  return (
    <Screen>
      <View className="gap-6">
        <View className="gap-1 rounded-xl border border-border bg-surface p-4">
          <Text className="font-heading text-lg text-ink">{user?.name}</Text>
          <Text className="text-base text-ink-soft">{user?.email}</Text>
          {user?.phone ? <Text className="text-base text-ink-soft">{user.phone}</Text> : null}
          <Text className="text-sm text-ink-muted">{ROLE_LABELS[user?.role ?? ''] ?? user?.role}</Text>
        </View>

        <View className="gap-3">
          <Row icon={<UserPen color={colors.ink} size={20} />} label="Bilgilerimi Düzenle" onPress={() => router.push('/edit-profile')} />
          <Row icon={<KeyRound color={colors.ink} size={20} />} label="Şifre Değiştir" onPress={() => router.push('/change-password')} />
          <Row icon={<ShieldCheck color={colors.ink} size={20} />} label="KVKK Aydınlatma Metni" onPress={() => router.push('/kvkk')} />
          <Row icon={<LogOut color={colors.danger} size={20} />} label="Çıkış Yap" danger onPress={confirmLogout} />
        </View>
      </View>
    </Screen>
  );
}
