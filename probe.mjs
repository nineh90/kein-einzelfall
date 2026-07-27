import { chromium } from 'playwright';
const b = await chromium.launch();
const s = await b.newPage({ viewport: { width: 1000, height: 1000 } });
await s.goto('http://127.0.0.1:8000/gibtesnicht', { waitUntil: 'networkidle' });
await s.screenshot({ path: '/tmp/404.png', fullPage: true });
await b.close();
