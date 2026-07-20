import { describe, expect, it } from '@jest/globals';
import { fireEvent } from '@testing-library/react-native';

import { Input } from '@/components/ui';
import { renderScreen } from '@/test-utils';

describe('Input secureToggle', () => {
  it('starts masked, toggles visibility, and never touches the value', async () => {
    const screen = await renderScreen(
      <Input label="Şifre" secureToggle value="gizli123" onChangeText={() => {}} />
    );

    // Varsayılan: gizli.
    expect(screen.getByLabelText('Şifre').props.secureTextEntry).toBe(true);

    // "Şifreyi göster" -> maske kalkar, değer değişmez.
    await fireEvent.press(screen.getByLabelText('Şifreyi göster'));
    expect(screen.getByLabelText('Şifre').props.secureTextEntry).toBe(false);
    expect(screen.getByLabelText('Şifre').props.value).toBe('gizli123');

    // "Şifreyi gizle" -> tekrar maskelenir.
    await fireEvent.press(screen.getByLabelText('Şifreyi gizle'));
    expect(screen.getByLabelText('Şifre').props.secureTextEntry).toBe(true);
    expect(screen.getByLabelText('Şifre').props.value).toBe('gizli123');
  });
});
