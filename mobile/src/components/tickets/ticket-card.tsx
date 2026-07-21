import { createElement } from 'react';
import { Text, View, Pressable } from 'react-native';

import { Badge } from '@/components/ui';
import { relativeTime } from '@/lib/relative-time';
import { categoryIcon } from '@/lib/ticket-icons';
import type { Ticket } from '@/lib/types';
import { colors } from '@/theme/colors';

type Props = {
  ticket: Ticket;
  onPress: () => void;
};

/**
 * Taleplerim liste kartı: kategori ikonu + renk noktası, başlık (2 satır),
 * talep numarası + göreli zaman, durum rozeti. category.color backend
 * verisidir; runtime style ile kullanılır (sabit-hex yasağı kapsamı dışında).
 */
export function TicketCard({ ticket, onPress }: Props) {
  const accent = ticket.category?.color ?? colors.inkMuted;

  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      className="mb-3 flex-row gap-3 rounded-xl border border-border bg-surface p-4 active:opacity-70">
      <View className="h-11 w-11 items-center justify-center rounded-xl bg-surface-alt">
        {createElement(categoryIcon(ticket.category?.icon), { color: accent, size: 22 })}
      </View>

      <View className="flex-1 gap-1">
        <Text numberOfLines={2} className="font-heading text-base text-ink">
          {ticket.title}
        </Text>

        <View className="flex-row items-center gap-1.5">
          <View className="h-2 w-2 rounded-full" style={{ backgroundColor: accent }} />
          <Text className="text-xs text-ink-muted">{ticket.ticket_number}</Text>
          <Text className="text-xs text-ink-muted">·</Text>
          <Text className="text-xs text-ink-muted">{relativeTime(ticket.created_at)}</Text>
        </View>

        <View className="mt-1">
          <Badge status={ticket.status} />
        </View>
      </View>
    </Pressable>
  );
}
