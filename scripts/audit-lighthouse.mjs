/** Auditoria local. Dependências fornecidas por NC_LIGHTHOUSE_TOOLS (fora do tema). */
import fs from 'node:fs';
import {execFileSync} from 'node:child_process';
import path from 'node:path';
import os from 'node:os';
import {pathToFileURL} from 'node:url';
const root = process.env.NC_LIGHTHOUSE_TOOLS;
if (!root || !process.env.CHROME_PATH) throw new Error('Informe NC_LIGHTHOUSE_TOOLS e CHROME_PATH.');
const {default: lighthouse} = await import(pathToFileURL(path.join(root, 'node_modules/lighthouse/core/index.js')));
const chromeLauncher = await import(pathToFileURL(path.join(root, 'node_modules/chrome-launcher/dist/index.js')));
const {chromium} = await import(pathToFileURL(path.join(root, 'node_modules/playwright/index.mjs')));
const manifest = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const output = process.argv[3];
const formFactor = process.argv[4] || 'mobile';
const select = process.argv[5]?.split(',');
fs.mkdirSync(output, {recursive: true});
const summaryPath = path.join(output, `summary-${formFactor}.json`);
const summaries = fs.existsSync(summaryPath) ? JSON.parse(fs.readFileSync(summaryPath)) : [];
for (const route of manifest.filter(r => !select || select.includes(r.id))) {
  if (new URL(route.url).hostname !== 'localhost') throw new Error('Somente localhost.');
  if (summaries.some(r => r.id === route.id)) continue;
  let chrome;
  let profileDirectory;
  let browser;
  let fixtureUser;
  const start = Date.now();
  try {
    profileDirectory = fs.mkdtempSync(path.join(process.platform === 'linux' ? '/tmp' : os.tmpdir(), 'nc-lighthouse-'));
    // chrome-launcher trata WSL como Chrome do Windows; o binário usado aqui é Linux.
    // A última flag prevalece e conserva o perfil no caminho nativo correto.
    chrome = await chromeLauncher.launch({chromePath: process.env.CHROME_PATH, userDataDir:profileDirectory, chromeFlags: ['--headless', '--no-sandbox', '--disable-dev-shm-usage', `--user-data-dir=${profileDirectory}`], logLevel: 'error'});
    if (route.cart || route.measureErrorDocument || route.auth) {
      browser = await chromium.connectOverCDP(`http://localhost:${chrome.port}`);
      if (route.auth) {
        const php = `<?php $_SERVER['HTTP_HOST']='localhost:8080'; require '/var/www/html/wp-load.php'; if(!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1'],true))exit(1); add_filter('pre_wp_mail','__return_true'); $login='nc-perf-'.strtolower(wp_generate_password(12,false)); $id=wp_insert_user(['user_login'=>$login,'user_pass'=>wp_generate_password(32),'user_email'=>$login.'@example.invalid','role'=>'customer']); if(is_wp_error($id))exit(1); echo wp_json_encode(['id'=>$id,'name'=>LOGGED_IN_COOKIE,'value'=>wp_generate_auth_cookie($id,time()+3600,'logged_in')]);`;
        fixtureUser = JSON.parse(execFileSync('docker',['compose','exec','-T','wordpress','php'],{input:php,encoding:'utf8'}));
        await browser.contexts()[0].addCookies([{name:fixtureUser.name,value:fixtureUser.value,domain:'localhost',path:'/',httpOnly:true,sameSite:'Lax'}]);
        const authenticatedPage = await browser.contexts()[0].newPage();
        await authenticatedPage.goto(route.url, {waitUntil:'networkidle'});
        if (!await authenticatedPage.locator('.woocommerce-MyAccount-navigation').count()) throw new Error('Sessão temporária não autenticada.');
        const authCdp = await authenticatedPage.context().newCDPSession(authenticatedPage);
        await authCdp.send('Network.clearBrowserCache');
        await authenticatedPage.close();
      }
      if (route.measureErrorDocument) {
        await browser.contexts()[0].route('**/*', async request => {
          if (request.request().isNavigationRequest() && request.request().url() === route.url) {
            const response = await request.fetch();
            await request.fulfill({response, status:200});
          } else await request.continue();
        });
      }
      if (route.cart) {
      const page = await browser.contexts()[0].newPage();
      await page.goto('http://localhost:8080/?add-to-cart=92', {waitUntil: 'networkidle'});
      await page.goto('http://localhost:8080/?page_id=7', {waitUntil: 'networkidle'});
      if (!await page.locator('.wc-block-cart-items__row').count()) throw new Error('Carrinho não preenchido.');
      const cdp = await page.context().newCDPSession(page);
      await cdp.send('Network.clearBrowserCache');
      await page.close();
      }
    }
    const result = await lighthouse(route.url, {
      port: chrome.port, output: ['json', 'html'], logLevel: 'error',
      onlyCategories: ['performance','accessibility','best-practices','seo'],
      ...(formFactor === 'desktop' ? {formFactor:'desktop', screenEmulation: {mobile:false,width:1350,height:940,deviceScaleFactor:1,disabled:false}, throttling:{rttMs:40,throughputKbps:10240,cpuSlowdownMultiplier:1,requestLatencyMs:0,downloadThroughputKbps:0,uploadThroughputKbps:0}} : {}),
      ...(route.cart || route.auth ? {disableStorageReset:true} : {}),
    });
    const lhr = result.lhr;
    const prefix = path.join(output, `${route.id}-${formFactor}`);
    fs.writeFileSync(prefix+'.json', result.report[0]);
    fs.writeFileSync(prefix+'.html', result.report[1]);
    const metrics = {};
    for (const id of ['first-contentful-paint','largest-contentful-paint','total-blocking-time','cumulative-layout-shift','speed-index','total-byte-weight','server-response-time']) metrics[id] = lhr.audits[id]?.numericValue ?? null;
    const scores = Object.fromEntries(Object.entries(lhr.categories).map(([k,v])=>[k,v.score===null?null:Math.round(v.score*100)]));
    const failures = Object.entries(lhr.audits).filter(([k,v])=>v.score !== null && v.score < 1 && !['notApplicable','manual','informative'].includes(v.scoreDisplayMode)).map(([id,v])=>({id,title:v.title,score:v.score,displayValue:v.displayValue}));
    summaries.push({...route,formFactor,finalUrl:lhr.finalDisplayedUrl,version:lhr.lighthouseVersion,scores,metrics,runtimeError:lhr.runtimeError,warnings:lhr.runWarnings,failures});
    console.log(JSON.stringify({id:route.id,formFactor,scores,LCP:metrics['largest-contentful-paint'],KiB:Math.round((metrics['total-byte-weight']||0)/1024),error:lhr.runtimeError?.code,seconds:Math.round((Date.now()-start)/1000)}));
  } catch (error) {
    summaries.push({...route,formFactor,error:error.message});
    console.log(JSON.stringify({id:route.id,error:error.message}));
  } finally {
    if (fixtureUser) {
      const id = Number(fixtureUser.id);
      if (Number.isSafeInteger(id) && id > 0) execFileSync('docker',['compose','exec','-T','wordpress','php'],{input:`<?php require '/var/www/html/wp-load.php'; require_once ABSPATH.'wp-admin/includes/user.php'; wp_delete_user(${id});`});
    }
    if (browser) await browser.close().catch(()=>{});
    if (chrome) await chrome.kill();
    if (profileDirectory) fs.rmSync(profileDirectory, {recursive:true,force:true});
    fs.writeFileSync(summaryPath,JSON.stringify(summaries,null,2));
  }
}
