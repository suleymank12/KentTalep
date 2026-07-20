import { router } from 'expo-router';
import { ChevronLeft } from 'lucide-react-native';
import { Pressable } from 'react-native';

import { colors } from '@/theme/colors';

// Boş yığında bile çıkış garantisi: geri gidilebiliyorsa geri git, aksi halde köke dön.
// Anchor'ın yetmediği restore/deep-link/push durumları için emniyet kemeridir.
export function HeaderBack() {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel="Geri"
      hitSlop={12}
      onPress={() => (router.canGoBack() ? router.back() : router.replace('/'))}
      className="-ml-1 h-11 w-11 items-center justify-center">
      <ChevronLeft size={26} color={colors.ink} />
    </Pressable>
  );
}
