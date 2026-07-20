import { describe, expect, it, jest } from '@jest/globals';
import * as SecureStore from 'expo-secure-store';

import { api } from '@/lib/api';
import { useAuthStore } from '@/stores/auth';

// jest.mock, babel-plugin-jest-hoist ile import'ların üstüne taşınır.
jest.mock('@/lib/api', () => ({
  TOKEN_KEY: 'kenttalep_token',
  api: { post: jest.fn(), get: jest.fn() },
}));

type MockFn = {
  mockResolvedValue: (value: unknown) => void;
  mockRejectedValue: (value: unknown) => void;
  mockClear: () => void;
};

describe('auth store', () => {
  it('stores the token in secure storage and marks the session authenticated on login', async () => {
    (api.post as unknown as MockFn).mockResolvedValue({
      data: {
        token: 'tok-123',
        user: { id: 1, name: 'Ayşe', email: 'a@b.c', phone: null, role: 'citizen', is_active: true, created_at: null },
      },
    });

    await useAuthStore.getState().login('a@b.c', 'parola123');

    expect(await SecureStore.getItemAsync('kenttalep_token')).toBe('tok-123');
    expect(useAuthStore.getState().status).toBe('authenticated');
    expect(useAuthStore.getState().user?.name).toBe('Ayşe');
  });

  it('clears the token even when the logout request rejects', async () => {
    await SecureStore.setItemAsync('kenttalep_token', 'tok-abc');
    useAuthStore.setState({ token: 'tok-abc', user: null, status: 'authenticated' });
    (api.post as unknown as MockFn).mockRejectedValue(new Error('network'));

    await useAuthStore.getState().logout();

    expect(await SecureStore.getItemAsync('kenttalep_token')).toBeNull();
    expect(useAuthStore.getState().token).toBeNull();
    expect(useAuthStore.getState().status).toBe('guest');
  });

  it('clears the session locally without hitting the network', async () => {
    await SecureStore.setItemAsync('kenttalep_token', 'tok-local');
    useAuthStore.setState({ token: 'tok-local', user: null, status: 'authenticated' });
    (api.post as unknown as MockFn).mockClear();
    (api.get as unknown as MockFn).mockClear();

    await useAuthStore.getState().clearSession();

    expect(await SecureStore.getItemAsync('kenttalep_token')).toBeNull();
    expect(useAuthStore.getState().token).toBeNull();
    expect(useAuthStore.getState().status).toBe('guest');
    expect(api.post).not.toHaveBeenCalled();
    expect(api.get).not.toHaveBeenCalled();
  });
});
