import { describe, expect, it, jest } from '@jest/globals';
import { act, fireEvent } from '@testing-library/react-native';

import { ResetForm } from '@/components/auth/reset-form';
import { renderScreen } from '@/test-utils';

// jest.mock, babel-plugin-jest-hoist ile import'ların üstüne taşınır.
jest.mock('@/lib/api', () => ({
  TOKEN_KEY: 'kenttalep_token',
  api: { post: jest.fn(() => Promise.resolve({ data: {} })) },
}));

describe('ResetForm', () => {
  // Regresyon: geri sayaç tik'leri (60 sn) kod alanının değerini silmemeli.
  it('keeps the typed code across countdown ticks', async () => {
    jest.useFakeTimers();
    const screen = await renderScreen(<ResetForm email="user@example.com" onInfo={() => {}} />);

    await act(async () => {
      fireEvent.changeText(screen.getByLabelText('Doğrulama Kodu'), '123');
    });
    expect(screen.getByLabelText('Doğrulama Kodu').props.value).toBe('123');

    // 3 saniyelik tik (sayaç 60 -> 57): kod alanı değişmemeli.
    act(() => {
      jest.advanceTimersByTime(3000);
    });

    expect(screen.getByLabelText('Doğrulama Kodu').props.value).toBe('123');
    jest.useRealTimers();
  });
});
