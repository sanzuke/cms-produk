/* Pesan: stepper 4 langkah + ringkasan harga live. Tanpa backend, tanpa dependensi. */
(function () {
  var root = document.getElementById('order-form');
  if (!root) return;

  // Harga contoh (placeholder) — dalam rupiah penuh.
  var PKG = {
    harian: { label: 'Dokumentasi Harian', price: 3500000, unit: '1 hari kerja' },
    'aerial-lite': { label: 'Aerial Survey Lite', price: 5000000, unit: '1 hari, 1–2 area' },
    profile: { label: 'Profile Tambang', price: 12500000, unit: '2–3 hari, tim lengkap' },
    monitoring: { label: 'Monitoring Bulanan', price: 4000000, unit: 'per bulan, min. 3 bulan' }
  };
  var ADDON = {
    'extra-day': { label: 'Hari tambahan', price: 2500000, qty: true },
    'extra-revision': { label: 'Revisi tambahan', price: 750000, qty: true },
    'extra-drone': { label: 'Drone tambahan', price: 3000000, qty: false },
    express: { label: 'Express delivery (24 jam)', price: 1500000, qty: false },
    watermark: { label: 'Watermark custom', price: 750000, qty: false },
    timelapse: { label: 'Montase time-lapse', price: 1000000, qty: false }
  };

  var panels = Array.prototype.slice.call(root.querySelectorAll('.wiz-panel'));
  var steps = Array.prototype.slice.call(document.querySelectorAll('.stepper li'));
  var lineBox = document.getElementById('sum-lines');
  var totalBox = document.getElementById('sum-total');
  var stepNow = 0;

  var rupiah = new Intl.NumberFormat('id-ID');
  function money(n) { return 'Rp ' + rupiah.format(n); }

  function sel(name) { return root.querySelector('input[name="' + name + '"]:checked'); }
  function addons() {
    return Array.prototype.slice.call(root.querySelectorAll('input[name="addon"]:checked')).map(function (i) {
      var a = ADDON[i.value];
      var qty = a.qty ? Math.max(1, parseInt(document.getElementById('qty-' + i.value).value, 10) || 1) : 1;
      return { label: a.label + (a.qty ? ' ×' + qty : ''), total: a.price * qty };
    });
  }

  function render() {
    var lines = [];
    var custom = sel('jalur') && sel('jalur').value === 'custom';
    var pkg = custom ? null : sel('paket');
    if (pkg) lines.push({ label: PKG[pkg.value].label + ' (' + PKG[pkg.value].unit + ')', total: PKG[pkg.value].price });
    if (custom) lines.push({ label: 'Penawaran custom — layanan, cakupan, dan biaya ditentukan setelah survei', total: null });
    addons().forEach(function (a) { lines.push(a); });

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
    ['nama', 'perusahaan', 'lokasi', 'komoditas', 'durasi'].forEach(function (id) {
      var f = document.getElementById('f-' + id);
      var out = document.getElementById('r-' + id);
      if (f && out) out.textContent = f.value || '—';
    });
    var t = document.getElementById('f-tanggal');
    var outT = document.getElementById('r-tanggal');
    if (t && outT) outT.textContent = t.value ? new Date(t.value + 'T00:00').toLocaleDateString('id-ID', { dateStyle: 'long' }) : '—';
  }

  function show(i) {
    stepNow = Math.max(0, Math.min(panels.length - 1, i));
    panels.forEach(function (p, n) { p.classList.toggle('is-active', n === stepNow); });
    steps.forEach(function (s, n) {
      s.setAttribute('data-state', n === stepNow ? 'active' : n < stepNow ? 'done' : 'todo');
    });
    root.querySelector('.wiz-panel.is-active').scrollIntoView({ block: 'start' });
    recap();
    render();
  }

  function valid(panel) {
    var seen = [];
    var bad = Array.prototype.slice.call(panel.querySelectorAll('[required]')).filter(function (f) {
      if (seen.indexOf(f.name) > -1) return false; // radio: cukup periksa satu per kelompok
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

  // Jalur custom menyalin/menyembunyikan pilihan paket.
  root.addEventListener('change', function (e) {
    var isCustom = sel('jalur') && sel('jalur').value === 'custom';
    var box = document.getElementById('box-paket');
    var custom = document.getElementById('box-custom');
    if (box) box.hidden = isCustom;
    if (custom) custom.hidden = !isCustom;
    if (isCustom) {
      root.querySelectorAll('input[name="paket"], input[name="addon"]').forEach(function (i) { i.disabled = true; });
    } else {
      root.querySelectorAll('input[name="paket"], input[name="addon"]').forEach(function (i) { i.disabled = false; });
    }
    render();
  });

  document.querySelectorAll('input[name="addon"][data-qty]').forEach(function (i) {
    i.addEventListener('input', render);
  });


  root.addEventListener('submit', function (e) {
    e.preventDefault();
    for (var i = 0; i < stepNow; i++) if (!valid(panels[i])) { show(i); return; }
    var nomor = 'RSV-' + new Date().getFullYear() + '-XXXX';
    var ref = document.getElementById('confirm-ref');
    if (ref) ref.textContent = nomor;
    document.getElementById('wizard').hidden = true;
    document.getElementById('confirm').hidden = false;
    document.getElementById('confirm').scrollIntoView({ block: 'start' });
  });

  // Prefill dari halaman paket: pesan.html?paket=profile atau ?jalur=custom
  var q = new URLSearchParams(location.search);
  if (q.get('jalur') === 'custom') {
    var c = root.querySelector('input[name="jalur"][value="custom"]');
    if (c) c.checked = true;
  }
  var pk = q.get('paket');
  var p = pk && root.querySelector('input[name="paket"][value="' + pk + '"]');
  if (p) p.checked = true;

  root.dispatchEvent(new Event('change'));
  show(0);
})();