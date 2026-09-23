document.getElementById('year').textContent = new Date().getFullYear();

/* =========================================================
   1) CATÁLOGO DE CURSOS (EXEMPLO)
   Mesmo formato da v1 — troque pelos cursos/códigos reais do
   módulo Educacional do TOTVS RM antes de publicar.
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

const AREA_LABEL = { saude: 'Saúde', exatas: 'Exatas & Tech', humanas: 'Humanas & Direito', gestao: 'Gestão' };

const bento = document.getElementById('bento');
const cursoSelect = document.getElementById('curso');

function renderBento(filter = 'todos') {
  const lista = filter === 'todos' ? CURSOS : CURSOS.filter(c => c.area === filter);
  bento.innerHTML = lista.length
    ? lista.map(c => `
      <article class="bento-card" data-tilt>
        <span class="bento-card__tag">${AREA_LABEL[c.area]}</span>
        <h3>${c.nome}</h3>
        <p>Especialização lato sensu com foco prático e orientação de TCC.</p>
        <div class="bento-card__meta"><span>⏱ ${c.duracao}</span><span>💻 ${c.modalidade}</span></div>
        <a href="#matricula" class="bento-card__cta" data-curso="${c.nome}">Quero me inscrever →</a>
      </article>
    `).join('')
    : '<p class="bento__empty">Nenhum curso encontrado nesta área no momento.</p>';
  attachTilt();
}

function populateSelect() {
  cursoSelect.innerHTML = '<option value="" disabled selected>Selecione um curso</option>' +
    CURSOS.map(c => `<option value="${c.nome}">${c.nome}</option>`).join('');
}

function populateMarquee() {
  const track = document.getElementById('marqueeTrack');
  const items = CURSOS.map(c => `<span><strong>●</strong> ${c.nome}</span>`);
  track.innerHTML = items.join('') + items.join(''); // duplicado para loop contínuo
}

renderBento();
populateSelect();
populateMarquee();

document.getElementById('filters').addEventListener('click', (e) => {
  const btn = e.target.closest('.filter');
  if (!btn) return;
  document.querySelectorAll('.filter').forEach(f => f.classList.remove('is-active'));
  btn.classList.add('is-active');
  renderBento(btn.dataset.filter);
});

bento.addEventListener('click', (e) => {
  const link = e.target.closest('[data-curso]');
  if (!link) return;
  cursoSelect.value = link.dataset.curso;
});

/* =========================================================
   2) Tilt sutil nos cards (segue o mouse via CSS custom props)
========================================================= */
function attachTilt() {
  document.querySelectorAll('[data-tilt]').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      card.style.setProperty('--mx', `${e.clientX - r.left}px`);
      card.style.setProperty('--my', `${e.clientY - r.top}px`);
    });
  });
}

/* =========================================================
   3) Spotlight que segue o cursor (efeito ambiente)
========================================================= */
const spotlight = document.getElementById('spotlight');
window.addEventListener('pointermove', (e) => {
  spotlight.style.setProperty('--x', `${e.clientX}px`);
  spotlight.style.setProperty('--y', `${e.clientY}px`);
});

/* =========================================================
   4) Scroll reveal (IntersectionObserver)
========================================================= */
document.querySelectorAll('.section, .bento-card, .method__row, .card-quote').forEach(el => el.classList.add('reveal'));
const io = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      io.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));
// Re-observa cards do bento após re-render (filtros)
const bentoObserver = new MutationObserver(() => {
  bento.querySelectorAll('.bento-card').forEach(el => {
    el.classList.add('reveal', 'is-visible');
  });
});
bentoObserver.observe(bento, { childList: true });

/* =========================================================
   5) Menu mobile
========================================================= */
const navToggle = document.getElementById('navToggle');
const nav = document.getElementById('nav');
navToggle.addEventListener('click', () => nav.classList.toggle('is-open'));
nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => nav.classList.remove('is-open')));

/* =========================================================
   6) FAQ (accordion)
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
   7) Contador regressivo — configure a data REAL do fim da
   condição de campanha antes de publicar. Um prazo falso é
   prática enganosa; use sempre uma data verdadeira.
========================================================= */
const CAMPAIGN_DEADLINE = new Date('2026-10-31T23:59:59-03:00'); // TODO: ajustar para a data real da campanha

function updateCountdown() {
  const diff = CAMPAIGN_DEADLINE.getTime() - Date.now();
  const els = {
    d: document.getElementById('cd-d'),
    h: document.getElementById('cd-h'),
    m: document.getElementById('cd-m'),
    s: document.getElementById('cd-s'),
  };
  if (diff <= 0) {
    els.d.textContent = els.h.textContent = els.m.textContent = els.s.textContent = '0';
    return;
  }
  const d = Math.floor(diff / 86400000);
  const h = Math.floor((diff % 86400000) / 3600000);
  const m = Math.floor((diff % 3600000) / 60000);
  const s = Math.floor((diff % 60000) / 1000);
  els.d.textContent = String(d);
  els.h.textContent = String(h).padStart(2, '0');
  els.m.textContent = String(m).padStart(2, '0');
  els.s.textContent = String(s).padStart(2, '0');
}
updateCountdown();
setInterval(updateCountdown, 1000);

/* =========================================================
   8) UTM tracking
========================================================= */
const params = new URLSearchParams(window.location.search);
['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'].forEach(key => {
  const el = document.getElementById(key);
  if (el) el.value = params.get(key) || '';
});

/* =========================================================
   9) Máscara de WhatsApp
========================================================= */
const whatsInput = document.getElementById('whatsapp');
whatsInput.addEventListener('input', () => {
  let v = whatsInput.value.replace(/\D/g, '').slice(0, 11);
  if (v.length > 6) v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
  else if (v.length > 2) v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
  whatsInput.value = v.trim();
});

/* =========================================================
   10) Envio do formulário — mesma lógica da v1.
   INTEGRAÇÃO PENDENTE: ver README para Rubeus/Meta Pixel/Worknow.
========================================================= */
const RUBEUS_WEBHOOK_URL = ''; // TODO: preencher com a URL real do Rubeus

const form = document.getElementById('leadForm');
const formMsg = document.getElementById('formMsg');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  formMsg.textContent = '';
  formMsg.className = 'form__msg';

  const data = Object.fromEntries(new FormData(form).entries());

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
        body: JSON.stringify({ ...data, origem: 'landing-pos-graduacao-v2', criado_em: new Date().toISOString() }),
      });
    } else {
      const leads = JSON.parse(localStorage.getItem('leads_pos_graduacao') || '[]');
      leads.push({ ...data, criado_em: new Date().toISOString() });
      localStorage.setItem('leads_pos_graduacao', JSON.stringify(leads));
    }

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
