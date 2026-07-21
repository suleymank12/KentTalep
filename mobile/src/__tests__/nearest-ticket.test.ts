import { describe, expect, it } from '@jest/globals';

import { metersPerPixel, nearestTicket } from '@/lib/nearest-ticket';
import { makeTicket } from '@/test-fixtures';

// Fixture talebi Ankara'da (39.925, 32.854). Bu enlem/zoom'da eşik ~kaç metre?
const TICKET_LAT = 39.925;
const TICKET_LNG = 32.854;

describe('nearestTicket', () => {
  it('selects the ticket when the press lands within the touch threshold', () => {
    const ticket = makeTicket({ id: 7, latitude: TICKET_LAT, longitude: TICKET_LNG });
    // z18'de eşik ≈ metersPerPixel * 28 ≈ 0.46m/px * 28 ≈ 13m. 5m'lik kayma içeride.
    const nearLat = TICKET_LAT + 0.00004; // ~4.5m kuzey
    const result = nearestTicket([ticket], [TICKET_LNG, nearLat], 18);
    expect(result?.id).toBe(7);
  });

  it('returns null when the press is outside the threshold', () => {
    const ticket = makeTicket({ id: 7, latitude: TICKET_LAT, longitude: TICKET_LNG });
    // ~0.01° ≈ 1.1km uzak — hiçbir zoom'da eşiğe girmez.
    const result = nearestTicket([ticket], [TICKET_LNG, TICKET_LAT + 0.01], 18);
    expect(result).toBeNull();
  });

  it('picks the closest of two candidates within range', () => {
    const near = makeTicket({ id: 1, latitude: TICKET_LAT + 0.00002, longitude: TICKET_LNG });
    const far = makeTicket({ id: 2, latitude: TICKET_LAT + 0.00006, longitude: TICKET_LNG });
    const result = nearestTicket([far, near], [TICKET_LNG, TICKET_LAT], 18);
    expect(result?.id).toBe(1);
  });

  it('returns null for an empty ticket list', () => {
    expect(nearestTicket([], [TICKET_LNG, TICKET_LAT], 18)).toBeNull();
  });

  it('computes a larger meters-per-pixel at lower zoom', () => {
    // Her zoom seviyesi çözünürlüğü ikiye katlar: z17, z18'in iki katı m/px olmalı.
    expect(metersPerPixel(TICKET_LAT, 17)).toBeCloseTo(metersPerPixel(TICKET_LAT, 18) * 2, 5);
  });
});
