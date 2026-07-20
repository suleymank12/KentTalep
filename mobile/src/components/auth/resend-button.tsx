import { useEffect, useState } from 'react';
import { Alert } from 'react-native';

import { Button } from '@/components/ui';
import { api } from '@/lib/api';
import { errorMessage } from '@/lib/errors';

type ResendButtonProps = {
  email: string;
  onInfo: (message: string) => void;
};

// Geri sayaç bu bileşende yaşar: saniyelik tik'ler yalnız bu bileşeni yeniden
// render eder, üst ekranın form alanlarına dokunmaz (kod alanı temizlenmez).
export function ResendButton({ email, onInfo }: ResendButtonProps) {
  const [countdown, setCountdown] = useState(60);

  useEffect(() => {
    if (countdown <= 0) {
      return;
    }
    const timer = setTimeout(() => setCountdown((value) => value - 1), 1000);
    return () => clearTimeout(timer);
  }, [countdown]);

  const resend = async () => {
    if (countdown > 0) {
      return;
    }
    try {
      await api.post('/auth/forgot-password', { email });
      setCountdown(60);
      onInfo('Kod yeniden gönderildi.');
    } catch (error) {
      Alert.alert('Hata', errorMessage(error));
    }
  };

  return (
    <Button
      label={countdown > 0 ? `Kodu tekrar gönder (${countdown})` : 'Kodu tekrar gönder'}
      variant="ghost"
      disabled={countdown > 0}
      onPress={resend}
    />
  );
}
