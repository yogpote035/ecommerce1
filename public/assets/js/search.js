document.addEventListener('DOMContentLoaded', function () {
  var searchInputs = document.querySelectorAll('[data-search-input]');

  function navigateSearchLink(event) {
    var link = event.target.closest('.search-dropdown a[href]');
    if (!link) return;
    event.preventDefault();
    event.stopPropagation();
    window.location.assign(link.href);
  }

  function hideDropdown(dropdownElement) {
    if (!dropdownElement) return;
    dropdownElement.classList.add('d-none');
  }

  function showDropdown(dropdownElement) {
    if (!dropdownElement) return;
    var hasResults = dropdownElement.querySelector('[data-search-results]').children.length > 0;
    var hasSuggestions = dropdownElement.querySelector('[data-search-suggestions]').children.length > 0;
    var hasHistory = dropdownElement.querySelector('[data-search-history]').children.length > 0;
    if (hasResults || hasSuggestions || hasHistory) {
      dropdownElement.classList.remove('d-none');
    }
  }

  function renderResults(resultsElement, items) {
    if (!resultsElement) return;
    resultsElement.innerHTML = '';
    if (!items.length) {
      var noResult = document.createElement('div');
      noResult.className = 'list-group-item text-muted';
      noResult.textContent = 'No matching products found.';
      resultsElement.appendChild(noResult);
      return;
    }

    items.forEach(function (item) {
      var result = document.createElement('a');
      result.href = item.url;
      result.setAttribute('data-search-link', 'true');
      result.className = 'list-group-item list-group-item-action d-flex align-items-center py-2';
      result.innerHTML =
        '<img src="' + item.image + '" alt="' + item.title + '" class="rounded mr-3" style="width: 48px; height: 48px; object-fit: cover;">' +
        '<div>' +
        '<div class="font-weight-bold">' + item.title + '</div>' +
        '<small class="text-muted">' + item.brand + ' · ' + item.category + ' · ₹' + item.price + '</small>' +
        '</div>';
      resultsElement.appendChild(result);
    });
  }

  function buildSearchUrl(form, query) {
    var target = new URL(form ? form.action : 'search.php', window.location.href);
    target.searchParams.set('q', query || '');
    return target.toString();
  }

  function renderSuggestions(suggestionsElement, historiesElement, data) {
    if (!suggestionsElement || !historiesElement) return;
    suggestionsElement.innerHTML = '';
    historiesElement.innerHTML = '';

    if (data.suggestions && data.suggestions.length) {
      var suggestionHeader = document.createElement('div');
      suggestionHeader.className = 'list-group-item small text-uppercase text-muted border-bottom-0';
      suggestionHeader.textContent = 'Suggestions';
      suggestionsElement.appendChild(suggestionHeader);

      data.suggestions.forEach(function (item) {
        var suggestion = document.createElement('a');
        var form = suggestionsElement.closest('form');
        suggestion.href = item.url || buildSearchUrl(form, item.query || item.text);
        suggestion.setAttribute('data-search-link', 'true');
        suggestion.className = 'list-group-item list-group-item-action text-left';
        suggestion.textContent = item.text;
        suggestionsElement.appendChild(suggestion);
      });
    }

    if (data.history && data.history.length) {
      var historyHeader = document.createElement('div');
      historyHeader.className = 'list-group-item small text-uppercase text-muted border-bottom-0';
      historyHeader.textContent = 'Recent searches';
      historiesElement.appendChild(historyHeader);

      data.history.forEach(function (item) {
        var historyItem = document.createElement('a');
        var form = historiesElement.closest('form');
        historyItem.href = item.url || buildSearchUrl(form, item.query || item.text);
        historyItem.setAttribute('data-search-link', 'true');
        historyItem.className = 'list-group-item list-group-item-action text-left';
        historyItem.textContent = item.text;
        historiesElement.appendChild(historyItem);
      });
    }
  }

  function fetchResults(input, dropdown) {
    var query = input.value.trim();
    if (!dropdown) return;
    var form = input.closest('form');
    var resultsElement = dropdown.querySelector('[data-search-results]');
    var suggestionsElement = dropdown.querySelector('[data-search-suggestions]');

    if (query === '') {
      // Allow the API to return recent search history when the input is empty.
      resultsElement.innerHTML = '';
      suggestionsElement.innerHTML = '';
    }

    var params = new URLSearchParams();
    params.set('q', query);

    var apiUrl = new URL('api/search.php', form ? form.action : window.location.href);
    apiUrl.search = params.toString();

    fetch(apiUrl.toString())
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data.success) {
          hideDropdown(dropdown);
          return;
        }
        if (query !== '') {
          renderResults(resultsElement, data.results || []);
        } else {
          resultsElement.innerHTML = '';
        }
        renderSuggestions(suggestionsElement, dropdown.querySelector('[data-search-history]'), data);
        showDropdown(dropdown);
      })
      .catch(function () {
        hideDropdown(dropdown);
      });
  }

  searchInputs.forEach(function (inputElement) {
    var form = inputElement.closest('form');
    if (!form) return;
    var dropdown = form.querySelector('.search-dropdown');
    if (!dropdown) return;
    var timeoutId = null;

    dropdown.addEventListener('pointerdown', navigateSearchLink, true);
    dropdown.addEventListener('click', navigateSearchLink, true);

    inputElement.addEventListener('input', function () {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(function () {
        fetchResults(inputElement, dropdown);
      }, 250);
    });

    inputElement.addEventListener('focus', function () {
      var trimmedValue = inputElement.value.trim();
      if (trimmedValue.length >= 2 || trimmedValue.length === 0) {
        fetchResults(inputElement, dropdown);
      }
    });

    document.addEventListener('click', function (event) {
      if (!form.contains(event.target)) {
        hideDropdown(dropdown);
      }
    });
  });
});
