/* Pesan: peningkatan progresif form 4 langkah + ringkasan harga indikatif.
   Form berfungsi penuh tanpa JS (semua langkah tampil, dikirim ke server);
   skrip ini hanya memecahnya menjadi langkah. Harga dibaca dari atribut data-*. */
(function () {
  var root = document.getElementById('order-form');
  var wizard = document.getElementById('wizard');
  if (!root || !wizard) return;

  wizard.classList.add('js-wizard');
  root.noValidate = true; // validasi per langkah ditangani di bawah

  var panels = Array.prototype.slice.call(root.querySelectorAll('.wiz-panel'));
  var steps = Array.prototype.slice.call(root.querySelectorAll('.stepper li'));
  var lineBox = document.getElementById('sum-lines');
  var totalBox = document.getElementById('sum-total');
  var stepNow = 0;

  var rupiah = new Intl.NumberFormat('id-ID');
  function money(n) { return 'Rp ' + rupiah.format(n); }

  function sel(name) { return root.querySelector('input[name="' + name + '"]:checked'); }
  function isCustom() { var j = sel('jalur'); return !!j && j.value === 'custom'; }

  function addons() {
    return Array.prototype.slice.call(root.querySelectorAll('input[name="addon[]"]:checked')).map(function (i) {
      var price = parseInt(i.getAttribute('data-price'), 10) || 0;
      var qty = 1;
      if (i.hasAttribute('data-qty')) {
        var q = document.getElementById('qty-' + i.value);
        qty = Math.max(1, parseInt(q && q.value, 10) || 1);
      }
      return { label: i.getAttribute('data-label') + (i.hasAttribute('data-qty') ? ' ×' + qty : ''), total: price * qty };
    });
  }

  function render() {
    var lines = [];
    var pkg = isCustom() ? null : sel('paket');
    if (pkg) {
      var unit = pkg.getAttribute('data-unit');
      lines.push({ label: pkg.getAttribute('data-label') + (unit ? ' (' + unit + ')' : ''), total: parseInt(pkg.getAttribute('data-price'), 10) || 0 });
    }
    if (isCustom()) lines.push({ label: 'Penawaran custom — layanan, cakupan, dan biaya ditentukan setelah survei', total: null });
    if (!isCustom()) addons().forEach(function (a) { lines.push(a); });

    lineBox.innerHTML = '';
    if (!lines.length) {
      var li = document.createElement('li');
      li.innerHTML = '<span>Belum ada pilihan</span><span>—</span>';
      lineBox.appendChild(li);
      totalBox.textContent = '—';
      return;
    }
    var subtotal = 0;
    var hasTotal = true;
    lines.forEach(function (l) {
      var li = document.createElement('li');
      var name = document.createElement('span');
      var val = document.createElement('span');
      name.textContent = l.label;
      val.textContent = l.total === null ? 'Menunggu penawaran' : money(l.total);
      li.appendChild(name); li.appendChild(val);
      lineBox.appendChild(li);
      if (l.total === null) hasTotal = false; else subtotal += l.total;
    });
    totalBox.textContent = hasTotal ? money(subtotal) : 'Akan dihitung';
  }

  function recap() {
    root.querySelectorAll('[data-recap]').forEach(function (out) {
      var f = document.getElementById(out.getAttribute('data-recap'));
      if (!f) return;
      var text = f.value;
      if (f.tagName === 'SELECT') text = f.value ? f.options[f.selectedIndex].text : '';
      else if (f.type === 'date' && f.value) text = new Date(f.value + 'T00:00').toLocaleDateString('id-ID', { dateStyle: 'long' });
      out.textContent = text || '—';
    });
  }

  function show(i, noScroll) {
    stepNow = Math.max(0, Math.min(panels.length - 1, i));
    panels.forEach(function (p, n) { p.classList.toggle('is-active', n === stepNow); });
    steps.forEach(function (s, n) {
      s.setAttribute('data-state', n === stepNow ? 'active' : n < stepNow ? 'done' : 'todo');
    });
    if (!noScroll) root.querySelector('.wiz-panel.is-active').scrollIntoView({ block: 'start' });
    recap();
    render();
  }

  function valid(panel) {
    var seen = [];
    var bad = Array.prototype.slice.call(panel.querySelectorAll('[required]')).filter(function (f) {
      if (f.disabled || seen.indexOf(f.name) > -1) return false; // radio: cukup periksa satu per kelompok
      seen.push(f.name);
      return !f.checkValidity();
    });
    if (bad.length) bad[0].reportValidity();
    return !bad.length;
  }

  root.addEventListener('click', function (e) {
    var next = e.target.closest('[data-next]');
    var prev = e.target.closest('[data-prev]');
    if (next) {
      if (!valid(panels[stepNow])) return;
      show(stepNow + 1);
    } else if (prev) {
      show(stepNow - 1);
    }
  });

  // Jalur custom menyembunyikan paket dan add-on; input yang dinonaktifkan tidak ikut terkirim.
  function syncJalur() {
    var custom = isCustom();
    var boxPaket = document.getElementById('box-paket');
    var boxAddon = document.getElementById('box-addon');
    var boxCustom = document.getElementById('box-custom');
    if (boxPaket) boxPaket.hidden = custom;
    if (boxAddon) boxAddon.hidden = custom;
    if (boxCustom) boxCustom.hidden = !custom;
    root.querySelectorAll('#box-paket input, #box-addon input').forEach(function (i) { i.disabled = custom; });
    render();
  }
  root.addEventListener('change', syncJalur);
  root.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty')) render();
  });

  root.addEventListener('submit', function (e) {
    for (var i = 0; i < panels.length; i++) {
      show(i, true);
      if (!valid(panels[i])) { e.preventDefault(); panels[i].scrollIntoView({ block: 'start' }); return; }
    }
    show(panels.length - 1, true);
  });

  syncJalur();

  // Setelah galat dari server, langsung buka langkah pertama yang bermasalah.
  var firstErr = root.querySelector('.field-error');
  var errPanel = firstErr && firstErr.closest('.wiz-panel');
  show(errPanel ? panels.indexOf(errPanel) : 0, true);
  if (errPanel) {
    var focusable = errPanel.querySelector('[aria-invalid="true"]');
    if (focusable) focusable.focus({ preventScroll: true });
  }
})();
