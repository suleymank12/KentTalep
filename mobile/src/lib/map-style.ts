import type { StyleSpecification } from '@maplibre/maplibre-react-native';

// Zorunlu harita atıf metni (OSM raster tile'ları için).
export const MAP_ATTRIBUTION = '© OpenStreetMap katkıda bulunanlar';

// settings'ten gelen tek raster tile URL'inden inline MapLibre stil objesi üretir.
// Anahtar/hesap gerektirmez; kaynak white-label olarak kurulumda değiştirilir.
export function rasterMapStyle(tileUrl: string): StyleSpecification {
  return {
    version: 8,
    sources: {
      raster: {
        type: 'raster',
        tiles: [tileUrl],
        tileSize: 256,
        // OSM tile.openstreetmap.org yalnızca z19'a kadar kare servis eder.
        // maxzoom bildirilmezse MapLibre z20+ için var olmayan kareleri ister
        // ve "HTTP 400" ile döner. maxzoom: 19 → z19 üstünde tile İSTENMEZ,
        // MapLibre son seviyeyi büyüterek (overzoom) gösterir.
        maxzoom: 19,
        attribution: MAP_ATTRIBUTION,
      },
    },
    layers: [
      {
        id: 'raster',
        type: 'raster',
        source: 'raster',
      },
    ],
  };
}
