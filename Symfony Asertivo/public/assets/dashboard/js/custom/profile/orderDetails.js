$(document).ready(function () {

    if ($('.my-plan').length) {
        // Initialize variables
        let isEditing = false;
        const modalForm = $('#modalAddCompanyForm');
        const modal = $('#modalAddCompany');
        const billingContainer = $('.my-plan .board.has-loader');
        const radioBtnGroup = $('#radioBtnGroup');
        const regionFilter = $('#regionFilter');
        const city = $('#city');
        const planName = $('.plan-name');
        const planAddress = $('.plan-address');
        const addNewCompany = $('.add-new-company');
        const modalTitle = $('#modalTitle');
        const billingData = $('.billing-data');
        const regionFilterError = $('#regionFilter-error');
        const cityError = $('#city-error');
        const companyNameInput = $('#companyName');
        const cuiInput = $('#cui');
        const companyRegisterNumberInput = $('#companyRegisterNumber');
        const ibanInput = $('#iban');
        const emailInput = $('#email');
        const phoneInput = $('#phone');

        // Setup validation
        setupValidation();

        // regionFilter.select2({
        //     dropdownParent: modalForm
        // });
        // city.select2({
        //     dropdownParent: modalForm
        // });

        // Add event listener using change event
        $(document).on('click', '.billing-data input[type="radio"]', function (event) {
            event.preventDefault();

            // Check if the clicked input has data-favorite="1"
            if ($(this).closest('.radio-group').hasClass('checked-input')) {
                return;
            }

            const uuid = $(this).closest('.radio-group').data('index');

            if (uuid !== undefined) {
                handleSelectBillingInfo(uuid, this);
            } else {
                const uuid = $(this).data('index');
                handleSelectBillingInfo(uuid, this);
            }
        });

        $(document).on('click', '.modify-company', function (e) {
            e.preventDefault();

            const uuid = $(this).data('index'); // Capture the data-index value as uuid
            modalForm.find('.error').removeClass('error');
            modalForm.find('input[name="uuid"]').val(uuid);

            $.ajax({
                url: window.userBillingAddress,
                method: 'post',
                cache: false,
                data: {
                    uuid: uuid
                },
                beforeSend: function () {
                    billingContainer.addClass("show-loader");
                },
                success: function (response) {
                    if (response.status) {
                        populateModal(response.company);
                        setTimeout(function () {
                            modal.modal('show');
                        }, 400);
                    } else {
                        if (response.errors && Object.keys(response.errors).length > 0) {
                            parseBackendError(response.errors);
                        }
                    }
                },
                complete: function () {

                    setTimeout(function () {
                        billingContainer.removeClass("show-loader");
                    }, 500);
                },
                error: function () {
                }
            });

            modalTitle.text(`Modifică persoana juridica`);
        });

        addNewCompany.on('click', function () {
            modalTitle.text('Adauga persoana juridica');

            // Reset form
            resetForm();

            // Reset value of "uuid" input
            $('input[name="uuid"]').val(0);
        });

        // Usage example for delete click event
        $('#radioBtnGroup').on('click', '.delete-company', function (e) {
            e.preventDefault();
            const uuid = $(this).data('index');
            handleDeleteCompany(uuid);
        });

        modalForm.on('hidden.bs.modal', function () {
            clearValidationErrors();
            if (isEditing) {
                resetForm();
                isEditing = false;

                // Clear the UUID hidden field
                modalForm.find('input[name="uuid"]').val(0);

                // Reset county select field (Select2)
                regionFilter.val(null).trigger('change');

                // Reset city select field and disable it until a new county is selected
                city.empty().append('<option value="">Localitate</option>').val(null).trigger('change');
            }
        });

        function appendNewRadioButton(company) {

            const radioGroupHTML = `
                      <div class="card card-bordered shadow-sm alert alert-custom alert-default radio-group ${company.isFavorite ? 'checked-input' : ''}" data-index="${company.uuid}">
                        <div class="card-body d-flex align-items-center justify-content-between">
                          <div class="radio-content d-flex align-items-center">
                            <input type="radio" id="${company.uuid}" name="option" value="${company.companyName}" ${company.isFavorite ? 'checked' : ''} data-index="${company.uuid}" required="required" class="form-check-input me-3">
                            <label for="${company.uuid}" class="form-check-label">
                              <span class="fw-bold">${company.companyName}</span>
                              <div class="text-muted fs-7">
                                ${company.address}, ${company.countyName}, ${company.cityName}
                              </div>
                            </label>
                          </div>
                          <div class="button-wrapper d-flex">
                            <a href="#" class="modify-company btn btn-sm btn-light-primary me-2" data-index="${company.uuid}" data-toggle="modal" data-target="#modalAddCompany">
                              <i class="bi bi-pencil"></i> Modifică
                            </a>
                            <a href="#" class="delete-company btn btn-sm btn-light-danger" data-index="${company.uuid}">
                              <i class="bi bi-trash"></i> Șterge
                            </a>
                          </div>
                        </div>
                      </div>`;


            // Add to the top of the list of radio buttons
            radioBtnGroup.append(radioGroupHTML);

            if (company.isFavorite) {
                planName.text(company.companyName)
                planAddress.text(`${company.address}, ${company.countyName}, ${company.cityName}`);
            }

        }

        function handleDeleteCompany(uuid, message, icon = 'warning') {
            Swal.fire({
                text: message || 'Sigur doriți să ștergeți acest continut?',
                icon: icon,
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: 'Da',
                cancelButtonText: 'Nu',
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: window.userBillingAddressActionRemove,
                        type: 'POST',
                        cache: false,
                        data: {
                            uuid: uuid
                        },
                        beforeSend: function () {
                            billingContainer.addClass("show-loader");
                        },
                        success: function (response) {
                            KTSwal.showSwal(response.message, response.success ? 'error' : 'success')

                            if (response.status) {
                                fetchData();
                            }
                            // Logic to remove the company from DOM
                            const radioGroup = $(`#companyDetails-${uuid + 1}`).closest('.radio-group');

                            // Check if the deleted company is the currently selected one
                            if (radioGroup.find('input:checked').length > 0) {
                                // If it's the last one, deselect it
                                const isLastChecked = radioGroup.find('input:checked').attr('id') === `companyDetails-${uuid + 1}`;
                                if (isLastChecked) {
                                    // Deselect the radio button
                                    radioGroup.find('input:checked').prop('checked', false).removeAttr('checked');
                                    radioGroup.removeClass('checked-input'); // Remove checked class
                                }
                            }
                            // Remove the radio group element from DOM
                            radioGroup.remove();
                        },
                        error: function (error) {
                            KTSwal.showSwal(`A intervenit o eroare neprevăzută. Te rugăm să încerci din nou sau mai târziu`, error ? 'error' : 'success')

                        },
                        complete: function () {
                            setTimeout(function () {
                                billingContainer.removeClass("show-loader");
                            }, 500);
                        },
                    });
                }
            });
        }

        // Function to handle selecting billing information
        function handleSelectBillingInfo(uuid, radioInput) {

            const radioGroup = $(radioInput).closest('.radio-group');

            // Extract company details from the radio group
            const getCompanyName = radioGroup.find('label').contents().filter(function () {
                return this.nodeType === 3; // Only keep text nodes (nodeType 3)
            }).text().trim();
            const getCompanyDetails = radioGroup.find('label span').text().trim(); // Get address, county, city from the span

            Swal.fire({
                text: "You want to select this billing information as current data",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: 'Da',
                cancelButtonText: 'Nu',
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: window.userBillingAddressActionModerate,
                        type: 'POST',
                        cache: false,
                        data: {
                            uuid: uuid
                        },
                        beforeSend: function () {
                            billingContainer.addClass("show-loader");
                        },
                        success: function (response) {
                            KTSwal.showSwal(response.message, response.status ? 'success' : 'error')

                            // If successful, check the radio input and update the class 'checked-input'
                            const container = billingData;
                            container.find('.radio-group').removeClass('checked-input');

                            // Add the class to the closest .radio-group and check the radio input
                            const radioGroup = $(radioInput).closest('.radio-group');
                            radioGroup.addClass('checked-input');
                            $(radioInput).prop('checked', true); // Check the radio button

                            fetchData();

                            planName.text(getCompanyName);          // Update the company name
                            planAddress.text(getCompanyDetails);    // Update the company details (address, county, city)
                        },
                        error: function (error) {
                            KTSwal.showSwal(`A intervenit o eroare neprevăzută. Te rugăm să încerci din nou sau mai târziu`, error ? 'error' : 'success')

                        },
                        complete: function () {
                            setTimeout(function () {
                                billingContainer.removeClass("show-loader");
                            }, 500);
                        },
                    });
                }
            });
        }

        // Function to fetch data
        async function fetchData() {
            try {
                const response = await fetch(window.userBillingAddresses);
                const data = await response.json();

                if (data.companies) {
                    radioBtnGroup.empty();
                    planName.empty();
                    planAddress.empty();
                    data.companies.forEach((company) => {
                        appendNewRadioButton(company);
                    });
                } else {
                    console.error("Nu s-au găsit datele companiei.");
                }

            } catch (error) {
                KTSwal.showSwal(`A intervenit o eroare neprevăzută. Te rugăm să încerci din nou sau mai târziu.`, error ? 'error' : 'success')
            }
        }

        // Function to set up validation
        function setupValidation() {
            modalForm.validate({
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
                        // regNumber: true
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
                    companyName: 'Vă rugăm să introduceți numele companiei',
                    cui: {
                        required: `Vă rugăm să introduceți codul unic de înregistrare (CUI)`,
                        validCIF: `Vă rugăm să introduceți un CIF valid`,
                    },
                    companyRegisterNumber: {
                        required: `Vă rugăm să introduceți numărul de înregistrare al companiei`,
                        regNumber: `Vă rugăm să introduceți un număr de înregistrare valid`,
                    },
                    iban: `Vă rugăm să introduceți un IBAN valid`,
                    email: `Formatul adresei de email este invalid`,
                    phone: `Numărul de telefon nu este valid`,
                    county: `Vă rugăm să introduceți județul`,
                    city: `Vă rugăm să introduceți orașul`,
                    address: `Vă rugăm să introduceți adresa`,
                },

                submitHandler: function (form, event) {
                    event.preventDefault();

                    $.ajax({
                        url: window.userBillingAddressActions,
                        method: 'post',
                        cache: false,
                        data: modalForm.serialize(),
                        success: function (response) {
                            KTSwal.showSwal(response.message, response.status ? 'success' : 'error')

                            if (response.errors && Object.keys(response.errors).length > 0) {
                                parseBackendError(response.errors);
                            }

                            if (response.status) {
                                // Reset form
                                resetForm();

                                //Fetch data API
                                fetchData();

                                // Close modal
                                modal.modal('hide');

                                regionFilterError.remove();
                                cityError.remove();
                                clearValidationErrors();
                            }
                        },
                        complete: function () {
                            setTimeout(function () {
                                billingContainer.removeClass("show-loader");
                            }, 500);
                        },
                        error: function () {
                        }
                    });
                }
            });
        }

        function populateModal(company) {
            companyNameInput.val(company.companyName);
            cuiInput.val(company.cui);
            companyRegisterNumberInput.val(company.companyRegisterNumber);
            ibanInput.val(company.iban);
            emailInput.val(company.email);
            phoneInput.val(company.phone);

            regionFilterError.remove();
            regionFilter.select2('destroy');

            regionFilter.val(company.countyId).select2({
                dropdownParent: modalForm
            });
            regionFilter.trigger('change');

            cityError.remove();
            city.select2('destroy');
            setTimeout(function () {
                city.val(company.cityId).select2({
                    dropdownParent: modalForm
                });
            }, 1000);

            $('#address').val(company.address);
        }

        function resetForm() {
            companyNameInput.val('');
            cuiInput.val('');
            companyRegisterNumberInput.val('');
            ibanInput.val('');
            emailInput.val('');
            phoneInput.val('');

            regionFilter.val(null).trigger('change');

            city.val(null).trigger('change');

            $('#address').val('');
            clearValidationErrors();
        }

        // Function to clear validation errors
        function clearValidationErrors() {
            modalForm.find('span.error').remove();
        }
    }
});