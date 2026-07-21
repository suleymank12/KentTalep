import {
  useInfiniteQuery,
  useMutation,
  useQuery,
  useQueryClient,
  type QueryClient,
} from '@tanstack/react-query';

import { api } from '@/lib/api';
import type { Category, Paginated, Ticket, TicketStatusLog } from '@/lib/types';

export const TICKETS_PER_PAGE = 20;

// Taleplerim listesinin filtre parametreleri. `status` tek değer ya da virgüllü
// liste olabilir (backend whereIn ile doğrular).
export type TicketFilters = {
  status?: string;
};

// —— Fetch'ler ——

async function fetchCategories(): Promise<Category[]> {
  const { data } = await api.get<{ data: Category[] }>('/categories');
  return data.data;
}

async function fetchTickets(filters: TicketFilters, page: number): Promise<Paginated<Ticket>> {
  const { data } = await api.get<Paginated<Ticket>>('/tickets', {
    params: { ...filters, per_page: TICKETS_PER_PAGE, page },
  });
  return data;
}

async function fetchTicket(id: number): Promise<Ticket> {
  const { data } = await api.get<{ data: Ticket }>(`/tickets/${id}`);
  return data.data;
}

async function fetchTicketLogs(id: number): Promise<TicketStatusLog[]> {
  const { data } = await api.get<{ data: TicketStatusLog[] }>(`/tickets/${id}/logs`);
  return data.data;
}

async function patchTransition(
  id: number,
  action: 'close' | 'reopen' | 'cancel',
  body: Record<string, string> = {},
): Promise<Ticket> {
  const { data } = await api.patch<{ data: Ticket }>(`/tickets/${id}/${action}`, body);
  return data.data;
}

// —— Query key'leri ——

export const ticketKeys = {
  categories: ['categories'] as const,
  lists: ['tickets'] as const,
  list: (filters: TicketFilters) => ['tickets', filters] as const,
  detail: (id: number) => ['ticket', id] as const,
  logs: (id: number) => ['ticket-logs', id] as const,
};

// Bir mutasyon sonrası ilgili talep + tüm listeleri + zaman çizelgesini tazeler.
function invalidateTicket(qc: QueryClient, id: number): void {
  void qc.invalidateQueries({ queryKey: ticketKeys.detail(id) });
  void qc.invalidateQueries({ queryKey: ticketKeys.logs(id) });
  void qc.invalidateQueries({ queryKey: ticketKeys.lists });
}

// —— Query hook'ları ——

export function useCategories() {
  return useQuery({
    queryKey: ticketKeys.categories,
    queryFn: fetchCategories,
    staleTime: 30 * 60 * 1000,
  });
}

export function useTickets(filters: TicketFilters) {
  return useInfiniteQuery({
    queryKey: ticketKeys.list(filters),
    queryFn: ({ pageParam }) => fetchTickets(filters, pageParam),
    initialPageParam: 1,
    getNextPageParam: (last) =>
      last.meta.current_page < last.meta.last_page ? last.meta.current_page + 1 : undefined,
  });
}

export function useTicket(id: number) {
  return useQuery({
    queryKey: ticketKeys.detail(id),
    queryFn: () => fetchTicket(id),
    enabled: Number.isFinite(id) && id > 0,
  });
}

export function useTicketLogs(id: number) {
  return useQuery({
    queryKey: ticketKeys.logs(id),
    queryFn: () => fetchTicketLogs(id),
    enabled: Number.isFinite(id) && id > 0,
  });
}

// —— Mutasyonlar ——

export function useCloseTicket(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => patchTransition(id, 'close'),
    onSuccess: () => invalidateTicket(qc, id),
  });
}

export function useReopenTicket(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (note: string) => patchTransition(id, 'reopen', { note }),
    onSuccess: () => invalidateTicket(qc, id),
  });
}

export function useCancelTicket(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (note?: string) =>
      patchTransition(id, 'cancel', note && note.trim() !== '' ? { note } : {}),
    onSuccess: () => invalidateTicket(qc, id),
  });
}
