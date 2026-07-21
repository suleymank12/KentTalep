import { ScrollView, Text, Pressable } from 'react-native';

import { TICKET_FILTER_CHIPS } from '@/lib/ticket-filters';

type Props = {
  activeKey: string;
  onSelect: (key: string) => void;
};

/**
 * Taleplerim filtre çipleri. Aktif çip primary zeminli; diğerleri kenarlıklı.
 * Tam satır genişliğinde, yatay kaydırılır: çipler asla daralmaz (`shrink-0`) ve
 * etiket tek satırda kırpılmadan yazılır — 360dp'de de "Çözüldü" tam görünür.
 */
export function FilterChips({ activeKey, onSelect }: Props) {
  return (
    <ScrollView
      horizontal
      className="grow-0"
      showsHorizontalScrollIndicator={false}
      contentContainerStyle={{ gap: 8, paddingVertical: 4 }}>
      {TICKET_FILTER_CHIPS.map((chip) => {
        const active = chip.key === activeKey;
        return (
          <Pressable
            key={chip.key}
            accessibilityRole="button"
            accessibilityState={{ selected: active }}
            onPress={() => onSelect(chip.key)}
            className={`h-9 shrink-0 justify-center rounded-full px-4 ${
              active ? 'bg-primary' : 'border border-border bg-surface'
            }`}>
            <Text
              numberOfLines={1}
              className={`font-medium text-sm ${active ? 'text-on-primary' : 'text-ink-soft'}`}>
              {chip.label}
            </Text>
          </Pressable>
        );
      })}
    </ScrollView>
  );
}
