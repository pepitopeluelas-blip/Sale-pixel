// Renderiza secciones de un HTML de publicación (1080x1350) a PNG.
// Uso: node render_post.js <ruta absoluta del .html> <salida_base> a b ...
//   -> <salida_base>-a.png, <salida_base>-b.png (cada sección se elige con #a, #b en la URL)
const { chromium } = require('playwright');
const [,, file, base, ...ids] = process.argv;
(async () => {
  const b = await chromium.launch();
  for (const id of ids) {
    const p = await b.newPage({ viewport: { width: 1080, height: 1350 } });
    await p.goto(`file://${file}#${id}`, { waitUntil: 'networkidle' });
    await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: `${base}-${id}.png` });
    await p.close();
    console.log(`${base}-${id}.png`);
  }
  await b.close();
})();
