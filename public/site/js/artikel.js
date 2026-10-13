(function () {
  var input = document.querySelector(".post-search input[type=search]");
  if (!input) return;
  // Tombol "x" bawaan kolom pencarian: kirim ulang form agar hasil ikut ter-reset.
  input.addEventListener("search", function () {
    if (input.value === "") input.form.submit();
  });
})();
