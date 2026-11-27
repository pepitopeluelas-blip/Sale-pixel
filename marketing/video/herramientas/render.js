const { chromium } = require('playwright');
const fs = require('fs');
const [,, file, dur, mode, out, times] = process.argv;
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1080, height: 1920 } });
  await p.goto('file://' + file, { waitUntil: 'networkidle' });
  await p.evaluate(() => document.fonts.ready);
  fs.mkdirSync(out, { recursive: true });
  const FPS = 30;
  const ts = mode === 'preview' ? times.split(',').map(Number) : [...Array(Math.round(FPS * dur)).keys()].map(i => i / FPS);
  let n = 0;
  for (const t of ts) {
    await p.evaluate(t => window.seek(t), t);
    await p.screenshot({ path: `${out}/f${String(n++).padStart(4, '0')}.jpg`, type: 'jpeg', quality: 92 });
  }
  console.log('frames', n);
  await b.close();
})();
