import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Text, View } from 'react-native';

import { Button, FormError, Input, Screen, Wordmark } from '@/components/ui';
import { splitServerErrors } from '@/lib/errors';
import { loginSchema, type LoginValues } from '@/schemas/auth';
import { useAuthStore } from '@/stores/auth';

export default function LoginScreen() {
  const login = useAuthStore((s) => s.login);
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  const { control, handleSubmit, setError, formState } = useForm<LoginValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setSubmitting(true);
    setFormError(null);
    try {
      await login(values.email, values.password);
      router.replace('/(tabs)');
    } catch (error) {
      const { fields, general } = splitServerErrors(error, ['email', 'password']);
      Object.keys(fields).forEach((key) =>
        setError(key as keyof LoginValues, { message: fields[key] })
      );
      setFormError(general);
    } finally {
      setSubmitting(false);
    }
  });

  return (
    <Screen>
      <View className="grow justify-center gap-8">
        <View className="items-center gap-2">
          <Wordmark />
          <Text className="text-base text-ink-soft">Vatandaş girişi</Text>
        </View>

        <View className="gap-4">
          <Controller
            control={control}
            name="email"
            render={({ field }) => (
              <Input
                label="E-posta"
                autoCapitalize="none"
                keyboardType="email-address"
                value={field.value}
                onChangeText={field.onChange}
                error={formState.errors.email?.message}
              />
            )}
          />
          <Controller
            control={control}
            name="password"
            render={({ field }) => (
              <Input
                label="Şifre"
                secureToggle
                value={field.value}
                onChangeText={field.onChange}
                error={formState.errors.password?.message}
              />
            )}
          />
          <FormError message={formError} />
          <Button label="Giriş Yap" onPress={onSubmit} loading={submitting} />
        </View>

        <View className="items-center gap-3">
          <Link href="/(auth)/forgot-password">
            <Text className="font-medium text-primary">Şifremi Unuttum</Text>
          </Link>
          <Link href="/(auth)/register">
            <Text className="font-medium text-primary">Hesabın yok mu? Kayıt Ol</Text>
          </Link>
        </View>
      </View>
    </Screen>
  );
}
