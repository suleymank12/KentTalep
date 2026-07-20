import { describe, expect, it } from '@jest/globals';
import { fireEvent, render } from '@testing-library/react-native';
import { router } from 'expo-router';

import { HeaderBack } from '@/components/ui';

type CanGoBackMock = { mockReturnValue: (value: boolean) => void };
type ResetMock = { mockClear: () => void };

describe('HeaderBack', () => {
  it('goes back when the stack can go back', async () => {
    (router.canGoBack as unknown as CanGoBackMock).mockReturnValue(true);
    (router.back as unknown as ResetMock).mockClear();

    const { getByLabelText } = await render(<HeaderBack />);
    await fireEvent.press(getByLabelText('Geri'));

    expect(router.back).toHaveBeenCalledTimes(1);
  });

  it('replaces with the root anchor when the stack cannot go back', async () => {
    (router.canGoBack as unknown as CanGoBackMock).mockReturnValue(false);
    (router.replace as unknown as ResetMock).mockClear();

    const { getByLabelText } = await render(<HeaderBack />);
    await fireEvent.press(getByLabelText('Geri'));

    expect(router.replace).toHaveBeenCalledWith('/');
  });
});
