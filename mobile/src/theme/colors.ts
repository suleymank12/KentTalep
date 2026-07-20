// Imperatif renk değerleri (ActivityIndicator, ikon, placeholder gibi className
// alamayan yerler için tek kaynak). Sabit hex yalnız burada ve
// tailwind.config.js'de bulunur. primary varsayılandır; çalışma zamanı
// white-label rengi ise settings store'dan (primaryColor) okunur.
export const colors = {
  primary: '#0F766E',
  onPrimary: '#FFFFFF',
  ink: '#0F172A',
  inkSoft: '#475569',
  inkMuted: '#94A3B8',
  surface: '#FFFFFF',
  surfaceAlt: '#F8FAFC',
  border: '#E2E8F0',
  danger: '#DC2626',
} as const;
