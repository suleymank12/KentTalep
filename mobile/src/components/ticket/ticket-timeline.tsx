import { Text, View } from 'react-native';

import { relativeTime } from '@/lib/relative-time';
import { CREATED_ICON, statusIcon } from '@/lib/ticket-icons';
import type { TicketStatusLog } from '@/lib/types';
import { colors } from '@/theme/colors';

/**
 * Dikey durum zaman çizelgesi (en yeni üstte). old_status null olan satır
 * (talebin ilk kaydı) "Talep oluşturuldu" etiketiyle gösterilir.
 */
export function TicketTimeline({ logs }: { logs: TicketStatusLog[] }) {
  if (logs.length === 0) {
    return null;
  }

  return (
    <View>
      {logs.map((log, index) => {
        const isCreation = log.old_status === null;
        const Icon = isCreation ? CREATED_ICON : statusIcon(log.new_status);
        const label = isCreation ? 'Talep oluşturuldu' : log.new_status_label;
        const isLast = index === logs.length - 1;

        return (
          <View key={log.id} className="flex-row gap-3">
            <View className="items-center">
              <View className="h-8 w-8 items-center justify-center rounded-full bg-surface-alt">
                <Icon size={16} color={colors.primary} />
              </View>
              {!isLast ? <View className="w-px flex-1 bg-border" /> : null}
            </View>

            <View className="flex-1 pb-5">
              <Text className="font-semibold text-sm text-ink">{label}</Text>
              {log.note ? (
                <Text className="mt-0.5 text-sm text-ink-soft">“{log.note}”</Text>
              ) : null}
              <View className="mt-1 flex-row items-center gap-2">
                {log.changed_by?.name ? (
                  <Text className="text-xs text-ink-muted">{log.changed_by.name}</Text>
                ) : null}
                <Text className="text-xs text-ink-muted">{relativeTime(log.created_at)}</Text>
              </View>
            </View>
          </View>
        );
      })}
    </View>
  );
}
