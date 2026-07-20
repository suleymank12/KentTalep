import { describe, expect, it } from '@jest/globals';

import { splitServerErrors } from '@/lib/errors';

const axios422 = (errors: Record<string, string[]>) => ({
  isAxiosError: true,
  response: { status: 422, data: { errors } },
});
const axios429 = () => ({ isAxiosError: true, response: { status: 429, data: {} } });

describe('splitServerErrors', () => {
  it('maps known field keys into fields and leaves general null', () => {
    const result = splitServerErrors(axios422({ email: ['E-posta zaten alınmış'] }), [
      'email',
      'password',
    ]);

    expect(result.fields).toEqual({ email: 'E-posta zaten alınmış' });
    expect(result.general).toBeNull();
  });

  it('routes an unknown key (auth) to general, not to a field', () => {
    const result = splitServerErrors(axios422({ auth: ['E-posta veya şifre hatalı.'] }), [
      'email',
      'password',
    ]);

    expect(result.fields).toEqual({});
    expect(result.general).toBe('E-posta veya şifre hatalı.');
  });

  it('returns a Turkish general message for a 429 rate limit', () => {
    const result = splitServerErrors(axios429(), ['email', 'password']);

    expect(result.fields).toEqual({});
    expect(result.general).toBe('Çok sık denediniz, lütfen biraz sonra tekrar deneyin.');
  });
});
