# Revisão visual — primeira rodada, 14/10/2026

Prioridade escolhida: aparência de loja, mantendo a imagem do banner e a paleta.

## Referências

Foram extraídos seis frames do vídeo fornecido, nos segundos 0, 36, 60, 84, 108 e 132. O arquivo `frames-resumo.jpg` reúne os seis em ordem de leitura. As capturas `celular-depois.png` e `desktop-depois.png` mostram a home com as imagens carregadas e as seções reveladas.

## Alterações desta rodada

- Cabeçalho do celular em duas áreas: marca/ações e navegação. Os links deixam de ficar espremidos numa coluna.
- Submenu com largura que inclui padding e borda, evitando ultrapassar a tela de 320 px.
- Banner com `box-sizing: border-box`, altura e espaçamentos mais contidos.
- Cards da home em duas colunas no celular, imagens sem recorte e ações alinhadas.
- Classes de posicionamento do cabeçalho presentes no HTML inicial, evitando a troca de fluxo após executar o JavaScript. Sobreposição desativada no celular.
- Preservação de `srcset`, `sizes`, `decoding` e `fetchpriority` nos elementos de imagem gerados pelas APIs do WordPress/WooCommerce. O filtro genérico `wp_kses_post` removia esses atributos.
- Preload do banner alinhado ao `srcset` e `sizes` da imagem, para o navegador escolher o recurso adequado.
- Fallback para a imagem nativa do WooCommerce quando o anexo configurado como substituto não existe mais no disco. Nenhum dado de produto foi modificado.

## Resultados e limites

Medições em Chromium local, sem sessão administrativa e sem simulação de rede/CPU do Lighthouse. São verificações de desenvolvimento, não uma nova nota Lighthouse.

| Indicador local | Antes | Depois |
| --- | --- | --- |
| Cabeçalho em 320 px | 202 px | 140 px |
| Cabeçalho em 390 px | 160 px | 140 px |
| Banner em 390 px | 848 px | 480 px |
| CLS em 1366 px | 0,0586 | aproximadamente 0,000004 |
| CLS em 768 px | 0,0900 | aproximadamente 0,00001 |
| Recurso com erro 404 | imagem substituta | nenhum nos testes da home |

Os JSONs de medição inicial estão anexados para consulta. O LCP local variou entre execuções; não foi comprovada uma melhoria estável de LCP. As medidas locais não são comparáveis diretamente ao LCP próximo de 7,9 s da captura enviada. Também não foi medida uma nova nota global, TBT ou Speed Index.

A redução do arquivo do banner no celular não implica redução do tráfego total: restaurar imagens responsivas e aproximar conteúdo da primeira tela pode carregar outros recursos mais cedo. Uma rodada futura deve repetir o Lighthouse nas mesmas condições da captura original.

## Verificações

- Home em 320, 390, 768 e 1366 px: largura, altura, atributos do banner, imagens carregadas, erros JavaScript e submenu por teclado/Escape.
- Catálogo, página Sobre, carrinho vazio e página de produto em 390 px: sem transbordamento horizontal ou erros JavaScript.
- Sintaxe PHP e JavaScript e `git diff --check`.
- Inspeção visual das capturas desktop e celular.

## Próxima decisão

Revisar esta rodada antes de mudar novamente a estrutura. Possíveis próximos passos: busca acessível no celular ou refinamento dos cartões e fotos dos produtos. A imagem do banner, os textos comerciais e as condições de venda continuam como cadastrados.
