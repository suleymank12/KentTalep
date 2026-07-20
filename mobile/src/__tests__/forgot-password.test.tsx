import { describe, expect, it, jest } from '@jest/globals';
import { fireEvent, waitFor } from '@testing-library/react-native';

import ForgotPasswordScreen from '@/app/(auth)/forgot-password';
import { renderScreen } from '@/test-utils';

// jest.mock, babel-plugin-jest-hoist ile import'ların üstüne taşınır.
jest.mock('@/lib/api', () => ({
  TOKEN_KEY: 'kenttalep_token',
  api: { post: jest.fn(() => Promise.resolve({ data: {} })) },
}));

describe('ForgotPasswordScreen', () => {
  it('moves from the email stage to the code stage after sending a code', async () => {
    const { getByText, getByLabelText } = await renderScreen(<ForgotPasswordScreen />);

    await fireEvent.changeText(getByLabelText('E-posta'), 'user@example.com');
    await fireEvent.press(getByText('Kod Gönder'));

    await waitFor(() => {
      expect(getByLabelText('Doğrulama Kodu')).toBeTruthy();
      expect(getByText('Şifreyi Sıfırla')).toBeTruthy();
    });
  });
});
