import { isAxiosError } from 'axios';

/**
 * Genel (alan dışı) hata mesajı. 429 için özel Türkçe mesaj döner.
 */
export function errorMessage(error: unknown): string {
  if (isAxiosError(error)) {
    if (error.response?.status === 429) {
      return 'Çok sık denediniz, lütfen biraz sonra tekrar deneyin.';
    }
    const message = error.response?.data?.message;
    if (typeof message === 'string') {
      return message;
    }
  }
  return 'Bir hata oluştu. Lütfen tekrar deneyin.';
}

type SplitServerErrors = {
  fields: Record<string, string>;
  general: string | null;
};

/**
 * 422 doğrulama hatalarını forma göre ikiye ayırır:
 * - `knownFields` içindeki anahtarlar `fields`'a (alan altında gösterilir),
 * - eşleşmeyen ilk anahtarın ilk mesajı `general`'a (form bandında gösterilir).
 *
 * 422 dışı hatalarda `fields` boş, `general` = {@link errorMessage} olur.
 * Böylece login'in alan-dışı 'auth' hatası e-posta alanına düşmez.
 */
export function splitServerErrors(error: unknown, knownFields: string[]): SplitServerErrors {
  if (isAxiosError(error) && error.response?.status === 422) {
    const bag = error.response.data?.errors as Record<string, string[]> | undefined;
    const fields: Record<string, string> = {};
    let general: string | null = null;

    if (bag) {
      const known = new Set(knownFields);
      for (const [key, messages] of Object.entries(bag)) {
        if (known.has(key)) {
          fields[key] = messages[0];
        } else if (general === null) {
          general = messages[0];
        }
      }
    }

    // 422 ama ne bilinen alan ne de eşleşmeyen mesaj varsa generic'e düş.
    if (general === null && Object.keys(fields).length === 0) {
      general = errorMessage(error);
    }
    return { fields, general };
  }

  return { fields: {}, general: errorMessage(error) };
}
