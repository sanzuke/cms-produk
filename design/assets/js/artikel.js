(function () {
  var chips = document.querySelectorAll("#kategori .chip");
  var posts = document.querySelectorAll("#post-grid .post");
  var empty = document.getElementById("empty");
  chips.forEach(function (chip) {
    chip.addEventListener("click", function () {
      var k = chip.getAttribute("data-kategori");
      chips.forEach(function (c) { c.setAttribute("aria-pressed", String(c === chip)); });
      var shown = 0;
      posts.forEach(function (p) {
        var ok = !k || p.getAttribute("data-kategori") === k;
        p.hidden = !ok;
        if (ok) shown++;
      });
      empty.hidden = shown !== 0;
    });
  });
})();
