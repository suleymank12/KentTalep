import { describe, expect, it } from '@jest/globals';

import { relativeTime } from '@/lib/relative-time';

// Sabit referans an: deterministik göreli metin için `now` parametresi verilir.
const NOW = new Date('2026-07-20T12:00:00Z').getTime();
const ago = (seconds: number) => new Date(NOW - seconds * 1000).toISOString();

describe('relativeTime', () => {
  it('returns "az önce" under a minute', () => {
    expect(relativeTime(ago(30), NOW)).toBe('az önce');
  });

  it('returns minutes', () => {
    expect(relativeTime(ago(5 * 60), NOW)).toBe('5 dk önce');
  });

  it('returns hours', () => {
    expect(relativeTime(ago(2 * 3600), NOW)).toBe('2 sa önce');
  });

  it('returns days under a week', () => {
    expect(relativeTime(ago(3 * 86400), NOW)).toBe('3 gün önce');
  });

  it('returns a short date beyond a week (same year, no year suffix)', () => {
    expect(relativeTime(ago(10 * 86400), NOW)).toBe('10 Tem');
  });

  it('includes the year for a different year', () => {
    expect(relativeTime('2025-01-01T12:00:00Z', NOW)).toBe('1 Oca 2025');
  });

  it('treats future timestamps as "az önce"', () => {
    expect(relativeTime(ago(-120), NOW)).toBe('az önce');
  });

  it('returns empty string for null or invalid input', () => {
    expect(relativeTime(null, NOW)).toBe('');
    expect(relativeTime(undefined, NOW)).toBe('');
    expect(relativeTime('not-a-date', NOW)).toBe('');
  });
});
