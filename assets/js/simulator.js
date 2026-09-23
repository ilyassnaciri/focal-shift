(() => {
  'use strict';
  const form = document.querySelector('[data-simulator-form]');
  if (!form) return;

  const status = document.querySelector('[data-simulator-status]');
  const placeholder = document.querySelector('[data-result-placeholder]');
  const content = document.querySelector('[data-result-content]');
  const endpoint = new URL('api/simulator.php', window.location.href).toString();
  const euros = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });
  const category = form.querySelector('#sim_category');
  const preview = document.querySelector('[data-simulator-preview]');
  const previewImage = document.querySelector('[data-simulator-preview-image]');
  const previewCaption = document.querySelector('[data-simulator-preview-caption]');
  const resultPanel = document.querySelector('[data-simulator-result]');
  const steps = [...form.querySelectorAll('[data-step]')];
  const nextButton = form.querySelector('[data-step-next]');
  const prevButton = form.querySelector('[data-step-prev]');
  const submitButton = form.querySelector('[data-step-submit]');
  const stepLabel = form.querySelector('[data-step-label]');
  const stepProgress = form.querySelector('[data-step-progress]');
  let currentStep = 0;

  const showStep = (index) => {
    currentStep = Math.max(0, Math.min(index, steps.length - 1));
    steps.forEach((step, i) => { step.hidden = i !== currentStep; step.classList.toggle('is-active', i === currentStep); });
    if (stepLabel) stepLabel.textContent = `Étape ${currentStep + 1} sur ${steps.length}`;
    if (stepProgress) stepProgress.style.width = `${((currentStep + 1) / steps.length) * 100}%`;
    if (prevButton) prevButton.hidden = currentStep === 0;
    if (nextButton) nextButton.hidden = currentStep === steps.length - 1;
    if (submitButton) submitButton.hidden = currentStep !== steps.length - 1;
    steps[currentStep]?.querySelector('input, select, button')?.focus({ preventScroll: true });
  };

  const validateStep = () => {
    const fields = [...steps[currentStep].querySelectorAll('input, select, textarea')];
    return fields.every((field) => field.reportValidity());
  };
  nextButton?.addEventListener('click', () => { if (validateStep()) showStep(currentStep + 1); });
  prevButton?.addEventListener('click', () => showStep(currentStep - 1));
  showStep(0);

  const updatePreview = () => {
    const option = category?.selectedOptions?.[0];
    if (!option?.dataset.image || !preview || !previewImage) {
      if (preview) preview.hidden = true;
      return;
    }
    const label = option.dataset.categoryName || option.textContent.trim();
    previewImage.src = option.dataset.image;
    previewImage.alt = `Aperçu : ${label}`;
    if (previewCaption) previewCaption.textContent = label;
    preview.hidden = false;
  };
  category?.addEventListener('change', updatePreview);
  updatePreview();
  document.querySelectorAll('[data-demo-product]').forEach((button) => {
    button.addEventListener('click', () => {
      category.value = button.dataset.category || '';
      form.elements.brand.value = button.dataset.brand || '';
      form.elements.model.value = button.dataset.model || '';
      updatePreview();
      button.closest('.demo-products')?.querySelectorAll('button').forEach((item) => item.classList.toggle('is-selected', item === button));
    });
  });

  const setText = (selector, value) => {
    const element = document.querySelector(selector);
    if (element) element.textContent = value;
  };

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    status.textContent = 'Calcul en cours…';
    const submit = submitButton;
    submit.disabled = true;

    try {
      const response = await fetch(endpoint, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'fetch' } });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.message || 'Calcul indisponible.');

      const data = result.data;
      setText('[data-resale-value]', euros.format(data.resale_value));
      setText('[data-sale-net]', euros.format(data.sale_net));
      setText('[data-rental-day]', euros.format(data.rental_day));
      setText('[data-rental-10]', euros.format(data.rental_10));
      setText('[data-rental-30]', euros.format(data.rental_30));
      setText('[data-rental-60]', euros.format(data.rental_60));
      setText('[data-break-even]', String(data.break_even_days));
      setText('[data-match-label]', data.match_type === 'exact' ? 'Référence modèle exacte' : 'Moyenne de catégorie');
      setText('[data-score-symbol]', data.score_symbol);
      setText('[data-score-label]', data.score_label);
      setText('[data-score-benefit]', data.score_summary + (data.discount_percent > 0 ? ` Avantage locataire : -${data.discount_percent}% sur les frais de service.` : ' Aucune réduction associée à ce niveau.'));
      sessionStorage.setItem('focal_simulation', JSON.stringify({
        category_id: form.elements.category_id.value,
        brand: form.elements.brand.value,
        model: form.elements.model.value,
        purchase_year: form.elements.purchase_year.value,
        condition_grade: form.elements.condition_grade.value,
        reuse_count: form.elements.reuse_count.value,
        distance_km: form.elements.distance_km.value,
        repaired: form.elements.repaired.checked,
        sale_price: data.resale_value,
        rental_price_day: data.rental_day,
        sale_min: data.sale_min,
        sale_max: data.sale_max,
        rental_min: data.rental_min,
        rental_max: data.rental_max,
        saved_at: Date.now()
      }));
      const scoreCard = document.querySelector('[data-score-card]');
      if (scoreCard) scoreCard.className = `sim-score-card score-panel-${data.score_class}`;
      placeholder.hidden = true;
      content.hidden = false;
      if (resultPanel) resultPanel.hidden = false;
      status.textContent = 'Estimation calculée.';
      window.FocalAnalytics?.track('simulator_complete', { match_type: data.match_type });
      content.focus({ preventScroll: true });
      content.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
      status.textContent = error.message || 'Une erreur est survenue.';
    } finally {
      submit.disabled = false;
    }
  });
})();
