document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('.shop-ref-filter-form');
  if (!form) {
    return;
  }

  var scrollStateKey = 'powerup_shop_scroll_restore';
  var productsSection = document.querySelector('.shop-ref-products-wrap');

  var scrollToProducts = function () {
    if (!productsSection) {
      return;
    }

    var header = document.querySelector('.site-header');
    var headerOffset = header ? header.offsetHeight : 0;
    var targetTop = productsSection.getBoundingClientRect().top + window.pageYOffset - headerOffset - 12;

    window.scrollTo(0, Math.max(0, targetTop));
  };

  var restoreScrollPosition = function () {
    try {
      var raw = sessionStorage.getItem(scrollStateKey);
      if (!raw) {
        return;
      }

      var state = JSON.parse(raw);
      sessionStorage.removeItem(scrollStateKey);

      if (!state || state.path !== window.location.pathname) {
        return;
      }

      window.requestAnimationFrame(function () {
        if (state.target === 'products') {
          scrollToProducts();
          return;
        }

        if (typeof state.scrollY === 'number') {
          window.scrollTo(0, Math.max(0, state.scrollY));
        }
      });
    } catch (err) {
      // Ignore storage errors and keep default browser behavior.
    }
  };

  restoreScrollPosition();

  var buildNormalizedQuery = function () {
    var params = new URLSearchParams();

    var searchInput = form.querySelector('input[name="q"]');
    var keyword = searchInput ? searchInput.value.trim() : '';
    if (keyword) {
      params.append('q', keyword);
    }

    var categories = Array.from(form.querySelectorAll('input[name="pcat[]"]:checked'))
      .map(function (input) {
        return input.value.trim();
      })
      .filter(Boolean)
      .filter(function (value, index, arr) {
        return arr.indexOf(value) === index;
      })
      .sort();

    categories.forEach(function (value) {
      params.append('pcat[]', value);
    });

    var prices = Array.from(form.querySelectorAll('input[name="price[]"]:checked'))
      .map(function (input) {
        return parseInt(input.value, 10);
      })
      .filter(function (value) {
        return !Number.isNaN(value);
      })
      .filter(function (value, index, arr) {
        return arr.indexOf(value) === index;
      })
      .sort(function (a, b) {
        return a - b;
      });

    prices.forEach(function (value) {
      params.append('price[]', String(value));
    });

    return params;
  };

  var submitNormalized = function () {
    var params = buildNormalizedQuery();
    var targetUrl = form.action || window.location.pathname;
    var queryString = params.toString();

    try {
      sessionStorage.setItem(
        scrollStateKey,
        JSON.stringify({
          path: window.location.pathname,
          target: 'products',
        })
      );
    } catch (err) {
      // Ignore storage errors and continue submitting.
    }

    window.location.assign(queryString ? targetUrl + '?' + queryString : targetUrl);
  };

  form.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
    input.addEventListener('change', function () {
      submitNormalized();
    });
  });

  var searchInput = form.querySelector('input[name="q"]');
  if (searchInput) {
    var debounceTimer;
    searchInput.addEventListener('input', function () {
      window.clearTimeout(debounceTimer);
      debounceTimer = window.setTimeout(function () {
        submitNormalized();
      }, 450);
    });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    submitNormalized();
  });
});
