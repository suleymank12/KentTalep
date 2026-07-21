import { afterEach, beforeEach, describe, expect, it, jest } from '@jest/globals';
import { act, fireEvent, waitFor } from '@testing-library/react-native';

import { TicketActions } from '@/components/ticket/ticket-actions';
import { api } from '@/lib/api';
import { renderWithProviders } from '@/test-utils';
import { useAuthStore } from '@/stores/auth';
import { makeTicket } from '@/test-fixtures';

describe('TicketActions', () => {
  beforeEach(() => {
    useAuthStore.setState({
      user: { id: 5, name: 'V', email: 'v@x.com', phone: null, role: 'citizen', is_active: true, created_at: null },
      token: 'token',
      status: 'authenticated',
    });
    jest.spyOn(api, 'patch').mockResolvedValue({ data: { data: makeTicket() } } as never);
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it('shows the resolution card for a resolved ticket (owner) and no cancel', async () => {
    const { getByText, queryByText } = await renderWithProviders(
      <TicketActions ticket={makeTicket({ status: 'resolved' })} />,
    );
    expect(getByText('Sorun çözüldü mü?')).toBeTruthy();
    expect(queryByText('Talebi İptal Et')).toBeNull();
  });

  it('shows the cancel action for a pending ticket and no resolution card', async () => {
    const { getByText, queryByText } = await renderWithProviders(
      <TicketActions ticket={makeTicket({ status: 'pending' })} />,
    );
    expect(getByText('Talebi İptal Et')).toBeTruthy();
    expect(queryByText('Sorun çözüldü mü?')).toBeNull();
  });

  it('renders no actions for a closed ticket', async () => {
    const { queryByText } = await renderWithProviders(
      <TicketActions ticket={makeTicket({ status: 'closed' })} />,
    );
    expect(queryByText('Sorun çözüldü mü?')).toBeNull();
    expect(queryByText('Talebi İptal Et')).toBeNull();
  });

  it('rejects an empty reopen note and does not call the mutation', async () => {
    const patchSpy = api.patch as unknown as jest.Mock;
    const { getByText, findByText } = await renderWithProviders(
      <TicketActions ticket={makeTicket({ status: 'resolved' })} />,
    );

    fireEvent.press(getByText('Sorun Devam Ediyor'));
    const submit = await findByText('Gönder');
    fireEvent.press(submit);

    expect(await findByText('Lütfen sorunun neden devam ettiğini yazın.')).toBeTruthy();
    expect(patchSpy).not.toHaveBeenCalled();
  });

  it('calls the reopen mutation with the note when provided', async () => {
    const patchSpy = api.patch as unknown as jest.Mock;
    const { getByText, findByLabelText } = await renderWithProviders(
      <TicketActions ticket={makeTicket({ id: 1, status: 'resolved' })} />,
    );

    fireEvent.press(getByText('Sorun Devam Ediyor'));
    const input = await findByLabelText('Açıklama');
    // Her etkileşimi ayrı act ile tam olarak flush et: aksi halde changeText'in
    // state güncellemesi, basılan "Gönder"in closure'ına yansımadan press olur.
    await act(async () => {
      fireEvent.changeText(input, 'Sorun hâlâ sürüyor');
    });
    await act(async () => {
      fireEvent.press(getByText('Gönder'));
    });

    await waitFor(() =>
      expect(patchSpy).toHaveBeenCalledWith('/tickets/1/reopen', { note: 'Sorun hâlâ sürüyor' }),
    );
  });
});
