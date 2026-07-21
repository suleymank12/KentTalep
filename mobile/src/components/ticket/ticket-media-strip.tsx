import { Image } from 'expo-image';
import { X } from 'lucide-react-native';
import { useState } from 'react';
import { Modal, Pressable, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { absoluteMediaUrl } from '@/lib/api';
import type { TicketMedia } from '@/lib/types';
import { useAuthStore } from '@/stores/auth';
import { colors } from '@/theme/colors';

// Stream uç noktaları auth'ludur; expo-image source header'ında bearer token.
function authHeaders(token: string | null): Record<string, string> {
  return token ? { Authorization: `Bearer ${token}` } : {};
}

/**
 * Yatay medya thumb şeridi (expo-image, Authorization header'lı). Thumb'a
 * basınca tam ekran modalda asıl görsel açılır.
 */
export function TicketMediaStrip({ media }: { media: TicketMedia[] }) {
  const token = useAuthStore((s) => s.token);
  const [active, setActive] = useState<TicketMedia | null>(null);
  const headers = authHeaders(token);

  if (media.length === 0) {
    return null;
  }

  return (
    <View>
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={{ gap: 8 }}>
        {media.map((item) => (
          <Pressable
            key={item.id}
            accessibilityRole="imagebutton"
            accessibilityLabel={`${item.type_label} fotoğraf`}
            onPress={() => setActive(item)}>
            <Image
              source={{ uri: absoluteMediaUrl(item.thumb_url), headers }}
              style={{ width: 96, height: 96, borderRadius: 12 }}
              contentFit="cover"
              transition={150}
            />
          </Pressable>
        ))}
      </ScrollView>

      <Modal visible={active !== null} transparent animationType="fade" onRequestClose={() => setActive(null)}>
        <SafeAreaView className="flex-1 bg-black">
          <Pressable
            accessibilityRole="button"
            accessibilityLabel="Kapat"
            onPress={() => setActive(null)}
            className="absolute right-4 top-12 z-10 h-11 w-11 items-center justify-center rounded-full bg-black/60">
            <X size={24} color={colors.onPrimary} />
          </Pressable>
          {active ? (
            <Image
              source={{ uri: absoluteMediaUrl(active.url), headers }}
              style={{ flex: 1 }}
              contentFit="contain"
              transition={150}
            />
          ) : null}
        </SafeAreaView>
      </Modal>
    </View>
  );
}
