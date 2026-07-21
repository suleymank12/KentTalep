import { useLocalSearchParams } from 'expo-router';
import { ScrollView, Text, View } from 'react-native';

import { useTicket, useTicketLogs } from '@/api/tickets';
import { TicketActions } from '@/components/ticket/ticket-actions';
import { TicketMediaStrip } from '@/components/ticket/ticket-media-strip';
import { TicketSummary } from '@/components/ticket/ticket-summary';
import { TicketTimeline } from '@/components/ticket/ticket-timeline';
import { Button, HeaderBack, Screen } from '@/components/ui';
import type { Ticket, TicketStatusLog } from '@/lib/types';

export default function TicketDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const ticketId = Number(id);

  const ticketQuery = useTicket(ticketId);
  const logsQuery = useTicketLogs(ticketId);

  return (
    <Screen scroll={false}>
      <View className="flex-row items-center gap-1 pb-2">
        <HeaderBack />
        <Text className="font-heading text-lg text-ink">
          {ticketQuery.data?.ticket_number ?? 'Talep'}
        </Text>
      </View>

      {ticketQuery.isPending ? (
        <DetailSkeleton />
      ) : ticketQuery.isError ? (
        <DetailError
          onRetry={() => {
            void ticketQuery.refetch();
            void logsQuery.refetch();
          }}
        />
      ) : (
        <DetailBody ticket={ticketQuery.data} logs={logsQuery.data ?? []} />
      )}
    </Screen>
  );
}

function DetailBody({ ticket, logs }: { ticket: Ticket; logs: TicketStatusLog[] }) {
  return (
    <ScrollView
      className="flex-1"
      contentContainerStyle={{ paddingBottom: 32, gap: 20 }}
      showsVerticalScrollIndicator={false}>
      <TicketSummary ticket={ticket} />

      {ticket.media && ticket.media.length > 0 ? (
        <View className="gap-3">
          <Text className="font-heading text-base text-ink">Fotoğraflar</Text>
          <TicketMediaStrip media={ticket.media} />
        </View>
      ) : null}

      <TicketActions ticket={ticket} />

      <View className="gap-3">
        <Text className="font-heading text-base text-ink">Durum Geçmişi</Text>
        <TicketTimeline logs={logs} />
      </View>
    </ScrollView>
  );
}

function DetailSkeleton() {
  return (
    <View accessibilityLabel="Yükleniyor" className="gap-4 pt-2">
      <View className="h-6 w-24 rounded-full bg-surface-alt" />
      <View className="h-7 w-3/4 rounded bg-surface-alt" />
      <View className="h-4 w-full rounded bg-surface-alt" />
      <View className="h-4 w-2/3 rounded bg-surface-alt" />
      <View className="h-44 w-full rounded-xl bg-surface-alt" />
    </View>
  );
}

function DetailError({ onRetry }: { onRetry: () => void }) {
  return (
    <View className="grow items-center justify-center gap-4">
      <Text className="text-center text-base text-ink-soft">
        Talep yüklenemedi. Lütfen tekrar deneyin.
      </Text>
      <Button label="Tekrar Dene" variant="secondary" onPress={onRetry} />
    </View>
  );
}
