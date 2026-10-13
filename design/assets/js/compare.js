/* Before/after slider: setiap .compare berisi input.compare__range (0-100) yang menggerakkan --pos. */
document.querySelectorAll('.compare').forEach(function (el) {
  var range = el.querySelector('.compare__range');
  if (!range) return;
  var set = function () { el.style.setProperty('--pos', range.value + '%'); };
  range.addEventListener('input', set);
  set();
});
