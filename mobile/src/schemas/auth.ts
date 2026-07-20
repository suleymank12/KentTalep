import { z } from 'zod';

const email = z.string().min(1, 'E-posta zorunludur').email('Geçerli bir e-posta girin');
const optionalPhone = z.string().max(20, 'Telefon en fazla 20 karakter').optional().or(z.literal(''));
const newPassword = z
  .string()
  .min(8, 'Şifre en az 8 karakter olmalı')
  .regex(/[0-9]/, 'Şifre en az bir rakam içermeli')
  .regex(/[A-Za-zğüşiöçİĞÜŞÖÇ]/, 'Şifre en az bir harf içermeli');

export const loginSchema = z.object({
  email,
  password: z.string().min(1, 'Şifre zorunludur'),
});
export type LoginValues = z.infer<typeof loginSchema>;

export const registerSchema = z
  .object({
    name: z.string().min(3, 'Ad en az 3 karakter olmalı').max(255, 'Ad çok uzun'),
    email,
    phone: optionalPhone,
    password: newPassword,
    password_confirmation: z.string(),
    kvkk_accepted: z.boolean(),
  })
  .refine((d) => d.password === d.password_confirmation, {
    path: ['password_confirmation'],
    message: 'Şifreler eşleşmiyor',
  })
  .refine((d) => d.kvkk_accepted, {
    path: ['kvkk_accepted'],
    message: 'KVKK aydınlatma metnini onaylamalısınız',
  });
export type RegisterValues = z.infer<typeof registerSchema>;

export const forgotEmailSchema = z.object({ email });
export type ForgotEmailValues = z.infer<typeof forgotEmailSchema>;

export const resetSchema = z
  .object({
    code: z.string().length(6, 'Kod 6 haneli olmalı'),
    password: newPassword,
    password_confirmation: z.string(),
  })
  .refine((d) => d.password === d.password_confirmation, {
    path: ['password_confirmation'],
    message: 'Şifreler eşleşmiyor',
  });
export type ResetValues = z.infer<typeof resetSchema>;

export const changePasswordSchema = z
  .object({
    current_password: z.string().min(1, 'Mevcut şifre zorunludur'),
    password: newPassword,
    password_confirmation: z.string(),
  })
  .refine((d) => d.password === d.password_confirmation, {
    path: ['password_confirmation'],
    message: 'Şifreler eşleşmiyor',
  });
export type ChangePasswordValues = z.infer<typeof changePasswordSchema>;

export const profileSchema = z.object({
  name: z.string().min(3, 'Ad en az 3 karakter olmalı').max(255, 'Ad çok uzun'),
  phone: optionalPhone,
});
export type ProfileValues = z.infer<typeof profileSchema>;
