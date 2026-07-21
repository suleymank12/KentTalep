// Taleplerim ekranındaki filtre çipleri ile backend status listesinin eşlemesi.
// Tek kaynak: çip → status (virgüllü liste). "Tümü" çipi status göndermez.
export type TicketFilterChip = {
  key: string;
  label: string;
  status?: string;
};

export const TICKET_FILTER_CHIPS: TicketFilterChip[] = [
  { key: 'all', label: 'Tümü' },
  { key: 'active', label: 'Devam Eden', status: 'pending,assigned,in_progress' },
  { key: 'resolved', label: 'Çözüldü', status: 'resolved' },
  { key: 'closed', label: 'Kapalı', status: 'closed,cancelled,rejected' },
];
