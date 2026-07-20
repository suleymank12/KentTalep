import { create } from 'zustand';

import { DEFAULT_PRIMARY } from '@/theme/vars';

const DEFAULT_TILE = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';

type SettingsState = {
  municipalityName: string;
  primaryColor: string;
  mapCenterLat: number;
  mapCenterLng: number;
  mapZoom: number;
  mapTileUrl: string;
  apply: (raw: Record<string, string | null>) => void;
};

export const useSettingsStore = create<SettingsState>((set) => ({
  municipalityName: 'KentTalep',
  primaryColor: DEFAULT_PRIMARY,
  mapCenterLat: 39.925,
  mapCenterLng: 32.854,
  mapZoom: 13,
  mapTileUrl: DEFAULT_TILE,
  apply: (raw) =>
    set({
      municipalityName: raw.municipality_name ?? 'KentTalep',
      primaryColor: raw.primary_color ?? DEFAULT_PRIMARY,
      mapCenterLat: Number(raw.map_center_lat ?? 39.925),
      mapCenterLng: Number(raw.map_center_lng ?? 32.854),
      mapZoom: Number(raw.map_zoom ?? 13),
      mapTileUrl: raw.map_tile_url ?? DEFAULT_TILE,
    }),
}));
