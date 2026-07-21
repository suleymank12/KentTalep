import { View } from 'react-native';

// İlk yükleme iskeleti: kart düzenini taklit eden nötr bloklar (5 satır).
function SkeletonCard() {
  return (
    <View className="mb-3 flex-row gap-3 rounded-xl border border-border bg-surface p-4">
      <View className="h-11 w-11 rounded-xl bg-surface-alt" />
      <View className="flex-1 gap-2">
        <View className="h-4 w-3/4 rounded bg-surface-alt" />
        <View className="h-3 w-1/2 rounded bg-surface-alt" />
        <View className="mt-1 h-6 w-24 rounded-full bg-surface-alt" />
      </View>
    </View>
  );
}

export function TicketListSkeleton({ count = 5 }: { count?: number }) {
  return (
    <View accessibilityLabel="Yükleniyor" className="pt-2">
      {Array.from({ length: count }).map((_, index) => (
        <SkeletonCard key={index} />
      ))}
    </View>
  );
}
