import fs from 'node:fs';
import path from 'node:path';
import {pathToFileURL} from 'node:url';
const {chromium} = await import(pathToFileURL(path.join(process.env.NC_LIGHTHOUSE_TOOLS,'node_modules/playwright/index.mjs')));
const browser=await chromium.launch({executablePath:process.env.CHROME_PATH,args:['--no-sandbox','--disable-dev-shm-usage']});
const manifest=JSON.parse(fs.readFileSync(process.argv[2]));
const out=process.argv[3]; fs.mkdirSync(out,{recursive:true});
const rows=[];
try {
for(const width of (process.argv[4]==='--flow-only'?[]:[390,768,1366])) {
 const context=await browser.newContext({viewport:{width,height:844}});
 for(const route of manifest.filter(r=>!r.cart&&!r.auth)) {
  const page=await context.newPage(); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
  const response=await page.goto(route.url,{waitUntil:'networkidle'});
  const layout=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth+1,mainCount:document.querySelectorAll('main,[role="main"]').length,h1Count:document.querySelectorAll('h1').length,headerHeight:document.querySelector('.nc-header').getBoundingClientRect().height,bodyFont:getComputedStyle(document.body).fontFamily,formFont:document.querySelector('input')?getComputedStyle(document.querySelector('input')).fontFamily:null,helpCards:document.querySelectorAll('.nc-support-grid > .nc-support-card').length,emptyHelpCards:[...document.querySelectorAll('.nc-support-card')].filter(a=>!a.textContent.trim()).length,images:[...document.images].map(i=>({src:i.currentSrc,width:i.getBoundingClientRect().width,height:i.getBoundingClientRect().height,complete:i.complete,broken:!i.complete||!i.naturalWidth})),cardWidths:[...document.querySelectorAll('ul.products > li.product')].slice(0,4).map(i=>Math.round(i.getBoundingClientRect().width))}));
  const menu=page.locator('.nc-header__category-dropdown > summary');
  if(await menu.count()){await menu.focus();await page.keyboard.press('Enter');await page.keyboard.press('Escape');layout.escapeCloses=await page.locator('.nc-header__category-dropdown').evaluate(e=>!e.open);}
  rows.push({id:route.id,width,status:response.status(),errors,...layout});
  if(['home','ajuda','loja','404'].includes(route.id)&&width!==768) await page.screenshot({path:path.join(out,`${route.id}-${width}.png`),fullPage:false});
  await page.close();
 }
 await context.close();
 console.log('width',width,'complete');
}
// Fluxo com carrinho real no navegador, sem finalizar pedido.
const context=await browser.newContext({viewport:{width:390,height:844}});
const page=await context.newPage(); const errors=[]; page.on('pageerror',e=>errors.push(e.message));
await page.goto('http://localhost:8080/?post_type=product',{waitUntil:'networkidle'});
const add=page.locator('a.ajax_add_to_cart[data-product_id="92"]');
const added = page.waitForResponse(r => r.url().includes('wc-ajax=add_to_cart') && r.request().method() === 'POST');
await add.click(); const addedResponse = await added; if (!addedResponse.ok()) throw new Error('Falha na adição AJAX.');
await page.goto('http://localhost:8080/?page_id=7',{waitUntil:'networkidle'});
await page.locator('.wc-block-cart-items__row').first().waitFor();
const qty=page.locator('.wc-block-components-quantity-selector__input').first();await qty.fill('2');await qty.press('Tab');
await page.waitForTimeout(1500);await page.reload({waitUntil:'networkidle'});
const persisted=await page.locator('.wc-block-components-quantity-selector__input').first().inputValue()==='2';
await page.locator('.wc-block-cart__submit-button').click();await page.waitForURL('**page_id=8**');
await page.locator('.wc-block-checkout').first().waitFor();
rows.push({id:'cart-checkout-flow',persistedQuantity:persisted,checkoutVisible:true,errors});
await page.screenshot({path:path.join(out,'checkout-390.png')});await context.close();
} finally {fs.writeFileSync(path.join(out,'browser.json'),JSON.stringify(rows,null,2));await browser.close();}
const failures=rows.filter(r=>r.overflow||r.errors.length||r.mainCount!==undefined&&r.mainCount!==1||r.escapeCloses===false||r.persistedQuantity===false||r.emptyHelpCards>0||(r.id==='ajuda'&&r.helpCards!==7));
console.log(JSON.stringify({scenarios:rows.length,failures:failures.map(r=>({id:r.id,width:r.width,overflow:r.overflow,mainCount:r.mainCount,errors:r.errors}))},null,2));
if(failures.length)process.exitCode=1;
