import { Camera, Map, Marker } from '@maplibre/maplibre-react-native';
import { MapPin } from 'lucide-react-native';
import { createElement, useMemo } from 'react';
import { Text, View } from 'react-native';

import { Badge } from '@/components/ui';
import { rasterMapStyle } from '@/lib/map-style';
import { categoryIcon } from '@/lib/ticket-icons';
import type { Ticket } from '@/lib/types';
import { useSettingsStore } from '@/stores/settings';
import { colors } from '@/theme/colors';

// Talep özeti: rozet, başlık, açıklama, kategori, konum ve tek marker'lı
// etkileşimi kapalı mini harita. category.color runtime style ile kullanılır.
export function TicketSummary({ ticket }: { ticket: Ticket }) {
  const tileUrl = useSettingsStore((s) => s.mapTileUrl);
  const mapStyle = useMemo(() => rasterMapStyle(tileUrl), [tileUrl]);

  const accent = ticket.category?.color ?? colors.inkMuted;
  const location =
    ticket.location_address ??
    `${ticket.latitude.toFixed(5)}, ${ticket.longitude.toFixed(5)}`;

  return (
    <View className="gap-4">
      <Badge status={ticket.status} />

      <Text className="font-title text-2xl text-ink">{ticket.title}</Text>

      {ticket.category ? (
        <View className="flex-row items-center gap-2">
          {createElement(categoryIcon(ticket.category.icon), { size: 18, color: accent })}
          <Text className="text-sm text-ink-soft">{ticket.category.name}</Text>
        </View>
      ) : null}

      <Text className="text-base text-ink-soft">{ticket.description}</Text>

      <View className="flex-row items-start gap-2">
        <MapPin size={18} color={colors.inkMuted} />
        <Text className="flex-1 text-sm text-ink-soft">{location}</Text>
      </View>

      <View className="h-44 overflow-hidden rounded-xl border border-border">
        <Map
          mapStyle={mapStyle}
          style={{ flex: 1 }}
          logo={false}
          attribution={false}
          compass={false}
          dragPan={false}
          touchZoom={false}
          doubleTapZoom={false}
          touchRotate={false}
          touchPitch={false}>
          <Camera center={[ticket.longitude, ticket.latitude]} zoom={15} />
          <Marker id={String(ticket.id)} lngLat={[ticket.longitude, ticket.latitude]}>
            <View
              className="h-5 w-5 rounded-full border-2 border-white"
              style={{ backgroundColor: accent }}
            />
          </Marker>
        </Map>
      </View>
    </View>
  );
}
