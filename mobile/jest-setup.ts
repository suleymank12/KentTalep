import { jest } from '@jest/globals';

// Testlerde native secure-store'u bellek içi sahte ile değiştir.
jest.mock('expo-secure-store', () => {
  const store = new Map<string, string>();
  return {
    getItemAsync: jest.fn((key: string) => Promise.resolve(store.get(key) ?? null)),
    setItemAsync: jest.fn((key: string, value: string) => {
      store.set(key, value);
      return Promise.resolve();
    }),
    deleteItemAsync: jest.fn((key: string) => {
      store.delete(key);
      return Promise.resolve();
    }),
  };
});

// expo-device modelName'i deterministik yap.
jest.mock('expo-device', () => ({ modelName: 'Test Device' }));

// expo-router: Link çocuğu render eder, router no-op.
jest.mock('expo-router', () => ({
  Link: ({ children }: { children: unknown }) => children,
  router: { replace: jest.fn(), push: jest.fn(), back: jest.fn(), canGoBack: jest.fn(() => false) },
  Redirect: () => null,
  useLocalSearchParams: jest.fn(() => ({ id: '1' })),
}));

// MapLibre native modülü Jest'te yoktur. Camera/Marker çocuklarını olduğu gibi
// geçirir (Marker artık basış işlemez — seçim JS'te Map onPress ile yapılır).
// Map, "Harita yüzeyi" etiketli className'siz bir Pressable'a sarılır; testte
// koordinatlı event `fireEvent.press(el, { nativeEvent: { lngLat: [...] } })`
// ile verilir. Mock, gerçek native jest/dokunma boyutunu doğrulayamaz.
type MapMockProps = {
  children?: import('react').ReactNode;
  onPress?: (event: unknown) => void;
};

jest.mock('@maplibre/maplibre-react-native', () => {
  const react = jest.requireActual<typeof import('react')>('react');
  const rn = jest.requireActual<typeof import('react-native')>('react-native');

  const Passthrough = ({ children }: MapMockProps) => children ?? null;

  const Map = ({ children, onPress }: MapMockProps) =>
    react.createElement(
      rn.Pressable,
      { accessibilityRole: 'button', accessibilityLabel: 'Harita yüzeyi', onPress },
      children,
    );

  return { Map, Camera: Passthrough, Marker: Passthrough };
});

// expo-image: testte görsel decode edilmez.
jest.mock('expo-image', () => ({ Image: () => null }));

// expo-location: izin varsayılan olarak reddedilmiş (buton gizli kalır).
jest.mock('expo-location', () => ({
  getForegroundPermissionsAsync: jest.fn(() =>
    Promise.resolve({ granted: false, status: 'undetermined', canAskAgain: true }),
  ),
  requestForegroundPermissionsAsync: jest.fn(() =>
    Promise.resolve({ granted: false, status: 'denied', canAskAgain: false }),
  ),
  getCurrentPositionAsync: jest.fn(() =>
    Promise.resolve({ coords: { latitude: 39.9, longitude: 32.8 } }),
  ),
}));
