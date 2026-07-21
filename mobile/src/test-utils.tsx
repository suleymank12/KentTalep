import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { render } from '@testing-library/react-native';
import type { ReactElement } from 'react';
import { SafeAreaProvider } from 'react-native-safe-area-context';

const METRICS = {
  frame: { x: 0, y: 0, width: 400, height: 800 },
  insets: { top: 0, left: 0, right: 0, bottom: 0 },
};

/**
 * Ekranları safe-area sağlayıcısıyla (sabit metriklerle) render eden test
 * yardımcısı. RNTL 14 render async'tir; çağıran await etmelidir.
 */
export function renderScreen(ui: ReactElement) {
  return render(<SafeAreaProvider initialMetrics={METRICS}>{ui}</SafeAreaProvider>);
}

/**
 * TanStack Query + safe-area sağlayıcılarıyla render eder. Testlerde retry
 * kapalıdır ki hata durumları anında (retry beklemeden) çözülsün.
 */
export function renderWithProviders(ui: ReactElement, client?: QueryClient) {
  const queryClient =
    client ?? new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });

  return render(
    <QueryClientProvider client={queryClient}>
      <SafeAreaProvider initialMetrics={METRICS}>{ui}</SafeAreaProvider>
    </QueryClientProvider>,
  );
}
