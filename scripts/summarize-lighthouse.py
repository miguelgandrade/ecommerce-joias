#!/usr/bin/env python3
"""Consolida Lighthouse local em relatório legível e tabela interativa offline."""
import html
import json
import statistics
from pathlib import Path

ROOT = Path('docs/lighthouse-2026-10-16')
def load(path):
    p = ROOT / path
    return json.loads(p.read_text()) if p.exists() else []

def score(row, name='performance'):
    return row.get('scores', {}).get(name)

def metric(row, name):
    return row.get('metrics', {}).get(name)

def shown(value, decimals=0):
    return '—' if value is None else f'{value:.{decimals}f}'

def seconds(row, name='largest-contentful-paint'):
    value = metric(row, name)
    return shown(value / 1000 if value is not None else None, 2)

before = {r['id']: r for r in load('antes/summary-mobile.json')}
for r in load('ensaio-recuperar-senha/summary-mobile.json'):
    before.setdefault(r['id'], r)
mobile = load('depois/summary-mobile.json')
desktop = load('depois/summary-desktop.json')
after = {r['id']: r for r in mobile}
all_rows = mobile + desktop
manifest = load('rotas.json')
expected_runs = len(manifest) * 2
unexpected = [r for r in all_rows if r.get('error') or (r.get('runtimeError') and r.get('expectedStatus', 200) == 200)]
lines = ['# Lighthouse — tema completo — 16/10/2026', '',
'As páginas próprias ficaram mais leves. Carrinho e checkout de blocos continuam sendo o principal gargalo no celular. Nenhuma cobrança real foi feita.', '',
f'Foram concluídas {len(all_rows)}/{expected_runs} execuções finais: {len(mobile)} mobile e {len(desktop)} desktop. O manifesto inclui todas as páginas publicadas, seis produtos, sete categorias, vitrines, buscas vazias, recuperação de senha, carrinho vazio/preenchido, checkout e quatro telas autenticadas. Duas entradas extras medem o conteúdo de erro com status normalizado no navegador.', '',
'Abra [a tabela interativa](index.html) no navegador para filtrar as páginas e acessar os relatórios completos locais.', '',
'## Método e limites', '',
'- Lighthouse 13.5.0 e Chrome for Testing 155.0.8059.84, Node 22.19.0, loja local WordPress/WooCommerce em Docker no WSL2. Testes sequenciais, sem extensões ou barra administrativa. Mobile usa configuração padrão e throttling simulado; desktop usa 1350×940, RTT de 40 ms, 10 Mbit/s e CPU sem redução.',
'- Cada execução abre um perfil novo. Carrinho/checkout são preparados com um produto real do catálogo, sem pedido; o cache HTTP é limpo após a preparação. Clientes temporários são criados para a conta, sem e-mail, e removidos ao final. Nenhuma credencial é persistida nos relatórios.',
'- A linha de base é uma amostra por cenário representativo. O resultado de uma página não representa todas as outras. Variações pequenas de nota ou tempo não comprovam ganho estável; o volume transferido é evidência adicional.',
'- Estes são ensaios de laboratório local, não dados de usuários reais nem resultado de PageSpeed Insights em hospedagem pública. Não reproduzem o painel de administração nem pedidos reais de clientes.',
'- O Lighthouse interrompe a auditoria ao receber 404/503. As rotas reais conservam esses códigos. As linhas com sufixo `-render` usam o mesmo HTML, com resposta 200 somente interceptada no navegador de teste. Suas notas avaliam renderização; não validam o SEO ou o status HTTP de produção. O erro original também foi salvo.',
'- SEO de carrinho, checkout, conta, buscas e páginas transitórias pode ser baixo por noindex intencional ou ausência de descrição. Não se deve expor essas telas à indexação só para aumentar a nota.',
'- Relatórios brutos HTML/JSON estão disponíveis nesta cópia local, mas são ignorados pelo Git para evitar dezenas de MB no repositório. Resumos, inventário e scripts são mantidos; capturas ficam disponíveis localmente.', '',
'## Comparação mobile', '',
'| Página | Antes | Depois | LCP antes → depois | Transferência antes → depois |',
'| --- | ---: | ---: | --- | --- |']
for key in ['home','loja','produtos','sobre-a-loja','ajuda','minha-conta','produto-92','carrinho','carrinho-preenchido','checkout','404-render','manutencao-render','recuperar-senha']:
    if key not in before or key not in after:
        continue
    b, a = before[key], after[key]
    kb = lambda r: shown(metric(r,'total-byte-weight') / 1024 if metric(r,'total-byte-weight') is not None else None)
    lines.append(f"| {a['label']} | {shown(score(b))} | {shown(score(a))} | {seconds(b)} → {seconds(a)} s | {kb(b)} → {kb(a)} KiB |")
lines += ['', 'A linha de base de recuperação de senha foi capturada após os ajustes gerais, antes da remoção do avaliador da tela que pede e-mail. Os produtos e a página de exemplo foram repetidos após os últimos ajustes de prioridade de imagem e alvos de toque.', '', '## Resultado por página e dispositivo', '', '| Página | Dispositivo | Desempenho | Acessib. | Boas práticas | SEO | FCP | LCP | TBT | CLS | KiB |', '| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |']
for r in all_rows:
    link = f"depois/{r['id']}-{r['formFactor']}.html"
    kb = metric(r,'total-byte-weight')
    lines.append(f"| [{r['label']}]({link}) | {r['formFactor']} | {shown(score(r))} | {shown(score(r,'accessibility'))} | {shown(score(r,'best-practices'))} | {shown(score(r,'seo'))} | {seconds(r,'first-contentful-paint')} s | {seconds(r)} s | {shown(metric(r,'total-blocking-time'))} ms | {shown(metric(r,'cumulative-layout-shift'),3)} | {shown(kb/1024 if kb is not None else None)} |")
lines += ['', '## Alterações aplicadas', '',
'- Templates completos do filho dispensam CSS, JavaScript e fontes de ícones do pai e estilos WooCommerce não usados. Páginas com blocos/shortcodes de compra mantêm os recursos. Catálogo, produto, conta, carrinho e checkout conservam as dependências de sua implementação nativa.',
'- Ícones essenciais do cabeçalho/voltar ao topo são SVGs pequenos. As fontes de ícones permanecem nas telas herdadas que ainda usam seus símbolos.',
'- Inter é servida pelo tema com `font-display:swap`, pesos 400–800, subconjuntos latino/latino estendido e licença OFL. A tipografia da loja foi preservada.',
'- O logo informa `sizes` compatível com sua exibição. No celular, o navegador passou do PNG de 500 px / 41,4 KiB para a variante de 150 px / 8,1 KiB. Originais e identidade visual não foram sobrescritos.',
'- Catálogo/produtos usam `defer` nativo do WordPress e prioridade alta somente na primeira miniatura da vitrine ou na foto principal do produto. A tentativa extra de `defer` em carrinho/checkout não apresentou ganho consistente e foi retirada.',
'- CSS, JavaScript e fontes do próprio tema recebem 30 dias de cache estático no Apache. O HTML e as respostas PHP de carrinho/conta continuam sem cache. Fontes substituídas devem receber novos nomes; CSS/JS já usam versão por alteração.',
'- Corrigidos contraste, links identificáveis, rótulos de quantidade, alvo do botão de senha e nome acessível do carrinho. Descrições de páginas públicas foram acrescentadas, respeitando plugins de SEO e as telas que não devem ser indexadas.',
'- Corrigidos cards vazios na ajuda: o conteúdo gerado agora é um bloco HTML, impedindo a quebra dos links pelo `wpautop`. Conteúdo alterado pelo usuário é preservado pelo script de configuração.', '',
'O medidor de força de senha deixou de ser carregado na solicitação de link por e-mail, eliminando cerca de 391 KiB de zxcvbn e suas dependências nessa tela. Continua habilitado na redefinição e na edição da conta, que possuem campos de senha.', '', '## O que ainda melhorar, por prioridade', '',
'1. **Carrinho e checkout — impacto alto.** Mantêm os blocos atuais. Na linha de base, `wp-components` sozinho transferiu cerca de 266 KiB (817 KiB descompactados); há React, dados e outros pacotes necessários à hidratação. Não remover esses arquivos isoladamente. Um próximo experimento é comparar, em cópia da loja, carrinho/checkout clássicos com o visual atual; isso muda a implementação e exige validar cupons, frete, gateways e extensões. Não foi feito nesta rodada.',
'2. **Fundo do hero da home — impacto médio/alto.** A variante mobile `depositphotos_…-768x432.jpg` tem 96,6 KiB, e o original 141,1 KiB. Exportar WebP/AVIF com a mesma aparência e testar qualidade. Metas iniciais: 20–50 KiB no mobile e 80–150 KiB no desktop; são alvos, não economia garantida. Veja [o inventário de imagens](imagens-prioritarias.md).',
'3. **Fotos do catálogo — impacto médio.** `images-6-300x300.jpg` tem 21,8 KiB e o original tem 37,4 KiB. Testar WebP/AVIF, dimensões reais dos cards e qualidade. Algumas imagens já são WebP/AVIF e não devem ser recomprimidas sem necessidade. Conferir nitidez e recorte da imagem larga do brinco.',
'4. **Cache de imagens e recursos de terceiros — impacto em visitas repetidas.** O cache aplicado aqui cobre somente o tema próprio. Uploads, fontes de ícones do pai e arquivos WordPress/WooCommerce ainda precisam de política no servidor/CDN. Preferir URLs versionadas/nomes novos ao substituir imagens. Não aplicar cache público a HTML de carrinho, checkout, Minha conta, APIs ou sessões.',
'5. **Hospedagem e cache de páginas públicas — validar em produção.** Medir TTFB, configurar OPcache e cache para visitantes no servidor/plugin, com exclusões de sessão e páginas de compra. Docker local não serve de previsão do tempo de uma hospedagem pública. A compressão gzip já está ativa; simplesmente habilitá-la de novo não é a solução.',
'6. **CSS/ícones legados nas telas nativas — impacto médio, mudança maior.** O tema pai ainda fornece layout e recursos para catálogo, produto e conta. Há CSS não usado na primeira tela e Boxicons de aproximadamente 73 KiB. Migrar esses componentes aos poucos, com comparação visual e funcional. Não remover uma folha inteira com base apenas na cobertura acima da dobra.',
'7. **Atribuição de pedidos — investigar se necessário.** O relatório inicial apontou execução relevante em `order-attribution.min.js`. Só considerar desativar a atribuição se a loja não precisar dos relatórios de origem das vendas; isso é uma decisão de operação, não uma remoção automática de script.',
'8. **Edição da conta — custo conhecido.** A tela de dados da conta carrega o avaliador de força de senha porque permite cadastrar uma senha nova. O pacote zxcvbn transfere cerca de 391 KiB. Uma evolução possível é carregá-lo ao começar a editar a senha, preservando a validação, bloqueio de senha fraca e compatibilidade com extensões. Não foi adiado automaticamente nesta rodada.',
'9. **CSS próprio e manutenção — prioridade baixa.** `style.css` tem aproximadamente 92 KiB sem compressão e 16 KiB transferidos. Separar/minificar por página pode ajudar, mas não deve vir antes do carrinho e do hero. Manter animações fora de tela pausadas e a preferência de movimento reduzido, já atendidas no JS atual.',
'10. **Conteúdo e publicação.** Revisar a página de exemplo publicada, textos e políticas comerciais antes de colocar a loja em produção. As notas locais não validam frete real, entrega de e-mails, políticas comerciais ou gateways.', '',
'## Validação e reprodução', '',
'- Passaram os testes HTTP existentes, a integração WooCommerce com dados descartáveis e a sintaxe PHP. O navegador aprovou 111 cenários de layout em 390, 768 e 1366 px, 31 cenários de cabeçalho (incluindo barra administrativa simulada) e o fluxo adicional AJAX → quantidade persistida → checkout. Os testes HTTP e de integração somam 54 verificações aprovadas. Capturas e resultados estão em `navegador/` e `navegador-fluxo/`.',
f'- Erros inesperados nas execuções finais: {len(unexpected)}. Erros HTTP 404/503 esperados são identificados separadamente.',
'- Os controles de quantidade e a preservação do medidor no formulário real de redefinição foram conferidos separadamente no navegador. Resultados em `navegador/quantity-controls.json` e `navegador/password-resources.json`.',
'- Resumos: [mobile](depois/summary-mobile.json), [desktop](depois/summary-desktop.json), [linha de base](antes/summary-mobile.json). Manifesto: [rotas.json](rotas.json). Inventário: [imagens.json](imagens.json).', '',
'As ferramentas foram instaladas somente em `/tmp/nc-lighthouse-tools`. Para repetir com Node >=22.19, Lighthouse 13.5.0, Playwright e Chrome disponíveis:', '',
'```bash',
'export NC_LIGHTHOUSE_TOOLS=/caminho/para/ferramentas',
'export CHROME_PATH=/caminho/para/chrome',
'node scripts/audit-lighthouse.mjs docs/lighthouse-2026-10-16/rotas.json /tmp/nova-auditoria mobile',
'node scripts/audit-lighthouse.mjs docs/lighthouse-2026-10-16/rotas.json /tmp/nova-auditoria desktop',
'# Para reconstruir o relatório desta rodada com os dados salvos em docs:',
'python3 scripts/summarize-lighthouse.py',
'```', '',
'Verificações adicionais: `node scripts/test-product-controls.mjs` e `node scripts/test-password-resources.mjs`, com as mesmas variáveis de ferramentas. A segunda cria e remove um cliente temporário, testa o formulário real de redefinição e confirma a avaliação de senha fraca, sem enviar e-mail ou concluir uma troca de senha real.', '', 'O runner ignora IDs que já estejam no resumo de destino. Use uma pasta nova para repetir medições. As rotas da conta requerem Docker Compose e removem os usuários temporários ao final; se o processo for terminado à força, confira usuários com prefixo `nc-perf-`.', '',
'## Fontes oficiais', '',
'- [Lighthouse CLI](https://github.com/GoogleChrome/lighthouse) — execução, cache e flags.',
'- [Entrega de imagens](https://developer.chrome.com/docs/performance/insights/image-delivery) — formato, dimensão e economia estimada.',
'- [Cache de recursos estáticos](https://developer.chrome.com/docs/performance/insights/cache) e [Apache mod_expires](https://httpd.apache.org/docs/2.4/mod/mod_expires.html).',
'- [Imagens responsivas no WordPress](https://developer.wordpress.org/reference/functions/wp_get_attachment_image/) e [estratégias de scripts](https://developer.wordpress.org/reference/functions/wp_enqueue_script/).',
'- [Inter e licença OFL](https://github.com/google/fonts/tree/main/ofl/inter).', '']
(ROOT/'README.md').write_text('\n'.join(lines))

# Tabela offline sem fetch: funciona ao abrir por file:// no Windows.
rows = []
for r in all_rows:
    scores = [score(r,k) for k in ['performance','accessibility','best-practices','seo']]
    label = html.escape(r['label'])
    tags = (' · Conta autenticada' if r.get('auth') else '') + (' · HTTP normalizado só no teste' if r.get('measureErrorDocument') else '')
    perf = score(r)
    klass = 'good' if perf is not None and perf>=90 else ('medium' if perf is not None and perf>=50 else 'bad')
    if perf is None: klass='na'
    cells=''.join(f'<td>{shown(s)}</td>' for s in scores[1:])
    kb=metric(r,'total-byte-weight')
    rows.append(f'<tr data-device="{r["formFactor"]}" data-search="{html.escape((r["label"]+tags).lower())}" data-score="{perf if perf is not None else 101}"><th scope="row"><a href="depois/{r["id"]}-{r["formFactor"]}.html">{label}</a><small>{html.escape(tags)}</small></th><td>{r["formFactor"]}</td><td class="{klass}">{shown(perf)}</td>{cells}<td>{seconds(r)} s</td><td>{shown(metric(r,"total-blocking-time"))} ms</td><td>{shown(kb/1024 if kb is not None else None)} KiB</td></tr>')
page='''<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lighthouse — NC Semijoias</title><style>*{box-sizing:border-box}body{margin:0;padding:clamp(16px,4vw,48px);background:#fbf5f1;color:#332927;font:16px/1.6 Arial,sans-serif}main{max-width:1400px;margin:auto}h1,h2{font-family:Georgia,serif;font-weight:400}h1{font-size:clamp(30px,4vw,48px)}a{color:#963b37}aside{background:#f2deda;padding:20px;border-radius:8px}label{display:inline-flex;flex-direction:column;margin:16px 20px 16px 0}input,select,button{font:inherit;padding:10px;border:1px solid #cfb9b0;background:#fffaf7;border-radius:4px;color:inherit}button{cursor:pointer}table{border-collapse:collapse;min-width:950px;width:100%;background:#fffaf7}th,td{padding:12px;text-align:left;border-bottom:1px solid #eadbd5}thead{background:#f2deda}th[scope=row]{font-weight:400;max-width:440px}small{display:block;font-size:12px;color:#75625e}.table{overflow-x:auto}td.good{color:#14683d;font-weight:bold}td.medium{color:#895300;font-weight:bold}td.bad{color:#a52424;font-weight:bold}.na{color:#75625e}tr[hidden]{display:none}:focus-visible{outline:3px solid #963b37;outline-offset:3px}</style><main><small>AUDITORIA LOCAL · 16/10/2026</small><h1>Lighthouse — NC Semijoias</h1><p>Notas por página, no celular e no desktop. Clique no nome para abrir o relatório Lighthouse completo desta cópia local.</p><aside><strong>Como interpretar:</strong> as notas são medições de laboratório e variam entre execuções. As páginas 404 e 503 mantêm o status correto; linhas marcadas como normalizadas medem apenas seu conteúdo. SEO de telas com noindex intencional pode ser baixo.</aside><p><a href="README.md">Análise completa e recomendações</a> · <a href="imagens-prioritarias.md">Pesos das imagens e metas</a></p><label>Buscar página<input id="query" type="search" placeholder="Ex.: ajuda, carrinho, produto"></label><label>Dispositivo<select id="device"><option value="mobile">Celular</option><option value="desktop">Desktop</option><option value="all">Todos</option></select></label><button id="sort" type="button">Menor desempenho primeiro</button><p id="count" aria-live="polite"></p><div class="table"><table><thead><tr><th>Página / cenário</th><th>Dispositivo</th><th>Desempenho</th><th>Acessib.</th><th>Boas práticas</th><th>SEO</th><th>LCP</th><th>TBT</th><th>Transferência</th></tr></thead><tbody>ROWS</tbody></table></div><h2>Próximos ganhos</h2><ol><li>Investigar uma alternativa mais leve para carrinho/checkout em uma cópia da loja.</li><li>Exportar o fundo da home em WebP/AVIF: variante mobile atual com 96,6 KiB.</li><li>Completar o cache de imagens e recursos WordPress/WooCommerce no servidor.</li><li>Medir a hospedagem real e validar cache com exclusões de sessão e compra.</li></ol><p>Otimizações aplicadas: páginas próprias sem pacotes legados, fonte local, logo responsivo, ícones essenciais em SVG, cache estático do tema e correções de acessibilidade.</p></main><script>const rows=[...document.querySelectorAll('tbody tr')],query=document.querySelector('#query'),device=document.querySelector('#device');function filter(){let n=0;for(const r of rows){r.hidden=!(device.value==='all'||r.dataset.device===device.value)||!r.dataset.search.includes(query.value.toLowerCase());if(!r.hidden)n++}document.querySelector('#count').textContent=n+' resultados.'}query.addEventListener('input',filter);device.addEventListener('change',filter);document.querySelector('#sort').addEventListener('click',()=>{rows.sort((a,b)=>Number(a.dataset.score)-Number(b.dataset.score));for(const row of rows)document.querySelector('tbody').append(row)});filter();</script></html>'''
(ROOT/'index.html').write_text(page.replace('ROWS',''.join(rows)))
repeats = load('repeticoes/summary-mobile.json')
if repeats:
    text = (ROOT/'README.md').read_text()
    section = ['## Variação de carrinho e checkout', '', 'Repetições mobile do estado final, com perfil novo, carrinho preparado e cache HTTP limpo em cada execução. A mediana final não tem uma mediana correspondente antes das mudanças; não comprova causalidade por si só.', '', '| Cenário | Notas nas três execuções | LCP nas três execuções | Mediana de desempenho | Mediana LCP |', '| --- | --- | --- | ---: | ---: |']
    for key in ['carrinho-preenchido','checkout']:
        samples = ([after[key]] if key in after else []) + [r for r in repeats if r.get('baseId') == key]
        samples = [r for r in samples if score(r) is not None]
        if samples:
            section.append(f"| {key} | {', '.join(str(score(r)) for r in samples)} | {', '.join(seconds(r) for r in samples)} s | {statistics.median(score(r) for r in samples):.0f} | {statistics.median(metric(r,'largest-contentful-paint') for r in samples)/1000:.2f} s |")
    (ROOT/'README.md').write_text(text.replace('## Resultado por página e dispositivo', '\n'.join(section)+'\n\n## Resultado por página e dispositivo'))
print(f'Relatório atualizado: {len(all_rows)}/{expected_runs} execuções; {len(unexpected)} erros inesperados.')
