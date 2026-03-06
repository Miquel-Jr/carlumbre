<script>
(function() {
  var debounceTimers = new WeakMap();
  var debouncedInputs = document.querySelectorAll('input[data-debounce-search]');

  debouncedInputs.forEach(function(input) {
    var debounceMs = parseInt(input.getAttribute('data-debounce-ms') || '300', 10);

    if (isNaN(debounceMs) || debounceMs < 0) {
      debounceMs = 300;
    }

    var targetForm = input.form;
    var formSelector = input.getAttribute('data-debounce-form');

    if (!targetForm && formSelector) {
      targetForm = document.querySelector(formSelector);
    }

    if (!targetForm) {
      return;
    }

    input.addEventListener('input', function() {
      var currentTimer = debounceTimers.get(input);
      if (currentTimer) {
        clearTimeout(currentTimer);
      }

      var nextTimer = setTimeout(function() {
        if (typeof targetForm.requestSubmit === 'function') {
          targetForm.requestSubmit();
          return;
        }

        targetForm.submit();
      }, debounceMs);

      debounceTimers.set(input, nextTimer);
    });
  });
})();
</script>
