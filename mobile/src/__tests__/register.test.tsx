import { describe, expect, it } from '@jest/globals';
import { fireEvent, waitFor } from '@testing-library/react-native';

import RegisterScreen from '@/app/(auth)/register';
import { renderScreen } from '@/test-utils';

describe('RegisterScreen', () => {
  it('blocks registration when KVKK consent is not given', async () => {
    const { getByText } = await renderScreen(<RegisterScreen />);

    await fireEvent.press(getByText('Kayıt Ol'));

    await waitFor(() => {
      expect(getByText('KVKK aydınlatma metnini onaylamalısınız')).toBeTruthy();
    });
  });
});
