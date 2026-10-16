# E-commerce de Joias

Projeto guia desenvolvido durante meus estudos de Engenharia de Software.

## Objetivo

Desenvolver e evoluir um e-commerce de joias aplicando, na prática, os conceitos estudados de engenharia de software.

## Tecnologias

- WordPress
- WooCommerce
- Git e GitHub
- Docker Compose
- Tema filho personalizado baseado no Orchid Store

## Desenvolvimento do tema

O tema da loja fica em `wp-content/themes/ecommerce-joias` e herda o Orchid Store,
que deve permanecer instalado em `wp-content/themes/orchid-store`.
A pasta `my-theme`, quando presente, guarda o trabalho anterior apenas como
referência local e não é versionada.

Para iniciar o ambiente ou aplicar a alteração das montagens do Compose:

```bash
docker compose up -d
```

Em um clone novo, conclua a instalação do WordPress e instale pelo painel o
tema **Orchid Store** (Aparência → Temas → Adicionar tema) e o plugin
**WooCommerce** (Plugins → Adicionar plugin). Esses componentes de terceiros,
uploads e traduções não são versionados; o Git guarda o tema próprio.
O banco de dados fica no volume Docker local e também não acompanha o clone.
As credenciais do Compose são destinadas apenas ao ambiente de desenvolvimento.

Acesse `http://localhost:8080/wp-admin` e, em **Aparência → Temas**, ative
**Ecommerce Joias**. O Orchid Store deve permanecer instalado, mas o tema ativo
será o filho. A ativação pode exigir reconfigurar menus, widgets e opções visuais
anteriormente configurados em outro tema.

- `style.css`: identidade visual e estilos próprios, carregados após o CSS do pai.
  A versão do arquivo usa sua data de alteração para atualizar o cache do navegador.
- `functions.php`: funções próprias, ações e filtros. Use o prefixo
  `ecommerce_joias_` nas funções para evitar conflitos com o tema pai.
- Para alterar um template, copie somente o arquivo necessário do Orchid Store
  para o filho, mantendo o caminho relativo, e edite a cópia. Templates do
  WooCommerce ficam em `woocommerce/` dentro do filho e precisam ser revisados
  quando o plugin atualizar esses templates.

Os templates ausentes no filho são herdados do pai. O `functions.php` do filho
e o do pai são carregados pelo WordPress; não copie o `functions.php` do pai.
Para modificar hooks registrados pelo pai, faça isso em um hook posterior,
como `after_setup_theme`, com prioridade adequada.

Produtos, textos, imagens, menus e opções disponíveis no Personalizador são
gerenciados no painel do WordPress. Regras de negócio que precisam continuar
funcionando ao trocar de tema devem ficar em um plugin próprio.

### Página Sobre a loja

Em **Aparência → Personalizar → Sobre a loja**, configure os textos da abertura,
história, valores, informações e encerramento; as três fotos; as cores de fundo,
destaque e texto; e o texto e endereço do botão final. O link vazio usa a página
Produtos, com a loja WooCommerce como alternativa.

Os controles também permitem ajustar altura da abertura, posição vertical das
fotos, escurecimento e intensidade do parallax, além de ativar ou desativar
parallax, entradas suaves, movimento dos cartões, foto fixa da história e
encerramento. A foto fixa da história começa desativada. Parallax e foto fixa
funcionam no computador; a preferência por movimento reduzido desativa animações.
Sem fotos, abertura e encerramento exibem fundo suave e mantêm o texto visível.

O template atende à página com endereço `sobre-a-loja` ou pode ser escolhido como
**Sobre a loja** nas configurações de outra página. O texto adicional do editor
continua disponível abaixo das seções.

A foto da história (ou o bloco NC, quando não há foto) tem um efeito de pop-up
mais marcado: começa menor, sobe 64px, amplia um pouco além do tamanho final
e se acomoda. Por padrão, espera a área alcançar aproximadamente o meio da tela.
Em **Sobre a loja**, os controles **Efeito pop-up na foto / bloco NC**,
**Intensidade do pop-up da foto** e **Duração do pop-up da foto** ajustam esse
efeito independentemente das entradas dos demais textos. **Quanto descer a tela
antes do pop-up** ajusta o momento de entrada; quanto maior o valor, mais tarde
aparece. O efeito se prepara novamente ao voltar ao início ou quando a área
fica abaixo da tela, repetindo ao descer de novo. As demais entradas continuam
acontecendo uma vez, e movimento reduzido mantém tudo visível.

### Cards de produto

Em **Aparência → Personalizar → Cards de produto**, ajuste as cores do fundo,
área da foto, borda, nome e preço, além do arredondamento dos cantos. Os controles
valem para os destaques da home e cards WooCommerce. O texto “Ver produto” da
home segue **Loja NC → Texto para visualizar produto**. Fotos, nomes e preços
continuam sendo gerenciados no cadastro dos produtos.

### Cabeçalho transparente

Em **Aparência → Personalizar → Cabeçalho NC**, a opção **Cabeçalho transparente
sobre o hero com foto** sobrepõe a navegação à abertura da home e do Sobre. Quando
a abertura sai de trás do cabeçalho, o fundo e as cores normais retornam. Páginas
sem foto de abertura mantêm o cabeçalho normal. A cor de texto sobre a home tem
controle próprio; no Sobre, segue a cor **Textos sobre as fotos** dessa página.

### Carrossel de categorias

Em **Aparência → Personalizar → Página inicial**, a opção **Exibir categorias
em carrossel** alterna entre navegação horizontal e grade. Os cards têm largura
inicial de 200px e fotos quadradas. O movimento é contínuo em loop, pausa ao
passar o mouse e retoma ao sair. As setas laterais aparecem ao passar o mouse ou
focar com o teclado; em telas de toque ficam visíveis. Elas avançam um card por
vez. O painel permite ajustar largura, velocidade e movimento automático.
Há também um botão para pausar permanentemente até escolher retomar.
No celular, é possível deslizar. Foco por teclado e interação por toque pausam o
movimento; fora da tela ou com a aba oculta, a animação para. Movimento reduzido
desativa a rotação automática e mantém as setas. Uma única categoria fica estática.
Todas as categorias com produtos participam, ordenadas pela quantidade de
produtos. Nomes e fotos são editados em **Produtos → Categorias**.

### Vitrine e transições da home

Em **Aparência → Personalizar → Página inicial**, ajuste a largura máxima dos
cards de produtos da home (216px por padrão), os fundos de categorias e produtos
e a altura dos degradês entre seções. A vitrine tem fotos quadradas e centraliza
os produtos, com adaptação para telas pequenas. Os cards WooCommerce de outras
páginas continuam seguindo os controles de **Cards de produto**.

As opções **Suavizar as mudanças de fundo entre seções** e **Entrada suave dos
títulos e cards ao rolar** são independentes. As entradas acontecem uma vez;
conteúdo já visível, foco de teclado, movimento reduzido e ausência de JavaScript
mantêm os elementos acessíveis. Os degradês acompanham as cores das seções
presentes, incluindo o fechamento junto ao rodapé.

As referências e a adaptação de design estão em
[referencias-transicoes-home.md](docs/referencias-transicoes-home.md).


### Auditoria de desempenho do tema

A rodada de 16/10/2026 cobre todas as páginas e cenários inventariados em mobile
e desktop, incluindo conta autenticada, carrinho e checkout. Consulte o
[relatório completo](docs/lighthouse-2026-10-16/README.md), a
[tabela interativa](docs/lighthouse-2026-10-16/index.html) e os
[pesos de imagens e recomendações](docs/lighthouse-2026-10-16/imagens-prioritarias.md).
Os resultados são medições locais; carrinho e checkout de blocos ainda exigem
melhorias no celular. Os relatórios Lighthouse completos estão disponíveis
localmente e são ignorados pelo Git; resumos e scripts são versionados.

`inc/performance.php` concentra o carregamento condicional, a fonte Inter local,
as imagens prioritárias e o logo responsivo. A solicitação de link de recuperação
não baixa o medidor de senha; redefinição e edição de conta continuam com ele.
O `.htaccess` do tema configura 30 dias de cache apenas para CSS, JS e fontes no
Apache. Ao substituir uma fonte, use outro nome de arquivo para invalidar o cache.
