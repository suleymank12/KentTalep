import { ActivityIndicator, Pressable, Text } from 'react-native';

import { colors } from '@/theme/colors';

type Variant = 'primary' | 'secondary' | 'ghost';

type ButtonProps = {
  label: string;
  onPress?: () => void;
  variant?: Variant;
  loading?: boolean;
  disabled?: boolean;
};

const CONTAINER: Record<Variant, string> = {
  primary: 'bg-primary active:bg-primary-press',
  secondary: 'bg-surface border border-border',
  ghost: 'bg-transparent',
};

const LABEL: Record<Variant, string> = {
  primary: 'text-on-primary',
  secondary: 'text-ink',
  ghost: 'text-primary',
};

export function Button({
  label,
  onPress,
  variant = 'primary',
  loading = false,
  disabled = false,
}: ButtonProps) {
  const isDisabled = disabled || loading;

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ disabled: isDisabled, busy: loading }}
      disabled={isDisabled}
      onPress={onPress}
      className={`h-[52px] flex-row items-center justify-center rounded-xl px-4 ${CONTAINER[variant]} ${isDisabled ? 'opacity-50' : ''}`}>
      {loading ? (
        <ActivityIndicator color={variant === 'primary' ? colors.onPrimary : colors.primary} />
      ) : (
        <Text className={`font-semibold text-base ${LABEL[variant]}`}>{label}</Text>
      )}
    </Pressable>
  );
}
