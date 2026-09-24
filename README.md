# Landing Page — Campanha Pós-Graduação Univiçosa

Landing page estática (HTML/CSS/JS, sem build) para divulgação da campanha de
pós-graduação lato sensu do Centro Universitário de Viçosa.

Há duas versões de layout, com o mesmo conteúdo e a mesma lógica de
formulário/integrações — escolha uma para publicar (ou peça ajustes em
qualquer uma antes de decidir):

| Versão | Estilo | Caminho |
|---|---|---|
| **v1** | Institucional, azul-marinho + dourado, mais conservador | `index.html` |
| **v2** | Dark theme, glassmorphism, bento grid, tipografia grande, mais arrojado/moderno | `v2/index.html` |

## Estrutura

```
index.html      v1 — página única (hero, cursos, diferenciais, depoimentos, formulário, FAQ)
css/style.css   v1 — estilos (paleta institucional placeholder: azul-marinho + dourado)
js/script.js    v1 — catálogo de cursos, filtros, formulário de leads, UTM, FAQ

v2/index.html   v2 — mesma estrutura de conteúdo, layout mais sofisticado
v2/css/style.css  v2 — dark theme, glassmorphism, bento grid, gradientes animados
v2/js/script.js   v2 — mesma lógica da v1 + contador regressivo, tilt nos cards, scroll-reveal
```

Para visualizar localmente: `python3 -m http.server 8000` na raiz do projeto e
abrir `http://localhost:8000` (v1) ou `http://localhost:8000/v2/` (v2).

## O que precisa ser ajustado antes de publicar

Os pontos abaixo valem para as duas versões (`js/script.js` e `v2/js/script.js`
têm o mesmo catálogo e as mesmas constantes de integração).

0. **Contador regressivo (só na v2)** (`v2/js/script.js`, constante `CAMPAIGN_DEADLINE`)
   Está com uma data de exemplo (`2026-10-31`). Ajuste para a data real de
   encerramento da condição de campanha — um prazo falso é prática enganosa
   e não deve ser publicado.

1. **Catálogo de cursos** (`js/script.js`, array `CURSOS`)
   Os cursos, durações, modalidades e `codRM` são **exemplos**. Substitua
   pelos cursos de pós-graduação realmente ativos no módulo Educacional do
   TOTVS RM (mesmos códigos de turma usados na matrícula).

2. **Meta Pixel** (`index.html`, `<head>`)
   Troque `PIXEL_ID_AQUI` pelo ID real da conta de anúncios gerenciada nos
   Ads (Meta). O evento `Lead` já é disparado no envio do formulário para
   otimização de campanha.

3. **Webhook do Rubeus** (`js/script.js`, constante `RUBEUS_WEBHOOK_URL`)
   Enquanto vazio, o formulário salva o lead em `localStorage` (apenas no
   navegador do próprio candidato) e abre o WhatsApp com os dados
   preenchidos — **não substitui um CRM**. Para produção, aponte para o
   endpoint de captação de leads do Rubeus (ou um endpoint intermediário
   seu que grave no Rubeus e/ou dispare a rotina de pré-matrícula no RM).
   Se preferir, isso também pode ser resolvido via automação n8n, no mesmo
   padrão já usado nos fluxos de atendimento da instituição.

4. **Números/contatos** (`index.html`)
   Substitua `5531900000000` (WhatsApp, aparece 2x) e o e-mail do rodapé
   pelos contatos reais da secretaria de pós-graduação. O número do
   WhatsApp deve ser o mesmo conectado à Worknow, para manter o
   atendimento centralizado.

5. **Identidade visual** (`css/style.css`, bloco `:root`)
   Cores e fontes atuais são um placeholder profissional. Ajuste as
   variáveis de cor e troque `logo__mark`/inclua o logo oficial da
   Univiçosa quando disponível.

6. **Política de Privacidade/LGPD**
   Os links de "Política de Privacidade" e "Termos de Uso" estão como
   âncoras (`#`). Aponte para as páginas reais antes de publicar, já que
   o formulário coleta dados pessoais (nome, e-mail, WhatsApp).

## Deploy no Apache (segurança)

O repositório já inclui:

- **`.htaccess`** (raiz) — força HTTPS, aplica headers de segurança (HSTS,
  X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP), desativa
  listagem de diretórios e bloqueia `.git`/arquivos sensíveis. Funciona em
  hospedagens compartilhadas (precisa de `AllowOverride All` ou pelo menos
  `AllowOverride FileInfo Options`).
- **`deploy/apache-vhost.conf.example`** — mesmo hardening, mas como
  `VirtualHost` (recomendado se você tem acesso root ao Apache): evita o
  overhead de reler `.htaccess` a cada request e permite `AllowOverride None`.

Passo a passo (Debian/Ubuntu, acesso root):

```bash
sudo a2enmod ssl rewrite headers deflate
sudo cp deploy/apache-vhost.conf.example /etc/apache2/sites-available/pos-graduacao.conf
# edite ServerName e DocumentRoot no arquivo copiado
sudo a2ensite pos-graduacao
sudo apachectl configtest
sudo certbot --apache -d pos.univicosa.edu.br   # emite o certificado TLS
sudo systemctl reload apache2
```

Se usar o `.htaccess` em vez do vhost (hospedagem compartilhada) e a página
der erro 500 ao subir, normalmente é `AllowOverride` insuficiente — peça ao
provedor para liberar `AllowOverride FileInfo Options Indexes` na pasta, ou
peça para aplicarem os headers direto no vhost deles.

Depois de publicar, vale conferir os headers com
`curl -sI https://pos.univicosa.edu.br` ou em securityheaders.com.

## Rastreamento de campanha

A página já captura `utm_source`, `utm_medium`, `utm_campaign` e
`utm_content` da URL e envia junto com o lead — use esses parâmetros nos
links de anúncio (Meta/Google Ads) para saber qual criativo/campanha gerou
cada inscrição.

## Próximos passos sugeridos

- Conectar o formulário ao Rubeus (webhook nativo ou via n8n) para que o
  lead entre automaticamente no funil de atendimento, com o mesmo padrão
  Chatwoot + n8n + Qdrant já usado no NEAD/TI.
  Se o webhook exigir chamada server-side (chave de API, CORS), a
  submissão deve passar por uma função/backend intermediário — o
  JavaScript do navegador não deve guardar credenciais do Rubeus.
- Configurar o Pixel e uma conversão personalizada de "Lead Pós-Graduação"
  na conta de Ads para otimizar a campanha por resultado, não só por
  cliques.
- Validar com a secretaria acadêmica se a matrícula será feita
  automaticamente no RM a partir do lead qualificado, ou se segue manual.
