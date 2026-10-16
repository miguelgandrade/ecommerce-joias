# E-commerce de Joias

Projeto guia desenvolvido durante meus estudos de Engenharia de Software.

## Objetivo

Desenvolver e evoluir um template personalizado para um e-commerce de joias aplicando, na prática, os conceitos estudados de engenharia de software.

## Tecnologias

- WordPress
- WooCommerce
- Git e GitHub
- Docker Compose
- Tema filho personalizado baseado no Orchid Store

## Ambiente de desenvolvimento e sistemas compatíveis

O ambiente principal de desenvolvimento deste tema é o **Ubuntu no WSL2**, dentro
do Windows, com o projeto salvo no sistema de arquivos Linux (por exemplo,
`~/projetos/ecommerce-joias`). O tema em si é um tema WordPress em PHP, CSS e
JavaScript: ele não depende do WSL e também pode ser executado no Windows, no
macOS e no Linux por meio do Docker Compose.

| Ambiente | Como rodar | Diferença no uso diário |
| --- | --- | --- |
| **Ubuntu no WSL2 (ambiente usado no projeto)** | Docker Desktop no Windows com integração à distribuição Ubuntu | Execute os comandos no terminal do Ubuntu e mantenha o projeto em `~/projetos`, no sistema de arquivos Linux. |
| **Windows com PowerShell** | Docker Desktop configurado para containers Linux | Clone o projeto em uma pasta do Windows e execute os comandos no PowerShell. Os caminhos usam o formato `C:\Users\seu-usuario\projetos\ecommerce-joias`. |
| **macOS** | Docker Desktop para Mac | Use o Terminal e uma pasta como `~/projetos/ecommerce-joias`. Não é necessário instalar WSL ou Ubuntu. |
| **Linux, incluindo Ubuntu sem WSL** | Docker Engine com plugin Compose ou Docker Desktop | Use o terminal Linux; com Docker Engine, o serviço Docker precisa estar iniciado. |

Os comandos `git` e `docker compose` apresentados abaixo são os mesmos nesses
ambientes. O que muda é a instalação do Docker, o terminal e os caminhos das
pastas. No Windows com WSL2, prefira guardar o projeto dentro do Ubuntu, em vez
de `/mnt/c`, para reduzir o custo de acesso aos arquivos compartilhados.
Windows e macOS usam uma máquina virtual Linux gerenciada pelo Docker Desktop
para executar os containers; no Linux com Docker Engine, eles rodam diretamente
sobre o kernel Linux do sistema.

A loja abre em **http://localhost:8080** em todos esses ambientes. Não é
necessário alterar o tema ou o Compose para seguir a instalação básica. O
ambiente usado no desenvolvimento é o Ubuntu/WSL2; a execução no Windows pelo
PowerShell e no macOS não foi validada nesta alteração.

## Pré-requisitos

- **Git**, para baixar e atualizar o projeto.
- **Docker** com **Docker Compose v2** (`docker compose`). No Windows, use o
  Docker Desktop com integração ao WSL2 habilitada para sua distribuição.
  No macOS, use o Docker Desktop para Mac. No Linux, use o Docker Engine com
  o plugin do Compose ou o Docker Desktop.
- Um navegador e conexão com a internet para baixar as imagens Docker, o tema
  pai e os plugins na primeira instalação.
- A porta **8080** disponível no computador.

PHP, MySQL e o servidor web rodam nos containers; não é necessário instalá-los
separadamente no computador. Node.js também não é necessário para abrir a loja.

Confira se as ferramentas estão disponíveis e o Docker está funcionando:

```bash
git --version
docker --version
docker compose version
docker info
```

## Como rodar pela primeira vez

Abra o terminal e baixe o projeto:

```bash
git clone https://github.com/MiguelGoncalvesAndrade10/ecommerce-joias.git
cd ecommerce-joias
```

Se já tiver o projeto no computador, entre na pasta onde ele foi salvo. Por
exemplo, no ambiente WSL usado neste projeto:

```bash
cd ~/projetos/ecommerce-joias
```

Com o Docker iniciado, suba os serviços:

```bash
docker compose up -d
```

Na primeira execução, aguarde o download das imagens e a inicialização do banco.
Confira o estado dos containers:

```bash
docker compose ps
```

Abra no navegador:

- **Loja:** [http://localhost:8080](http://localhost:8080)
- **Painel WordPress:** [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin)

Em uma instalação nova, siga o assistente do WordPress para escolher o idioma,
o nome do site e criar seu usuário e senha de administrador. O banco já está
configurado pelo Compose; você não precisa preencher suas credenciais no painel.
Depois:

1. Em **Aparência → Temas → Adicionar tema**, instale **Orchid Store**.
2. Em **Plugins → Adicionar plugin**, instale e ative **WooCommerce**.
3. Em **Aparência → Temas**, ative **Ecommerce Joias**, mantendo o Orchid Store instalado.
4. Conclua a configuração do WooCommerce e cadastre os produtos pelo painel.

Um clone novo não inclui produtos, imagens, pedidos nem configurações do banco
de outra instalação. Se você já usa este ambiente, os dados existentes serão
reaproveitados pelos volumes Docker.

## Comandos do dia a dia

Execute os comandos abaixo dentro da pasta do projeto:

| O que fazer | Comando |
| --- | --- |
| Iniciar a loja em segundo plano | `docker compose up -d` |
| Ver o estado dos serviços | `docker compose ps` |
| Acompanhar os logs (sair com Ctrl+C) | `docker compose logs -f --tail=100` |
| Ver somente os logs do WordPress | `docker compose logs -f --tail=100 wordpress` |
| Parar os serviços | `docker compose stop` |
| Reiniciar o WordPress | `docker compose restart wordpress` |
| Remover os containers, mantendo os volumes de dados | `docker compose down` |

Após parar ou remover os containers, use `docker compose up -d` para voltar.
Não acrescente `-v` ao comando `down` se quiser manter o banco e os demais dados
salvos nos volumes.

As alterações nos arquivos de `wp-content` são compartilhadas diretamente com o
container. Para editar o tema, normalmente basta salvar o arquivo e atualizar a
página no navegador.

## Problemas comuns ao iniciar

### Erro de conexão com `/var/run/docker.sock`

Se aparecer `failed to connect to the docker API` ou `no such file or directory`,
confira se o Docker está iniciado:

- **Windows/WSL2:** abra o Docker Desktop, aguarde a inicialização e confira em
  **Settings → Resources → WSL Integration** se sua distribuição está habilitada.
- **Linux com Docker Engine e systemd:** inicie o serviço com o comando abaixo.

```bash
sudo systemctl start docker
```

Depois confirme a conexão e tente novamente:

```bash
docker info
docker compose up -d
```

### A página ainda não abriu

A primeira inicialização do MySQL pode levar alguns instantes. Consulte
`docker compose ps` e `docker compose logs --tail=100` para verificar se os
serviços iniciaram. Se o erro indicar que a porta 8080 está ocupada, pare o outro
serviço que a utiliza ou altere o mapeamento `8080:80` no `docker-compose.yml`
(por exemplo, para `8081:80`) e execute `docker compose up -d` novamente. Nesse
caso, abra a porta escolhida no navegador.

## Testes funcionais locais

Com o Docker iniciado:

```bash
python3 scripts/test-storefront.py
docker compose exec -T -e NC_RUN_LOCAL_TESTS=1 wordpress php < scripts/test-woocommerce.php
```

O primeiro verifica páginas, busca, filtros e navegação por HTTP. O segundo usa
contas, produtos, cupom e pedidos descartáveis para testar carrinho, checkout
simulado e acesso à conta, com e-mails e HTTP externo bloqueados no processo.
Os dados de teste são removidos ao final. Execute apenas no ambiente local.
Resultados e limites estão em [testes-v1.md](docs/testes-v1.md).

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

### Logo no cabeçalho

Em **Aparência → Personalizar → Identidade do site → Logo**, selecione ou envie
o arquivo e clique em **Publicar**. O logo substitui automaticamente o nome da
loja no cabeçalho. Prefira PNG com fundo transparente, sem grandes margens vazias.
Se o recorte sugerido cortar o desenho, escolha **Pular recorte**. A imagem mantém
suas proporções, com altura máxima de 56 px e largura máxima de 180 px.

### Área Minha conta

Clientes autenticados encontram navegação lateral (adaptada ao celular), atalhos
para pedidos, endereços e dados, e o pedido mais recente. Quem ainda não comprou
vê uma orientação para explorar o catálogo. Os formulários, permissões e pedidos
continuam sendo gerenciados pelo WooCommerce.

O painel usa o override `woocommerce/myaccount/dashboard.php`, baseado na versão
4.4.0 do template original; revisar sua compatibilidade ao atualizar o plugin.

### Carrinho

O carrinho usa os blocos nativos do WooCommerce, com cabeçalho, etapas da compra,
mensagem de carrinho vazio e dúvidas expansíveis acrescentados pelo tema.
Entrega e cupom aparecem nas orientações quando esses recursos estão habilitados.
Produtos sugeridos continuam sendo configurados no editor da página Carrinho.
Referências e verificações estão em [design-carrinho.md](docs/design-carrinho.md).

### Rodapé

Em **Aparência → Personalizar → Rodapé NC**, configure e-mail, Instagram,
WhatsApp (país + DDD + número), horário de atendimento e páginas de ajuda,
privacidade e termos. Links de páginas não publicadas ou protegidas por senha
não aparecem. Sem páginas próprias de cuidados e tamanhos, o rodapé apresenta
orientações curtas expansíveis. A orientação de entrega depende do envio estar
habilitado no WooCommerce.

Em **Aparência → Menus → Gerenciar posições**, a posição **Navegação do rodapé**
permite escolher um menu independente do cabeçalho. Sem menu atribuído, aparecem
catálogo e página Sobre, quando disponíveis. Conta e pedidos têm atalhos próprios.

Os nomes dos meios de pagamento aparecem somente quando habilitados no
WooCommerce, com condições confirmadas no checkout. Não são exibidos selos de
certificação nem identificação empresarial.

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

### Revisão do cabeçalho e desempenho

No celular, o botão **Menu** abre os links em uma lista com áreas amplas para
toque e categorias expansíveis. Escape fecha o painel e devolve o foco ao botão. No tablet, navegação e busca ocupam áreas separadas. O cabeçalho
mantém fundo sólido durante a rolagem fora do hero.

As medições Lighthouse e verificações em navegador estão em
[revisao-header-lighthouse](docs/revisao-header-lighthouse/README.md).


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
