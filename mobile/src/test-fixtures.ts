import type { Ticket, TicketStatus, TicketStatusLog } from '@/lib/types';

// Testler için TicketResource şekline uygun örnek talep üretir.
export function makeTicket(overrides: Partial<Ticket> = {}): Ticket {
  return {
    id: 1,
    ticket_number: '2026-000123',
    title: 'Sokak lambası yanmıyor',
    description: 'İki gündür sokak lambası yanmıyor.',
    status: 'pending',
    status_label: 'Beklemede',
    priority: 'medium',
    priority_label: 'Orta',
    latitude: 39.925,
    longitude: 32.854,
    location_address: 'Örnek Mah. 1. Cad.',
    category: { id: 3, name: 'Sokak Aydınlatması', icon: 'lightbulb', color: '#CA8A04' },
    assignee: null,
    user: { id: 5, name: 'Vatandaş Test' },
    media: [],
    resolved_at: null,
    closed_at: null,
    created_at: '2026-07-20T09:00:00Z',
    ...overrides,
  };
}

export function makeLog(overrides: Partial<TicketStatusLog> = {}): TicketStatusLog {
  return {
    id: 1,
    old_status: 'pending' as TicketStatus,
    old_status_label: 'Beklemede',
    new_status: 'assigned',
    new_status_label: 'Atandı',
    note: null,
    changed_by: { id: 9, name: 'Personel Test' },
    created_at: '2026-07-20T10:00:00Z',
    ...overrides,
  };
}
