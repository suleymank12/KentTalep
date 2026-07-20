import * as Device from 'expo-device';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';
import { create } from 'zustand';

import { api, TOKEN_KEY } from '@/lib/api';
import type { User } from '@/lib/types';

type RegisterPayload = {
  name: string;
  email: string;
  phone?: string;
  password: string;
  password_confirmation: string;
  kvkk_accepted: boolean;
};

type AuthState = {
  token: string | null;
  user: User | null;
  status: 'loading' | 'authenticated' | 'guest';
  hydrate: () => Promise<void>;
  login: (email: string, password: string) => Promise<void>;
  register: (payload: RegisterPayload) => Promise<void>;
  clearSession: () => Promise<void>;
  logout: () => Promise<void>;
  setUser: (user: User) => void;
};

function deviceMeta() {
  return {
    device_name: Device.modelName ?? 'Bilinmeyen Cihaz',
    platform: Platform.OS === 'ios' ? 'ios' : 'android',
  };
}

export const useAuthStore = create<AuthState>((set, get) => ({
  token: null,
  user: null,
  status: 'loading',

  hydrate: async () => {
    const token = await SecureStore.getItemAsync(TOKEN_KEY);
    if (!token) {
      set({ status: 'guest' });
      return;
    }
    try {
      const { data } = await api.get('/auth/me');
      set({ token, user: data.data, status: 'authenticated' });
    } catch {
      await get().clearSession();
    }
  },

  login: async (email, password) => {
    const { data } = await api.post('/auth/login', { email, password, ...deviceMeta() });
    await SecureStore.setItemAsync(TOKEN_KEY, data.token);
    set({ token: data.token, user: data.user, status: 'authenticated' });
  },

  register: async (payload) => {
    const { data } = await api.post('/auth/register', {
      ...payload,
      ...deviceMeta(),
    });
    await SecureStore.setItemAsync(TOKEN_KEY, data.token);
    set({ token: data.token, user: data.user, status: 'authenticated' });
  },

  // Yerel oturumu temizler: token silinir, state guest'e döner. HİÇBİR API
  // çağrısı yapmaz ve üst üste çağrılabilir (idempotent).
  clearSession: async () => {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    set({ token: null, user: null, status: 'guest' });
  },

  logout: async () => {
    try {
      await api.post('/auth/logout');
    } catch {
      // API çağrısı düşse bile yerel oturum aşağıda temizlenir.
    } finally {
      await get().clearSession();
    }
  },

  setUser: (user) => set({ user }),
}));
