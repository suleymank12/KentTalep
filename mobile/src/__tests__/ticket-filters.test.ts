import { describe, expect, it } from '@jest/globals';

import { TICKET_FILTER_CHIPS } from '@/lib/ticket-filters';

function statusFor(key: string): string | undefined {
  return TICKET_FILTER_CHIPS.find((chip) => chip.key === key)?.status;
}

describe('TICKET_FILTER_CHIPS', () => {
  it('maps each chip to the expected status list', () => {
    expect(statusFor('all')).toBeUndefined();
    expect(statusFor('active')).toBe('pending,assigned,in_progress');
    expect(statusFor('resolved')).toBe('resolved');
    expect(statusFor('closed')).toBe('closed,cancelled,rejected');
  });

  it('exposes exactly the four filter chips in order', () => {
    expect(TICKET_FILTER_CHIPS.map((chip) => chip.key)).toEqual([
      'all',
      'active',
      'resolved',
      'closed',
    ]);
  });
});
