const form = document.querySelector('#anmeldung-form');
const feedback = document.querySelector('#feedback');
const fotoInput = document.querySelector('#foto');
const uploadTitle = document.querySelector('#upload-title');

fotoInput.addEventListener('change', () => {
  const file = fotoInput.files[0];
  if (!file) return;
  if (file.size > 10 * 1024 * 1024) {
    fotoInput.value = '';
    uploadTitle.textContent = 'Die Datei ist grösser als 10 MB. Bitte wähle ein anderes Foto.';
    return;
  }
  uploadTitle.textContent = file.name;
});

form.addEventListener('submit', (event) => {
  event.preventDefault();
  const name = form.elements.vorname.value.trim();
  feedback.textContent = `Danke${name ? `, ${name}` : ''}! Deine Anmeldung wurde auf dieser Seite erfasst.`;
  form.reset();
  uploadTitle.textContent = 'Foto auswählen oder hier ablegen';
});
