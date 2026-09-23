document.getElementById('year').textContent = new Date().getFullYear();

/* =========================================================
   1) CATÁLOGO DE CURSOS (EXEMPLO)
   Substitua pelos cursos e códigos reais cadastrados no
   módulo Educacional do TOTVS RM antes de publicar.
   "codRM" é o campo sugerido para casar com o código da
   turma/curso no RM na hora de gerar a matrícula.
========================================================= */
const CURSOS = [
  { nome: 'MBA em Gestão Escolar e Docência do Ensino Superior', area: 'humanas', duracao: '18 meses', modalidade: 'Híbrido', codRM: 'PG-DOC-01' },
  { nome: 'Pós em Direito Civil e Processual Civil', area: 'humanas', duracao: '15 meses', modalidade: 'Híbrido', codRM: 'PG-DIR-01' },
  { nome: 'Pós em Direito Penal e Processual Penal', area: 'humanas', duracao: '15 meses', modalidade: 'Híbrido', codRM: 'PG-DIR-02' },
  { nome: 'Pós em Enfermagem em Urgência, Emergência e UTI', area: 'saude', duracao: '18 meses', modalidade: 'Híbrido', codRM: 'PG-ENF-01' },
  { nome: 'Pós em Fisioterapia Traumato-Ortopédica', area: 'saude', duracao: '18 meses', modalidade: 'Híbrido', codRM: 'PG-FIS-01' },
  { nome: 'Pós em Neuropsicologia', area: 'saude', duracao: '18 meses', modalidade: 'Híbrido', codRM: 'PG-PSI-01' },
  { nome: 'Pós em Ortodontia e Estética em Odontologia', area: 'saude', duracao: '24 meses', modalidade: 'Presencial', codRM: 'PG-ODO-01' },
  { nome: 'Pós em Clínica e Cirurgia de Pequenos Animais', area: 'saude', duracao: '18 meses', modalidade: 'Híbrido', codRM: 'PG-VET-01' },
  { nome: 'Pós em Farmácia Clínica e Estética', area: 'saude', duracao: '15 meses', modalidade: 'Híbrido', codRM: 'PG-FAR-01' },
  { nome: 'Pós em Engenharia de Segurança do Trabalho', area: 'exatas', duracao: '18 meses', modalidade: 'EAD', codRM: 'PG-ENG-01' },
  { nome: 'Pós em Estruturas e Patologias das Construções', area: 'exatas', duracao: '18 meses', modalidade: 'Híbrido', codRM: 'PG-ENG-02' },
  { nome: 'Pós em Paisagismo e Design de Interiores', area: 'exatas', duracao: '15 meses', modalidade: 'Híbrido', codRM: 'PG-ARQ-01' },
  { nome: 'Pós em Ciência de Dados e Inteligência Artificial', area: 'exatas', duracao: '18 meses', modalidade: 'EAD', codRM: 'PG-TEC-01' },
  { nome: 'Pós em Segurança da Informação', area: 'exatas', duracao: '18 meses', modalidade: 'EAD', codRM: 'PG-TEC-02' },
  { nome: 'MBA em Gestão de Pessoas', area: 'gestao', duracao: '15 meses', modalidade: 'Híbrido', codRM: 'PG-ADM-01' },
  { nome: 'MBA em Gestão Financeira e Controladoria', area: 'gestao', duracao: '15 meses', modalidade: 'Híbrido', codRM: 'PG-ADM-02' },
  { nome: 'Pós em Perícia e Auditoria Contábil', area: 'gestao', duracao: '15 meses', modalidade: 'EAD', codRM: 'PG-CONT-01' },
  { nome: 'Pós em Estética Avançada', area: 'saude', duracao: '15 meses', modalidade: 'Presencial', codRM: 'PG-EST-01' },
];

const AREA_LABEL = { saude: 'Saúde', exatas: 'Exatas & Tecnologia', humanas: 'Humanas & Direito', gestao: 'Gestão & Negócios' };

const grid = document.getElementById('coursesGrid');
const cursoSelect = document.getElementById('curso');

function renderCourses(filter = 'todos') {
  const lista = filter === 'todos' ? CURSOS : CURSOS.filter(c => c.area === filter);
  grid.innerHTML = lista.length
    ? lista.map(c => `
      <article class="course">
        <span class="course__tag">${AREA_LABEL[c.area]}</span>
        <h3>${c.nome}</h3>
        <p>Especialização lato sensu com foco prático e orientação de TCC.</p>
        <div class="course__meta">
          <span>⏱ ${c.duracao}</span>
          <span>💻 ${c.modalidade}</span>
        </div>
        <a href="#inscricao" class="course__cta" data-curso="${c.nome}">Quero me inscrever</a>
      </article>
    `).join('')
    : '<p class="courses__empty">Nenhum curso encontrado nesta área no momento.</p>';
}

function populateSelect() {
  cursoSelect.innerHTML = '<option value="" disabled selected>Selecione um curso</option>' +
    CURSOS.map(c => `<option value="${c.nome}">${c.nome}</option>`).join('');
}

renderCourses();
populateSelect();

// Filtro por área
document.getElementById('filters').addEventListener('click', (e) => {
  const btn = e.target.closest('.filter');
  if (!btn) return;
  document.querySelectorAll('.filter').forEach(f => f.classList.remove('is-active'));
  btn.classList.add('is-active');
  renderCourses(btn.dataset.filter);
});

// Pré-seleciona o curso no formulário ao clicar em "Quero me inscrever" no card
grid.addEventListener('click', (e) => {
  const link = e.target.closest('[data-curso]');
  if (!link) return;
  cursoSelect.value = link.dataset.curso;
});

/* =========================================================
   2) MENU MOBILE
========================================================= */
const navToggle = document.getElementById('navToggle');
const nav = document.getElementById('nav');
navToggle.addEventListener('click', () => nav.classList.toggle('is-open'));
nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => nav.classList.remove('is-open')));

/* =========================================================
   3) FAQ (accordion)
========================================================= */
document.getElementById('accordion').addEventListener('click', (e) => {
  const trigger = e.target.closest('.accordion__trigger');
  if (!trigger) return;
  const item = trigger.closest('.accordion__item');
  const wasOpen = item.classList.contains('is-open');
  document.querySelectorAll('.accordion__item').forEach(i => i.classList.remove('is-open'));
  if (!wasOpen) item.classList.add('is-open');
});

/* =========================================================
   4) UTM tracking (atribuição de campanha)
========================================================= */
const params = new URLSearchParams(window.location.search);
['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'].forEach(key => {
  const el = document.getElementById(key);
  if (el) el.value = params.get(key) || '';
});

/* =========================================================
   5) Máscara simples de WhatsApp
========================================================= */
const whatsInput = document.getElementById('whatsapp');
whatsInput.addEventListener('input', () => {
  let v = whatsInput.value.replace(/\D/g, '').slice(0, 11);
  if (v.length > 6) v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
  else if (v.length > 2) v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
  whatsInput.value = v.trim();
});

/* =========================================================
   6) Envio do formulário de captação de leads
   -------------------------------------------------------
   INTEGRAÇÃO PENDENTE (ajustar antes de publicar a campanha):
   - Rubeus (CRM): substituir RUBEUS_WEBHOOK_URL pela URL do
     webhook/API de captação de leads do Rubeus.
   - Worknow: se o disparo de WhatsApp automático ficar a
     cargo da plataforma, envie o lead também para o endpoint
     do Worknow (ou deixe o Rubeus orquestrar via integração
     nativa, se já configurada).
   - TOTVS RM: a pré-matrícula segue sendo feita manualmente
     pela secretaria a partir do lead qualificado no Rubeus,
     a menos que exista rotina de importação automática.
   Enquanto a integração não está configurada, o formulário
   guarda o lead no navegador (localStorage) e abre o
   WhatsApp com os dados preenchidos, para não perder contato.
========================================================= */
const RUBEUS_WEBHOOK_URL = ''; // TODO: preencher com a URL real do Rubeus

const form = document.getElementById('leadForm');
const formMsg = document.getElementById('formMsg');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  formMsg.textContent = '';
  formMsg.className = 'form__msg';

  const data = Object.fromEntries(new FormData(form).entries());

  // Validação básica de campos obrigatórios
  let valid = true;
  ['nome', 'email', 'whatsapp', 'curso', 'lgpd'].forEach(name => {
    const field = form.querySelector(`[name="${name}"]`)?.closest('.field') || form.querySelector(`[name="${name}"]`)?.closest('.consent');
    const filled = name === 'lgpd' ? form.lgpd.checked : !!data[name];
    if (field) field.classList.toggle('is-invalid', !filled);
    if (!filled) valid = false;
  });

  if (!valid) {
    formMsg.textContent = 'Preencha todos os campos obrigatórios antes de enviar.';
    formMsg.classList.add('is-error');
    return;
  }

  const submitBtn = form.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Enviando...';

  try {
    if (RUBEUS_WEBHOOK_URL) {
      await fetch(RUBEUS_WEBHOOK_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...data, origem: 'landing-pos-graduacao', criado_em: new Date().toISOString() }),
      });
    } else {
      // Fallback local enquanto a integração com o Rubeus não está ativa
      const leads = JSON.parse(localStorage.getItem('leads_pos_graduacao') || '[]');
      leads.push({ ...data, criado_em: new Date().toISOString() });
      localStorage.setItem('leads_pos_graduacao', JSON.stringify(leads));
    }

    // Disparo do evento de conversão para o Pixel (Meta Ads)
    if (typeof fbq === 'function') {
      fbq('track', 'Lead', { content_name: data.curso });
    }

    formMsg.textContent = 'Recebemos seus dados! Em instantes você será direcionado ao WhatsApp da nossa equipe.';
    formMsg.classList.add('is-success');
    form.reset();

    const msg = encodeURIComponent(
      `Olá, meu nome é ${data.nome}. Tenho interesse na pós-graduação em "${data.curso}" e gostaria de receber mais informações.`
    );
    setTimeout(() => window.open(`https://wa.me/5531900000000?text=${msg}`, '_blank'), 1200);
  } catch (err) {
    formMsg.textContent = 'Não foi possível enviar agora. Tente novamente ou fale direto pelo WhatsApp.';
    formMsg.classList.add('is-error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Quero receber a proposta';
  }
});
