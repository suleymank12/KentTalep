import { describe, expect, it } from '@jest/globals';

import { TicketTimeline } from '@/components/ticket/ticket-timeline';
import { renderScreen } from '@/test-utils';
import { makeLog } from '@/test-fixtures';

describe('TicketTimeline', () => {
  it('labels the creation row (old_status null) as "Talep oluşturuldu"', async () => {
    const logs = [
      makeLog({ id: 2, old_status: 'assigned', new_status: 'in_progress', new_status_label: 'Devam Ediyor' }),
      makeLog({
        id: 1,
        old_status: null,
        old_status_label: null,
        new_status: 'pending',
        new_status_label: 'Beklemede',
        note: 'İlk kayıt',
      }),
    ];

    const { getByText, queryByText } = await renderScreen(<TicketTimeline logs={logs} />);

    expect(getByText('Talep oluşturuldu')).toBeTruthy();
    // Oluşturma satırında new_status_label ("Beklemede") etiket olarak KULLANILMAZ.
    expect(queryByText('Beklemede')).toBeNull();
    expect(getByText('Devam Ediyor')).toBeTruthy();
    // Not tırnak içinde gösterilir.
    expect(getByText('“İlk kayıt”')).toBeTruthy();
  });
});
