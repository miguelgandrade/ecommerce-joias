# Segunda rodada — cartões de produtos da home

Alterações concentradas em `front-page.php` e no bloco final de `style.css`.

- Preços maiores e títulos com quebra de linha mais natural.
- Fotos inteiras com margem interna; produtos sem imagem mostram a identificação “Foto indisponível”.
- Botões com moldura e altura mínima de 44 px, alinhados ao final do cartão.
- Selos “Promoção” ou “Esgotado” condicionados aos dados do WooCommerce. Esgotado tem precedência; os links continuam levando à página do produto.
- Links de imagem e botão identificam o produto para leitores de tela.
- Mantidos carregamento lazy, imagens responsivas e as cores configuráveis dos cartões.

As capturas `produtos-celular.png` e `produtos-desktop.png` registram o resultado. Os dados de preço, estoque e fotos não foram modificados.

Verificação no navegador em 320, 390, 768 e 1366 px: sem transbordamento horizontal, imagens carregadas, links identificados, botões de pelo menos 44 px e nenhum erro JavaScript. Sintaxe PHP e `git diff --check` aprovados. Esta rodada não incluiu uma nova medição Lighthouse.
