document.getElementById('year').textContent = new Date().getFullYear();

/* =========================================================
   1) MENU MOBILE
========================================================= */
const navToggle = document.getElementById('navToggle');
const nav = document.getElementById('nav');
navToggle.addEventListener('click', () => nav.classList.toggle('is-open'));
nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => nav.classList.remove('is-open')));

/* =========================================================
   2) FAQ (accordion)
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
   3) UTM tracking (atribuição de campanha)
========================================================= */
const params = new URLSearchParams(window.location.search);
['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'].forEach(key => {
  const el = document.getElementById(key);
  if (el) el.value = params.get(key) || '';
});

/* =========================================================
   4) Máscara simples de WhatsApp
========================================================= */
const whatsInput = document.getElementById('whatsapp');
whatsInput.addEventListener('input', () => {
  let v = whatsInput.value.replace(/\D/g, '').slice(0, 11);
  if (v.length > 6) v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
  else if (v.length > 2) v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
  whatsInput.value = v.trim();
});

/* =========================================================
   5) Envio do formulário de captação de leads
   -------------------------------------------------------
   INTEGRAÇÃO PENDENTE (ajustar antes de publicar a campanha):
   - Rubeus (CRM): substituir RUBEUS_WEBHOOK_URL pela URL do
     webhook/API de captação de leads do Rubeus.
   - Worknow: se o disparo de WhatsApp automático ficar a
     cargo da plataforma, envie o lead também para o endpoint
     do Worknow (ou deixe o Rubeus orquestrar via integração
     nativa, se já configurada).
   - TOTVS RM: a pré-matrícula na turma de Endodontia (módulo
     Educacional) segue sendo feita manualmente pela secretaria
     a partir do lead qualificado no Rubeus, a menos que exista
     rotina de importação automática.
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

  let valid = true;
  ['nome', 'email', 'whatsapp', 'lgpd'].forEach(name => {
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
        body: JSON.stringify({ ...data, origem: 'landing-endodontia', criado_em: new Date().toISOString() }),
      });
    } else {
      const leads = JSON.parse(localStorage.getItem('leads_endodontia') || '[]');
      leads.push({ ...data, criado_em: new Date().toISOString() });
      localStorage.setItem('leads_endodontia', JSON.stringify(leads));
    }

    // Disparo do evento de conversão para o Pixel (Meta Ads)
    if (typeof fbq === 'function') {
      fbq('track', 'Lead', { content_name: data.curso });
    }

    formMsg.textContent = 'Recebemos seus dados! Em instantes você será direcionado ao WhatsApp da nossa equipe.';
    formMsg.classList.add('is-success');
    form.reset();
    document.getElementById('curso').value = 'Aperfeiçoamento em Endodontia';

    const msg = encodeURIComponent(
      `Olá, meu nome é ${data.nome}. Tenho interesse no curso de "${data.curso}" e gostaria de receber mais informações.`
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
