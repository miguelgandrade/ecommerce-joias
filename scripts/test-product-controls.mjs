/** Verificação funcional local com Playwright; sem pedido ou e-mail real. */
import fs from 'node:fs';
import path from 'node:path';
import {pathToFileURL} from 'node:url';
if (!process.env.NC_LIGHTHOUSE_TOOLS || !process.env.CHROME_PATH) throw new Error('Informe NC_LIGHTHOUSE_TOOLS e CHROME_PATH.');
const {chromium} = await import(pathToFileURL(path.join(process.env.NC_LIGHTHOUSE_TOOLS,'node_modules/playwright/index.mjs')));
const browser=await chromium.launch({executablePath:process.env.CHROME_PATH,args:['--no-sandbox','--disable-dev-shm-usage']});
const rows=[];
try {
 for(const width of [390,1366]) {
  const page=await browser.newPage({viewport:{width,height:844}});const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto('http://localhost:8080/?product=brinco-bacana',{waitUntil:'networkidle'});
  const qty=page.locator('form.cart input.qty');
  await page.getByRole('button',{name:'Aumentar quantidade',exact:true}).click();
  const increased=await qty.inputValue()==='2';
  await page.getByRole('button',{name:'Diminuir quantidade',exact:true}).click();
  const decreased=await qty.inputValue()==='1';
  const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1);
  rows.push({width,increased,decreased,overflow,errors});
  await page.screenshot({path:`docs/lighthouse-2026-10-16/navegador/produto-${width}.png`});await page.close();
 }
} finally {fs.writeFileSync('docs/lighthouse-2026-10-16/navegador/quantity-controls.json',JSON.stringify(rows,null,2));await browser.close();}
console.log(JSON.stringify(rows,null,2));if(rows.some(r=>!r.increased||!r.decreased||r.overflow||r.errors.length))process.exitCode=1;
