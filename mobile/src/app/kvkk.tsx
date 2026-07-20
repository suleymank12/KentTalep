import { ScrollView, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

const PARAGRAPHS = [
  'Bu metin, KentTalep uygulaması üzerinden ilettiğiniz kişisel verilerin 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında işlenmesine ilişkin sizi bilgilendirmek amacıyla hazırlanmıştır.',
  'Ad, e-posta, telefon ve talep konumu gibi verileriniz; talebinizin ilgili belediye birimine iletilmesi, sürecin takibi ve size bildirim yapılması amacıyla işlenir.',
  'Talep fotoğraflarınız sunucuya yüklenmeden önce konum (EXIF/GPS) bilgisi temizlenir; verileriniz yalnız hizmetin sunulması için gerekli süre boyunca saklanır.',
  'Kanundan doğan haklarınız (bilgi talebi, düzeltme, silme vb.) kapsamında ilgili belediyeye başvurabilirsiniz. Bu metin demo amaçlı jenerik bir örnektir; kurulumda belediyenin resmi metniyle değiştirilir.',
];

export default function KvkkScreen() {
  return (
    <SafeAreaView className="flex-1 bg-surface" edges={['bottom', 'left', 'right']}>
      <ScrollView contentContainerStyle={{ padding: 16 }}>
        <View className="mx-auto w-full max-w-[480px] gap-4">
          <Text className="font-title text-xl text-ink">
            Kişisel Verilerin Korunması Aydınlatma Metni
          </Text>
          {PARAGRAPHS.map((paragraph, index) => (
            <Text key={index} className="text-base leading-6 text-ink-soft">
              {paragraph}
            </Text>
          ))}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}
