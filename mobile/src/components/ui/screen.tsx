import type { ReactNode } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, View } from 'react-native';
import { SafeAreaView, type Edge } from 'react-native-safe-area-context';

type ScreenProps = {
  children: ReactNode;
  scroll?: boolean;
  edges?: Edge[];
};

/**
 * Safe-area + klavye kaçınma + (varsayılan) kaydırma. İçerik tablette en fazla
 * 480dp sütunda ortalanır (bkz. mobil tasarım brief'i). Header'lı ekranlarda
 * üst kenar `edges` ile çıkarılabilir.
 */
export function Screen({
  children,
  scroll = true,
  edges = ['top', 'bottom', 'left', 'right'],
}: ScreenProps) {
  const body = scroll ? (
    <ScrollView
      className="flex-1"
      contentContainerStyle={{ flexGrow: 1 }}
      keyboardShouldPersistTaps="handled">
      <View className="mx-auto w-full max-w-[480px] grow p-4">{children}</View>
    </ScrollView>
  ) : (
    <View className="mx-auto w-full max-w-[480px] flex-1 p-4">{children}</View>
  );

  return (
    <SafeAreaView className="flex-1 bg-surface-alt" edges={edges}>
      <KeyboardAvoidingView
        className="flex-1"
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        {body}
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}
