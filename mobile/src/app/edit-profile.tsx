import { zodResolver } from '@hookform/resolvers/zod';
import { router } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Alert, View } from 'react-native';

import { Button, FormError, Input, Screen } from '@/components/ui';
import { api } from '@/lib/api';
import { splitServerErrors } from '@/lib/errors';
import type { User } from '@/lib/types';
import { profileSchema, type ProfileValues } from '@/schemas/auth';
import { useAuthStore } from '@/stores/auth';

export default function EditProfileScreen() {
  const user = useAuthStore((s) => s.user);
  const setUser = useAuthStore((s) => s.setUser);
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const { control, handleSubmit, setError, formState } = useForm<ProfileValues>({
    resolver: zodResolver(profileSchema),
    defaultValues: { name: user?.name ?? '', phone: user?.phone ?? '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setSubmitting(true);
    setFormError(null);
    try {
      const { data } = await api.patch('/auth/profile', {
        name: values.name,
        phone: values.phone ? values.phone : null,
      });
      setUser(data.data as User);
      Alert.alert('Başarılı', 'Bilgileriniz güncellendi.');
      router.back();
    } catch (error) {
      const { fields, general } = splitServerErrors(error, ['name', 'phone']);
      Object.keys(fields).forEach((key) =>
        setError(key as keyof ProfileValues, { message: fields[key] })
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
          name="name"
          render={({ field }) => (
            <Input label="Ad Soyad" value={field.value} onChangeText={field.onChange} error={formState.errors.name?.message} />
          )}
        />
        <Controller
          control={control}
          name="phone"
          render={({ field }) => (
            <Input label="Telefon" keyboardType="phone-pad" value={field.value ?? ''} onChangeText={field.onChange} error={formState.errors.phone?.message} />
          )}
        />
        <FormError message={formError} />
        <Button label="Kaydet" onPress={onSubmit} loading={submitting} />
      </View>
    </Screen>
  );
}
