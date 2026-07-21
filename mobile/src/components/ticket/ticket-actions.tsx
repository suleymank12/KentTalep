import { useState } from 'react';
import { Modal, Text, View } from 'react-native';

import { useCancelTicket, useCloseTicket, useReopenTicket } from '@/api/tickets';
import { Button, FormError, Input } from '@/components/ui';
import { splitServerErrors } from '@/lib/errors';
import type { Ticket } from '@/lib/types';
import { useAuthStore } from '@/stores/auth';

type Sheet = 'none' | 'reopen' | 'cancel';

const NOTE_STYLE = { height: 96, paddingTop: 12, textAlignVertical: 'top' as const };

/**
 * Talep sahibi vatandaşa özel aksiyonlar. resolved → çözüm kartı (kapat /
 * not zorunlu yeniden aç); pending|assigned → iptal sheet'i (opsiyonel neden).
 * Diğer durumlarda hiçbir aksiyon gösterilmez. 403/422 form-error bandıyla.
 */
export function TicketActions({ ticket }: { ticket: Ticket }) {
  const user = useAuthStore((s) => s.user);
  const isOwner = user?.role === 'citizen' && ticket.user?.id === user.id;

  const close = useCloseTicket(ticket.id);
  const reopen = useReopenTicket(ticket.id);
  const cancel = useCancelTicket(ticket.id);

  const [sheet, setSheet] = useState<Sheet>('none');
  const [note, setNote] = useState('');
  const [noteError, setNoteError] = useState<string | null>(null);
  const [generalError, setGeneralError] = useState<string | null>(null);

  const showResolved = ticket.status === 'resolved';
  const showCancel = ticket.status === 'pending' || ticket.status === 'assigned';

  if (!isOwner || (!showResolved && !showCancel)) {
    return null;
  }

  function openSheet(kind: Sheet): void {
    setNote('');
    setNoteError(null);
    setGeneralError(null);
    setSheet(kind);
  }

  function handleError(error: unknown): void {
    setGeneralError(splitServerErrors(error, []).general);
  }

  async function onConfirmClose(): Promise<void> {
    setGeneralError(null);
    try {
      await close.mutateAsync();
    } catch (error) {
      handleError(error);
    }
  }

  async function onSubmitReopen(): Promise<void> {
    if (note.trim() === '') {
      setNoteError('Lütfen sorunun neden devam ettiğini yazın.');
      return;
    }
    try {
      await reopen.mutateAsync(note.trim());
      setSheet('none');
    } catch (error) {
      handleError(error);
    }
  }

  async function onSubmitCancel(): Promise<void> {
    try {
      await cancel.mutateAsync(note.trim() || undefined);
      setSheet('none');
    } catch (error) {
      handleError(error);
    }
  }

  const isReopen = sheet === 'reopen';

  return (
    <View className="gap-3">
      {sheet === 'none' ? <FormError message={generalError} /> : null}

      {showResolved ? (
        <View className="gap-3 rounded-xl border border-border bg-surface p-4">
          <Text className="font-heading text-base text-ink">Sorun çözüldü mü?</Text>
          <Text className="text-sm text-ink-soft">
            Ekip bu talebi çözüldü olarak işaretledi. Onaylarsanız talep kapanır.
          </Text>
          <Button label="Onayla ve Kapat" onPress={onConfirmClose} loading={close.isPending} />
          <Button
            label="Sorun Devam Ediyor"
            variant="secondary"
            onPress={() => openSheet('reopen')}
          />
        </View>
      ) : null}

      {showCancel ? (
        <Button label="Talebi İptal Et" variant="secondary" onPress={() => openSheet('cancel')} />
      ) : null}

      <Modal
        visible={sheet !== 'none'}
        transparent
        animationType="slide"
        onRequestClose={() => setSheet('none')}>
        <View className="flex-1 justify-end bg-black/40">
          <View className="gap-4 rounded-t-2xl bg-surface p-5">
            <Text className="font-heading text-lg text-ink">
              {isReopen ? 'Sorun Devam Ediyor' : 'Talebi İptal Et'}
            </Text>
            <Text className="text-sm text-ink-soft">
              {isReopen
                ? 'Talebi yeniden açmak için lütfen kısa bir açıklama girin.'
                : 'Talebi iptal etmek istediğinize emin misiniz? Dilerseniz bir neden ekleyebilirsiniz.'}
            </Text>

            <Input
              label={isReopen ? 'Açıklama' : 'Neden (isteğe bağlı)'}
              value={note}
              onChangeText={setNote}
              error={noteError ?? undefined}
              multiline
              style={NOTE_STYLE}
              placeholder={isReopen ? 'Sorun hâlâ devam ediyor çünkü…' : 'İsteğe bağlı'}
            />

            <FormError message={generalError} />

            <View className="gap-2">
              <Button
                label={isReopen ? 'Gönder' : 'Talebi İptal Et'}
                onPress={isReopen ? onSubmitReopen : onSubmitCancel}
                loading={isReopen ? reopen.isPending : cancel.isPending}
              />
              <Button label="Vazgeç" variant="ghost" onPress={() => setSheet('none')} />
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
}
