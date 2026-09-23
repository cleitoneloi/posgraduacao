# Teste — Landing Page Pós-Graduação (layout inovador)

Página de teste, separada da landing page principal (`/index.html`), com um
layout redesenhado: hero com mesh gradient, cards em bento grid, filtros de
curso por área, seção de CTA final com WhatsApp e widget flutuante.

```
teste-rubeus/index.html   página única
teste-rubeus/styles.css   estilos (paleta institucional azul-marinho + dourado)
teste-rubeus/script.js    scroll reveal, contadores, filtros de curso, nav
```

Para visualizar localmente: `python3 -m http.server 8000` na raiz do
projeto e abrir `http://localhost:8000/teste-rubeus/`.

## Ajustar antes de publicar

1. **WhatsApp** (`index.html`) — troque `5531900000000` (aparece no botão
   flutuante e no CTA final) pelo número real conectado à Worknow.
2. **Links dos cursos** — apontam para `pos.univicosa.com.br`; confirme se
   as URLs e a lista de cursos ativos batem com o módulo Educacional do RM.
3. **Meta Pixel/GA** — nenhum script de tracking foi incluído; adicione se
   esta página for usada em campanha paga.
4. Esta página **não tem formulário de captação** — o CTA principal é o
   WhatsApp. Se precisar de lead qualificado no CRM, integrar com o Rubeus
   (webhook nativo ou via n8n), no mesmo padrão do `/index.html` original.
