import { create, isAxiosError } from 'axios';
import * as SecureStore from 'expo-secure-store';

export const TOKEN_KEY = 'kenttalep_token';

// API kökü (baseURL'den "/api" soneki çıkarılır); medya yolları "/api/..."
// ile başladığı için mutlak adres kök + yol ile kurulur.
const API_ORIGIN = (process.env.EXPO_PUBLIC_API_URL ?? '').replace(/\/api\/?$/, '');

/**
 * TicketMedia'nın göreli url/thumb_url yolunu mutlak adrese çevirir.
 */
export function absoluteMediaUrl(path: string): string {
  return `${API_ORIGIN}${path}`;
}

export const api = create({
  baseURL: process.env.EXPO_PUBLIC_API_URL,
  headers: { Accept: 'application/json' },
});

// İstek: secure-store'daki token'ı Bearer olarak ekle (token asla loglanmaz).
api.interceptors.request.use(async (config) => {
  const token = await SecureStore.getItemAsync(TOKEN_KEY);
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

let onUnauthorized: (() => void) | null = null;

export function setUnauthorizedHandler(handler: () => void): void {
  onUnauthorized = handler;
}

// Yanıt: 401'de oturum temizlenir ve girişe yönlendirilir (handler root'ta set edilir).
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (isAxiosError(error) && error.response?.status === 401) {
      onUnauthorized?.();
    }
    return Promise.reject(error);
  }
);
