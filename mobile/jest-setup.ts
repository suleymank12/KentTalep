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
}));
