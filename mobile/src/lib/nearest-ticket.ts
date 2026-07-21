import type { Ticket } from '@/lib/types';

// Web Mercator'da zoom 0, ekvatorda çözünürlük (metre/piksel). Her zoom
// seviyesinde ikiye bölünür; enlemde cos ile daralır.
const METERS_PER_PIXEL_EQUATOR = 156543.03392;

// Basış toleransı (piksel): parmak ucu ~44dp; yarıçap olarak ~28px makul bir
// isabet alanı verir (görsel nokta 20dp olsa da yakın basış da yakalanır).
const TOUCH_SLOP_PX = 28;

const EARTH_RADIUS_M = 6_371_000;

function toRadians(degrees: number): number {
  return (degrees * Math.PI) / 180;
}

/**
 * Verilen enlem ve zoom için Web Mercator zemin çözünürlüğü (metre/piksel):
 * 156543.03392 * cos(lat) / 2^zoom.
 */
export function metersPerPixel(latitude: number, zoom: number): number {
  return (METERS_PER_PIXEL_EQUATOR * Math.cos(toRadians(latitude))) / 2 ** zoom;
}

// İki coğrafi nokta arası büyük-daire (haversine) mesafesi, metre.
function haversineMeters(
  lat1: number,
  lng1: number,
  lat2: number,
  lng2: number,
): number {
  const dLat = toRadians(lat2 - lat1);
  const dLng = toRadians(lng2 - lng1);
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * Math.sin(dLng / 2) ** 2;
  return 2 * EARTH_RADIUS_M * Math.asin(Math.min(1, Math.sqrt(a)));
}

/**
 * Haritada basılan noktaya en yakın talebi döndürür. Eşik, güncel zoom'dan
 * türetilen ~28px'in metre karşılığıdır; bu yarıçapın dışında aday yoksa null
 * döner (boşluğa basış → seçim kapanır). Native marker hit-test'e bağımlı
 * değildir: seçim tamamen JS'te, basış koordinatından hesaplanır.
 */
export function nearestTicket(
  tickets: Ticket[],
  pressLngLat: [longitude: number, latitude: number],
  zoom: number,
): Ticket | null {
  const [pressLng, pressLat] = pressLngLat;
  const thresholdMeters = metersPerPixel(pressLat, zoom) * TOUCH_SLOP_PX;

  let best: Ticket | null = null;
  let bestDistance = Number.POSITIVE_INFINITY;

  for (const ticket of tickets) {
    const distance = haversineMeters(pressLat, pressLng, ticket.latitude, ticket.longitude);
    if (distance < bestDistance) {
      bestDistance = distance;
      best = ticket;
    }
  }

  return best !== null && bestDistance <= thresholdMeters ? best : null;
}
