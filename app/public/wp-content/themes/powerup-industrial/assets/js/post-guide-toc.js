(function () {
  var tocRoot = document.querySelector('[data-guide-toc]');
  var contentRoot = document.getElementById('post-guide-content');
  if (!tocRoot) {
    return;
  }

  var links = Array.prototype.slice.call(tocRoot.querySelectorAll('[data-guide-link]'));
  if (!links.length) {
    return;
  }

  var entries = links
    .map(function (link) {
      var href = link.getAttribute('href') || '';
      var id = href.charAt(0) === '#' ? href.slice(1) : '';
      if (!id) {
        return null;
      }

      var section = document.getElementById(id);
      if (!section) {
        return null;
      }

      return { link: link, section: section };
    })
    .filter(Boolean);

  if (!entries.length) {
    return;
  }

  var setActive = function (sectionId) {
    entries.forEach(function (entry) {
      var targetId = entry.section.id;
      entry.link.classList.toggle('is-active', targetId === sectionId);
    });
  };

  var observer = new IntersectionObserver(
    function (records) {
      var visible = records
        .filter(function (record) {
          return record.isIntersecting;
        })
        .sort(function (a, b) {
          return b.intersectionRatio - a.intersectionRatio;
        });

      if (visible.length) {
        setActive(visible[0].target.id);
      }
    },
    {
      rootMargin: '-28% 0px -55% 0px',
      threshold: [0.1, 0.3, 0.6],
    }
  );

  entries.forEach(function (entry) {
    observer.observe(entry.section);
  });

  var initialHash = window.location.hash ? window.location.hash.slice(1) : entries[0].section.id;
  setActive(initialHash);

  var progressBar = tocRoot.querySelector('[data-guide-progress-bar]');
  var progressText = tocRoot.querySelector('[data-guide-progress-text]');

  if (!contentRoot || !progressBar || !progressText) {
    return;
  }

  var rafId = null;
  var updateProgress = function () {
    var contentTop = contentRoot.getBoundingClientRect().top + window.pageYOffset;
    var contentHeight = contentRoot.offsetHeight;
    var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
    var raw = (window.pageYOffset - contentTop + viewportHeight * 0.25) / Math.max(contentHeight, 1);
    var percent = Math.max(0, Math.min(100, Math.round(raw * 100)));

    progressBar.style.width = percent + '%';
    progressText.textContent = percent + '%';
  };

  var onScrollLike = function () {
    if (rafId) {
      return;
    }

    rafId = window.requestAnimationFrame(function () {
      updateProgress();
      rafId = null;
    });
  };

  window.addEventListener('scroll', onScrollLike, { passive: true });
  window.addEventListener('resize', onScrollLike);
  updateProgress();
})();
