(function () {
  var storageKey = 'powerupHelpfulReviews';
  var helpfulState = {};

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

  function setHelpfulButtonState(button) {
    var reviewId = button.getAttribute('data-review-id');
    if (!reviewId || !helpfulState[reviewId]) {
      return;
    }

    button.classList.add('is-marked');
    button.textContent = 'Marked helpful';
    button.setAttribute('aria-pressed', 'true');
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

  function handleHelpful(button) {
    var reviewId = button.getAttribute('data-review-id');
    if (!reviewId) {
      return;
    }

    helpfulState[reviewId] = true;
    writeHelpfulState();
    setHelpfulButtonState(button);
    setActionStatus(button, 'Thank you for your feedback.');
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
