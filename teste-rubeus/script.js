// ===================================
// UNIVIÇOSA — PÓS-GRADUAÇÃO (TESTE)
// ===================================
(function () {
  'use strict';

  function initScrollAnimations() {
    const items = document.querySelectorAll('[data-animate]');
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('in-view');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -60px 0px' }
    );
    items.forEach((el) => observer.observe(el));
  }

  function initNavScroll() {
    const nav = document.getElementById('nav');
    if (!nav) return;
    const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 12);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  function initScrollProgress() {
    const bar = document.getElementById('scroll-progress');
    if (!bar) return;
    const onScroll = () => {
      const height = document.documentElement.scrollHeight - window.innerHeight;
      const pct = height > 0 ? (window.scrollY / height) * 100 : 0;
      bar.style.width = pct + '%';
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  function initScrollTop() {
    const btn = document.getElementById('scroll-top');
    if (!btn) return;
    window.addEventListener(
      'scroll',
      () => btn.classList.toggle('is-visible', window.scrollY > 400),
      { passive: true }
    );
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  function initSmoothAnchors() {
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (!href || href === '#') return;
        const target = document.querySelector(href);
        if (!target) return;
        e.preventDefault();
        const offset = target.getBoundingClientRect().top + window.scrollY - 84;
        window.scrollTo({ top: offset, behavior: 'smooth' });
      });
    });
  }

  function formatCounterValue(value, el) {
    if (el.dataset.decimal) {
      return value.toFixed(Number(el.dataset.decimal));
    }
    return Math.round(value) + (el.dataset.suffix || '');
  }

  function animateCounter(el) {
    const target = Number(el.dataset.target || 0);
    const duration = 1600;
    const start = performance.now();

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = formatCounterValue(target * eased, el);
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  function initCounters() {
    const counters = document.querySelectorAll('.stat-number[data-target]');
    if (!counters.length) return;
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !entry.target.dataset.counted) {
            entry.target.dataset.counted = 'true';
            animateCounter(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.6 }
    );
    counters.forEach((el) => observer.observe(el));
  }

  function initCourseFilters() {
    const filters = document.getElementById('filters');
    const grid = document.getElementById('courses-grid');
    const emptyState = document.getElementById('filters-empty');
    if (!filters || !grid) return;

    const chips = Array.from(filters.querySelectorAll('.filter-chip'));
    const cards = Array.from(grid.querySelectorAll('.course-card'));

    filters.addEventListener('click', (e) => {
      const chip = e.target.closest('.filter-chip');
      if (!chip) return;

      chips.forEach((c) => c.classList.toggle('is-active', c === chip));

      const filter = chip.dataset.filter;
      let visibleCount = 0;

      cards.forEach((card) => {
        const matches = filter === 'todos' || card.dataset.category === filter;
        card.classList.toggle('is-hidden', !matches);
        if (matches) visibleCount += 1;
      });

      if (emptyState) emptyState.hidden = visibleCount > 0;
    });
  }

  function initAnalytics() {
    document.querySelectorAll('.btn').forEach((btn) => {
      btn.addEventListener('click', function () {
        const label = this.textContent.trim();
        if (typeof gtag !== 'undefined') {
          gtag('event', 'click', { event_category: 'CTA', event_label: label });
        }
      });
    });
  }

  function initAll() {
    initScrollAnimations();
    initNavScroll();
    initScrollProgress();
    initScrollTop();
    initSmoothAnchors();
    initCounters();
    initCourseFilters();
    initAnalytics();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
