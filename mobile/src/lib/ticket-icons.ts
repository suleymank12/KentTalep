import {
  Ban,
  CheckCheck,
  CirclePlus,
  CircleCheck,
  CircleX,
  Clock,
  Construction,
  Droplet,
  Footprints,
  Hammer,
  Lightbulb,
  PawPrint,
  Road,
  Tag,
  Trash2,
  TrafficCone,
  Trees,
  TriangleAlert,
  UserCheck,
  type LucideIcon,
} from 'lucide-react-native';

import type { TicketStatus } from '@/lib/types';

// Kategori ikon adı (backend, Lucide kebab-case) → mobil bileşen. Eski Lucide
// adları (alert-triangle, more-horizontal) yeni karşılıklarına eşlenir.
// Bilinmeyen ad için Tag fallback döner.
const CATEGORY_ICONS: Record<string, LucideIcon> = {
  road: Road,
  'alert-triangle': TriangleAlert,
  'triangle-alert': TriangleAlert,
  construction: Construction,
  trees: Trees,
  lightbulb: Lightbulb,
  'trash-2': Trash2,
  droplet: Droplet,
  footprints: Footprints,
  'traffic-cone': TrafficCone,
  'paw-print': PawPrint,
  'more-horizontal': Tag,
  tag: Tag,
};

export function categoryIcon(name?: string | null): LucideIcon {
  if (name && CATEGORY_ICONS[name]) {
    return CATEGORY_ICONS[name];
  }
  return Tag;
}

// Durum → zaman çizelgesi ikonu.
const STATUS_ICONS: Record<TicketStatus, LucideIcon> = {
  pending: Clock,
  assigned: UserCheck,
  in_progress: Hammer,
  resolved: CircleCheck,
  closed: CheckCheck,
  cancelled: CircleX,
  rejected: Ban,
};

// old_status null (talep oluşturuldu) satırının ikonu.
export const CREATED_ICON: LucideIcon = CirclePlus;

export function statusIcon(status: TicketStatus): LucideIcon {
  return STATUS_ICONS[status];
}
