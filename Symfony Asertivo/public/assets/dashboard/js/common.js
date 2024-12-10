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
            showSwal("A intervenit o eroare neprevazuta. Te rugam sa incerci din nou mai tarziu.", "error");
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

        $(".monthpicker").flatpickr({
            plugins: [
                new monthSelectPlugin({
                    shorthand: true, //defaults to false
                    dateFormat: "m-Y", //defaults to "F Y"
                    altFormat: "F Y" //defaults to "F Y"
                })
            ]
        });

        $(".timepicker").flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i"
        });
    };

    return {
        // Public functions
        init: function () {
            initDatePickers();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatePickers.init();
});