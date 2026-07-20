import { PlusCircle } from 'lucide-react-native';
import { Text, View } from 'react-native';

import { Screen } from '@/components/ui';
import { colors } from '@/theme/colors';

// Faz 3A yer tutucusu — 3 adımlı talep oluşturma Faz 3B kapsamındadır.
export default function CreateTicketScreen() {
  return (
    <Screen scroll={false}>
      <View className="grow items-center justify-center gap-3">
        <PlusCircle color={colors.inkMuted} size={48} />
        <Text className="font-heading text-lg text-ink">Yeni Talep</Text>
        <Text className="text-center text-base text-ink-soft">
          Konum, kategori ve fotoğrafla talep oluşturma akışı yakında eklenecek.
        </Text>
      </View>
    </Screen>
  );
}
