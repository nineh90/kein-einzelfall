import { firefox } from 'playwright';
const b = await firefox.launch();
for (const w of [1280, 1024, 768, 390]) {
  const k = await b.newContext({ viewport: { width: w, height: 900 }, locale: 'de-DE' });
  await k.addInitScript(() => localStorage.setItem('ke.trigger.aus', '1'));
  const p = await k.newPage();
  await p.goto('http://localhost:8000/red-flags');
  await p.locator('header[data-anschliessend]').screenshot({ path: `/tmp/ke-v/rf3-${w}.png` });
  await k.close();
}
await b.close();
