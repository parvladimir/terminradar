document.addEventListener('submit', (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) return;
  const button = form.querySelector('button[type="submit"]');
  if (button) {
    button.dataset.originalText = button.textContent || '';
    button.setAttribute('aria-busy', 'true');
  }
});
