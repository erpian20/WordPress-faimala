document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('.shop-ref-filter-form');
  if (!form) {
    return;
  }

  var buildNormalizedQuery = function () {
    var params = new URLSearchParams();

    var searchInput = form.querySelector('input[name="q"]');
    var keyword = searchInput ? searchInput.value.trim() : '';
    if (keyword) {
      params.append('q', keyword);
    }

    var categories = Array.from(form.querySelectorAll('input[name="cat[]"]:checked'))
      .map(function (input) {
        return input.value.trim();
      })
      .filter(Boolean)
      .filter(function (value, index, arr) {
        return arr.indexOf(value) === index;
      })
      .sort();

    categories.forEach(function (value) {
      params.append('cat[]', value);
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
