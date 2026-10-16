/** Verificação funcional local com Playwright; sem pedido ou e-mail real. */
import {execFileSync} from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import {pathToFileURL} from 'node:url';
if (!process.env.NC_LIGHTHOUSE_TOOLS || !process.env.CHROME_PATH) throw new Error('Informe NC_LIGHTHOUSE_TOOLS e CHROME_PATH.');
const {chromium} = await import(pathToFileURL(path.join(process.env.NC_LIGHTHOUSE_TOOLS,'node_modules/playwright/index.mjs')));
let user;let browser;const result={};
try {
 const php=`<?php $_SERVER['HTTP_HOST']='localhost:8080';require '/var/www/html/wp-load.php';if(!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1'],true))exit(1);add_filter('pre_wp_mail','__return_true');$login='nc-perf-reset-'.strtolower(wp_generate_password(10,false));$id=wp_insert_user(['user_login'=>$login,'user_pass'=>wp_generate_password(32),'user_email'=>$login.'@example.invalid','role'=>'customer']);if(is_wp_error($id))exit(1);$key=get_password_reset_key(get_userdata($id));echo wp_json_encode(['id'=>$id,'key'=>$key]);`;
 user=JSON.parse(execFileSync('docker',['compose','exec','-T','wordpress','php'],{input:php,encoding:'utf8'}));
 browser=await chromium.launch({executablePath:process.env.CHROME_PATH,args:['--no-sandbox','--disable-dev-shm-usage']});
 const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto('http://localhost:8080/?page_id=9&lost-password',{waitUntil:'networkidle'});
 result.emailFormVisible=await page.locator('#user_login').count()===1;
 result.emailFormHasNoMeter=await page.locator('#wc-password-strength-meter-js').count()===0;
 await page.goto(`http://localhost:8080/?page_id=9&lost-password&key=${encodeURIComponent(user.key)}&id=${user.id}`,{waitUntil:'networkidle'});
 result.actualResetFormVisible=await page.locator('#password_1').count()===1;
 result.actualResetHasMeter=await page.locator('#wc-password-strength-meter-js').count()===1;
 if(result.actualResetFormVisible){await page.locator('#password_1').fill('12345');await page.locator('#password_1').press('Tab');await page.waitForTimeout(500);result.weakPasswordIsChecked=await page.locator('.woocommerce-password-strength').count()>0;}
 result.noJavaScriptErrors=errors.length===0;
} finally {
 if(user){const id=Number(user.id);if(Number.isSafeInteger(id)&&id>0)execFileSync('docker',['compose','exec','-T','wordpress','php'],{input:`<?php require '/var/www/html/wp-load.php';require_once ABSPATH.'wp-admin/includes/user.php';wp_delete_user(${id});`});}
 if(browser)await browser.close();fs.writeFileSync('docs/lighthouse-2026-10-16/navegador/password-resources.json',JSON.stringify(result,null,2));
}
console.log(JSON.stringify(result,null,2));if(Object.values(result).some(v=>v!==true))process.exitCode=1;
