# Landing Page — Campanha Pós-Graduação Univiçosa

Landing page estática (HTML/CSS/JS, sem build) para divulgação da campanha de
pós-graduação lato sensu do Centro Universitário de Viçosa.

## Estrutura

```
index.html      página única (hero, cursos, diferenciais, depoimentos, formulário, FAQ)
css/style.css   estilos (paleta institucional placeholder: azul-marinho + dourado)
js/script.js    catálogo de cursos, filtros, formulário de leads, UTM, FAQ
```

Para visualizar localmente: `python3 -m http.server 8000` na raiz do projeto e
abrir `http://localhost:8000`.

## O que precisa ser ajustado antes de publicar

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
