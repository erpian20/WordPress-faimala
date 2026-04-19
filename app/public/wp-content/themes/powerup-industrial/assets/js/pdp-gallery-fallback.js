(function () {
  var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  function updateZoom(mainWrap) {
    if (!canHover || !mainWrap) {
      return;
    }

    if (mainWrap.classList.contains('is-video-active')) {
      return;
    }

    var zoomPane = mainWrap.querySelector('.powerup-amz-zoom-pane');
    var mainImage = mainWrap.querySelector('.powerup-amz-main-image');

    if (!zoomPane || !mainImage) {
      return;
    }

    var zoomSrc = mainWrap.getAttribute('data-zoom-image') || mainImage.getAttribute('src') || '';
    if (zoomSrc) {
      zoomPane.style.backgroundImage = 'url("' + zoomSrc + '")';
    }
  }

  function bindZoom() {
    if (!canHover) {
      return;
    }

    var mainAreas = document.querySelectorAll('.powerup-amz-main');

    mainAreas.forEach(function (mainWrap) {
      var mainImage = mainWrap.querySelector('.powerup-amz-main-image');
      var mainVideo = mainWrap.querySelector('.powerup-amz-main-video');
      var zoomPane = mainWrap.querySelector('.powerup-amz-zoom-pane');
      var lens = mainWrap.querySelector('.powerup-amz-lens');

      if (!mainImage || !zoomPane || !lens) {
        return;
      }

      updateZoom(mainWrap);

      function placeZoomPane() {
        mainWrap.classList.remove('zoom-below');

        var rect = mainWrap.getBoundingClientRect();
        var estimatedPaneWidth = Math.min(window.innerWidth * 0.44, 560);
        var rightGap = window.innerWidth - rect.right;

        if (rightGap < estimatedPaneWidth + 24) {
          mainWrap.classList.add('zoom-below');
        }
      }

      mainWrap.addEventListener('mouseenter', function () {
        if (mainWrap.classList.contains('is-video-active')) {
          return;
        }
        updateZoom(mainWrap);
        placeZoomPane();
        mainWrap.classList.add('is-zoom-active');
      });

      mainWrap.addEventListener('mouseleave', function () {
        mainWrap.classList.remove('is-zoom-active');
      });

      mainWrap.addEventListener('mousemove', function (event) {
        if (mainWrap.classList.contains('is-video-active')) {
          return;
        }

        var rect = mainWrap.getBoundingClientRect();
        if (!rect.width || !rect.height) {
          return;
        }

        var x = ((event.clientX - rect.left) / rect.width) * 100;
        var y = ((event.clientY - rect.top) / rect.height) * 100;

        x = Math.max(0, Math.min(100, x));
        y = Math.max(0, Math.min(100, y));

        zoomPane.style.backgroundPosition = x + '% ' + y + '%';

        var lensX = event.clientX - rect.left;
        var lensY = event.clientY - rect.top;
        lens.style.left = lensX + 'px';
        lens.style.top = lensY + 'px';
      });

      window.addEventListener('resize', placeZoomPane);

      if (mainVideo) {
        mainVideo.addEventListener('play', function () {
          mainWrap.classList.remove('is-zoom-active');
        });
      }
    });
  }

  document.addEventListener('click', function (event) {
    var thumb = event.target.closest('.powerup-amz-thumb');
    if (!thumb) {
      return;
    }

    var gallery = thumb.closest('[data-powerup-pdp-gallery="1"]');
    if (!gallery) {
      return;
    }

    var mainImage = gallery.querySelector('.powerup-amz-main-image');
    var mainVideo = gallery.querySelector('.powerup-amz-main-video');
    var mainWrap = gallery.querySelector('.powerup-amz-main');
    if (!mainImage || !mainWrap) {
      return;
    }

    var mediaType = thumb.getAttribute('data-type') || 'image';
    var image = thumb.getAttribute('data-image');
    var zoomImage = thumb.getAttribute('data-zoom') || image;
    var video = thumb.getAttribute('data-video') || '';
    var alt = thumb.getAttribute('data-alt') || '';

    if (mediaType === 'video' && video) {
      mainWrap.classList.add('is-video-active');
      mainWrap.classList.remove('is-zoom-active');
      mainWrap.setAttribute('data-zoom-image', '');

      mainImage.hidden = true;

      if (mainVideo) {
        var source = mainVideo.querySelector('source');
        if (source) {
          source.setAttribute('src', video);
        } else {
          source = document.createElement('source');
          source.setAttribute('src', video);
          source.setAttribute('type', 'video/mp4');
          mainVideo.appendChild(source);
        }
        mainVideo.hidden = false;
        mainVideo.load();
      }
    } else {
      if (!image) {
        return;
      }

      mainWrap.classList.remove('is-video-active');
      mainImage.hidden = false;
      mainImage.setAttribute('src', image);
      mainImage.setAttribute('alt', alt);
      mainWrap.setAttribute('data-zoom-image', zoomImage || '');
      updateZoom(mainWrap);

      if (mainVideo) {
        mainVideo.pause();
        mainVideo.hidden = true;
      }
    }

    var thumbs = gallery.querySelectorAll('.powerup-amz-thumb');
    thumbs.forEach(function (item) {
      item.classList.remove('is-active');
    });
    thumb.classList.add('is-active');
  });

  bindZoom();
})();
