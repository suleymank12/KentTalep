import { router } from 'expo-router';
import { Inbox } from 'lucide-react-native';
import { useMemo, useState } from 'react';
import { ActivityIndicator, FlatList, RefreshControl, Text, View } from 'react-native';

import { useTickets, type TicketFilters } from '@/api/tickets';
import { FilterChips } from '@/components/tickets/filter-chips';
import { ListMapToggle, type TicketsView } from '@/components/tickets/list-map-toggle';
import { TicketCard } from '@/components/tickets/ticket-card';
import { TicketListSkeleton } from '@/components/tickets/ticket-skeleton';
import { TicketsMap } from '@/components/tickets/tickets-map';
import { Button, Screen } from '@/components/ui';
import { TICKET_FILTER_CHIPS } from '@/lib/ticket-filters';
import type { Ticket } from '@/lib/types';
import { colors } from '@/theme/colors';

type TicketsQuery = ReturnType<typeof useTickets>;

function openTicket(id: number): void {
  router.push({ pathname: '/ticket/[id]', params: { id: String(id) } });
}

export default function MyTicketsScreen() {
  const [chipKey, setChipKey] = useState('all');
  const [view, setView] = useState<TicketsView>('list');

  const filters = useMemo<TicketFilters>(() => {
    const chip = TICKET_FILTER_CHIPS.find((c) => c.key === chipKey);
    return chip?.status ? { status: chip.status } : {};
  }, [chipKey]);

  const query = useTickets(filters);
  const tickets = query.data?.pages.flatMap((page) => page.data) ?? [];

  return (
    <Screen scroll={false}>
      {/* Üst bar iki satır: (1) tam genişlik Liste|Harita toggle, (2) altında
          yatay kayan filtre çipleri. Toggle satırının sağında ~48dp boşluk
          bırakılır: Expo dev client'ın yüzen dişli düğmesi (release'de yoktur)
          dev'de sağ üstte durur, toggle onun altında kalmasın. */}
      <View className="gap-2">
        <View className="pr-12">
          <ListMapToggle value={view} onChange={setView} />
        </View>
        <FilterChips activeKey={chipKey} onSelect={setChipKey} />
      </View>

      {view === 'map' ? (
        <View className="mt-3 flex-1">
          <TicketsMap tickets={tickets} onOpen={openTicket} />
        </View>
      ) : (
        <TicketList query={query} tickets={tickets} />
      )}
    </Screen>
  );
}

function EmptyState() {
  return (
    <View className="grow items-center justify-center gap-3 py-16">
      <Inbox color={colors.inkMuted} size={48} />
      <Text className="font-heading text-lg text-ink">Henüz talebiniz yok</Text>
      <Text className="text-center text-base text-ink-soft">
        Bir sorun bildirmek için &quot;Yeni Talep&quot; sekmesini kullanın.
      </Text>
    </View>
  );
}

function ListError({ onRetry }: { onRetry: () => void }) {
  return (
    <View className="grow items-center justify-center gap-4 py-16">
      <Text className="text-center text-base text-ink-soft">
        Talepler yüklenemedi. Bağlantınızı kontrol edip tekrar deneyin.
      </Text>
      <Button label="Tekrar Dene" variant="secondary" onPress={onRetry} />
    </View>
  );
}

function TicketList({ query, tickets }: { query: TicketsQuery; tickets: Ticket[] }) {
  if (query.isPending) {
    return <TicketListSkeleton />;
  }

  if (query.isError && tickets.length === 0) {
    return <ListError onRetry={() => void query.refetch()} />;
  }

  return (
    <FlatList
      data={tickets}
      keyExtractor={(item) => String(item.id)}
      renderItem={({ item }) => <TicketCard ticket={item} onPress={() => openTicket(item.id)} />}
      contentContainerStyle={{ paddingTop: 8, flexGrow: 1 }}
      showsVerticalScrollIndicator={false}
      ListEmptyComponent={<EmptyState />}
      onEndReachedThreshold={0.5}
      onEndReached={() => {
        if (query.hasNextPage && !query.isFetchingNextPage) {
          void query.fetchNextPage();
        }
      }}
      ListFooterComponent={
        query.isFetchingNextPage ? (
          <ActivityIndicator className="py-4" color={colors.primary} />
        ) : null
      }
      refreshControl={
        <RefreshControl
          refreshing={query.isRefetching && !query.isFetchingNextPage}
          onRefresh={() => void query.refetch()}
          tintColor={colors.primary}
        />
      }
    />
  );
}
