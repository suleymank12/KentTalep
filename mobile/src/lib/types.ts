export type User = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: string;
  is_active: boolean;
  created_at: string | null;
};

export const ROLE_LABELS: Record<string, string> = {
  citizen: 'Vatandaş',
  staff: 'Personel',
  manager: 'Yönetici',
  admin: 'Admin',
};

// Backend enum'larıyla birebir (App\Enums\TicketStatus / TicketPriority).
export type TicketStatus =
  | 'pending'
  | 'assigned'
  | 'in_progress'
  | 'resolved'
  | 'closed'
  | 'cancelled'
  | 'rejected';

export type TicketPriority = 'low' | 'medium' | 'high';

export type TicketMediaKind = 'before' | 'after';

// CategoryResource ile birebir.
export type Category = {
  id: number;
  name: string;
  icon: string | null;
  color: string | null;
  parent_id: number | null;
  sort_order: number;
  children?: Category[];
};

// TicketResource "category" alt kümesi (whenLoaded).
export type TicketCategory = {
  id: number;
  name: string;
  icon: string | null;
  color: string | null;
};

export type PersonRef = {
  id: number;
  name: string;
};

// TicketMediaResource ile birebir. url/thumb_url API'ye göreli yollardır
// (ör. "/api/ticket-media/12"); mutlak adres için baseURL ile birleştirilir.
export type TicketMedia = {
  id: number;
  type: TicketMediaKind;
  type_label: string;
  url: string;
  thumb_url: string;
  width: number | null;
  height: number | null;
  created_at: string | null;
};

// TicketStatusLogResource ile birebir. old_status ilk kayıtta null olur
// (talebin oluşturulması). changed_by yalnız yüklenmişse gelir.
export type TicketStatusLog = {
  id: number;
  old_status: TicketStatus | null;
  old_status_label: string | null;
  new_status: TicketStatus;
  new_status_label: string;
  note: string | null;
  changed_by?: PersonRef | null;
  created_at: string | null;
};

// TicketResource ile birebir. İlişkiler backend'de whenLoaded olduğundan
// opsiyoneldir; liste kartı category+assignee ile, detay hepsiyle gelir.
export type Ticket = {
  id: number;
  ticket_number: string;
  title: string;
  description: string;
  status: TicketStatus;
  status_label: string;
  priority: TicketPriority;
  priority_label: string;
  latitude: number;
  longitude: number;
  location_address: string | null;
  category?: TicketCategory;
  assignee?: PersonRef | null;
  user?: PersonRef;
  media?: TicketMedia[];
  resolved_at: string | null;
  closed_at: string | null;
  created_at: string | null;
};

// Laravel sayfalandırma zarfı (data + meta).
export type Paginated<T> = {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
};
