import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Text, View } from 'react-native';

import { Button, Checkbox, FormError, Input, Screen, Wordmark } from '@/components/ui';
import { splitServerErrors } from '@/lib/errors';
import { registerSchema, type RegisterValues } from '@/schemas/auth';
import { useAuthStore } from '@/stores/auth';

export default function RegisterScreen() {
  const register = useAuthStore((s) => s.register);
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const { control, handleSubmit, setError, formState } = useForm<RegisterValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      kvkk_accepted: false,
    },
  });

  const onSubmit = handleSubmit(async (values) => {
    setSubmitting(true);
    setFormError(null);
    try {
      await register({
        name: values.name,
        email: values.email,
        phone: values.phone || undefined,
        password: values.password,
        password_confirmation: values.password_confirmation,
        kvkk_accepted: values.kvkk_accepted,
      });
      router.replace('/(tabs)');
    } catch (error) {
      const { fields, general } = splitServerErrors(error, [
        'name',
        'email',
        'phone',
        'password',
        'password_confirmation',
        'kvkk_accepted',
      ]);
      Object.keys(fields).forEach((key) =>
        setError(key as keyof RegisterValues, { message: fields[key] })
      );
      setFormError(general);
    } finally {
      setSubmitting(false);
    }
  });

  return (
    <Screen>
      <View className="gap-6 py-4">
        <View className="items-center gap-1">
          <Wordmark />
          <Text className="text-base text-ink-soft">Yeni hesap oluştur</Text>
        </View>

        <View className="gap-4">
          <Controller
            control={control}
            name="name"
            render={({ field }) => (
              <Input label="Ad Soyad" value={field.value} onChangeText={field.onChange} error={formState.errors.name?.message} />
            )}
          />
          <Controller
            control={control}
            name="email"
            render={({ field }) => (
              <Input label="E-posta" autoCapitalize="none" keyboardType="email-address" value={field.value} onChangeText={field.onChange} error={formState.errors.email?.message} />
            )}
          />
          <Controller
            control={control}
            name="phone"
            render={({ field }) => (
              <Input label="Telefon (isteğe bağlı)" keyboardType="phone-pad" value={field.value ?? ''} onChangeText={field.onChange} error={formState.errors.phone?.message} />
            )}
          />
          <Controller
            control={control}
            name="password"
            render={({ field }) => (
              <Input label="Şifre" secureToggle value={field.value} onChangeText={field.onChange} error={formState.errors.password?.message} />
            )}
          />
          <Controller
            control={control}
            name="password_confirmation"
            render={({ field }) => (
              <Input label="Şifre (tekrar)" secureToggle value={field.value} onChangeText={field.onChange} error={formState.errors.password_confirmation?.message} />
            )}
          />
          <Controller
            control={control}
            name="kvkk_accepted"
            render={({ field }) => (
              <Checkbox checked={field.value} onChange={field.onChange} error={formState.errors.kvkk_accepted?.message}>
                <Text className="text-sm text-ink-soft">
                  <Link href="/kvkk">
                    <Text className="font-medium text-primary">KVKK Aydınlatma Metni</Text>
                  </Link>
                  &apos;ni okudum ve onaylıyorum.
                </Text>
              </Checkbox>
            )}
          />
          <FormError message={formError} />
          <Button label="Kayıt Ol" onPress={onSubmit} loading={submitting} />
        </View>

        <View className="items-center">
          <Link href="/(auth)/login">
            <Text className="font-medium text-primary">Zaten hesabın var mı? Giriş Yap</Text>
          </Link>
        </View>
      </View>
    </Screen>
  );
}
