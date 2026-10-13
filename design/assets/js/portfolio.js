/* Filter portofolio: chip [data-filter="jenis|komoditas|area|tahun"][data-value]; kartu memakai data-* yang sama. */
(function () {
  var cards = Array.prototype.slice.call(document.querySelectorAll('.pf-card'));
  var chips = Array.prototype.slice.call(document.querySelectorAll('.chip'));
  var count = document.getElementById('pf-count');
  var empty = document.getElementById('pf-empty');
  var state = {};

  function apply() {
    var shown = 0;
    cards.forEach(function (c) {
      var ok = Object.keys(state).every(function (k) { return !state[k] || c.dataset[k] === state[k]; });
      c.hidden = !ok;
      if (ok) shown++;
    });
    if (count) count.textContent = shown;
    if (empty) empty.hidden = shown !== 0;
  }

  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      var k = chip.dataset.filter, v = chip.dataset.value;
      state[k] = v;
      chips.filter(function (x) { return x.dataset.filter === k; }).forEach(function (x) {
        x.setAttribute('aria-pressed', String(x === chip));
      });
      apply();
    });
  });

  document.querySelectorAll('[data-reset]').forEach(function (b) {
    b.addEventListener('click', function () {
      state = {};
      chips.forEach(function (x) { x.setAttribute('aria-pressed', String(x.dataset.value === '')); });
      apply();
    });
  });
  apply();
})();
