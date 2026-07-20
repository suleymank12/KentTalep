import { zodResolver } from '@hookform/resolvers/zod';
import { Link } from 'expo-router';
import { useState } from 'react';
import { Controller, useForm } from 'react-hook-form';
import { Alert, Text, View } from 'react-native';

import { ResetForm } from '@/components/auth/reset-form';
import { Button, Input, Screen } from '@/components/ui';
import { api } from '@/lib/api';
import { errorMessage } from '@/lib/errors';
import { forgotEmailSchema, type ForgotEmailValues } from '@/schemas/auth';

export default function ForgotPasswordScreen() {
  const [stage, setStage] = useState<'email' | 'reset'>('email');
  const [email, setEmail] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [info, setInfo] = useState<string | null>(null);

  const emailForm = useForm<ForgotEmailValues>({
    resolver: zodResolver(forgotEmailSchema),
    defaultValues: { email: '' },
  });

  const sendCode = emailForm.handleSubmit(async (values) => {
    setSubmitting(true);
    try {
      await api.post('/auth/forgot-password', { email: values.email });
      setEmail(values.email);
      setStage('reset');
      setInfo('E-posta kayıtlıysa sıfırlama kodu gönderildi.');
    } catch (error) {
      Alert.alert('Hata', errorMessage(error));
    } finally {
      setSubmitting(false);
    }
  });

  return (
    <Screen>
      <View className="grow justify-center gap-6">
        <Text className="font-title text-2xl text-ink">Şifre Sıfırlama</Text>
        {info ? <Text className="text-sm text-ink-soft">{info}</Text> : null}

        {stage === 'email' ? (
          <View className="gap-4">
            <Controller
              control={emailForm.control}
              name="email"
              render={({ field }) => (
                <Input
                  label="E-posta"
                  autoCapitalize="none"
                  keyboardType="email-address"
                  value={field.value}
                  onChangeText={field.onChange}
                  error={emailForm.formState.errors.email?.message}
                />
              )}
            />
            <Button label="Kod Gönder" onPress={sendCode} loading={submitting} />
          </View>
        ) : (
          <ResetForm email={email} onInfo={setInfo} />
        )}

        <View className="items-center">
          <Link href="/(auth)/login">
            <Text className="font-medium text-primary">Girişe dön</Text>
          </Link>
        </View>
      </View>
    </Screen>
  );
}
