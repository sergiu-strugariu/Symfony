"use strict";

// Class for swal
var KTSwal = function () {
    // show Swal
    var showSwal = function (text, icon) {
        Swal.fire({
            html: text,
            icon: icon,
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    };

    return {
        // Public functions
        showSwal: function (text, icon) {
            showSwal(text, icon);
        },
        showDefaultErrorSwal: function () {
            showSwal("An unexpected error occurred. Please try again later.", "error");
        }
    };
}();

var KTDatePickers = function () {
    // Init date picker
    var initDatePickers = () => {
        $(".datepicker").flatpickr();
        $(".datetimepicker").flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d H:i"
        });
    };

    return {
        // Public functions
        init: function () {
            initDatePickers();
        }
    };
}();

var KTEditors = function () {
    // Init date picker
    var initEditors = () => {
        if (document.querySelector('.tinymce')) {
            tinymce.init({
                selector: '.tinymce',
                plugins: 'advlist autolink lists preview code image',
                extended_valid_elements: 'span',
                toolbar_mode: 'floating',
                height: 300,
                branding: false,
                toolbar: 'image',
                images_upload_url: '/upload',
                automatic_uploads: true,
                file_picker_types: 'image',
                images_upload_handler: function (blobInfo, success, failure) {
                    var xhr, formData;

                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '/dashboard/ajax/upload-image');

                    xhr.onload = function() {
                        var json;

                        if (xhr.status != 200) {
                            failure('HTTP Error: ' + xhr.status);
                            return;
                        }

                        json = JSON.parse(xhr.responseText);

                        success(json.path);
                    };

                    formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    xhr.send(formData);
                }
            });
        }
    };

    return {
        init: function () {
            initEditors();
        }
    };
}();


var KTRepeater = function () {
    var initRepeater = () => {
        $('.repeater').repeater({
            initEmpty: true,
            defaultValues: {},
            show: function () {
                tinymce.init({
                    selector: '.editor',
                    plugins: 'advlist autolink lists preview code',
                    extended_valid_elements: 'span',
                    toolbar_mode: 'floating',
                    height: 300,
                    branding: false
                });

                KTDatePickers.init();
                $(this).slideDown();
            },

            hide: function (deleteElement) {
                $(this).slideUp(deleteElement);
            },
            isFirstItemUndeletable: false
        });
    };

    return {
        init: function () {
            initRepeater();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatePickers.init();
    KTEditors.init();
});
