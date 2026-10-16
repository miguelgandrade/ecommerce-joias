# Lighthouse — tema completo — 16/10/2026

As páginas próprias ficaram mais leves. Carrinho e checkout de blocos continuam sendo o principal gargalo no celular. Nenhuma cobrança real foi feita.

Foram concluídas 86/86 execuções finais: 43 mobile e 43 desktop. O manifesto inclui todas as páginas publicadas, seis produtos, sete categorias, vitrines, buscas vazias, recuperação de senha, carrinho vazio/preenchido, checkout e quatro telas autenticadas. Duas entradas extras medem o conteúdo de erro com status normalizado no navegador.

Abra [a tabela interativa](index.html) no navegador para filtrar as páginas e acessar os relatórios completos locais.

## Método e limites

- Lighthouse 13.5.0 e Chrome for Testing 155.0.8059.84, Node 22.19.0, loja local WordPress/WooCommerce em Docker no WSL2. Testes sequenciais, sem extensões ou barra administrativa. Mobile usa configuração padrão e throttling simulado; desktop usa 1350×940, RTT de 40 ms, 10 Mbit/s e CPU sem redução.
- Cada execução abre um perfil novo. Carrinho/checkout são preparados com um produto real do catálogo, sem pedido; o cache HTTP é limpo após a preparação. Clientes temporários são criados para a conta, sem e-mail, e removidos ao final. Nenhuma credencial é persistida nos relatórios.
- A linha de base é uma amostra por cenário representativo. O resultado de uma página não representa todas as outras. Variações pequenas de nota ou tempo não comprovam ganho estável; o volume transferido é evidência adicional.
- Estes são ensaios de laboratório local, não dados de usuários reais nem resultado de PageSpeed Insights em hospedagem pública. Não reproduzem o painel de administração nem pedidos reais de clientes.
- O Lighthouse interrompe a auditoria ao receber 404/503. As rotas reais conservam esses códigos. As linhas com sufixo `-render` usam o mesmo HTML, com resposta 200 somente interceptada no navegador de teste. Suas notas avaliam renderização; não validam o SEO ou o status HTTP de produção. O erro original também foi salvo.
- SEO de carrinho, checkout, conta, buscas e páginas transitórias pode ser baixo por noindex intencional ou ausência de descrição. Não se deve expor essas telas à indexação só para aumentar a nota.
- Relatórios brutos HTML/JSON estão disponíveis nesta cópia local, mas são ignorados pelo Git para evitar dezenas de MB no repositório. Resumos, inventário e scripts são mantidos; capturas ficam disponíveis localmente.

## Comparação mobile

| Página | Antes | Depois | LCP antes → depois | Transferência antes → depois |
| --- | ---: | ---: | --- | --- |
| Início | 92 | 98 | 3.24 → 2.26 s | 556 → 424 KiB |
| Loja | 82 | 91 | 3.67 → 3.19 s | 447 → 341 KiB |
| Produtos | 86 | 100 | 3.31 → 1.39 s | 439 → 173 KiB |
| Sobre a loja | 87 | 100 | 3.16 → 1.21 s | 333 → 67 KiB |
| Como podemos ajudar? | 90 | 100 | 3.01 → 1.19 s | 313 → 47 KiB |
| Minha conta | 87 | 94 | 3.32 → 2.77 s | 386 → 280 KiB |
| Brinco bacana | 82 | 88 | 3.55 → 3.49 s | 461 → 428 KiB |
| Carrinho | 49 | 52 | 9.02 → 8.89 s | 1169 → 1136 KiB |
| Carrinho com produto | 39 | 38 | 9.21 → 8.01 s | 1214 → 1108 KiB |
| Finalizar compra com produto | 55 | 38 | 8.75 → 8.46 s | 1135 → 1029 KiB |
| Página 404 (conteúdo com status normalizado só no navegador de teste) | 89 | 100 | 3.15 → 1.26 s | 345 → 70 KiB |
| Voltamos em breve (conteúdo com status normalizado só no navegador de teste) | 87 | 100 | 3.30 → 1.26 s | 346 → 71 KiB |
| Recuperar senha | 74 | 94 | 5.27 → 2.78 s | 680 → 280 KiB |

A linha de base de recuperação de senha foi capturada após os ajustes gerais, antes da remoção do avaliador da tela que pede e-mail. Os produtos e a página de exemplo foram repetidos após os últimos ajustes de prioridade de imagem e alvos de toque.

## Variação de carrinho e checkout

Repetições mobile do estado final, com perfil novo, carrinho preparado e cache HTTP limpo em cada execução. A mediana final não tem uma mediana correspondente antes das mudanças; não comprova causalidade por si só.

| Cenário | Notas nas três execuções | LCP nas três execuções | Mediana de desempenho | Mediana LCP |
| --- | --- | --- | ---: | ---: |
| carrinho-preenchido | 38, 45, 42 | 8.01, 8.60, 8.61 s | 42 | 8.60 s |
| checkout | 38, 51, 48 | 8.46, 8.45, 8.44 s | 48 | 8.45 s |

## Resultado por página e dispositivo

| Página | Dispositivo | Desempenho | Acessib. | Boas práticas | SEO | FCP | LCP | TBT | CLS | KiB |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| [Início](depois/home-mobile.html) | mobile | 98 | 100 | 100 | 100 | 0.91 s | 2.26 s | 73 ms | 0.000 | 424 |
| [Loja](depois/loja-mobile.html) | mobile | 91 | 100 | 100 | 100 | 1.97 s | 3.19 s | 6 ms | 0.000 | 341 |
| [Carrinho](depois/carrinho-mobile.html) | mobile | 52 | 100 | 100 | 58 | 3.95 s | 8.89 s | 412 ms | 0.000 | 1136 |
| [Minha conta](depois/minha-conta-mobile.html) | mobile | 94 | 100 | 100 | 58 | 2.12 s | 2.77 s | 0 ms | 0.000 | 280 |
| [Produtos](depois/produtos-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.91 s | 1.39 s | 4 ms | 0.000 | 173 |
| [Sobre a loja](depois/sobre-a-loja-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.91 s | 1.21 s | 33 ms | 0.000 | 67 |
| [Como podemos ajudar?](depois/ajuda-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.90 s | 1.19 s | 20 ms | 0.000 | 47 |
| [Entrega e frete](depois/entrega-e-frete-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.91 s | 1.16 s | 10 ms | 0.000 | 47 |
| [Trocas e devoluções](depois/trocas-e-devolucoes-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.90 s | 1.15 s | 0 ms | 0.000 | 47 |
| [Cancelamentos e reembolsos](depois/cancelamentos-e-reembolsos-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.91 s | 1.18 s | 12 ms | 0.000 | 47 |
| [Cuidados com as peças](depois/cuidados-com-as-pecas-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.91 s | 1.18 s | 14 ms | 0.000 | 47 |
| [Guia de tamanhos](depois/guia-de-tamanhos-mobile.html) | mobile | 100 | 100 | 100 | 100 | 0.91 s | 1.23 s | 73 ms | 0.000 | 48 |
| [Pagamento não concluído](depois/pagamento-nao-concluido-mobile.html) | mobile | 100 | 100 | 100 | 58 | 0.90 s | 1.17 s | 15 ms | 0.000 | 47 |
| [Pedido cancelado](depois/pedido-cancelado-mobile.html) | mobile | 100 | 100 | 100 | 58 | 0.91 s | 1.17 s | 11 ms | 0.000 | 47 |
| [Voltamos em breve](depois/manutencao-mobile.html) | mobile | — | — | — | — | — s | — s | — ms | — | — |
| [Categoria Sem categoria](depois/categoria-sem-categoria-mobile.html) | mobile | 98 | 100 | 100 | 100 | 1.67 s | 2.29 s | 0 ms | 0.000 | 207 |
| [Categoria Aneis](depois/categoria-aneis-mobile.html) | mobile | 97 | 100 | 100 | 100 | 1.82 s | 2.43 s | 0 ms | 0.000 | 266 |
| [Categoria Brincos](depois/categoria-brinco-mobile.html) | mobile | 95 | 100 | 100 | 100 | 1.82 s | 2.73 s | 0 ms | 0.000 | 269 |
| [Categoria Colar](depois/categoria-colar-mobile.html) | mobile | 95 | 100 | 100 | 100 | 1.82 s | 2.73 s | 0 ms | 0.000 | 271 |
| [Categoria Escapulário](depois/categoria-escapulario-mobile.html) | mobile | 95 | 100 | 100 | 100 | 1.81 s | 2.74 s | 3 ms | 0.000 | 293 |
| [Categoria Pulseiras](depois/categoria-pulseiras-mobile.html) | mobile | 97 | 100 | 100 | 100 | 1.81 s | 2.42 s | 0 ms | 0.000 | 272 |
| [Categoria Tornozeleira](depois/categoria-tornozeleira-mobile.html) | mobile | 94 | 100 | 100 | 100 | 1.81 s | 2.89 s | 11 ms | 0.000 | 288 |
| [Novidades](depois/novidades-mobile.html) | mobile | 91 | 100 | 100 | 100 | 1.81 s | 3.26 s | 19 ms | 0.000 | 363 |
| [Mais Vendidos](depois/mais-vendidos-mobile.html) | mobile | 98 | 100 | 100 | 100 | 1.52 s | 2.28 s | 0 ms | 0.000 | 207 |
| [Busca de produtos sem resultados](depois/busca-vazia-mobile.html) | mobile | 98 | 100 | 100 | 63 | 1.67 s | 2.28 s | 0 ms | 0.000 | 207 |
| [Busca geral sem resultados](depois/busca-geral-vazia-mobile.html) | mobile | 100 | 100 | 100 | 54 | 0.91 s | 1.17 s | 10 ms | 0.000 | 47 |
| [Página 404](depois/404-mobile.html) | mobile | — | — | — | — | — s | — s | — ms | — | — |
| [Carrinho com produto](depois/carrinho-preenchido-mobile.html) | mobile | 38 | 100 | 100 | 58 | 2.63 s | 8.01 s | 2137 ms | 0.031 | 1108 |
| [Finalizar compra com produto](depois/checkout-mobile.html) | mobile | 38 | 100 | 100 | 58 | 3.05 s | 8.46 s | 2119 ms | 0.000 | 1029 |
| [Voltamos em breve (conteúdo com status normalizado só no navegador de teste)](depois/manutencao-render-mobile.html) | mobile | 100 | 100 | 100 | 58 | 0.90 s | 1.26 s | 11 ms | 0.000 | 71 |
| [Página 404 (conteúdo com status normalizado só no navegador de teste)](depois/404-render-mobile.html) | mobile | 100 | 100 | 100 | 91 | 0.90 s | 1.26 s | 20 ms | 0.000 | 70 |
| [Painel da conta autenticada](depois/conta-dashboard-mobile.html) | mobile | 91 | 100 | 100 | 58 | 2.27 s | 3.02 s | 22 ms | 0.000 | 306 |
| [Endereços da conta autenticada](depois/conta-edit-address-mobile.html) | mobile | 94 | 100 | 100 | 58 | 1.97 s | 2.82 s | 74 ms | 0.000 | 256 |
| [Dados da conta autenticada](depois/conta-edit-account-mobile.html) | mobile | 72 | 100 | 100 | 58 | 2.29 s | 5.33 s | 278 ms | 0.000 | 680 |
| [Pedidos (conta sem compras)](depois/conta-orders-mobile.html) | mobile | 91 | 100 | 100 | 58 | 2.27 s | 3.02 s | 6 ms | 0.000 | 306 |
| [Página de exemplo](depois/pagina-exemplo-mobile.html) | mobile | 95 | 100 | 100 | 100 | 2.03 s | 2.48 s | 16 ms | 0.000 | 208 |
| [Brinco bacana](depois/produto-92-mobile.html) | mobile | 88 | 100 | 100 | 100 | 2.28 s | 3.49 s | 0 ms | 0.000 | 428 |
| [Tornozeleira chique](depois/produto-82-mobile.html) | mobile | 85 | 100 | 100 | 100 | 2.42 s | 3.86 s | 0 ms | 0.000 | 473 |
| [Escapulário Bonito](depois/produto-76-mobile.html) | mobile | 85 | 100 | 100 | 100 | 2.42 s | 3.79 s | 0 ms | 0.000 | 408 |
| [Pulseira prata](depois/produto-34-mobile.html) | mobile | 87 | 100 | 100 | 100 | 2.43 s | 3.53 s | 0 ms | 0.000 | 387 |
| [Colar Elegance](depois/produto-33-mobile.html) | mobile | 86 | 100 | 100 | 100 | 2.43 s | 3.65 s | 0 ms | 0.000 | 430 |
| [Anel Dourado](depois/produto-29-mobile.html) | mobile | 87 | 100 | 100 | 100 | 2.43 s | 3.51 s | 0 ms | 0.000 | 381 |
| [Recuperar senha](depois/recuperar-senha-mobile.html) | mobile | 94 | 100 | 100 | 58 | 2.12 s | 2.78 s | 7 ms | 0.000 | 280 |
| [Início](depois/home-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.26 s | 0.55 s | 0 ms | 0.003 | 465 |
| [Página de exemplo](depois/pagina-exemplo-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.43 s | 0.56 s | 0 ms | 0.005 | 252 |
| [Loja](depois/loja-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.46 s | 0.67 s | 0 ms | 0.000 | 323 |
| [Carrinho](depois/carrinho-desktop.html) | desktop | 75 | 100 | 100 | 58 | 0.60 s | 1.91 s | 243 ms | 0.162 | 1137 |
| [Minha conta](depois/minha-conta-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.49 s | 0.60 s | 0 ms | 0.000 | 276 |
| [Produtos](depois/produtos-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.25 s | 0.31 s | 0 ms | 0.000 | 105 |
| [Sobre a loja](depois/sobre-a-loja-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.24 s | 0.29 s | 0 ms | 0.000 | 64 |
| [Como podemos ajudar?](depois/ajuda-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.25 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Entrega e frete](depois/entrega-e-frete-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.24 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Trocas e devoluções](depois/trocas-e-devolucoes-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.25 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Cancelamentos e reembolsos](depois/cancelamentos-e-reembolsos-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.24 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Cuidados com as peças](depois/cuidados-com-as-pecas-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.25 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Guia de tamanhos](depois/guia-de-tamanhos-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.24 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Pagamento não concluído](depois/pagamento-nao-concluido-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.25 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Pedido cancelado](depois/pedido-cancelado-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.24 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Voltamos em breve](depois/manutencao-desktop.html) | desktop | — | — | — | — | — s | — s | — ms | — | — |
| [Brinco bacana](depois/produto-92-desktop.html) | desktop | 96 | 100 | 100 | 100 | 0.50 s | 0.74 s | 0 ms | 0.109 | 421 |
| [Tornozeleira chique](depois/produto-82-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.53 s | 0.76 s | 0 ms | 0.000 | 454 |
| [Escapulário Bonito](depois/produto-76-desktop.html) | desktop | 99 | 100 | 100 | 100 | 0.54 s | 0.77 s | 0 ms | 0.000 | 405 |
| [Pulseira prata](depois/produto-34-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.54 s | 0.73 s | 0 ms | 0.000 | 383 |
| [Colar Elegance](depois/produto-33-desktop.html) | desktop | 99 | 100 | 100 | 100 | 0.54 s | 0.76 s | 0 ms | 0.000 | 419 |
| [Anel Dourado](depois/produto-29-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.54 s | 0.72 s | 0 ms | 0.000 | 378 |
| [Categoria Sem categoria](depois/categoria-sem-categoria-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.42 s | 0.55 s | 0 ms | 0.000 | 251 |
| [Categoria Aneis](depois/categoria-aneis-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.41 s | 0.58 s | 0 ms | 0.000 | 259 |
| [Categoria Brincos](depois/categoria-brinco-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.42 s | 0.55 s | 0 ms | 0.000 | 266 |
| [Categoria Colar](depois/categoria-colar-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.42 s | 0.59 s | 0 ms | 0.000 | 257 |
| [Categoria Escapulário](depois/categoria-escapulario-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.42 s | 0.58 s | 0 ms | 0.000 | 274 |
| [Categoria Pulseiras](depois/categoria-pulseiras-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.41 s | 0.58 s | 0 ms | 0.000 | 263 |
| [Categoria Tornozeleira](depois/categoria-tornozeleira-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.42 s | 0.59 s | 0 ms | 0.000 | 263 |
| [Novidades](depois/novidades-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.42 s | 0.64 s | 0 ms | 0.000 | 322 |
| [Mais Vendidos](depois/mais-vendidos-desktop.html) | desktop | 100 | 100 | 100 | 100 | 0.42 s | 0.50 s | 0 ms | 0.000 | 251 |
| [Busca de produtos sem resultados](depois/busca-vazia-desktop.html) | desktop | 100 | 100 | 100 | 63 | 0.42 s | 0.56 s | 0 ms | 0.000 | 251 |
| [Busca geral sem resultados](depois/busca-geral-vazia-desktop.html) | desktop | 100 | 100 | 100 | 54 | 0.25 s | 0.28 s | 0 ms | 0.000 | 44 |
| [Página 404](depois/404-desktop.html) | desktop | — | — | — | — | — s | — s | — ms | — | — |
| [Recuperar senha](depois/recuperar-senha-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.49 s | 0.62 s | 0 ms | 0.000 | 277 |
| [Carrinho com produto](depois/carrinho-preenchido-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.56 s | 0.69 s | 15 ms | 0.017 | 1067 |
| [Finalizar compra com produto](depois/checkout-desktop.html) | desktop | 91 | 100 | 100 | 58 | 0.55 s | 1.69 s | 120 ms | 0.001 | 1026 |
| [Voltamos em breve (conteúdo com status normalizado só no navegador de teste)](depois/manutencao-render-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.24 s | 0.31 s | 0 ms | 0.000 | 68 |
| [Página 404 (conteúdo com status normalizado só no navegador de teste)](depois/404-render-desktop.html) | desktop | 100 | 100 | 100 | 91 | 0.24 s | 0.31 s | 0 ms | 0.000 | 67 |
| [Painel da conta autenticada](depois/conta-dashboard-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.58 s | 0.70 s | 0 ms | 0.003 | 350 |
| [Endereços da conta autenticada](depois/conta-edit-address-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.52 s | 0.67 s | 0 ms | 0.000 | 300 |
| [Dados da conta autenticada](depois/conta-edit-account-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.51 s | 0.66 s | 2 ms | 0.000 | 676 |
| [Pedidos (conta sem compras)](depois/conta-orders-desktop.html) | desktop | 100 | 100 | 100 | 58 | 0.58 s | 0.70 s | 0 ms | 0.000 | 350 |

## Alterações aplicadas

- Templates completos do filho dispensam CSS, JavaScript e fontes de ícones do pai e estilos WooCommerce não usados. Páginas com blocos/shortcodes de compra mantêm os recursos. Catálogo, produto, conta, carrinho e checkout conservam as dependências de sua implementação nativa.
- Ícones essenciais do cabeçalho/voltar ao topo são SVGs pequenos. As fontes de ícones permanecem nas telas herdadas que ainda usam seus símbolos.
- Inter é servida pelo tema com `font-display:swap`, pesos 400–800, subconjuntos latino/latino estendido e licença OFL. A tipografia da loja foi preservada.
- O logo informa `sizes` compatível com sua exibição. No celular, o navegador passou do PNG de 500 px / 41,4 KiB para a variante de 150 px / 8,1 KiB. Originais e identidade visual não foram sobrescritos.
- Catálogo/produtos usam `defer` nativo do WordPress e prioridade alta somente na primeira miniatura da vitrine ou na foto principal do produto. A tentativa extra de `defer` em carrinho/checkout não apresentou ganho consistente e foi retirada.
- CSS, JavaScript e fontes do próprio tema recebem 30 dias de cache estático no Apache. O HTML e as respostas PHP de carrinho/conta continuam sem cache. Fontes substituídas devem receber novos nomes; CSS/JS já usam versão por alteração.
- Corrigidos contraste, links identificáveis, rótulos de quantidade, alvo do botão de senha e nome acessível do carrinho. Descrições de páginas públicas foram acrescentadas, respeitando plugins de SEO e as telas que não devem ser indexadas.
- Corrigidos cards vazios na ajuda: o conteúdo gerado agora é um bloco HTML, impedindo a quebra dos links pelo `wpautop`. Conteúdo alterado pelo usuário é preservado pelo script de configuração.

O medidor de força de senha deixou de ser carregado na solicitação de link por e-mail, eliminando cerca de 391 KiB de zxcvbn e suas dependências nessa tela. Continua habilitado na redefinição e na edição da conta, que possuem campos de senha.

## O que ainda melhorar, por prioridade

1. **Carrinho e checkout — impacto alto.** Mantêm os blocos atuais. Na linha de base, `wp-components` sozinho transferiu cerca de 266 KiB (817 KiB descompactados); há React, dados e outros pacotes necessários à hidratação. Não remover esses arquivos isoladamente. Um próximo experimento é comparar, em cópia da loja, carrinho/checkout clássicos com o visual atual; isso muda a implementação e exige validar cupons, frete, gateways e extensões. Não foi feito nesta rodada.
2. **Fundo do hero da home — impacto médio/alto.** A variante mobile `depositphotos_…-768x432.jpg` tem 96,6 KiB, e o original 141,1 KiB. Exportar WebP/AVIF com a mesma aparência e testar qualidade. Metas iniciais: 20–50 KiB no mobile e 80–150 KiB no desktop; são alvos, não economia garantida. Veja [o inventário de imagens](imagens-prioritarias.md).
3. **Fotos do catálogo — impacto médio.** `images-6-300x300.jpg` tem 21,8 KiB e o original tem 37,4 KiB. Testar WebP/AVIF, dimensões reais dos cards e qualidade. Algumas imagens já são WebP/AVIF e não devem ser recomprimidas sem necessidade. Conferir nitidez e recorte da imagem larga do brinco.
4. **Cache de imagens e recursos de terceiros — impacto em visitas repetidas.** O cache aplicado aqui cobre somente o tema próprio. Uploads, fontes de ícones do pai e arquivos WordPress/WooCommerce ainda precisam de política no servidor/CDN. Preferir URLs versionadas/nomes novos ao substituir imagens. Não aplicar cache público a HTML de carrinho, checkout, Minha conta, APIs ou sessões.
5. **Hospedagem e cache de páginas públicas — validar em produção.** Medir TTFB, configurar OPcache e cache para visitantes no servidor/plugin, com exclusões de sessão e páginas de compra. Docker local não serve de previsão do tempo de uma hospedagem pública. A compressão gzip já está ativa; simplesmente habilitá-la de novo não é a solução.
6. **CSS/ícones legados nas telas nativas — impacto médio, mudança maior.** O tema pai ainda fornece layout e recursos para catálogo, produto e conta. Há CSS não usado na primeira tela e Boxicons de aproximadamente 73 KiB. Migrar esses componentes aos poucos, com comparação visual e funcional. Não remover uma folha inteira com base apenas na cobertura acima da dobra.
7. **Atribuição de pedidos — investigar se necessário.** O relatório inicial apontou execução relevante em `order-attribution.min.js`. Só considerar desativar a atribuição se a loja não precisar dos relatórios de origem das vendas; isso é uma decisão de operação, não uma remoção automática de script.
8. **Edição da conta — custo conhecido.** A tela de dados da conta carrega o avaliador de força de senha porque permite cadastrar uma senha nova. O pacote zxcvbn transfere cerca de 391 KiB. Uma evolução possível é carregá-lo ao começar a editar a senha, preservando a validação, bloqueio de senha fraca e compatibilidade com extensões. Não foi adiado automaticamente nesta rodada.
9. **CSS próprio e manutenção — prioridade baixa.** `style.css` tem aproximadamente 92 KiB sem compressão e 16 KiB transferidos. Separar/minificar por página pode ajudar, mas não deve vir antes do carrinho e do hero. Manter animações fora de tela pausadas e a preferência de movimento reduzido, já atendidas no JS atual.
10. **Conteúdo e publicação.** Revisar a página de exemplo publicada, textos e políticas comerciais antes de colocar a loja em produção. As notas locais não validam frete real, entrega de e-mails, políticas comerciais ou gateways.

## Validação e reprodução

- Passaram os testes HTTP existentes, a integração WooCommerce com dados descartáveis e a sintaxe PHP. O navegador aprovou 111 cenários de layout em 390, 768 e 1366 px, 31 cenários de cabeçalho (incluindo barra administrativa simulada) e o fluxo adicional AJAX → quantidade persistida → checkout. Os testes HTTP e de integração somam 54 verificações aprovadas. Capturas e resultados estão em `navegador/` e `navegador-fluxo/`.
- Erros inesperados nas execuções finais: 0. Erros HTTP 404/503 esperados são identificados separadamente.
- Os controles de quantidade e a preservação do medidor no formulário real de redefinição foram conferidos separadamente no navegador. Resultados em `navegador/quantity-controls.json` e `navegador/password-resources.json`.
- Resumos: [mobile](depois/summary-mobile.json), [desktop](depois/summary-desktop.json), [linha de base](antes/summary-mobile.json). Manifesto: [rotas.json](rotas.json). Inventário: [imagens.json](imagens.json).

As ferramentas foram instaladas somente em `/tmp/nc-lighthouse-tools`. Para repetir com Node >=22.19, Lighthouse 13.5.0, Playwright e Chrome disponíveis:

```bash
export NC_LIGHTHOUSE_TOOLS=/caminho/para/ferramentas
export CHROME_PATH=/caminho/para/chrome
node scripts/audit-lighthouse.mjs docs/lighthouse-2026-10-16/rotas.json /tmp/nova-auditoria mobile
node scripts/audit-lighthouse.mjs docs/lighthouse-2026-10-16/rotas.json /tmp/nova-auditoria desktop
# Para reconstruir o relatório desta rodada com os dados salvos em docs:
python3 scripts/summarize-lighthouse.py
```

Verificações adicionais: `node scripts/test-product-controls.mjs` e `node scripts/test-password-resources.mjs`, com as mesmas variáveis de ferramentas. A segunda cria e remove um cliente temporário, testa o formulário real de redefinição e confirma a avaliação de senha fraca, sem enviar e-mail ou concluir uma troca de senha real.

O runner ignora IDs que já estejam no resumo de destino. Use uma pasta nova para repetir medições. As rotas da conta requerem Docker Compose e removem os usuários temporários ao final; se o processo for terminado à força, confira usuários com prefixo `nc-perf-`.

## Fontes oficiais

- [Lighthouse CLI](https://github.com/GoogleChrome/lighthouse) — execução, cache e flags.
- [Entrega de imagens](https://developer.chrome.com/docs/performance/insights/image-delivery) — formato, dimensão e economia estimada.
- [Cache de recursos estáticos](https://developer.chrome.com/docs/performance/insights/cache) e [Apache mod_expires](https://httpd.apache.org/docs/2.4/mod/mod_expires.html).
- [Imagens responsivas no WordPress](https://developer.wordpress.org/reference/functions/wp_get_attachment_image/) e [estratégias de scripts](https://developer.wordpress.org/reference/functions/wp_enqueue_script/).
- [Inter e licença OFL](https://github.com/google/fonts/tree/main/ofl/inter).
