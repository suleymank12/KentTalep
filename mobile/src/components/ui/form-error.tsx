import { Text } from 'react-native';

// Form-seviyesi (alan dışı) hata bandı: submit butonunun üstünde, danger
// renginde ve ekran okuyuculara canlı bölge olarak duyurulur. Renk token'dan.
export function FormError({ message }: { message: string | null }) {
  if (!message) {
    return null;
  }
  return (
    <Text
      accessibilityRole="alert"
      accessibilityLiveRegion="polite"
      className="text-sm text-danger">
      {message}
    </Text>
  );
}
