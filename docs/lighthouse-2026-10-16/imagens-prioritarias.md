# Imagens: pesos medidos e próximas ações

KiB = 1024 bytes. As metas abaixo são pontos de partida para exportação, não medições de arquivos já convertidos. Conferir nitidez, cores e transparência antes de substituir. Nenhum original foi sobrescrito.

| Uso | Arquivo / variante | Peso atual | Ação sugerida |
| --- | --- | ---: | --- |
| Fundo da abertura da home | `2026/10/depositphotos_352150452-stock-illustration-diamond-seamless-pattern-gem-vector.jpg` (1600×900) | 141.1 KiB | Exportar WebP/AVIF, mantendo proporção e enquadramento. Testar variante mobile entre 20–50 KiB e desktop entre 80–150 KiB; é uma textura detalhada e a qualidade visual deve decidir a meta final. |
| Variante fundo da abertura da home | `depositphotos_352150452-stock-illustration-diamond-seamless-pattern-gem-vector-768x432.jpg` (768×432) | 96.6 KiB | Conferir o uso em cada relatório Lighthouse. |
| Variante fundo da abertura da home | `depositphotos_352150452-stock-illustration-diamond-seamless-pattern-gem-vector-300x300.jpg` (300×300) | 74.5 KiB | Conferir o uso em cada relatório Lighthouse. |
| Logo do cabeçalho e rodapé | `2026/10/cropped-Logo-NC-semojias-transparente-1-2.png` (500×500) | 41.4 KiB | O tema já solicita a variante de 150 px (8,1 KiB no celular medido) em vez do original de 500 px (41,4 KiB). Opcional: testar WebP com transparência, sem redesenhar a marca. |
| Escapulário / categorias | `2026/10/images-6.jpg` (447×447) | 37.4 KiB | Testar WebP/AVIF em 300–600 px. Meta inicial de 10–20 KiB para miniaturas, revisando a qualidade. |
| Variante escapulário / categorias | `images-6-300x300.jpg` (300×300) | 21.8 KiB | Conferir o uso em cada relatório Lighthouse. |
| Tornozeleira / categorias | `2026/10/37-1.avif` (1000×1000) | 54.4 KiB | Já está em AVIF; verificar a variante realmente pedida pelo navegador antes de recomprimir. Priorizar dimensões e evitar degradar a joia. |
| Foto do brinco | `2026/09/cropped-brinco-prata-925-folha-trabalhada-02060478-02-20251008150735-1759948784.0888.webp` (1920×597) | 48.4 KiB | Original largo de 1920×597. Conferir proporção da foto e o recorte usado no card; isso afeta qualidade percebida, além do peso. |
| Foto da página Sobre | `2026/10/images-7.jpg` (447×447) | 16.9 KiB | 17 KiB no original: prioridade baixa diante do JavaScript do carrinho. |

O peso total da pasta uploads inclui originais e miniaturas não baixados na mesma página. Não use o tamanho da pasta como se fosse o peso de carregamento. O inventário completo está em [imagens.json](imagens.json).

A recomendação de formato e dimensão segue o [diagnóstico de entrega de imagens do Chrome](https://developer.chrome.com/docs/performance/insights/image-delivery).
