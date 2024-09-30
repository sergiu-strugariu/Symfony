$(document).ready(function () {

  if ($('.my-plan').length) {
      // Initialize variables
      const radioButtons = $('input[type="radio"]');
      const formMessageList = $('.form-message-list');
      const formMessageModal = $('.form-message-modal');
      const modal = $('#modalAddCompany');
      const wrapperModal = modal.find(".modal-body");
      let companyData = [];
      let isEditing = false;

      // Setup validation
      setupValidation();

      // Fetch initial data
      fetchData();

      // Event listeners
      $(document).on('change', '.billing-data input[type="radio"]', function() {
        const container = $('.billing-data');
        container.find('.radio-group').removeClass('checked-input');
        $(this).closest('.radio-group').addClass('checked-input');
      });

      $(document).on('click', '.modify-company', function(e) {
        e.preventDefault();
        isEditing = true;
        $('#modalAddCompanyForm').find('.error').removeClass('error');
    
        const companyIndex = $(this).data('index'); // Get the index from the clicked button
    
        $.ajax({
            url: window.companyDetails,
            method: 'get',
            cache: false,
            beforeSend: function() {
                $(this).parent('.radio-group').addClass("show-loader");
            },
            success: function(response) {
                if (response.status) {
                    const companyDetails = response.companyDetails[companyIndex]; // Get the specific company details based on the index
                    populateModal(companyDetails); // Pass only the selected company to the modal
    
                    setTimeout(function() {
                        modal.modal('show');
                    }, 400);
                } else {
                    formMessageList.html(self.messageTemplate(response.status, response.message));
    
                    if (response.errors && Object.keys(response.errors).length > 0) {
                        parseBackendError(response.errors);
                    }
                }
            },
            complete: function() {
                $(this).parent('.radio-group').removeClass("show-loader");
            },
            error: function() {
                formMessageList.html(`${window.translations.form.messages.form_details_error}`);
            }
        });
    
        $('#modalTitle').text(`${window.translations.form.common.comapny_details_modify}`);
    });

      $('.add-new-company').on('click', function() {
      $('#modalTitle').text(`${window.translations.form.common.comapny_details_add}`);
      });

      modal.on('hidden.bs.modal', function() {
      clearValidationErrors();
      if (isEditing) {
          resetForm();
          isEditing = false;

          // Reset county select field (Select2)
          $('#regionFilter').val(null).trigger('change');

          // Reset city select field and disable it until a new county is selected
          $('#city').empty().append('<option value="">Localitate</option>').val(null).trigger('change');
      }
      });

      // Function to fetch data
      async function fetchData() {
      try {
          const response = await fetch(window.companyDetails);
          const data = await response.json();
          renderRadioButtons(data.companyDetails);


      } catch (error) {
          console.error(`${window.translations.form.messages.form_details_error}`, error);
        }
      }

      // Function to set up validation
      function setupValidation() {
      $.validator.addMethod("phone_ro", function(value, element) {
          return this.optional(element) || /^(\+4|)?(07[0-8][0-9]{7})$/g.test(value);
      }, `${window.translations.form.phone.phone_ro}`);

      $.validator.addMethod("validCIF", function(value, element) {
          if (this.optional(element)) {
          return true;
          }

          if (typeof value !== 'string') {
          return false;
          }

          let cif = value.toUpperCase();
          cif = (cif.indexOf('RO') > -1) ? cif.substring(2) : cif;
          cif = cif.replace(/\s/g, '');
          if (cif.length < 2 || cif.length > 10) {
          return false;
          }
          if (Number.isNaN(parseInt(cif))) {
          return false;
          }
          const testKey = '753217532';
          const controlNumber = parseInt(cif.substring(cif.length - 1));
          cif = cif.substring(0, cif.length - 1);
          while (cif.length !== testKey.length) {
          cif = '0' + cif;
          }
          let sum = 0;
          let i = cif.length;

          while (i--) {
          sum += (parseInt(cif.charAt(i)) * parseInt(testKey.charAt(i)));
          }

          let calculatedControlNumber = sum * 10 % 11;

          if (calculatedControlNumber === 10) {
          calculatedControlNumber = 0;
          }
          return controlNumber === calculatedControlNumber;
      }, `${window.translations.form.cui.validCIF}`);

      $.validator.addMethod("regNumber", function(value, element) {
          return this.optional(element) || /^[JFCjfc][0-9]{2}\/[0-9]+\/(19|20)[0-9]{2}$/g.test(value);
      }, `${window.translations.form.companyRegisterNumber.regNumber}`);

      $.validator.addMethod("iban", function(value, element) {
          if (this.optional(element)) {
          return true;
          }

          var iban = value.replace(/ /g, "").toUpperCase(),
              ibancheckdigits = "",
              leadingZeroes = true,
              cRest = "",
              cOperator = "",
              countrycode, ibancheck, charAt, cChar, bbanpattern, bbancountrypatterns, ibanregexp, i, p;

          if (iban.length < 5) {
          return false;
          }

          countrycode = iban.substring(0, 2);
          bbancountrypatterns = {
          "RO": "[A-Z]{4}[\\dA-Z]{16}",
          };

          bbanpattern = bbancountrypatterns[countrycode];

          if (typeof bbanpattern !== "undefined") {
          ibanregexp = new RegExp("^[A-Z]{2}\\d{2}" + bbanpattern + "$", "");
          if (!ibanregexp.test(iban)) {
              return false;
          }
          }

          ibancheck = iban.substring(4) + iban.substring(0, 4);
          for (i = 0; i < ibancheck.length; i++) {
          charAt = ibancheck.charAt(i);
          if (charAt !== "0") {
              leadingZeroes = false;
          }
          if (!leadingZeroes) {
              ibancheckdigits += "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".indexOf(charAt);
          }
          }

          for (p = 0; p < ibancheckdigits.length; p++) {
          cChar = ibancheckdigits.charAt(p);
          cOperator = "" + cRest + "" + cChar;
          cRest = cOperator % 97;
          }
          return cRest === 1;
      }, `${window.translations.form.iban.required}`);

      $('#modalAddCompanyForm').validate({
          rules: {
          companyName: {
              required: true
          },
          cui: {
              required: true,
              validCIF: true
          },
          companyRegisterNumber: {
              required: true,
              regNumber: true
          },
          iban: {
              required: true,
              iban: true
          },
          email: {
              required: true,
              email: true
          },
          phone: {
              required: true,
              phone_ro: true
          },
          county: {
              required: true
          },
          city: {
              required: true
          },
          address: {
              required: true
          }
          },
          errorElement: "span",
          messages: {
          companyName: `${window.translations.form.companyName.required}`,
          cui: {
              required: `${window.translations.form.cui.required}`,
              validCIF: `${window.translations.form.cui.validCIF}`,
          },
          companyRegisterNumber: {
              required: `${window.translations.form.companyRegisterNumber.required}`,
              regNumber: `${window.translations.form.companyRegisterNumber.regNumber}`,
          },
          iban: `${window.translations.form.iban.required}`,
          email: `${window.translations.form.email.email}`,
          phone: `${window.translations.form.phone.phone_ro}`,
          county: `${window.translations.form.county.required}`,
          city: `${window.translations.form.city.required}`,
          address: `${window.translations.form.address.required}`,
          },
          submitHandler: function(form, event) {
          event.preventDefault();
          formMessageModal.html('');

          $.ajax({
              url: window.companyPaymentDetails,
              method: 'post',
              cache: false,
              data: $('#modalAddCompanyForm').serialize(),
              beforeSend: function() {
              wrapperModal.addClass("show-loader");
              },
              success: function(response) {
              wrapperModal.html(formMessageHelper.messageTemplate(response.status, response.message, formMessageModal));

              if (response.errors && Object.keys(response.errors).length > 0) {
                  parseBackendError(response.errors);
              }

              if (response.status) {
                  resetForm();
                  formMessageModal.html('');
                  fetchData();
                  modal.modal('hide');
              } else {
                  formMessageModal.html(`${window.translations.form.messages.form_details_error}`);
              }
              },
              complete: function() {
              wrapperModal.removeClass("show-loader");
              },
              error: function() {
              formMessageModal.html(`${window.translations.form.messages.form_details_error}`);
              }
          });
          }
      });
      }

      // Function to render radio buttons
      function renderRadioButtons(data) {
        const radioBtnGroup = $('#radioBtnGroup');
        radioBtnGroup.empty(); // Clear existing content
        data.forEach((company, index) => {
          const checkedAttribute = index === 0 ? 'checked' : ''; // Add checked attribute to the first radio button
          const radioGroupHTML = `
            <div class="radio-group has-loader ${checkedAttribute ? 'checked-input' : ''}">
              <div class="radio-content">
                <input type="radio" id="companyDetails-${index + 1}" name="option" value="${company.companyName}" data-index="${index}" ${checkedAttribute} required="required">
                <label for="companyDetails-${index + 1}">
                  ${company.companyName}
                  <span>${company.address}, ${company.city}, ${company.county}</span>
                </label>
              </div>
              <div class="button-wrapper">
                <a href="#" class="modify-company" data-index="${index}" data-toggle="modal" data-target="#modalAddCompany">${window.translations.dashboard.common.modify}</a>
                <a href="#" class="delete-company" data-index="${index}">${window.translations.dashboard.common.delete}</a>
              </div>
            </div>
          `;
          radioBtnGroup.append(radioGroupHTML);
        });
        
        // Usage example for delete click event
        $('#radioBtnGroup').on('click', '.delete-company', function (e) {
            e.preventDefault();
            const companyIndex = $(this).data('index');
            handleDeleteCompany(companyIndex);
        });
      }

      // Function to populate the modal with company details
      function populateModal(companyDetails) {
      // Populate the modal fields with company details
      $('#companyName').val(companyDetails.companyName);
      $('#cui').val(companyDetails.cui);
      $('#companyRegisterNumber').val(companyDetails.companyRegisterNumber);
      $('#iban').val(companyDetails.iban);
      $('#email').val(companyDetails.email);
      $('#phone').val(companyDetails.phone);
      $('#regionFilter').val(companyDetails.county).trigger('change');
      $('#city').val(companyDetails.city).trigger('change');
      $('#address').val(companyDetails.address); 

      // $('#regionFilter').select2('destroy');
      $('#regionFilter').val(companyDetails.county).select2({
          dropdownParent: $('#modalAddCompanyForm')
      });

      
      // $('#city').select2('destroy');
    
      setTimeout(function (){
        $('#city').val(companyDetails.city).select2({
            dropdownParent: $('#modalAddCompanyForm')
        });
        }, 400);
      }

      // Function to reset the form
      function resetForm() {
      $('#modalAddCompanyForm')[0].reset();
      $('#modalAddCompanyForm').find('.error').removeClass('error');
      $('.form-message-list').html('');
      }

      // Function to clear validation errors
      function clearValidationErrors() {
        $('#modalAddCompanyForm').find('span.error').remove();
      }

      // Function to parse backend error messages
      function parseBackendError(errors) {
        for (const [key, value] of Object.entries(errors)) {
            $(`#${key}`).addClass('error');
            $(`#${key}-error`).text(value);
        }
      }

     /**
     * @param status
     * @param message
     */
    function messageTemplate(status, message, container) {
        let checkStatus = status ? 'success' : 'error';
        container.addClass(checkStatus);
        container.append(`
            <div aria-atomic="true" role="alert" class="${checkStatus}-message">
              <div class="${checkStatus} message">
                <p>${message}</p>
              </div>
            </div>
        `);
    }

    function parseBackendError(fields) {
        $.each(fields, function (key, value) {
            if (key === 'default') {
                this.formMessage.html(this.messageTemplate(0, value));
                return true;
            }
    
            // Get field by name
            let field = $(`${key === 'message' ? 'textarea' : 'input'}[name="${key}"]`);
    
            // Check existing field in form
            if (field.length > 0) {
                // Clear any previous error messages
                field.next('.error-message').remove();
    
                // Set error message
                field.after(`<span class="form-text text-danger text-left">${value}</span>`);
            }
        });
      }
  
    function resetRecaptcha() {
      setTimeout(function () {
        grecaptcha.reset();
      }, 500);
    };
      
  }
  
});
