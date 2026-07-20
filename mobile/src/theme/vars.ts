import { vars } from 'nativewind';

import { colors } from '@/theme/colors';

export const DEFAULT_PRIMARY = colors.primary;

/**
 * NativeWind CSS değişkeni üretir; kök View'a uygulanınca tüm `primary`
 * token'ları bu renge bağlanır (white-label runtime teması).
 */
export function themeVars(primaryColor?: string | null) {
  return vars({ '--color-primary': primaryColor || DEFAULT_PRIMARY });
}
