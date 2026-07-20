import { Check } from 'lucide-react-native';
import type { ReactNode } from 'react';
import { Pressable, Text, View } from 'react-native';

import { colors } from '@/theme/colors';

type CheckboxProps = {
  checked: boolean;
  onChange: (value: boolean) => void;
  children: ReactNode;
  error?: string;
};

export function Checkbox({ checked, onChange, children, error }: CheckboxProps) {
  return (
    <View className="gap-1">
      <Pressable
        accessibilityRole="checkbox"
        accessibilityState={{ checked }}
        onPress={() => onChange(!checked)}
        className="flex-row items-start gap-3">
        <View
          className={`mt-0.5 h-6 w-6 items-center justify-center rounded-md border ${checked ? 'border-primary bg-primary' : 'border-border bg-surface'}`}>
          {checked ? <Check size={16} color={colors.onPrimary} /> : null}
        </View>
        <View className="flex-1">{children}</View>
      </Pressable>
      {error ? <Text className="text-sm text-danger">{error}</Text> : null}
    </View>
  );
}
