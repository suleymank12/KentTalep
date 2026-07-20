import { describe, expect, it, jest } from '@jest/globals';
import { fireEvent, render } from '@testing-library/react-native';

import { Button } from '@/components/ui';

describe('Button', () => {
  it('shows the label and fires onPress when enabled', async () => {
    const onPress = jest.fn();
    const { getByText } = await render(<Button label="Kaydet" onPress={onPress} />);

    await fireEvent.press(getByText('Kaydet'));

    expect(onPress).toHaveBeenCalledTimes(1);
  });

  it('hides the label and does not fire onPress while loading', async () => {
    const onPress = jest.fn();
    const { queryByText, getByRole } = await render(
      <Button label="Kaydet" onPress={onPress} loading />
    );

    expect(queryByText('Kaydet')).toBeNull();

    await fireEvent.press(getByRole('button'));

    expect(onPress).not.toHaveBeenCalled();
  });

  it('does not fire onPress when disabled', async () => {
    const onPress = jest.fn();
    const { getByText } = await render(<Button label="Gönder" onPress={onPress} disabled />);

    await fireEvent.press(getByText('Gönder'));

    expect(onPress).not.toHaveBeenCalled();
  });
});
