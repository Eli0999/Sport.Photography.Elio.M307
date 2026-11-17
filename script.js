const form = document.querySelector('#anmeldung-form');
const feedback = document.querySelector('#feedback');
const fotoInput = document.querySelector('#foto');
const uploadTitle = document.querySelector('#upload-title');
const submit = form.querySelector('button[type="submit"]');
const uploadPrompt = uploadTitle.textContent;
const preview = document.querySelector('#vorschau');
let previewUrl;

function clearPreview() {
  preview.hidden = true;
  preview.removeAttribute('src');
  if (previewUrl) {
    URL.revokeObjectURL(previewUrl);
    previewUrl = undefined;
  }
}

function showFeedback(message, type) {
  feedback.textContent = message;
  feedback.dataset.type = type;
}

function clearFieldErrors() {
  form.querySelectorAll('[aria-invalid]').forEach((field) => {
    field.removeAttribute('aria-invalid');
    field.removeAttribute('aria-describedby');
  });
}

fotoInput.addEventListener('change', () => {
  clearPreview();
  const file = fotoInput.files[0];
  uploadTitle.textContent = file?.name || uploadPrompt;
  if (file && ['image/jpeg', 'image/png'].includes(file.type) && file.size <= 10 * 1024 * 1024) {
    previewUrl = URL.createObjectURL(file);
    preview.src = previewUrl;
    preview.hidden = false;
  }
});

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (submit.disabled) return;

  clearFieldErrors();
  showFeedback('Anmeldung wird gesendet …', 'pending');
  submit.disabled = true;

  try {
    let response;
    try {
      response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
      });
    } catch {
      showFeedback('Keine Verbindung zum Server. Bitte versuche es erneut.', 'error');
      return;
    }

    const data = await response.json();
    if (response.ok && data.ok) {
      form.reset();
      clearPreview();
      uploadTitle.textContent = uploadPrompt;
      showFeedback(data.meldung, 'success');
      return;
    }

    const errors = Object.entries(data.fehler ?? {});
    for (const [name] of errors) {
      form.querySelectorAll('[name]').forEach((field) => {
        if (field.name === name || field.name === name + '[]') {
          field.setAttribute('aria-invalid', 'true');
          field.setAttribute('aria-describedby', 'feedback');
        }
      });
    }
    showFeedback(errors.map(([, message]) => message).join(' ') || data.meldung, 'error');
    form.querySelector('[aria-invalid]')?.focus();
  } catch {
    showFeedback('Der Server hat eine unerwartete Antwort gesendet.', 'error');
  } finally {
    submit.disabled = false;
  }
});

// Bei aktivem JavaScript zeigt das Formular die Feldfehler aus PHP an.
form.noValidate = true;
