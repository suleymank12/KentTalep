import { zodResolver } from '@hookform/resolvers/zod';
import { router } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Alert, View } from 'react-native';

import { ResendButton } from '@/components/auth/resend-button';
import { Button, FormError, Input } from '@/components/ui';
import { api } from '@/lib/api';
import { splitServerErrors } from '@/lib/errors';
import { resetSchema, type ResetValues } from '@/schemas/auth';

type ResetFormProps = {
  email: string;
  onInfo: (message: string) => void;
};

// Sıfırlama aşaması ayrı bir bileşendir: e-posta aşamasından farklı bir eleman
// tipi olduğu için React, aşama geçişinde native TextInput'u yeniden kullanmaz
// (aksi halde önceki alanın native durumu kod alanına taşınıp girişi geri alır).
export function ResetForm({ email, onInfo }: ResetFormProps) {
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const { control, handleSubmit, setError, formState } = useForm<ResetValues>({
    resolver: zodResolver(resetSchema),
    defaultValues: { code: '', password: '', password_confirmation: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setSubmitting(true);
    setFormError(null);
    try {
      await api.post('/auth/reset-password', {
        email,
        code: values.code,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });
      router.replace('/(auth)/login');
      Alert.alert('Başarılı', 'Şifreniz güncellendi. Lütfen giriş yapın.');
    } catch (error) {
      const { fields, general } = splitServerErrors(error, [
        'code',
        'password',
        'password_confirmation',
      ]);
      Object.keys(fields).forEach((key) =>
        setError(key as keyof ResetValues, { message: fields[key] })
      );
      setFormError(general);
    } finally {
      setSubmitting(false);
    }
  });

  return (
    <View className="gap-4">
      <Controller
        control={control}
        name="code"
        render={({ field }) => (
          <Input label="Doğrulama Kodu" keyboardType="number-pad" maxLength={6} value={field.value} onChangeText={field.onChange} error={formState.errors.code?.message} />
        )}
      />
      <Controller
        control={control}
        name="password"
        render={({ field }) => (
          <Input label="Yeni Şifre" secureToggle value={field.value} onChangeText={field.onChange} error={formState.errors.password?.message} />
        )}
      />
      <Controller
        control={control}
        name="password_confirmation"
        render={({ field }) => (
          <Input label="Yeni Şifre (tekrar)" secureToggle value={field.value} onChangeText={field.onChange} error={formState.errors.password_confirmation?.message} />
        )}
      />
      <FormError message={formError} />
      <Button label="Şifreyi Sıfırla" onPress={onSubmit} loading={submitting} />
      <ResendButton email={email} onInfo={onInfo} />
    </View>
  );
}
