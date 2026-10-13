/* Admin CMS — vanilla JS minimal:
   1) drawer sidebar (mobile)  2) user menu  3) tabs  4) kanban/table toggle
   5) panel media + alt text  6) toast demo  7) placeholder toggle contoh */

/* 1) Drawer sidebar */
(function () {
  var side = document.querySelector('.sidebar');
  var scrim = document.querySelector('.scrim');
  var burger = document.querySelector('[data-drawer-toggle]');
  if (!side || !burger) return;

  function set(open) {
    side.setAttribute('data-open', String(open));
    burger.setAttribute('aria-expanded', String(open));
    if (scrim) scrim.hidden = !open;
  }
  burger.addEventListener('click', function () {
    set(side.getAttribute('data-open') !== 'true');
  });
  if (scrim) scrim.addEventListener('click', function () { set(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') set(false);
  });
  side.setAttribute('data-open', 'false');
})();

/* 2) User menu */
(function () {
  var wrap = document.querySelector('.user');
  if (!wrap) return;
  var btn = wrap.querySelector('.user__btn');
  var menu = wrap.querySelector('.user__menu');
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    var open = menu.hidden;
    menu.hidden = !open;
    btn.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) {
      menu.hidden = true;
      btn.setAttribute('aria-expanded', 'false');
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { menu.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
  });
  menu.hidden = true;
})();


/* 3) Tabs: container [data-tabs] > [role=tab][data-tab] + [role=tabpanel][data-panel] */
(function () {
  document.querySelectorAll('[data-tabs]').forEach(function (bar) {
    var tabs = Array.prototype.slice.call(bar.querySelectorAll('[role="tab"]'));
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () { select(tab.dataset.tab); });
    });
    function select(id) {
      tabs.forEach(function (t) {
        var on = t.dataset.tab === id;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        var p = document.getElementById(t.getAttribute('aria-controls'));
        if (p) p.hidden = !on;
      });
    }
    var initial = tabs[0];
    tabs.forEach(function (t) {
      if (t.getAttribute('aria-selected') === 'true') initial = t;
    });
    select(initial.dataset.tab);
  });
})();

/* 4) Tampilan kanban <-> tabel: [data-view-toggle][data-view] + [data-view-panel] */
(function () {
  document.querySelectorAll('[data-view-toggle]').forEach(function (seg) {
    var buttons = Array.prototype.slice.call(seg.querySelectorAll('button'));
    buttons.forEach(function (b) {
      b.addEventListener('click', function () {
        buttons.forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
        document.querySelectorAll('[data-view-panel]').forEach(function (p) {
          p.hidden = p.dataset.viewPanel !== b.dataset.view;
        });
      });
    });
  });
})();

/* 5) Panel media + alt text */
(function () {
  var grid = document.querySelector('[data-media-grid]');
  var panels = Array.prototype.slice.call(document.querySelectorAll('[data-media-panel]'));
  if (!grid || !panels.length) return;
  var items = Array.prototype.slice.call(grid.querySelectorAll('.media-item'));

  items.forEach(function (item) {
    item.addEventListener('click', function () {
      items.forEach(function (x) { x.setAttribute('aria-pressed', String(x === item)); });
      panels.forEach(function (panel) {
        panel.querySelector('[data-media-name]').textContent = item.dataset.name;
        panel.querySelector('[data-media-meta]').textContent = item.dataset.meta;
        panel.querySelector('[data-media-alt]').value = item.dataset.alt || '';
        var state = panel.querySelector('[data-media-alt-state]');
        state.textContent = item.dataset.alt ? 'Alt terisi' : 'Alt belum diisi';
        state.dataset.missing = String(!item.dataset.alt);
        panel.querySelector('[data-media-preview]').setAttribute('data-label', item.dataset.label || item.dataset.name);
        panel.hidden = false;
      });
    });
  });
})();

/* 6) Toast: [data-toast] pesan singkat saat tombol aksi diklik */
(function () {
  var toast = document.querySelector('[data-toast]');
  if (!toast) return;
  var timer;
  document.querySelectorAll('[data-toast-msg]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (el.tagName === 'A' && el.getAttribute('href') && el.getAttribute('href') !== '#') return;
      e.preventDefault();
      toast.textContent = el.dataset.toastMsg;
      toast.hidden = false;
      clearTimeout(timer);
      timer = setTimeout(function () { toast.hidden = true; }, 2600);
    });
  });
  toast.hidden = true;
})();

/* 7) Checklist + rich text: contoh isi, tidak menyimpan apa pun */
(function () {
  document.querySelectorAll('[data-demo]').forEach(function (b) {
    b.addEventListener('click', function () {
      var target = document.querySelector(b.dataset.demo);
      if (!target) return;
      if ('value' in target) target.value = b.dataset.demoValue || '';
      else target.innerHTML = '<p>' + (b.dataset.demoValue || '') + '</p>';
    });
  });
})();
