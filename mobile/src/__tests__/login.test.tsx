import { describe, expect, it, jest } from '@jest/globals';
import { act, fireEvent } from '@testing-library/react-native';

import LoginScreen from '@/app/(auth)/login';
import { renderScreen } from '@/test-utils';

// jest.mock, babel-plugin-jest-hoist ile import'ların üstüne taşınır.
jest.mock('@/lib/api', () => ({
  TOKEN_KEY: 'kenttalep_token',
  api: {
    post: jest.fn(() =>
      Promise.reject({
        isAxiosError: true,
        response: { status: 422, data: { errors: { auth: ['E-posta veya şifre hatalı.'] } } },
      })
    ),
  },
}));

// onSubmit -> login() -> api reject -> catch zincirini act içinde boşaltır.
const flush = () => new Promise((resolve) => setTimeout(resolve, 0));

describe('LoginScreen', () => {
  it('shows Turkish validation errors on empty submit', async () => {
    const screen = await renderScreen(<LoginScreen />);

    await act(async () => {
      fireEvent.press(screen.getByText('Giriş Yap'));
      await flush();
    });

    expect(screen.getByText('E-posta zorunludur')).toBeTruthy();
    expect(screen.getByText('Şifre zorunludur')).toBeTruthy();
  });

  it('shows the generic auth error in the band and does NOT flag the email field', async () => {
    const screen = await renderScreen(<LoginScreen />);

    await act(async () => {
      fireEvent.changeText(screen.getByLabelText('E-posta'), 'user@example.com');
      fireEvent.changeText(screen.getByLabelText('Şifre'), 'parola123');
    });
    await act(async () => {
      fireEvent.press(screen.getByText('Giriş Yap'));
      await flush();
    });

    // Genel hata bandı (accessibilityRole="alert") görünür.
    expect(screen.getByRole('alert').props.children).toBe('E-posta veya şifre hatalı.');
    // Mesaj YALNIZ bantta; e-posta alanının altında alan hatası olarak tekrarlanmaz.
    expect(screen.queryAllByText('E-posta veya şifre hatalı.')).toHaveLength(1);
  });
});
