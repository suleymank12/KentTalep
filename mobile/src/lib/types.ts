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
