import { Text } from 'react-native';

// Tipografik marka (Inter bold). AI-üretimi arma/logo kullanılmaz.
export function Wordmark({ className = '' }: { className?: string }) {
  return <Text className={`font-display text-3xl text-primary ${className}`}>KentTalep</Text>;
}
