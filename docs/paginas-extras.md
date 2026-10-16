# Páginas de ajuda e recuperação

Implementação local de 16/10/2026, aprovada após a maquete em `proposta-paginas-extras/preview.html`.

- `404.php`: erro HTTP 404, busca de produtos, coleção, início e ajuda.
- `search.php` e hook `woocommerce_no_products_found`: busca vazia com nova busca e limpeza dos filtros via URL do catálogo; preserva o estado específico de mais vendidos.
- Carrinho vazio: conserva a implementação existente em `inc/cart.php`.
- `page-ajuda.php`: template editável, breadcrumb e contato a partir das configurações do rodapé.
- `inc/support.php`: mensagens em pedidos realmente cancelados/com falha, verificando proprietário, administrador ou chave do pedido. Não altera pagamentos ou status.
- Página de manutenção: apenas sua própria URL responde 503 e Retry-After; não ativa manutenção global. Páginas transitórias recebem noindex.

## Conteúdo no WordPress

Central de ajuda, entrega e frete, trocas e devoluções, cancelamentos e reembolsos, cuidados, tamanhos, pagamento não concluído, pedido cancelado e manutenção. A central e os links do rodapé permitem acessar as orientações. Manutenção tem acesso separado, sem link comercial.

Edite em **Páginas**. Canais de contato em **Aparência → Personalizar → Rodapé NC**. Os textos não definem prazos de frete, de estorno ou garantias comerciais sem configuração da operação.

Para reproduzir em outro banco local:

```bash
docker compose exec -T -e NC_SETUP_SUPPORT=1 wordpress php < scripts/setup-support-pages.php
```

O script preserva páginas encontradas pelo slug e configurações já preenchidas; só completa uma central de ajuda sem conteúdo. Não executa automaticamente ao visitar a loja.

## Verificação

Sintaxe PHP do tema, nove páginas por HTTP, 404 real, buscas vazias geral e de produtos, 503 e Retry-After da manutenção, teste HTTP existente e integração WooCommerce passaram. Integração usa dados descartáveis, sem cobrança real. A revisão posterior instalou ferramentas temporárias e conferiu o layout em 390, 768 e 1366 px, além de executar Lighthouse mobile/desktop. Os detalhes estão em [Lighthouse — tema completo](lighthouse-2026-10-16/README.md).
