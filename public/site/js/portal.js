/* Portal klien: peningkatan progresif. Tanpa JS, tombol favorit tetap bekerja lewat form biasa. */
(function () {
  document.querySelectorAll('form.js-fav').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.fetch) return;
      event.preventDefault();

      var button = form.querySelector('.fav');
      button.disabled = true;

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      }).then(function (res) {
        if (!res.ok) throw new Error('fail');
        return res.json();
      }).then(function (data) {
        button.setAttribute('aria-pressed', data.favorite ? 'true' : 'false');
        button.querySelector('[data-fav-icon]').textContent = data.favorite ? '★' : '☆';
        var counter = document.querySelector('[data-fav-count]');
        if (counter) counter.textContent = data.count;
        button.disabled = false;
      }).catch(function () {
        // Gagal (mis. sesi habis): kirim form dengan cara biasa.
        form.submit();
      });
    });
  });
})();
