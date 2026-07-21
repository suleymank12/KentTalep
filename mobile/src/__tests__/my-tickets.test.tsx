import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import { fireEvent, waitFor } from '@testing-library/react-native';
import { router } from 'expo-router';

import { useTickets } from '@/api/tickets';
import MyTicketsScreen from '@/app/(tabs)/index';
import { MAP_ATTRIBUTION } from '@/lib/map-style';
import { renderWithProviders } from '@/test-utils';
import { makeTicket } from '@/test-fixtures';

jest.mock('@/api/tickets', () => ({
  useTickets: jest.fn(),
}));

const mockUseTickets = useTickets as unknown as jest.Mock;

function fakeQuery(tickets = [makeTicket()]) {
  return {
    data: { pages: [{ data: tickets, meta: { current_page: 1, last_page: 1, per_page: 20, total: tickets.length } }] },
    isPending: false,
    isError: false,
    isRefetching: false,
    isFetchingNextPage: false,
    hasNextPage: false,
    fetchNextPage: jest.fn(),
    refetch: jest.fn(),
  };
}

describe('MyTicketsScreen', () => {
  beforeEach(() => {
    mockUseTickets.mockReset();
    mockUseTickets.mockReturnValue(fakeQuery());
    (router.push as jest.Mock).mockReset();
  });

  it('renders a ticket card with its number and status badge', async () => {
    const { getByText } = await renderWithProviders(<MyTicketsScreen />);
    expect(getByText('2026-000123')).toBeTruthy();
    expect(getByText('Beklemede')).toBeTruthy();
  });

  it('changes the query status filter when a chip is selected', async () => {
    const { getByText } = await renderWithProviders(<MyTicketsScreen />);

    // Başlangıç: "Tümü" → filtre boş.
    expect(mockUseTickets).toHaveBeenLastCalledWith({});

    fireEvent.press(getByText('Çözüldü'));
    await waitFor(() => expect(mockUseTickets).toHaveBeenLastCalledWith({ status: 'resolved' }));

    fireEvent.press(getByText('Devam Eden'));
    await waitFor(() =>
      expect(mockUseTickets).toHaveBeenLastCalledWith({ status: 'pending,assigned,in_progress' }),
    );
  });

  it('switches to the map view showing the OSM attribution', async () => {
    const { getByText, queryByText } = await renderWithProviders(<MyTicketsScreen />);

    expect(queryByText(MAP_ATTRIBUTION)).toBeNull();

    fireEvent.press(getByText('Harita'));

    await waitFor(() => expect(getByText(MAP_ATTRIBUTION)).toBeTruthy());
  });

  it('opens the mini card when the map is pressed near a ticket, then navigates', async () => {
    const { getByText, getByLabelText, queryByText } = await renderWithProviders(
      <MyTicketsScreen />,
    );

    fireEvent.press(getByText('Harita'));
    await waitFor(() => expect(getByText(MAP_ATTRIBUTION)).toBeTruthy());

    // Harita görünümünde başlık yalnızca mini kart açıkken görünür.
    expect(queryByText('Sokak lambası yanmıyor')).toBeNull();

    // Talebin tam koordinatına basış (fixture: 39.925, 32.854) → mesafe 0,
    // her zoom'da eşik içinde → seçilir.
    fireEvent.press(getByLabelText('Harita yüzeyi'), {
      nativeEvent: { lngLat: [32.854, 39.925] },
    });
    await waitFor(() => expect(getByText('Sokak lambası yanmıyor')).toBeTruthy());

    fireEvent.press(getByText('Sokak lambası yanmıyor'));
    expect(router.push).toHaveBeenCalledWith({ pathname: '/ticket/[id]', params: { id: '1' } });
  });

  it('closes the mini card when the map is pressed away from any ticket', async () => {
    const { getByText, getByLabelText, queryByText } = await renderWithProviders(
      <MyTicketsScreen />,
    );

    fireEvent.press(getByText('Harita'));
    await waitFor(() => expect(getByText(MAP_ATTRIBUTION)).toBeTruthy());

    fireEvent.press(getByLabelText('Harita yüzeyi'), {
      nativeEvent: { lngLat: [32.854, 39.925] },
    });
    await waitFor(() => expect(getByText('Sokak lambası yanmıyor')).toBeTruthy());

    // Uzak bir noktaya basış (~1km) → nearestTicket null → kart kapanır.
    fireEvent.press(getByLabelText('Harita yüzeyi'), {
      nativeEvent: { lngLat: [32.9, 39.99] },
    });
    await waitFor(() => expect(queryByText('Sokak lambası yanmıyor')).toBeNull());
  });
});
