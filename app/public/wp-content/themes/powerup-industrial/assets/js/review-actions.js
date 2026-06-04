(function () {
  var storageKey = 'powerupHelpfulReviews';
  var helpfulState = {};
  var config = window.powerupReviewActionsConfig || {};

  function readHelpfulState() {
    try {
      helpfulState = JSON.parse(window.localStorage.getItem(storageKey) || '{}') || {};
    } catch (error) {
      helpfulState = {};
    }
  }

  function writeHelpfulState() {
    try {
      window.localStorage.setItem(storageKey, JSON.stringify(helpfulState));
    } catch (error) {
      // Local storage may be unavailable in private browsing. The click feedback still works.
    }
  }

  function formatHelpfulLabel(count, marked) {
    var label = marked ? 'Marked helpful' : 'Helpful';
    var numericCount = Number.parseInt(count, 10) || 0;

    if (numericCount < 1) {
      return label;
    }

    return label + ' (' + numericCount.toLocaleString() + ')';
  }

  function getHelpfulCount(button) {
    return Number.parseInt(button.getAttribute('data-helpful-count'), 10) || 0;
  }

  function setHelpfulButtonState(button, count) {
    var reviewId = button.getAttribute('data-review-id');
    var helpfulCount = typeof count === 'number' ? count : getHelpfulCount(button);
    var marked = !!(reviewId && helpfulState[reviewId]);

    button.setAttribute('data-helpful-count', String(helpfulCount));
    button.textContent = formatHelpfulLabel(helpfulCount, marked);

    if (marked) {
      button.classList.add('is-marked');
      button.setAttribute('aria-pressed', 'true');
    } else {
      button.classList.remove('is-marked');
      button.setAttribute('aria-pressed', 'false');
    }
  }

  function setActionStatus(button, message) {
    var actions = button.closest('.powerup-amz-review-actions');
    if (!actions) {
      return;
    }

    var status = actions.querySelector('.powerup-amz-review-action-status');
    if (!status) {
      return;
    }

    status.textContent = message;
  }

  function postHelpful(reviewId) {
    var body = new window.URLSearchParams();
    body.set('action', 'powerup_review_helpful');
    body.set('comment_id', reviewId);
    body.set('nonce', config.nonce || '');

    return window.fetch(config.ajaxUrl || '/wp-admin/admin-ajax.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: body.toString()
    }).then(function (response) {
      return response.json();
    });
  }

  function handleHelpful(button) {
    var reviewId = button.getAttribute('data-review-id');
    if (!reviewId) {
      return;
    }

    if (helpfulState[reviewId]) {
      setHelpfulButtonState(button);
      setActionStatus(button, 'You already marked this review helpful.');
      return;
    }

    button.disabled = true;
    setActionStatus(button, 'Saving your feedback...');

    postHelpful(reviewId).then(function (payload) {
      if (!payload || !payload.success) {
        throw new Error('Helpful request failed.');
      }

      var count = Number.parseInt(payload.data && payload.data.count, 10) || getHelpfulCount(button) + 1;

      helpfulState[reviewId] = true;
      writeHelpfulState();
      setHelpfulButtonState(button, count);
      setActionStatus(button, 'Thank you for your feedback.');
    }).catch(function () {
      setActionStatus(button, 'Sorry, this could not be saved. Please try again.');
    }).finally(function () {
      button.disabled = false;
    });
  }

  function handleReport(button) {
    setActionStatus(button, 'Thanks. Please contact support with this review if you need help.');
  }

  function init() {
    readHelpfulState();

    document.querySelectorAll('.powerup-amz-helpful-btn').forEach(function (button) {
      setHelpfulButtonState(button);
    });

    document.addEventListener('click', function (event) {
      var helpfulButton = event.target.closest('.powerup-amz-helpful-btn');
      if (helpfulButton) {
        handleHelpful(helpfulButton);
        return;
      }

      var reportButton = event.target.closest('.powerup-amz-report');
      if (reportButton) {
        handleReport(reportButton);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
