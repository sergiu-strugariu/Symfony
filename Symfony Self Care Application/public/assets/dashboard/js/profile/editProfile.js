$(document).ready(function () {
    if ($('.edit-profile').length) {
        /**
         * Declare global variables
         */
        let formMessage = $('.form-message');
        let modalChangeEmail = $('#modalChangeEmail');
        let modalChangePassword = $('#modalChangePassword');
        let modalChangeName = $('#modalChangeName');
        let modalChangeSurname = $('#modalChangeSurname');
        let modalChangePhoneNumber = $('#modalChangePhoneNumber');
        let messageTextarea = $('#shortMessage');
        let otherReasonRadio = $('#otherReason');

        function resetModalValues(modal, inputs) {
            inputs.forEach(input => $(input).val(''));
            modal.find('form').validate().resetForm();
            formMessage.html('');
        }

        async function handleEvents() {
            // Email Change Modal Close Event
            modalChangeEmail.find('.modal-close').on('click', function () {
                resetModalValues(modalChangeEmail, ['#emailAddress']);
            });
            modalChangeEmail.on('hide.bs.modal', function () {
                resetModalValues(modalChangeEmail, ['#emailAddress']);
            });

            // Password Change Modal Close Event
            modalChangePassword.find('.modal-close').on('click', function () {
                resetModalValues(modalChangePassword, ['#currentPassword', '#password', '#repeatPassword']);
                $('img[alt="visible-pass"]').removeClass('d-block').addClass('d-none');
                $('img[alt="hidden-pass"]').removeClass('d-none').addClass('d-block');
            });
            modalChangePassword.on('hide.bs.modal', function () {
                resetModalValues(modalChangePassword, ['#currentPassword', '#password', '#repeatPassword']);
            });

            // Name Change Modal Close Event
            modalChangeName.find('.modal-close').on('click', function () {
                resetModalValues(modalChangeName, ['#name']);
            });
            modalChangeName.on('hide.bs.modal', function () {
                resetModalValues(modalChangeName, ['#name']);
            });

            // Surname Change Modal Close Event
            modalChangeSurname.find('.modal-close').on('click', function () {
                resetModalValues(modalChangeSurname, ['#surname']);
            });
            modalChangeSurname.on('hide.bs.modal', function () {
                resetModalValues(modalChangeSurname, ['#surname']);
            });

            // Phone Change Modal Close Event
            modalChangePhoneNumber.find('.modal-close').on('click', function () {
                resetModalValues(modalChangePhoneNumber, ['#phone']);
            });
            modalChangePhoneNumber.on('hide.bs.modal', function () {
                resetModalValues(modalChangePhoneNumber, ['#phone']);
            });

            // Delete Change Modal Close Event
            $('#modalChangeDelete').find('.modal-close').on('click', function () {
                $('#shortMessage').val('');
            });
            $('#modalChangeDelete').on('hide.bs.modal', function () {
                $('#shortMessage').val('');
            });

            // Handle option change for shortMessage requirement
            $('input[name="option"]').on('change', function () {
                if (otherReasonRadio.is(':checked')) {
                    $('#modalChangeDeleteForm .row').show();
                    messageTextarea.attr('required', true);
                } else {
                    $('#modalChangeDeleteForm .row').hide();
                    messageTextarea.removeAttr('required');
                }
            });

            // Custom validation methods
            $.validator.addMethod("passwordMatch", function (value) {
                return value === $('#password').val();
            }, 'window.translation.form.password.passwordMatch');

            $.validator.addMethod("onlyLetters", function (value) {
                return /^[a-zA-ZțȚăĂîÎșȘâÂ-\s]+$/g.test(value);
            }, "");

            $.validator.addMethod("nameMatch", function (value) {
                return value !== $('#currentName').val();
            }, "Numele nou trebuie să fie diferit de cel actual.");

            $.validator.addMethod("surnameMatch", function (value) {
                return value !== $('#currentSurname').val();
            }, "Prenumele nou trebuie să fie diferit de cel actual.");

            $.validator.addMethod("phoneMatch", function (value) {
                return value !== $('#currentPhone').val();
            }, 'window.translation.form.phone.phone_match');

            $.validator.addMethod("phoneRO", function (value) {
                return /^(\+4|)?(07[0-8][0-9]{7})$/g.test(value);
            }, 'window.translation.form.phone.phone_ro');

            $.validator.addMethod("messageLength", function (value, element) {
                if ($('#otherReason').is(':checked')) {
                    return value.length >= 100 && value.length <= 250;
                }
                return true;
            }, 'Mesajul trebuie să aibă între 100 și 250 de caractere.');

            function handleAjax(form, url, beforeSendCallback, successCallback, errorCallback) {
                form.validate().form();
                if (!form.valid()) return;
                formMessage.html('');
                form.find('.text-danger').remove();

                let formData = {};
                // Different form data for different forms
                if (form.is('#modalChangeDeleteForm')) {
                    if (otherReasonRadio.is(':checked')) {
                        formData.shortMessage = messageTextarea.val();
                    } else {
                        formData.option = $('input[name="option"]:checked').val();
                    }
                } else {
                    formData = form.serialize();
                }

                $.ajax({
                    url: url,
                    method: 'post',
                    cache: false,
                    data: formData,
                    beforeSend: beforeSendCallback,
                    success: function (response) {
                        formMessage.html(messageTemplate(response.status, response.message));
                        if (response.errors && Object.keys(response.errors).length > 0) {
                            parseBackendError(response.errors);
                        }
                        if (response.status && successCallback) {
                            successCallback();
                        }
                    },
                    complete: function () {
                        if (errorCallback) errorCallback();
                    },
                    error: function () {
                        formMessage.html('${window.translation.form.messages.form_details_error}');
                    }
                });
            }

            function messageTemplate(status, message) {
                let checkStatus = status ? 'success' : 'error';
                formMessage.addClass(checkStatus);
                formMessage.append(`
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
                        formMessage.html(messageTemplate(0, value));
                        return true;
                    }
                    let field = $(`${key === 'message' || key === 'shortMessage' ? 'textarea' : 'input'}[name="${key}"]`);
                    if (field.length > 0) {
                        field.next('.error-message').remove();
                        field.after(`<span class="form-text text-danger text-left">${value}</span>`);
                    }
                });
            }

            $('#modalChangeEmail form').validate({
                rules: {
                    emailAddress: {
                        required: true,
                        email: true
                    }
                },
                messages: {
                    emailAddress: {
                        required: window.translations.form.email.required,
                        email:  window.translations.form.email.email
                    }
                },
                errorElement: "span",
                submitHandler: function (form, event) {
                    event.preventDefault();
                    handleAjax($(form), window.changeEmailPath,
                        function () {
                            $('#modalChangeEmail .modal-body').addClass("show-loader");
                        },
                        function () {
                            $('#emailAddress').val("");
                            window.location.href = window.logout;
                        },
                        function () {
                            $('#modalChangeEmail .modal-body').removeClass("show-loader");
                        });
                }
            });

            $('#modalChangePassword form').validate({
                rules: {
                    currentPassword: {
                        required: true,
                        minlength: 8,
                    },
                    password: {
                        required: true,
                        minlength: 8,
                    },
                    repeatPassword: {
                        required: true,
                        passwordMatch: true
                    }
                },
                messages: {
                    currentPassword: {
                        required: window.translations.form.password.required,
                        minlength: window.translations.form.password.minlength,
                    },
                    password: {
                        required: window.translations.form.password.required,
                        minlength: window.translations.form.password.minlength,
                    },
                    repeatPassword: {
                        required: window.translations.form.password.required,
                        passwordMatch: window.translations.form.password.passwordMatch,
                    }
                },
                errorElement: "span",
                highlight: function (element) {
                    $(element).parents(".row").addClass("error");
                },
                unhighlight: function (element) {
                    $(element).parents(".row").removeClass("error");
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    handleAjax($(form), window.changePasswordPath,
                        function () {
                            $('#modalChangePassword .modal-body').addClass("show-loader");
                        },
                        function () {
                            $('#currentPassword').val('').attr('type', 'password');
                            $('#password').val('').attr('type', 'password');
                            $('#repeatPassword').val('').attr('type', 'password');
                        },
                        function () {
                            $('#modalChangePassword .modal-body').removeClass("show-loader");
                        });
                }
            });

            $('#modalChangeName form').validate({
                rules: {
                    currentName: {
                        required: true,
                        minlength: 2,
                        onlyLetters: true
                    },
                    name: {
                        required: true,
                        minlength: 2,
                        onlyLetters: true,
                        nameMatch: true
                    }
                },
                messages: {
                    currentName: {
                        required: window.translations.form.name.required,
                        minlength: window.translations.form.name.minlength,
                        onlyLetters: window.translations.form.name.valid_name,
                    },
                    name: {
                        required: window.translations.form.name.required,
                        minlength: window.translations.form.name.minlength,
                        onlyLetters: window.translations.form.name.valid_name,
                        name_match: window.translations.form.name.name_match,
                    }
                },
                errorElement: "span",
                submitHandler: function (form, event) {
                    event.preventDefault();
                    handleAjax($(form), window.changeNamePath,
                        function () {
                            $('#modalChangeName .modal-body').addClass("show-loader");
                        },
                        function () {
                            var newName = $('#name').val();

                            if (newName) {
                                console.log($('#currentName'))
                                $('.current-user-name').text(newName);
                                $('#currentName').val(newName);
                            }
                            $('#name').val('');
                        },
                        function () {
                            $('#modalChangeName .modal-body').removeClass("show-loader");
                        });
                }
            });

            $('#modalChangeSurname form').validate({
                rules: {
                    currentSurname: {
                        required: true,
                        minlength: 2,
                        onlyLetters: true
                    },
                    surname: {
                        required: true,
                        minlength: 2,
                        onlyLetters: true,
                        surnameMatch: true
                    }
                },
                messages: {
                    currentSurname: {
                        required: window.translations.form.surname.required,
                        minlength: window.translations.form.surname.minlength,
                        onlyLetters: window.translations.form.surname.valid_name,
                    },
                    surname: {
                        required: window.translations.form.surname.required,
                        minlength: window.translations.form.surname.minlength,
                        onlyLetters: window.translations.form.surname.valid_name,
                        surnameMatch: window.translations.form.surname.surname_match,
                    }
                },
                errorElement: "span",
                submitHandler: function (form, event) {
                    event.preventDefault();
                    handleAjax($(form), window.changeSurnamePath,
                        function () {
                            $('#modalChangeSurname .modal-body').addClass("show-loader");
                        },
                        function () {
                            var newSurname = $('#surname').val();
                            
                            if (newSurname) {
                                $('.current-user-surname').text(newSurname);
                                
                                $('#currentSurname').val(newSurname);
                            } else {
                                console.warn('The new surname is empty.');
                            }
                            
                            $('#surname').val('');
                        },
                        function () {
                            $('#modalChangeSurname .modal-body').removeClass("show-loader");
                        });
                }
            });

            $('#modalChangePhoneNumber form').validate({
                rules: {
                    currentPhone: {
                        required: true,
                        phoneRO: true,
                    },
                    phone: {
                        required: true,
                        phoneRO: true,
                        phoneMatch: true,
                    }
                },
                messages: {
                    currentPhone: {
                        required: window.translations.form.phone.required,
                        phoneRO: window.translations.form.phone.phone_ro,
                    },
                    phone: {
                        required: window.translations.form.phone.required,
                        phoneRO: window.translations.form.phone.phone_ro,
                        phoneMatch: window.translations.form.phone.phone_match,
                    }
                },
                errorElement: "span",
                highlight: function (element) {
                    $(element).parents(".board").addClass("error");
                },
                unhighlight: function (element) {
                    $(element).parents(".board").removeClass("error");
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    handleAjax($(form), window.changePhonePath,
                        function () {
                            $('#modalChangePhoneNumber .modal-body').addClass("show-loader");
                        },
                        function () {
                            var newPhone = $('#phone').val();
                            
                            if (newPhone) {
                                $('#currentUserPhone').text(newPhone);
                                $('#currentPhone').val(newPhone);
                            } else {
                                console.warn('The new phone number is empty.');
                            }
                
                            $('#phone').val('');
                        },
                        function () {
                            $('#modalChangePhoneNumber .modal-body').removeClass("show-loader");
                        });
                }
            });

            $('#modalChangeDelete form').validate({
                rules: {
                    option: {
                        required: true
                    },
                    shortMessage: {
                        messageLength: true
                    }
                },
                shortMessage: { 
                    option: {
                        required: 'Te rugăm să selectezi un motiv pentru ștergerea contului.'
                    },
                    shortMessage: {
                        messageLength: 'Mesajul trebuie să aibă între 100 și 250 de caractere.'
                    }
                },
                errorElement: "span",
                highlight: function (element) {
                    $(element).parents(".radio-group, .row").addClass("error");
                },
                unhighlight: function (element) {
                    $(element).parents(".radio-group, .row").removeClass("error");
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    handleAjax($(form), window.changeDeletePath,
                        function () {
                            $('#modalChangeDelete .modal-body').addClass("show-loader");
                                window.location.href = window.logout;
                             setTimeout(function () {
                            $('#modalChangeDelete .modal-body').removeClass("show-loader");
                              }, 1500);
                        },
                        );
                }
            });

            // $('input[name="option"]').trigger('change');

            // Add event listeners for toggling password visibility
            $('.modal .board img').on('click', function () {
                let input = $(this).siblings('input');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).attr('src', "/assets/dashboard/media/dashboard/visible-pass.svg");
                    $(this).attr('alt', 'visible-pass');
                } else {
                    input.attr('type', 'password');
                    $(this).attr('src', "/assets/dashboard/media/dashboard/hidden-pass.svg");
                    $(this).attr('alt', 'hidden-pass');
                }
            });
        }

        handleEvents();
    }
});