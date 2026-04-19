document.addEventListener(
  'error',
  function (event) {
    var target = event.target;
    if (!(target instanceof HTMLImageElement)) {
      return;
    }

    if (target.dataset.fallbackApplied === '1') {
      return;
    }

    var src = target.getAttribute('src') || '';
    if (!/^https?:\/\//i.test(src)) {
      return;
    }

    var fallbackUrl =
      window.powerupImageFallbackConfig &&
      typeof window.powerupImageFallbackConfig.fallbackUrl === 'string'
        ? window.powerupImageFallbackConfig.fallbackUrl
        : '';

    if (!fallbackUrl) {
      return;
    }

    target.dataset.fallbackApplied = '1';
    target.src = fallbackUrl;
  },
  true
);
