import { zodResolver } from '@hookform/resolvers/zod';
import { router } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Alert, View } from 'react-native';

import { Button, FormError, Input, Screen } from '@/components/ui';
import { api } from '@/lib/api';
import { splitServerErrors } from '@/lib/errors';
import { changePasswordSchema, type ChangePasswordValues } from '@/schemas/auth';

export default function ChangePasswordScreen() {
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const { control, handleSubmit, setError, formState } = useForm<ChangePasswordValues>({
    resolver: zodResolver(changePasswordSchema),
    defaultValues: { current_password: '', password: '', password_confirmation: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setSubmitting(true);
    setFormError(null);
    try {
      await api.patch('/auth/password', {
        current_password: values.current_password,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });
      Alert.alert('Başarılı', 'Şifreniz değiştirildi. Diğer oturumlarınız kapatıldı.');
      router.back();
    } catch (error) {
      const { fields, general } = splitServerErrors(error, [
        'current_password',
        'password',
        'password_confirmation',
      ]);
      Object.keys(fields).forEach((key) =>
        setError(key as keyof ChangePasswordValues, { message: fields[key] })
      );
      setFormError(general);
    } finally {
      setSubmitting(false);
    }
  });

  return (
    <Screen edges={['bottom', 'left', 'right']}>
      <View className="gap-4 pt-4">
        <Controller
          control={control}
          name="current_password"
          render={({ field }) => (
            <Input label="Mevcut Şifre" secureToggle value={field.value} onChangeText={field.onChange} error={formState.errors.current_password?.message} />
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
        <Button label="Şifreyi Değiştir" onPress={onSubmit} loading={submitting} />
      </View>
    </Screen>
  );
}
