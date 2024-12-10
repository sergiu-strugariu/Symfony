/**
 * @param status
 * @param message
 * @returns {string} HTML string with the status and message
 */
function messageTemplate(status, message) {
  let checkStatus = status ? 'success' : 'error';

  // Return the HTML string instead of directly appending it to the DOM
  return `
    <div aria-atomic="true" role="alert" class="${checkStatus}-message">
      <div class="${checkStatus} message">
        <p>${message}</p>
      </div>
    </div>
  `;
}

function parseBackendError(fields) {
  $.each(fields, function (key, value) {
    if (key === 'default') {
      this.formMessage.html(this.messageTemplate(0, value));
      return true;
    }

    // Get field by name
    let field = '';

    if (key === 'county' || key === 'city') {
      field = $(`select[name="${key}"]`);
    } else if (key === 'message') {
      field = $(`textarea[name="${key}"]`);
    } else if (key === 'selectCategory') {
      field = $(`select[name="${key}"]`);
    } else {
      field = $(`input[name="${key}"]`);
    }

    // Check existing field in form
    if (field.length > 0) {
      // Clear any previous error messages
      field.next('.error-message').remove();

      // Set error message
      field.after(`<span class="form-text text-danger text-left">${value}</span>`);
    }
  });
}
