import { chromium } from 'playwright';
const [,, pfad, name, breite = '1400'] = process.argv;
const b = await chromium.launch();
const k = await b.newContext({ viewport: { width: +breite, height: 1000 } });
await k.addInitScript(() => localStorage.setItem('ke.trigger.aus', '1'));
const p = await k.newPage();
await p.goto('http://localhost:8000' + pfad, { waitUntil: 'networkidle' });
const h = await p.evaluate(() => document.documentElement.scrollHeight);
let i = 0;
for (let y = 0; y < h; y += 1000, i++) {
  await p.evaluate((y) => window.scrollTo(0, y), y);
  await p.waitForTimeout(150);
  await p.screenshot({ path: `/tmp/ke-v/${name}-${i}.png` });
}
console.log(h, i);
await b.close();
