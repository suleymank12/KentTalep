import { Text, View } from 'react-native';

import type { TicketStatus } from '@/lib/types';

export type { TicketStatus };

// Sınıf adları statik literaldir ki Tailwind içerik taraması token'ları üretsin.
const STATUS: Record<TicketStatus, { label: string; container: string; text: string }> = {
  pending: { label: 'Beklemede', container: 'bg-pending-bg', text: 'text-pending-fg' },
  assigned: { label: 'Atandı', container: 'bg-assigned-bg', text: 'text-assigned-fg' },
  in_progress: { label: 'Devam Ediyor', container: 'bg-inprogress-bg', text: 'text-inprogress-fg' },
  resolved: { label: 'Çözüldü', container: 'bg-resolved-bg', text: 'text-resolved-fg' },
  closed: { label: 'Kapatıldı', container: 'bg-closed-bg', text: 'text-closed-fg' },
  cancelled: { label: 'İptal Edildi', container: 'bg-cancelled-bg', text: 'text-cancelled-fg' },
  rejected: { label: 'Reddedildi', container: 'bg-rejected-bg', text: 'text-rejected-fg' },
};

export function Badge({ status }: { status: TicketStatus }) {
  const style = STATUS[status];

  return (
    <View className={`self-start rounded-full px-3 py-1 ${style.container}`}>
      <Text className={`font-medium text-sm ${style.text}`}>{style.label}</Text>
    </View>
  );
}
