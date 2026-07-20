import { Inbox } from 'lucide-react-native';
import { Text, View } from 'react-native';

import { Screen } from '@/components/ui';
import { colors } from '@/theme/colors';

// Faz 3A yer tutucusu — Taleplerim listesi/harita Faz 3B kapsamındadır.
export default function MyTicketsScreen() {
  return (
    <Screen scroll={false}>
      <View className="grow items-center justify-center gap-3">
        <Inbox color={colors.inkMuted} size={48} />
        <Text className="font-heading text-lg text-ink">Henüz talebiniz yok</Text>
        <Text className="text-center text-base text-ink-soft">
          Bir sorun bildirmek için &quot;Yeni Talep&quot; sekmesini kullanın.
        </Text>
      </View>
    </Screen>
  );
}
