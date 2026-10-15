# Terceira rodada — Coleção e Novidades

As duas entradas usam o catálogo do WooCommerce; Novidades seleciona a ordenação por data. Os cartões reutilizam o estilo da home, com fotos quadradas sem recorte, preços maiores, títulos legíveis, identificação de fotos ausentes e botões de pelo menos 44 px. As ações nativas de compra e as classes de AJAX foram preservadas.

O estilo é compartilhado em `style.css`. O módulo `inc/product-catalog.php` acrescenta os avisos de foto ausente e estoque pelos hooks do Orchid Store, sem copiar seus templates. Ele também ajusta os links de menu da loja para usar a URL do arquivo de produtos: o redirecionamento da página cadastrada descartava o parâmetro de ordenação de Novidades e Mais vendidos. Os menus salvos não foram alterados.

A grade tem duas colunas no celular. Foram ajustadas a especificidade das regras diante do tema pai e as regras de clearfix que não pertencem a um layout em grid.

Validações no Chromium local: Coleção, Novidades e categoria em 320, 390, 768 e 1366 px, sem transbordamento ou erros JavaScript, com imagens carregadas e botões acessíveis. Verificado também o clique real no link Novidades e a ordenação por data. O PHP passou na verificação de sintaxe e o diff passou em `git diff --check`. Não foi realizada compra, nem nova medição Lighthouse.
