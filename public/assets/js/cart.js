document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[name^="qty["]').forEach(function (input) {
    input.addEventListener('change', function () {
      const value = parseInt(input.value, 10);
      if (!Number.isFinite(value) || value < 1) {
        input.value = 1;
      }
    });
  });

  document.querySelectorAll('a[href^="delete_item.php"], a[href^="clear_cart.php"]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      const label = link.href.indexOf('clear_cart.php') !== -1 ? 'clear your cart' : 'remove this item';
      if (!window.confirm('Are you sure you want to ' + label + '?')) {
        event.preventDefault();
      }
    });
  });
});
