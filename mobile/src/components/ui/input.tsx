import { Eye, EyeOff } from 'lucide-react-native';
import { forwardRef, useState } from 'react';
import { Pressable, Text, TextInput, View, type TextInputProps } from 'react-native';

import { colors } from '@/theme/colors';

type InputProps = TextInputProps & {
  label: string;
  error?: string;
  secureToggle?: boolean;
};

// Etiket alan üstünde kalıcı, hata alan altında danger renginde (tasarım brief'i).
// secureToggle: sağda göz ikonu; yalnız maskeyi (secureTextEntry) değiştirir,
// value/onChange akışına dokunmaz. Varsayılan durum gizlidir.
export const Input = forwardRef<TextInput, InputProps>(function Input(
  { label, error, secureToggle = false, secureTextEntry, ...props },
  ref
) {
  const [hidden, setHidden] = useState(true);
  const masked = secureToggle ? hidden : secureTextEntry;

  return (
    <View className="gap-1.5">
      <Text className="font-medium text-sm text-ink-soft">{label}</Text>
      <View className="justify-center">
        <TextInput
          ref={ref}
          accessibilityLabel={label}
          placeholderTextColor={colors.inkMuted}
          secureTextEntry={masked}
          className={`h-[52px] rounded-xl border bg-surface px-4 ${secureToggle ? 'pr-14' : ''} text-base text-ink ${error ? 'border-danger' : 'border-border'}`}
          {...props}
        />
        {secureToggle ? (
          <Pressable
            accessibilityRole="button"
            accessibilityLabel={hidden ? 'Şifreyi göster' : 'Şifreyi gizle'}
            hitSlop={12}
            onPress={() => setHidden((value) => !value)}
            className="absolute right-2 h-11 w-11 items-center justify-center">
            {hidden ? <EyeOff size={20} color={colors.inkMuted} /> : <Eye size={20} color={colors.inkMuted} />}
          </Pressable>
        ) : null}
      </View>
      {error ? <Text className="text-sm text-danger">{error}</Text> : null}
    </View>
  );
});
