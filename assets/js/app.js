(() => {
  'use strict';

  const navToggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('#main-nav');
  navToggle?.addEventListener('click', () => {
    const open = nav?.classList.toggle('is-open') ?? false;
    navToggle.setAttribute('aria-expanded', String(open));
  });

  const banner = document.querySelector('[data-consent-banner]');
  const savedConsent = localStorage.getItem('focal_consent');
  if (banner && !savedConsent) banner.hidden = false;

  function applyConsent(value) {
    localStorage.setItem('focal_consent', value);
    if (banner) banner.hidden = true;
    document.documentElement.dataset.analyticsConsent = value;
  }

  document.querySelectorAll('[data-consent]').forEach((button) => {
    button.addEventListener('click', () => applyConsent(button.dataset.consent));
  });
  document.querySelectorAll('[data-open-consent]').forEach((button) => {
    button.addEventListener('click', () => { if (banner) banner.hidden = false; });
  });

  // File de données locale : aucun appel réseau tant que la mesure n'est pas intégrée en V1.
  window.FocalAnalytics = {
    track(eventName, parameters = {}) {
      if (localStorage.getItem('focal_consent') !== 'granted') return;
      window.focalDataLayer = window.focalDataLayer || [];
      window.focalDataLayer.push({ event: eventName, ...parameters, timestamp: new Date().toISOString() });
    }
  };

  document.querySelectorAll('[data-track]').forEach((element) => {
    element.addEventListener('click', () => window.FocalAnalytics.track(element.dataset.track));
  });

  const counters = document.querySelectorAll('[data-counter]');
  if (counters.length) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const animateCounter = (element) => {
      const target = Number(element.dataset.counter || 0);
      const suffix = element.dataset.suffix || '';
      if (reducedMotion || !Number.isFinite(target)) { element.textContent = `${target}${suffix}`; return; }
      const duration = 950;
      const started = performance.now();
      const step = (now) => {
        const progress = Math.min((now - started) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = target % 1 === 0 ? Math.round(target * eased) : (target * eased).toFixed(1).replace('.', ',');
        element.textContent = `${value}${suffix}`;
        if (progress < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (entry.isIntersecting && !entry.target.dataset.animated) {
        entry.target.dataset.animated = 'true'; animateCounter(entry.target); observer.unobserve(entry.target);
      }
    }), { threshold: .45 });
    counters.forEach((counter) => observer.observe(counter));
  }

  const depositForm = document.querySelector('[data-deposit-form]');
  if (depositForm) {
    const errorSummary = document.querySelector('[data-error-summary]');
    errorSummary?.focus();

    const fileInput = document.querySelector('#photo');
    const fileFeedback = document.querySelector('[data-file-feedback]');
    fileInput?.addEventListener('change', () => {
      if (fileFeedback) fileFeedback.textContent = fileInput.files?.[0]?.name || 'Aucun fichier choisi';
    });

    const syncPriceFields = () => {
      document.querySelectorAll('[data-price-toggle]').forEach((toggle) => {
        const field = document.querySelector(`[data-price-field="${toggle.dataset.priceToggle}"]`);
        if (field) {
          field.disabled = !toggle.checked;
          field.required = toggle.checked;
        }
      });
    };
    document.querySelectorAll('[data-price-toggle]').forEach((toggle) => toggle.addEventListener('change', syncPriceFields));
    syncPriceFields();

    const configureGauge = (type, minimum, maximum, suggested) => {
      const wrap = document.querySelector(`[data-gauge-wrap="${type}"]`);
      const gauge = document.querySelector(`[data-price-gauge="${type}"]`);
      const field = document.querySelector(`[data-price-field="${type}"]`);
      if (!wrap || !gauge || !field || !(minimum > 0) || !(maximum > minimum)) return;
      const money = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: type === 'rental' ? 2 : 0 });
      gauge.min = String(minimum); gauge.max = String(maximum); gauge.step = type === 'rental' ? '0.5' : '1'; gauge.value = String(suggested);
      field.min = String(minimum); field.max = String(maximum);
      const minLabel = document.querySelector(`[data-price-min="${type}"]`);
      const maxLabel = document.querySelector(`[data-price-max="${type}"]`);
      if (minLabel) minLabel.textContent = money.format(minimum);
      if (maxLabel) maxLabel.textContent = money.format(maximum);
      wrap.hidden = false;
      gauge.addEventListener('input', () => { field.value = gauge.value; });
      field.addEventListener('input', () => { gauge.value = field.value; });
    };

    const savedSimulation = sessionStorage.getItem('focal_simulation');
    if (savedSimulation) {
      try {
        const estimate = JSON.parse(savedSimulation);
        const sale = Number(estimate.sale_price);
        const rental = Number(estimate.rental_price_day);
        if (sale > 0 && rental > 0) {
          configureGauge('sale', Number(estimate.sale_min), Number(estimate.sale_max), sale);
          configureGauge('rental', Number(estimate.rental_min), Number(estimate.rental_max), rental);
          const money = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });
          document.querySelector('[data-pricing-values]')?.removeAttribute('hidden');
          document.querySelector('[data-apply-prices]')?.removeAttribute('hidden');
          const saleLabel = document.querySelector('[data-suggested-sale]');
          const rentalLabel = document.querySelector('[data-suggested-rental]');
          const message = document.querySelector('[data-pricing-message]');
          if (saleLabel) saleLabel.textContent = money.format(sale);
          if (rentalLabel) rentalLabel.textContent = money.format(rental);
          if (message) message.textContent = `Estimation disponible pour ${estimate.brand || ''} ${estimate.model || ''}.`;
          document.querySelector('[data-apply-prices]')?.addEventListener('click', () => {
            const fill = (selector, value) => {
              const field = document.querySelector(selector);
              if (field && value !== undefined && value !== null && value !== '') {
                field.value = String(value);
                field.dispatchEvent(new Event('change', { bubbles: true }));
              }
            };
            fill('#category_id', estimate.category_id);
            fill('#brand', estimate.brand);
            fill('#model', estimate.model);
            fill('#purchase_year', estimate.purchase_year);
            fill('#condition_grade', estimate.condition_grade);
            fill('#reuse_count', estimate.reuse_count);
            fill('#distance_km', estimate.distance_km);
            const repaired = document.querySelector('[name="repaired"]');
            if (repaired) repaired.checked = Boolean(estimate.repaired);
            document.querySelector('[data-price-field="sale"]').value = sale;
            document.querySelector('[data-price-field="rental"]').value = rental;
            const saleGauge = document.querySelector('[data-price-gauge="sale"]');
            const rentalGauge = document.querySelector('[data-price-gauge="rental"]');
            if (saleGauge) saleGauge.value = String(sale);
            if (rentalGauge) rentalGauge.value = String(rental);
            syncPriceFields();
            if (message) message.textContent = 'Fourchettes appliquées. Choisissez maintenant une vente ou une location, jamais les deux.';
            document.querySelector('#category_id')?.focus();
          });
        }
      } catch (_) { /* Une ancienne estimation invalide est simplement ignorée. */ }
    }

    depositForm.addEventListener('submit', (event) => {
      const checked = depositForm.querySelectorAll('[data-price-toggle]:checked').length > 0;
      if (!depositForm.checkValidity() || !checked) {
        event.preventDefault();
        if (!checked) alert('Choisissez la vente ou la location.');
        depositForm.reportValidity();
      }
    });
  }

  const demoButton = document.querySelector('[data-demo-action]');
  const toast = document.querySelector('[data-toast]');
  demoButton?.addEventListener('click', () => {
    if (!toast) return;
    toast.hidden = false;
    window.FocalAnalytics.track('demo_request_equipment');
    window.setTimeout(() => { toast.hidden = true; }, 4500);
  });
})();
