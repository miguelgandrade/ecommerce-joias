# Novidades e Mais vendidos — apresentações próprias

## Organização

As seleções possuem endereços próprios sobre o arquivo de produtos do WooCommerce:

- `/?post_type=product&nc_vitrine=novidades`
- `/?post_type=product&nc_vitrine=mais-vendidos`
- `/?post_type=product` para toda a coleção.

Não foram criadas páginas duplicadas no banco. A variável pública `nc_vitrine` identifica a apresentação e continua presente ao mudar a ordenação ou navegar pelo catálogo. Os menus existentes são adaptados na renderização, sem alterar registros salvos.

O módulo `inc/catalog-views.php` registra essa variável, os padrões de ordenação, as aberturas, os títulos do documento, o item ativo do menu e a seleção de produtos vendidos. O CSS mantém a paleta e os cartões já aprovados. Não foram adicionados scripts ou imagens externas.

Novidades apresenta primeiro os produtos publicados mais recentemente. Mais vendidos usa a ordenação nativa por vendas e inclui apenas produtos com `total_sales > 0`. São vendas acumuladas, sem recorte mensal. Como todos os produtos da instalação estão com zero vendas, essa seleção exibe um estado vazio com acesso às novidades; nenhum ranking ou prova social foi inventado.

## Referências

- Frames extraídos do vídeo da loja: cores, tipografia e estilo dos cartões existentes.
- [Novidades Vivara](https://www.vivara.com.br/lancamentos): título específico e apresentação curta da seleção.
- [Lançamentos de joias Vivara](https://www.vivara.com.br/vivara/joias/lancamentos): catálogo com ordenação de lançamentos.
- A pesquisa também localizou a seleção Best Sellers da Monte Carlo; a página completa não pôde ser recuperada por timeout. Ela não foi usada como referência visual direta.

## Verificação

As três seleções foram verificadas no Chromium em 320, 390, 768 e 1366 px: título próprio, menu e aba ativos, ausência de transbordamento, produtos/estado vazio e nenhum erro JavaScript. A troca de ordenação mantém Novidades. O clique real nos três links do menu leva ao destino correspondente.

O ranking com vendas e a paginação foram verificados em uma transação SQL com dois valores temporários de vendas, revertida com `ROLLBACK`. O teste confirmou ordem decrescente, exclusão de produtos com zero vendas e duas páginas com um item por página. Nenhum dado de teste persistiu.

PHP e diff verificados. Não houve nova medição Lighthouse. As imagens `pagina-*-celular.png` e `pagina-*-desktop.png` registram a apresentação entregue.
