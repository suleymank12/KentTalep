import { describe, expect, it, jest } from '@jest/globals';
import { Text } from 'react-native';

import { api } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import { renderWithProviders } from '@/test-utils';
import { ThemeProvider } from '@/theme/provider';

// Fontlar ve splash native modülleri testte deterministik yapılır.
jest.mock('@expo-google-fonts/inter', () => ({
  useFonts: () => [true],
  Inter_400Regular: 'Inter_400Regular',
  Inter_500Medium: 'Inter_500Medium',
  Inter_600SemiBold: 'Inter_600SemiBold',
  Inter_700Bold: 'Inter_700Bold',
}));

jest.mock('expo-splash-screen', () => ({
  preventAutoHideAsync: jest.fn(),
  hideAsync: jest.fn(() => Promise.resolve()),
}));

describe('ThemeProvider', () => {
  it('renders children with the default theme when the settings request fails', async () => {
    jest.spyOn(api, 'get').mockRejectedValue(new Error('network down'));
    useAuthStore.setState({ status: 'guest', user: null, token: null });

    const { findByText } = await renderWithProviders(
      <ThemeProvider>
        <Text>hazır</Text>
      </ThemeProvider>,
    );

    expect(await findByText('hazır')).toBeTruthy();
  });
});
