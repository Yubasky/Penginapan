(function () {
  var t = document.querySelector('.nav__toggle'), m = document.getElementById('menu');
  if (t && m) t.addEventListener('click', function () {
    var open = m.classList.toggle('is-open');
    t.setAttribute('aria-expanded', open);
  });
  var f = document.getElementById('filter-form');
  if (f) f.querySelectorAll('select, input[type=checkbox]').forEach(function (el) {
    el.addEventListener('change', function () { f.submit(); });
  });
})();
