// KentTalep uygulama ikonlarını KOD ile üretir (AI görsel yasağı — ADR-24).
// Kaynak: primary zemin + elle yazılmış geometrik "K" SVG path'i. Üretilen
// PNG'ler commit'e girer. Çalıştırma: npm run icons
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import sharp from 'sharp';

const PRIMARY = '#0F766E';
const WHITE = '#FFFFFF';
const SIZE = 1024;
const CORNER_RADIUS = 180;
const STROKE = 132;

const here = path.dirname(fileURLToPath(import.meta.url));
const outDir = path.join(here, '..', 'assets', 'images');

// Elle yazılmış geometrik "K": dikey gövde + iki çapraz kol, yuvarlak uçlu
// kalın stroke ile 1024 tuvalin merkezinde (kolların ucuna kadar ~274-750px,
// adaptive ikon güvenli bölgesinin rahatça içinde).
function kMark(color) {
  return (
    `<path d="M340 340 V684 M340 512 L684 340 M340 512 L684 684" ` +
    `fill="none" stroke="${color}" stroke-width="${STROKE}" ` +
    `stroke-linecap="round" stroke-linejoin="round"/>`
  );
}

function svg({ background = false, rounded = false, mark = WHITE } = {}) {
  const bg = background
    ? `<rect x="0" y="0" width="${SIZE}" height="${SIZE}" rx="${rounded ? CORNER_RADIUS : 0}" fill="${PRIMARY}"/>`
    : '';
  return (
    `<svg xmlns="http://www.w3.org/2000/svg" width="${SIZE}" height="${SIZE}" ` +
    `viewBox="0 0 ${SIZE} ${SIZE}">${bg}${mark ? kMark(mark) : ''}</svg>`
  );
}

async function render(markup, file, resize) {
  let pipeline = sharp(Buffer.from(markup)).png();
  if (resize) {
    pipeline = pipeline.resize(resize, resize);
  }
  const target = path.join(outDir, file);
  await pipeline.toFile(target);
  console.log(`✓ ${file}${resize ? ` (${resize}px)` : ` (${SIZE}px)`}`);
}

async function main() {
  await mkdir(outDir, { recursive: true });

  // Ana ikon: yuvarlatılmış primary kare + beyaz K.
  await render(svg({ background: true, rounded: true }), 'icon.png');
  // Web favicon: aynı ikon 64px.
  await render(svg({ background: true, rounded: true }), 'favicon.png', 64);
  // Splash: şeffaf zemin + beyaz K (plugin backgroundColor primary'dir).
  await render(svg({ background: false, mark: WHITE }), 'splash-icon.png');
  // Android adaptive: tam kare primary arka plan.
  await render(svg({ background: true, rounded: false, mark: null }), 'android-icon-background.png');
  // Adaptive ön plan: şeffaf + beyaz K (güvenli bölge içinde).
  await render(svg({ background: false, mark: WHITE }), 'android-icon-foreground.png');
  // Tek renkli (temalı ikon): sistem tonlar; beyaz silüet.
  await render(svg({ background: false, mark: WHITE }), 'android-icon-monochrome.png');

  console.log('Tüm ikonlar üretildi.');
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
