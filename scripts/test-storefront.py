#!/usr/bin/env python3
"""HTTP funcional local, sem navegador ou dependências externas."""
import http.cookiejar
import json
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from html import unescape
from html.parser import HTMLParser

BASE = sys.argv[1] if len(sys.argv) > 1 else 'http://localhost:8080'
if urllib.parse.urlsplit(BASE).hostname not in ('localhost', '127.0.0.1'):
    raise SystemExit('Este script testa somente a loja local.')
BASE = BASE.rstrip('/')
results = {}
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))


def get(url, data=None):
    url = urllib.parse.urljoin(BASE + '/', url)
    request = urllib.request.Request(url, data=data)
    try:
        with client.open(request, timeout=20) as response:
            return response.status, response.geturl(), response.read().decode('utf-8'), response.headers
    except urllib.error.HTTPError as error:
        return error.code, error.geturl(), error.read().decode('utf-8'), error.headers


def check(name, condition):
    results[name] = bool(condition)


class Page(HTMLParser):
    def __init__(self, text):
        super().__init__()
        self.links = set()
        self.assets = set()
        self.inputs = {}
        self.nav_depth = 0
        self.feed(text)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag in ('header', 'footer'):
            self.nav_depth += 1
        if tag == 'a' and self.nav_depth and attrs.get('href'):
            self.links.add(attrs['href'])
        if tag in ('img', 'script') and attrs.get('src'):
            self.assets.add(attrs['src'])
        if tag == 'link' and attrs.get('rel') == 'stylesheet' and attrs.get('href'):
            self.assets.add(attrs['href'])
        if tag == 'input' and attrs.get('name'):
            self.inputs[attrs['name']] = attrs.get('value', '')

    def handle_endtag(self, tag):
        if tag in ('header', 'footer'):
            self.nav_depth = max(0, self.nav_depth - 1)


def catalog_prices(html):
    # Apenas os preços dos cartões, sem subtotal de carrinho/menu/JSON.
    values = []
    for price in re.findall(r'<span class="price">(.*?)</span>\s*</span>', html, re.S):
        text = unescape(re.sub(r'<[^>]+>', '', price))
        match = re.search(r'([\d.]+,\d{2})', text)
        if match:
            values.append(int(match[1].replace('.', '').replace(',', '')))
    return values


try:
    status, _, home, _ = get('/')
    check('home_loads', status == 200 and 'nc-header' in home and 'nc-site-footer' in home)
    status, _, raw, _ = get('/?rest_route=/wp/v2/pages&per_page=100&_fields=id,slug,link')
    pages = {p['slug']: p['link'] for p in json.loads(raw)}
    check('published_pages_available', status == 200 and all(k in pages for k in ['loja', 'carrinho', 'minha-conta']))
    for slug in ['produtos', 'sobre-a-loja', 'loja', 'carrinho', 'minha-conta']:
        status, _, text, _ = get(pages[slug])
        check('page_' + slug, status == 200 and 'Fatal error' not in text and 'nc-site-footer' in text)
    account = pages['minha-conta']
    status, _, text, _ = get(account)
    check('guest_login_form', status == 200 and 'woocommerce-form-login' in text)
    status, _, text, _ = get(account + '&orders=')
    check('guest_orders_require_login', status == 200 and 'woocommerce-form-login' in text and 'woocommerce-orders-table__cell' not in text)
    status, _, text, _ = get(account + '&lost-password=')
    check('password_recovery_form', status == 200 and 'woocommerce-ResetPassword' in text and 'user_login' in text)
    status, _, text, _ = get(account)
    login_form = re.search(r'<form\b[^>]*class="[^"]*woocommerce-form-login[^>]*>(.*?)</form>', text, re.S)
    if not login_form:
        raise RuntimeError('Formulário de login não encontrado.')
    fields = Page(login_form.group(1)).inputs
    fields.update({'username': 'nc-invalid-user-for-test', 'password': 'invalid-password', 'login': 'Entrar'})
    status, _, text, _ = get(account, urllib.parse.urlencode(fields).encode())
    check('invalid_login_has_error', status == 200 and 'woocommerce-error' in text and 'woocommerce-form-login' in text)
    status, _, catalog, _ = get('/?post_type=product')
    check('catalog_order_and_filter_controls', status == 200 and 'woocommerce-ordering' in catalog and 'widget_price_filter' in catalog)
    for direction in ['price', 'price-desc']:
        status, _, text, _ = get('/?post_type=product&orderby=' + direction)
        prices = catalog_prices(text)
        check('ordering_' + direction, status == 200 and len(prices) > 1 and prices == sorted(prices, reverse=direction == 'price-desc'))
    status, _, text, _ = get('/?post_type=product&min_price=80&max_price=130')
    prices = catalog_prices(text)
    check('price_filter_results', status == 200 and bool(prices) and all(8000 <= p <= 13000 for p in prices))
    status, _, text, _ = get('/?post_type=product&nc_vitrine=novidades')
    check('new_arrivals_view', status == 200 and 'nc-catalog-intro--novidades' in text)
    status, _, text, _ = get('/?post_type=product&nc_vitrine=mais-vendidos')
    check('best_sellers_view', status == 200 and 'nc-catalog-view--mais-vendidos' in text)
    status, _, text, _ = get('/?s=colar&post_type=product')
    check('product_search', status == 200 and 'Colar' in unescape(text) and ('woocommerce-product-gallery' in text or 'woocommerce-loop-product__title' in text))
    status, _, text, _ = get('/?s=nc-zero-result-987654321&post_type=product')
    check('empty_search_message', status == 200 and any(s in text for s in ['Não encontramos', 'Nenhum produto', 'Nenhuma peça encontrada', 'No products were found']))
    status, _, text, _ = get('/?p=987654321')
    check('missing_page_returns_404', status == 404 and 'nc-header' in text and 'nc-site-footer' in text)
    status, _, text, _ = get(pages['carrinho'])
    check('empty_cart_context', status == 200 and 'nc-cart-empty' in text and 'nc-cart-faq' in text)

    # Links não destrutivos do cabeçalho/rodapé e assets locais da home/catálogo.
    broken_links = []
    broken_assets = []
    links = Page(home).links | Page(catalog).links
    for url in sorted(links):
        parsed = urllib.parse.urlsplit(urllib.parse.urljoin(BASE, url))
        if parsed.netloc != urllib.parse.urlsplit(BASE).netloc or parsed.scheme not in ('http', 'https'):
            continue
        if any(k in parsed.query for k in ['logout', 'add-to-cart', 'remove_item', '_wpnonce']):
            continue
        status, _, _, _ = get(url)
        if status >= 400:
            broken_links.append(urllib.parse.urlunsplit(('', '', parsed.path, parsed.query, '')))
    check('navigation_links_work', not broken_links)
    assets = Page(home).assets | Page(catalog).assets
    for url in sorted(assets):
        parsed = urllib.parse.urlsplit(urllib.parse.urljoin(BASE, url))
        if parsed.netloc != urllib.parse.urlsplit(BASE).netloc:
            continue
        # Assets podem ser binários: confira status sem tentar decodificar.
        try:
            with client.open(urllib.request.Request(urllib.parse.urljoin(BASE + '/', url), method='HEAD'), timeout=20) as response:
                if response.status >= 400:
                    broken_assets.append(parsed.path)
        except urllib.error.HTTPError:
            broken_assets.append(parsed.path)
    check('local_assets_load', not broken_assets)
    if broken_links:
        results['broken_link_paths'] = broken_links
    if broken_assets:
        results['broken_asset_paths'] = broken_assets
except Exception as error:
    results['failure'] = str(error)
print(json.dumps(results, indent=2, ensure_ascii=False))
sys.exit(1 if 'failure' in results or any(v is False for v in results.values()) else 0)
