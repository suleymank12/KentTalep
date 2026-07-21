import { List, Map as MapIcon } from 'lucide-react-native';
import { Text, View, Pressable } from 'react-native';

import { colors } from '@/theme/colors';

export type TicketsView = 'list' | 'map';

type Props = {
  value: TicketsView;
  onChange: (view: TicketsView) => void;
};

type Segment = { view: TicketsView; label: string; Icon: typeof List };

const SEGMENTS: Segment[] = [
  { view: 'list', label: 'Liste', Icon: List },
  { view: 'map', label: 'Harita', Icon: MapIcon },
];

// İki durumlu liste ⇄ harita segmenti (Lucide ikon + Türkçe etiket). Tam satır
// genişliğinde, iki eşit parçaya bölünür (her segment flex-1).
export function ListMapToggle({ value, onChange }: Props) {
  return (
    <View className="w-full flex-row rounded-xl border border-border bg-surface p-0.5">
      {SEGMENTS.map(({ view, label, Icon }) => {
        const active = view === value;
        return (
          <Pressable
            key={view}
            accessibilityRole="button"
            accessibilityState={{ selected: active }}
            accessibilityLabel={label}
            onPress={() => onChange(view)}
            className={`h-9 flex-1 flex-row items-center justify-center gap-1.5 rounded-lg px-3 ${
              active ? 'bg-primary' : ''
            }`}>
            <Icon size={16} color={active ? colors.onPrimary : colors.inkSoft} />
            <Text
              className={`font-medium text-sm ${active ? 'text-on-primary' : 'text-ink-soft'}`}>
              {label}
            </Text>
          </Pressable>
        );
      })}
    </View>
  );
}
