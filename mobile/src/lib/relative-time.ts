// Türkçe göreli zaman. Yeni bağımlılık yok; ay adları elde tutulur ki
// Hermes/Intl farklılıklarından etkilenmesin. `now` parametresi testler için
// deterministiklik sağlar (varsayılan: şu an).
const MONTHS_TR = [
  'Oca',
  'Şub',
  'Mar',
  'Nis',
  'May',
  'Haz',
  'Tem',
  'Ağu',
  'Eyl',
  'Eki',
  'Kas',
  'Ara',
];

const MINUTE = 60;
const HOUR = 60 * MINUTE;
const DAY = 24 * HOUR;
const WEEK = 7 * DAY;

function shortDate(date: Date, now: Date): string {
  const base = `${date.getDate()} ${MONTHS_TR[date.getMonth()]}`;
  return date.getFullYear() === now.getFullYear() ? base : `${base} ${date.getFullYear()}`;
}

/**
 * ISO tarihini Türkçe göreli metne çevirir:
 * "az önce" · "5 dk önce" · "2 sa önce" · "3 gün önce"; bir haftadan sonrası
 * kısa tarih ("5 Tem", farklı yılsa "5 Tem 2025"). Geçersiz/boş girdi "" döner.
 */
export function relativeTime(iso: string | null | undefined, now: number = Date.now()): string {
  if (!iso) {
    return '';
  }

  const then = new Date(iso).getTime();
  if (Number.isNaN(then)) {
    return '';
  }

  // Gelecekteki (saat farkı vb.) tarihleri "az önce" kabul et.
  const seconds = Math.max(0, Math.floor((now - then) / 1000));

  if (seconds < MINUTE) {
    return 'az önce';
  }
  if (seconds < HOUR) {
    return `${Math.floor(seconds / MINUTE)} dk önce`;
  }
  if (seconds < DAY) {
    return `${Math.floor(seconds / HOUR)} sa önce`;
  }
  if (seconds < WEEK) {
    return `${Math.floor(seconds / DAY)} gün önce`;
  }

  return shortDate(new Date(then), new Date(now));
}
