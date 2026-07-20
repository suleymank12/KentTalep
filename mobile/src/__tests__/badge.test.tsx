import { describe, expect, it } from '@jest/globals';
import { render } from '@testing-library/react-native';

import { Badge, type TicketStatus } from '@/components/ui';

const CASES: [TicketStatus, string][] = [
  ['pending', 'Beklemede'],
  ['assigned', 'Atandı'],
  ['in_progress', 'Devam Ediyor'],
  ['resolved', 'Çözüldü'],
  ['closed', 'Kapatıldı'],
  ['cancelled', 'İptal Edildi'],
  ['rejected', 'Reddedildi'],
];

describe('Badge', () => {
  it.each(CASES)('renders the %s status with its Turkish label', async (status, label) => {
    const { getByText } = await render(<Badge status={status} />);
    expect(getByText(label)).toBeTruthy();
  });
});
