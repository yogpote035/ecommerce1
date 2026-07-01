/* Reusable JS components and helpers */
const AppComponents = {
  toggleClass(el, className) {
    if (!el) return;
    el.classList.toggle(className);
  },

  initDropdowns() {
    document.querySelectorAll('[data-dropdown-toggle]').forEach(button => {
      const target = document.querySelector(button.dataset.dropdownToggle);
      if (!target) return;
      button.addEventListener('click', () => {
        target.classList.toggle('show');
      });
    });
  },

  initSearchDropdown() {
    // Search dropdown behavior is owned by public/assets/js/search.js.
  },

  initFormValidation() {
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', event => {
        var firstInvalid = null;
        form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback.dynamic-feedback').forEach(message => message.remove());

        form.querySelectorAll('input, select, textarea').forEach(field => {
          if (field.disabled || field.type === 'hidden' || field.type === 'button' || field.type === 'submit') {
            return;
          }

          var invalidMessage = '';
          var value = (field.value || '').trim();

          if (field.hasAttribute('required') && value === '' && field.type !== 'file') {
            invalidMessage = 'This field is required.';
          } else if (field.hasAttribute('required') && field.type === 'file' && (!field.files || field.files.length === 0)) {
            invalidMessage = 'Please select at least one file.';
          } else if (field.type === 'email' && value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            invalidMessage = 'Enter a valid email address.';
          } else if (field.type === 'number' && value !== '') {
            var numberValue = Number(value);
            var min = field.getAttribute('min');
            var max = field.getAttribute('max');
            if (Number.isNaN(numberValue)) {
              invalidMessage = 'Enter a valid number.';
            } else if (min !== null && numberValue < Number(min)) {
              invalidMessage = 'Value must be at least ' + min + '.';
            } else if (max !== null && numberValue > Number(max)) {
              invalidMessage = 'Value must be at most ' + max + '.';
            }
          } else if (field.type === 'file' && field.files && field.files.length) {
            Array.from(field.files).some(file => {
              if (field.accept && field.accept.indexOf('image/') !== -1 && !file.type.startsWith('image/')) {
                invalidMessage = 'Only image files are allowed.';
                return true;
              }
              if (file.size > 5 * 1024 * 1024) {
                invalidMessage = 'Each file must be 5 MB or smaller.';
                return true;
              }
              return false;
            });
          }

          if (invalidMessage) {
            field.classList.add('is-invalid');
            var feedback = document.createElement('div');
            feedback.className = 'invalid-feedback dynamic-feedback';
            feedback.textContent = invalidMessage;
            field.insertAdjacentElement('afterend', feedback);
            if (!firstInvalid) {
              firstInvalid = field;
            }
          }
        });

        if (firstInvalid) {
          event.preventDefault();
          firstInvalid.focus({ preventScroll: true });
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    });
  }
};

document.addEventListener('DOMContentLoaded', () => {
  AppComponents.initDropdowns();
  AppComponents.initSearchDropdown();
  AppComponents.initFormValidation();
});
