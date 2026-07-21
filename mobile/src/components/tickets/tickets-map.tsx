import {
  Camera,
  Map,
  Marker,
  type CameraRef,
} from '@maplibre/maplibre-react-native';
import type { NativeSyntheticEvent } from 'react-native';
import * as Location from 'expo-location';
import { LocateFixed } from 'lucide-react-native';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Text, View, Pressable } from 'react-native';

import { Badge } from '@/components/ui';
import { MAP_ATTRIBUTION, rasterMapStyle } from '@/lib/map-style';
import { nearestTicket } from '@/lib/nearest-ticket';
import type { Ticket } from '@/lib/types';
import { useSettingsStore } from '@/stores/settings';
import { colors } from '@/theme/colors';

// Map onPress ve onRegionDidChange event yükleri (kurulu paketin tip
// tanımlarından; @maplibre/maplibre-react-native 11.3.6). PressEvent.lngLat =
// [longitude, latitude]; ViewState.zoom = güncel zoom.
type MapPressEvent = NativeSyntheticEvent<{ lngLat: [number, number] }>;
type RegionChangeEvent = NativeSyntheticEvent<{ zoom: number }>;

type Props = {
  tickets: Ticket[];
  onOpen: (id: number) => void;
};

/**
 * Yüklü sayfalardaki taleplerin marker'larıyla MapLibre haritası. Stil, settings
 * store'daki tek raster tile URL'inden üretilir; merkez/zoom da settings'ten
 * gelir. Marker'a basınca mini kart → detaya gider. Zorunlu OSM atıf metni
 * altta gösterilir. "Konumuma Git" yalnız konum izni varsa görünür.
 *
 * Marker seçimi tamamen JS'te yapılır: native MarkerViewManager.findMarkerAtPoint
 * hit-test'i gerçek cihazda güvenilir tetiklenmediği için ona bağımlılık
 * kaldırıldı. Marker yalnızca görsel nokta çizer (onPress yok). Seçim, haritanın
 * kendi onPress'inden gelen coğrafi koordinatla `nearestTicket` üzerinden
 * hesaplanır; güncel zoom onRegionDidChange ile ref'te tutulur (onPress event'i
 * zoom taşımaz — kurulu sürümde PressEvent yalnızca lngLat/point içerir).
 */
export function TicketsMap({ tickets, onOpen }: Props) {
  const { mapCenterLat, mapCenterLng, mapZoom, mapTileUrl } = useSettingsStore();
  const cameraRef = useRef<CameraRef>(null);
  const zoomRef = useRef(mapZoom);
  const [selected, setSelected] = useState<Ticket | null>(null);
  const [canLocate, setCanLocate] = useState(false);

  const mapStyle = useMemo(() => rasterMapStyle(mapTileUrl), [mapTileUrl]);

  function handleMapPress(event: MapPressEvent) {
    const lngLat = event.nativeEvent?.lngLat;
    if (!lngLat) {
      setSelected(null);
      return;
    }
    setSelected(nearestTicket(tickets, lngLat, zoomRef.current));
  }

  useEffect(() => {
    let active = true;
    void Location.getForegroundPermissionsAsync().then((result) => {
      if (active) {
        setCanLocate(result.granted);
      }
    });
    return () => {
      active = false;
    };
  }, []);

  async function goToMyLocation() {
    let granted = canLocate;
    if (!granted) {
      const request = await Location.requestForegroundPermissionsAsync();
      granted = request.granted;
      setCanLocate(granted);
    }
    if (!granted) {
      return;
    }
    const position = await Location.getCurrentPositionAsync({});
    cameraRef.current?.flyTo({
      center: [position.coords.longitude, position.coords.latitude],
      zoom: 15,
      duration: 700,
    });
  }

  return (
    <View className="flex-1 overflow-hidden rounded-xl border border-border">
      <Map
        mapStyle={mapStyle}
        style={{ flex: 1 }}
        onPress={handleMapPress}
        onRegionDidChange={(event: RegionChangeEvent) => {
          const zoom = event.nativeEvent?.zoom;
          if (typeof zoom === 'number') {
            zoomRef.current = zoom;
          }
        }}>
        <Camera center={[mapCenterLng, mapCenterLat]} zoom={mapZoom} />
        {tickets.map((ticket) => (
          <Marker
            key={ticket.id}
            id={String(ticket.id)}
            lngLat={[ticket.longitude, ticket.latitude]}
            accessibilityLabel={`Talep ${ticket.ticket_number}`}>
            <View
              className="h-5 w-5 rounded-full border-2 border-white"
              style={{ backgroundColor: ticket.category?.color ?? colors.primary }}
            />
          </Marker>
        ))}
      </Map>

      {canLocate ? (
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Konumuma Git"
          onPress={goToMyLocation}
          className="absolute bottom-12 right-3 h-11 w-11 items-center justify-center rounded-full border border-border bg-surface">
          <LocateFixed size={20} color={colors.primary} />
        </Pressable>
      ) : null}

      {selected ? (
        <Pressable
          accessibilityRole="button"
          onPress={() => onOpen(selected.id)}
          className="absolute inset-x-3 bottom-12 flex-row items-center gap-3 rounded-xl border border-border bg-surface p-3 active:opacity-70">
          <View className="flex-1 gap-1">
            <Text className="text-xs text-ink-muted">{selected.ticket_number}</Text>
            <Text numberOfLines={1} className="font-heading text-sm text-ink">
              {selected.title}
            </Text>
          </View>
          <Badge status={selected.status} />
        </Pressable>
      ) : null}

      <View className="absolute bottom-1 left-2 rounded bg-surface/80 px-1.5 py-0.5">
        <Text className="text-[10px] text-ink-muted">{MAP_ATTRIBUTION}</Text>
      </View>
    </View>
  );
}
