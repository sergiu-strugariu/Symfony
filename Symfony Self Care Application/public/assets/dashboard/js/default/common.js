if (document.querySelector('.datepicker-inputs')) {
    /**
     * Config FlatPicker
     * @param startDateFlatpickr
     * @param endDateFlatpickr
     */
    function configFlatpickr(startDateFlatpickr, endDateFlatpickr) {
        startDateFlatpickr.set('onClose', function (selectedDates) {
            if (selectedDates[0] !== undefined) {
                endDateFlatpickr.set('minDate', selectedDates[0]);
            }
        });

        endDateFlatpickr.set('onClose', function (selectedDates) {
            if (selectedDates[0] !== undefined) {
                startDateFlatpickr.set('maxDate', selectedDates[0]);
            }
        });
    }


    /**
     * @param startDateInput
     * @param endDateInput
     * @param startDate
     * @param endDate
     */
    function initAndFilterFlatpickr(startDateInput, endDateInput, startDate, endDate) {
        let lastMonth = new Date();
        lastMonth.setMonth(lastMonth.getMonth() - 1);

        const startDateFlatpickr = flatpickr(startDateInput, {
            dateFormat: 'Y-m-d H:i',
            defaultDate: startDate.length > 0 ? new Date(startDate) : lastMonth,
            enableTime: true
        });

        const endDateFlatpickr = flatpickr(endDateInput, {
            dateFormat: 'Y-m-d H:i',
            defaultDate: endDate.length > 0 ? new Date(endDate) : 'today',
            enableTime: true
        });

        // Initialization
        configFlatpickr(startDateFlatpickr, endDateFlatpickr);
    }
}

if (document.querySelector('.flatpickr-init')) {
    /**
     * @param date
     * @param input
     */
    function flatpickrInit(date = '', input = '.flatpickr-init') {
        $(input).flatpickr({
            minDate: "today",
            dateFormat: "d.m.Y",
            defaultDate: date
        });
    }
}
if (document.querySelector('.tinymce-editor')) {
    /**
     * Init tinymce
     * @param inputId
     */
    function initEditor(inputId) {
        tinymce.init({
            selector: inputId,
            height: "500",
            plugins: 'advlist autolink lists preview code',
            toolbar_mode: 'floating',
            convert_urls: false,
            branding: false,
            valid_elements: '*[*]',
            extended_valid_elements: 'span[*]',
            forced_root_block: '',
            force_br_newlines: true,
            force_p_newlines: false,
        });
    }
}

if (document.querySelector('.tagify-input')) {
    function initTagify(input, data = [], maxTags = 20, maxItems = 20, className = '', enabled = 0, closeOnSelect = false) {
        new Tagify(input, {
            whitelist: data,
            maxTags: maxTags,
            delimiters: "\n",
            dropdown: {
                maxItems: maxItems,
                classname: className,
                enabled: 0,
                closeOnSelect: false
            }
        });
    }
}


if (document.querySelector('.counties') && document.querySelector('.cities')) {
    let county = $('.counties');
    let city = $('.cities');

    county.on('change', function () {
        $.ajax({
            method: "GET",
            url: window.countyCitiesPath,
            data: {'id': $(this).val()},
            cache: false,
            success: function (response) {
                city.empty();

                let optionTpl = `<option value="{{id}}">{{name}}</option>`;
                if (response.status) {
                    response.cities.forEach(function (item) {
                        let newTpl = optionTpl.replace('{{name}}', item.name)
                            .replace('{{id}}', item.id)
                            .replace('{{name}}', item.name);
                        $(newTpl).appendTo(city);
                    });
                }
            }
        });
    });
}

/**
 * @param icon
 * @param message
 * @param showConfirmButton
 * @param showCancelButton
 * @param showCloseButton
 */
function showSwalFire(icon = 'success', message, showConfirmButton = false, showCancelButton = false, showCloseButton = true) {
    Swal.fire({
        text: message,
        icon: icon,
        showConfirmButton: showConfirmButton,
        showCancelButton: showCancelButton,
        showCloseButton: showCloseButton
    });
}

if (document.querySelector('.only-one-category')) {
    let $select = $('.only-one-category');

    $select.on('select2:select', function (e) {
        // Get the selected element
        let selectedElement = e.params.data;

        // Clear all selections
        $select.val(null).trigger('change');

        // Select the new element
        $select.val(selectedElement.id).trigger('change');
    });
}

// Class for swal
let KTSwal = function () {
    // show Swal
    let showSwal = function (text, icon) {
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
