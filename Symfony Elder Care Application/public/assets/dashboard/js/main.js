"use strict";

// Datatable for users
var KTDatatablesUsers = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function (role) {
        dt = $("#kt_datatable_users").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[6, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/users",
                data: {'role': role}
            },
            columns: [
                {data: 'photo'},
                {data: 'name'},
                {data: 'jobName'},
                {data: 'email'},
                {data: 'phoneNumber'},
                {data: 'cnp'},
                {data: 'nursingHome'},
                {data: 'dateOfBirth'},
                {data: 'createdAt'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        var photoTemplate = `<img src="${cloudflareR2FilePath}/assets/media/avatars/blank.png" alt="" class="w-100">`;
                        if (data) {
                            photoTemplate = `<img src="${cloudflareR2FilePath}/uploads/${data}" alt="" class="w-100">`;
                        }
                        return `
                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-1">
                                <div class="symbol-label">
                                    ${photoTemplate}
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                Actiuni
                                <span class="svg-icon svg-icon-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-250px py-2" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/user/${data}/view" class="menu-link px-3" data-kt-docs-table-filter="edit_row">
                                        Editare
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-kt-docs-table-filter="daily_monitory_medical_row">
                                        Trimitere email cu datele de acces
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-user-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function (role) {
            initDatatable(role);
            handleSearchDatatable();
        }
    };
}();

// Datatable for pacients
var KTDatatablesPacients = function () {
    // Shared variables
    var table;
    var dt;
    var statuses = {
        'internat': {
            'label': 'Internat',
            'badge': 'badge-light-success'
        },
        'active': {
            'label': '-',
            'badge': 'badge-light-warning'
        },
        'externat': {
            'label': 'Externat',
            'badge': 'badge-light-danger'
        },
        'in procesare': {
            'label': 'In procesare',
            'badge': 'badge-light-warning'
        },
        'arhivat': {
            'label': 'Arhivat',
            'badge': 'badge-light-primary'
        }
    };

    // Private functions
    var initDatatable = function () {
        dt = $("#kt_datatable_pacients").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[2, 'DESC']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacients"
            },
            columns: [
                {data: 'photo'},
                {data: 'name'},
                {data: 'admissionDate'},
                {data: 'dischargeDate'},
                {data: 'cnp'},
                {data: 'uid'},
                {data: 'status'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        var photoTemplate = `<img src="${cloudflareR2FilePath}/assets/media/avatars/blank.png" alt="" class="w-100">`;
                        if (data) {
                            photoTemplate = `<img src="${cloudflareR2FilePath}/uploads/${data}" alt="" class="w-100">`;
                        }
                        return `
                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-1">
                                <div class="symbol-label">
                                    ${photoTemplate}
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    targets: 1,
                    className: 'text-left',
                    render: function (data, type, row) {
                        var rolesAllowed = ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC', 'ROLE_ASSISTANCE_MEDICAL'];
                        var template = `<div><a href="/dashboard/pacient/${row.uid}/overview" class="fw-bold">${row.name}</a></div>`;
                        if (rolesAllowed.includes(row.role)) {
                            if (row.cnp) {
                                template += `<div class="fs-7">${row.cnp}</div>`;
                            }
                        }
                        if (!row.roomNumber) {
                            template += `<div><a href="#" class="fs-7" data-bs-toggle="modal" data-bs-target="#kt_modal_assign_pacient_room" data-pacient-uuid="${row.uid}">Asociaza camera</a></div>`;
                        } else {
                            template += `<div><a href="/dashboard/pacient/${row.uid}/room"><span class="badge badge-light-success">${row.roomNumber}</span></a></div>`;
                        }
                        return template;
                    }
                },
                {
                    targets: 4,
                    className: 'text-left',
                    render: function (data, type, row) {
                        var template = '-';
                        if (!data) {
                            template = `<a href="/dashboard/pacient/${row.uid}/personal-data"><span class="badge badge-light-danger">lipsa cnp</span></a>`;
                        }
                        return template;
                    }
                },
                {
                    targets: 5,
                    className: 'text-left',
                    render: function (data, type, row) {
                        var template = '';
                        var rolesAllowed = ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC'];
                        if (rolesAllowed.includes(row.role)) {
                            template = `<a href="/dashboard/pacient/${row.uid}/digital-record"><span class="badge badge-light-success">${row.uploadedFilesCount} doc</span>&nbsp;&nbsp;<span class="badge badge-light-warning">${row.waitingFilesCount} doc</span></a>`;
                        }
                        return template;
                    }
                },
                {
                    targets: 6,
                    className: 'text-left',
                    render: function (data, type, row) {
                        var template = `<span class="badge ${statuses[data]['badge']}">${statuses[data]['label']}</span>`;
                        return template;
                    }
                },
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        var rolesAllowed = ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_MANAGEMENT', 'ROLE_MEDIC'];
                        if (rolesAllowed.includes(row.role)) {
                            var template = `
                                <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                    Actiuni
                                    <span class="svg-icon svg-icon-5 m-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                                <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                            </g>
                                        </svg>
                                    </span>
                                </a>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-250px py-2" data-kt-menu="true">
                                    {{overviewMenuItem}}
                                    {{dailyMonitoringMedicalMenuItem}}
                                    {{dailyMonitoringCareMenuItem}}
                                    {{personalDataMenuItem}}
                                    {{admissionWizardMenuItem}}
                                    {{deletePacientMenuItem}}
                                </div>
                                <!--end::Menu-->
                            `;

                            var overviewMenuItemTemplate = `<!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="/dashboard/pacient/${row.uid}/overview" class="menu-link px-3">
                                            Editare
                                        </a>
                                    </div>
                                    <!--end::Menu item-->`;
                            var dailyMonitoringMedicalMenuItemTemplate = `<!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="/dashboard/pacient/${row.uid}/daily-monitoring/medical" class="menu-link px-3">
                                            Monitorizare zilnica medicala
                                        </a>
                                    </div>
                                    <!--end::Menu item-->`;
                            var dailyMonitoringCareMenuItemTemplate = `<!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="/dashboard/pacient/${row.uid}/daily-monitoring/care" class="menu-link px-3">
                                            Monitorizare zilnica ingrijire
                                        </a>
                                    </div>
                                    <!--end::Menu item-->`;
                            var personalDataMenuItemTemplate = `<!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="/dashboard/pacient/${row.uid}/personal-data" class="menu-link px-3">
                                            Completeaza CNP
                                        </a>
                                    </div>
                                    <!--end::Menu item-->`;
                            var admissionWizardMenuItemTemplate = `<!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="/dashboard/pacients/admission/wizard/1?cnp=${row.cnp}" class="menu-link px-3">
                                            Interneaza
                                        </a>
                                    </div>
                                    <!--end::Menu item-->`;
                            var deletePacientMenuItemTemplate = `<!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" data-kt-table-action="delete" data-id="${row.uid}">
                                            Sterge
                                        </a>
                                    </div>
                                    <!--end::Menu item-->`;


                            if (row.status == 'in procesare') {
                                template = template.replace('{{overviewMenuItem}}', '')
                                    .replace('{{dailyMonitoringMedicalMenuItem}}', '')
                                    .replace('{{dailyMonitoringCareMenuItem}}', '');

                                if (row.cnp) {
                                    template = template.replace('{{admissionWizardMenuItem}}', admissionWizardMenuItemTemplate)
                                        .replace('{{personalDataMenuItem}}', '');
                                } else {
                                    template = template.replace('{{admissionWizardMenuItem}}', '')
                                        .replace('{{personalDataMenuItem}}', personalDataMenuItemTemplate);
                                }

                            } else {
                                template = template.replace('{{overviewMenuItem}}', overviewMenuItemTemplate)
                                    .replace('{{dailyMonitoringMedicalMenuItem}}', dailyMonitoringMedicalMenuItemTemplate)
                                    .replace('{{dailyMonitoringCareMenuItem}}', dailyMonitoringCareMenuItemTemplate)
                                    .replace('{{admissionWizardMenuItem}}', '')
                                    .replace('{{personalDataMenuItem}}', '');
                            }

                            if (row.role == 'ROLE_ADMIN' || row.role == 'ROLE_MANAGEMENT') {
                                template = template.replace('{{deletePacientMenuItem}}', deletePacientMenuItemTemplate);
                            } else {
                                template = template.replace('{{deletePacientMenuItem}}', '');
                            }

                            return template;
                        }

                        return '';
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacients-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Filter Datatable
    var handleFilterDatatable = function () {
        // Select filter options
        const filterButtons = document.querySelectorAll('[data-kt-pacients-table-filter="filter"]');

        // Filter datatable on click
        for (var i = 0; i < filterButtons.length; i++) {
            filterButtons[i].addEventListener('click', function (e) {
                e.preventDefault();
                let filterValue = this.getAttribute("data-filter");

                // Filter datatable --- official docs reference: https://datatables.net/reference/api/search()
                dt.search(filterValue).draw();
            }, false);
        }
    };


    // Handle delete
    let handleDeleteRow = () => {
        // Delete button on click
        let tbl = document.getElementById('kt_datatable_pacients');
        tbl.addEventListener("click", (e) => {
            let dataset = e.target.dataset;
            if (dataset.hasOwnProperty('ktTableAction') && dataset.ktTableAction === "delete") {
                e.preventDefault();

                Swal.fire({
                    text: "Esti sigur ca doresti sa stergi acest pacient?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Da",
                    cancelButtonText: "Nu",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: '/dashboard/ajax/pacient/' + dataset.id + '/delete',
                            method: 'GET',
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            }
                        });
                    }
                });
            }
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            handleFilterDatatable();
            handleDeleteRow();
        }
    };
}();

// Datatable for pacient general data
var KTDatatablesPacientGeneralData = function () {
    // Shared variables
    var table;
    var dt;
    const addButton = document.getElementById('kt_add_pacient_general_data');
    var pacientGeneralDataForm = $('#kt_pacient_general_data_form');
    var generalDataUuid = '';

    // Private functions
    var initDatatable = function (uuid) {
        dt = $("#kt_datatable_pacient_general_data").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacient/" + uuid + "/data/general"
            },
            columns: [
                {data: 'createdAt'},
                {data: 'name'},
                {data: 'pacientType'},
                {data: 'pacientMobility'},
                {data: 'requiresDiapers'},
                {data: 'requiresMedicalBed'},
                {data: 'diet'},
                {data: 'nosocomialInfection'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: [4, 5],
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        if (data) {
                            return 'da';
                        }
                        return 'nu';
                    }
                },
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-general-data="load" data-disabled="1" data-uuid="${data}" title="Vizualizare">
                                <i class="fas fa-eye text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-general-data="load" data-disabled="0" data-uuid="${data}" title="Editare">
                                <i class="fas fa-user-pen text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-general-data="delete" data-uuid="${data}" title="Stergere">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacient-general-data-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Init add general data
    var initAddGeneralData = (uuid) => {
        // Submit button handler
        addButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            addButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            addButton.disabled = true;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + uuid + '/quick-add-data/general',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    addButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    addButton.disabled = false;
                }
            });
        });
    };

    // Init load general data
    var initLoadGeneralData = () => {
        // Load button handler
        $(document).on('click', "[data-kt-pacient-general-data='load']", function (e) {
            e.preventDefault();
            var button = $(this);
            // Disable button to avoid multiple click 
            button.attr('disabled', 'disabled');
            var disabled = button.data('disabled');
            generalDataUuid = button.data('uuid');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + generalDataUuid + '/general',
                method: 'POST',
                data: {'disabled': disabled},
                dataType: 'html',
                beforeSend: function () {
                    pacientGeneralDataForm.html('');
                },
                success: function (response) {
                    pacientGeneralDataForm.html(response);
                    $(".datepicker").flatpickr();
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Enable button
                    button.removeAttr('disabled');
                }
            });
        });
    };

    // Init update general data
    var initUpdateGeneralData = () => {
        // Live edit handler
        pacientGeneralDataForm.on('change', 'input, select, textarea', function () {
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + generalDataUuid + '/update/general',
                method: 'POST',
                dataType: 'json',
                data: pacientGeneralDataForm.serialize(),
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Init delete general data
    var initDeleteGeneralData = () => {
        // Delete button handler
        $(document).on('click', "[data-kt-pacient-general-data='delete']", function (e) {
            e.preventDefault();
            var button = $(this);
            generalDataUuid = button.data('uuid');

            Swal.fire({
                text: "Esti sigur ca vrei sa stergi aceste date?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/data/' + generalDataUuid + '/delete/general',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            button.attr('disabled', 'disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            button.removeAttr('disabled');
                        }
                    });
                }
            });
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            initDatatable(uuid);
            handleSearchDatatable();
            initAddGeneralData(uuid);
            initLoadGeneralData();
            initUpdateGeneralData();
            initDeleteGeneralData();
        }
    };
}();

// Datatable for pacient medical data
var KTDatatablesPacientMedicalData = function () {
    // Shared variables
    var table;
    var dt;
    const addButton = document.getElementById('kt_add_pacient_medical_data');
    var pacientMedicalDataForm = $('#kt_pacient_medical_data_form');
    var medicalDataUuid = '';

    // Private functions
    var initDatatable = function (uuid) {
        dt = $("#kt_datatable_pacient_medical_data").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacient/" + uuid + "/data/medical"
            },
            columns: [
                {data: 'createdAt'},
                {data: 'name'},
                {data: 'bloodType'},
                {data: 'rh'},
                {data: 'vaccinatedAgainstCovid'},
                {data: 'hadCovid'},
                {data: 'weight'},
                {data: 'height'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: [4, 5],
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        if (data) {
                            return 'da';
                        }
                        return 'nu';
                    }
                },
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-medical-data="load" data-disabled="1" data-uuid="${data}" title="Vizualizare">
                                <i class="fas fa-eye text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-medical-data="load" data-disabled="0" data-uuid="${data}" title="Editare">
                                <i class="fas fa-user-pen text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-medical-data="delete" data-uuid="${data}" title="Stergere">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacient-medical-data-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Init add medical data
    var initAddMedicalData = (uuid) => {
        // Submit button handler
        addButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            addButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            addButton.disabled = true;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + uuid + '/quick-add-data/medical',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    addButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    addButton.disabled = false;
                }
            });
        });
    };

    // Init load medical data
    var initLoadMedicalData = () => {
        // Load button handler
        $(document).on('click', "[data-kt-pacient-medical-data='load']", function (e) {
            e.preventDefault();
            var button = $(this);
            // Disable button to avoid multiple click 
            button.attr('disabled', 'disabled');
            var disabled = button.data('disabled');
            medicalDataUuid = button.data('uuid');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + medicalDataUuid + '/medical',
                method: 'POST',
                data: {'disabled': disabled},
                dataType: 'html',
                beforeSend: function () {
                    pacientMedicalDataForm.html('');
                },
                success: function (response) {
                    pacientMedicalDataForm.html(response);
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Enable button
                    button.removeAttr('disabled');
                }
            });
        });
    };

    // Init update medical data
    var initUpdateMedicalData = () => {
        // Live edit handler
        pacientMedicalDataForm.on('change', 'input, select, textarea', function () {
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + medicalDataUuid + '/update/medical',
                method: 'POST',
                dataType: 'json',
                data: pacientMedicalDataForm.serialize(),
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Init delete medical data
    var initDeleteMedicalData = () => {
        // Delete button handler
        $(document).on('click', "[data-kt-pacient-medical-data='delete']", function (e) {
            e.preventDefault();
            var button = $(this);
            medicalDataUuid = button.data('uuid');

            Swal.fire({
                text: "Esti sigur ca vrei sa stergi aceste date?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/data/' + medicalDataUuid + '/delete/medical',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            button.attr('disabled', 'disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            button.removeAttr('disabled');
                        }
                    });
                }
            });
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            initDatatable(uuid);
            handleSearchDatatable();
            initAddMedicalData(uuid);
            initLoadMedicalData();
            initUpdateMedicalData();
            initDeleteMedicalData();
        }
    };
}();

// Datatable for pacient clinical exam
var KTDatatablesPacientClinicalExam = function () {
    // Shared variables
    var table;
    var dt;
    const addButton = document.getElementById('kt_add_pacient_clinical_exam');
    var pacientClinicalExamForm = $('#kt_pacient_clinical_exam_form');
    var clinicalExamUuid = '';

    // Private functions
    var initDatatable = function (uuid) {
        dt = $("#kt_datatable_pacient_clinical_exam").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacient/" + uuid + "/data/clinical-exam"
            },
            columns: [
                {data: 'createdAt'},
                {data: 'name'},
                {data: 'objectiveExamination'},
                {data: 'generalState'},
                {data: 'nutritionalStatus'},
                {data: 'stateOfConsciousness'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-clinical-exam="load" data-disabled="1" data-uuid="${data}" title="Vizualizare">
                                <i class="fas fa-eye text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-clinical-exam="load" data-disabled="0" data-uuid="${data}" title="Editare">
                                <i class="fas fa-user-pen text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-clinical-exam="delete" data-uuid="${data}" title="Stergere">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacient-clinical-exam-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Init add clinical exam data
    var initAddClinicalExam = (uuid) => {
        // Submit button handler
        addButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            addButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            addButton.disabled = true;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + uuid + '/quick-add-data/clinical-exam',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    addButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    addButton.disabled = false;
                }
            });
        });
    };

    // Init load clinical-exam data
    var initLoadClinicalExam = () => {
        // Load button handler
        $(document).on('click', "[data-kt-pacient-clinical-exam='load']", function (e) {
            e.preventDefault();
            var button = $(this);
            // Disable button to avoid multiple click 
            button.attr('disabled', 'disabled');
            var disabled = button.data('disabled');
            clinicalExamUuid = button.data('uuid');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + clinicalExamUuid + '/clinical-exam',
                method: 'POST',
                data: {'disabled': disabled},
                dataType: 'html',
                beforeSend: function () {
                    pacientClinicalExamForm.html('');
                },
                success: function (response) {
                    pacientClinicalExamForm.html(response);
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Enable button
                    button.removeAttr('disabled');
                }
            });
        });
    };

    // Init update clinical-exam data
    var initUpdateClinicalExam = () => {
        // Live edit handler
        pacientClinicalExamForm.on('change', 'input, select, textarea', function () {
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + clinicalExamUuid + '/update/clinical-exam',
                method: 'POST',
                dataType: 'json',
                data: pacientClinicalExamForm.serialize(),
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Init delete clinical-exam data
    var initDeleteClinicalExam = () => {
        // Delete button handler
        $(document).on('click', "[data-kt-pacient-clinical-exam='delete']", function (e) {
            e.preventDefault();
            var button = $(this);
            clinicalExamUuid = button.data('uuid');

            Swal.fire({
                text: "Esti sigur ca vrei sa stergi aceste date?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/data/' + clinicalExamUuid + '/delete/clinical-exam',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            button.attr('disabled', 'disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            button.removeAttr('disabled');
                        }
                    });
                }
            });
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            initDatatable(uuid);
            handleSearchDatatable();
            initAddClinicalExam(uuid);
            initLoadClinicalExam();
            initUpdateClinicalExam();
            initDeleteClinicalExam();
        }
    };
}();

// Datatable for pacient discharge data
var KTDatatablesPacientDischargeData = function () {
    // Shared variables
    var table;
    var dt;
    const addButton = document.getElementById('kt_add_pacient_discharge_data');
    var pacientDischargeDataForm = $('#kt_pacient_discharge_data_form');
    var dischargeDataUuid = '';

    // Private functions
    var initDatatable = function (uuid) {
        dt = $("#kt_datatable_pacient_discharge_data").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacient/" + uuid + "/data/discharge"
            },
            columns: [
                {data: 'dischargeDate'},
                {data: 'dischargedBy'},
                {data: 'dischargeReason'},
                {data: 'clinicalDiagnostic'},
                {data: 'paraclinicalDiagnostic'},
                {data: 'epicrisis'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-discharge-data="load" data-disabled="1" data-uuid="${data}" title="Vizualizare">
                                <i class="fas fa-eye text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-discharge-data="load" data-disabled="0" data-uuid="${data}" title="Editare">
                                <i class="fas fa-user-pen text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-secondary py-1" data-kt-pacient-discharge-data="delete" data-uuid="${data}" title="Stergere">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacient-discharge-data-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Init add discharge data
    var initAddDischargeData = (uuid) => {
        // Submit button handler
        addButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            addButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            addButton.disabled = true;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + uuid + '/quick-add-data/discharge',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    addButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    addButton.disabled = false;
                }
            });
        });
    };

    // Init load discharge data
    var initLoadDischargeData = () => {
        // Load button handler
        $(document).on('click', "[data-kt-pacient-discharge-data='load']", function (e) {
            e.preventDefault();
            var button = $(this);
            // Disable button to avoid multiple click 
            button.attr('disabled', 'disabled');
            var disabled = button.data('disabled');
            dischargeDataUuid = button.data('uuid');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + dischargeDataUuid + '/discharge',
                method: 'POST',
                data: {'disabled': disabled},
                dataType: 'html',
                beforeSend: function () {
                    pacientDischargeDataForm.html('');
                },
                success: function (response) {
                    pacientDischargeDataForm.html(response);
                    $(".datepicker").flatpickr();
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Enable button
                    button.removeAttr('disabled');
                }
            });
        });
    };

    // Init update discharge data
    var initUpdateDischargeData = () => {
        // Live edit handler
        pacientDischargeDataForm.on('change', 'input, select, textarea', function () {
            $.ajax({
                url: '/dashboard/ajax/pacient/data/' + dischargeDataUuid + '/update/discharge',
                method: 'POST',
                dataType: 'json',
                data: pacientDischargeDataForm.serialize(),
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Init delete discharge data
    var initDeleteDischargeData = () => {
        // Delete button handler
        $(document).on('click', "[data-kt-pacient-discharge-data='delete']", function (e) {
            e.preventDefault();
            var button = $(this);
            dischargeDataUuid = button.data('uuid');

            Swal.fire({
                text: "Esti sigur ca vrei sa stergi aceste date?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/data/' + dischargeDataUuid + '/delete/discharge',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            button.attr('disabled', 'disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            button.removeAttr('disabled');
                        }
                    });
                }
            });
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            initDatatable(uuid);
            handleSearchDatatable();
            initAddDischargeData(uuid);
            initLoadDischargeData();
            initUpdateDischargeData();
            initDeleteDischargeData();
        }
    };
}();

// Datatable for pacients prospects
var KTDatatablesPacientsProspects = function () {
    // Shared variables
    var table;
    var dt;
    const element = document.getElementById('kt_modal_add_pacient_prospect');
    var form, modal;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_pacient_prospect_form');
        modal = new bootstrap.Modal(element);
    }

    // Private functions
    var initDatatable = function (date, status) {
        dt = $("#kt_datatable_pacients_prospects").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacients/prospects/all",
                data: {
                    'date': date,
                    'status': status
                }
            },
            columns: [
                {data: 'createdAt'},
                {data: 'relationName'},
                {data: 'leadSource'},
                {data: 'beneficiaryName'},
                {data: 'phoneNumber'},
                {data: 'email'},
                {data: 'scheduledAt'},
                {data: 'offerSentAt'},
                {data: 'addedBy'},
                {data: 'status'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return `
                            <a href="/dashboard/pacient/prospect/${row.uid}">${data}</a>
                        `;
                    }
                },
                {
                    targets: 8,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <div class="symbol symbol-35px symbol-circle" title="${row.addedByName}">
				<img alt="Pic" src="${row.addedByPhoto}" />
			    </div>
                        `;
                    }
                },
                {
                    targets: 9,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        var template = '-';
                        if (data == 'In lucru') {
                            template = `<span class="badge badge-light badge-lg">In lucru</span>`;
                        } else if (data == 'Oferta acceptata') {
                            template = `<span class="badge badge-success badge-lg">Oferta acceptata</span>`;
                        } else if (data == 'Oferta refuzata') {
                            template = `<span class="badge badge-danger badge-lg">Oferta refuzata</span>`;
                        } else if (data == 'Lista asteptare') {
                            template = `<span class="badge badge-warning badge-lg">Lista asteptare</span>`;
                        } else {
                            template = `<span class="badge badge-dark badge-lg">Oferta arhivata</span>`;
                        }

                        return template;
                    }
                },
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                Actiuni
                                <span class="svg-icon svg-icon-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-200px py-2" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/pacient/prospect/${data}" class="menu-link px-3">
                                        Editeaza
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/pacient/prospect/${data}/offer" class="menu-link px-3">
                                        Propunere financiara
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/pacient/prospect/${data}/onboarding" class="menu-link px-3">
                                        Inroleaza ca pacient
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacients-prospects-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Init add pacient prospect
    var initAddPacientProspect = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'relationName': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un nume valid'
                            }
                        }
                    },
                    'nursing-home': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un camin'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-pacient-prospect-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/quick-add-prospect',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.href = response.redirect;
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-pacient-prospect-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-pacient-prospect-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    // Public methods
    return {
        init: function (date, status) {
            initDatatable(date, status);
            handleSearchDatatable();
            initAddPacientProspect();
        }
    };
}();

// Datatable for pacients prospects scheduled
var KTDatatablesPacientsProspectsScheduled = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function (date) {
        dt = $("#kt_datatable_pacients_prospects_scheduled").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacients/prospects/scheduled",
                data: {
                    'date': date
                }
            },
            columns: [
                {data: 'scheduledAt'},
                {data: 'relationName'},
                {data: 'beneficiaryName'},
                {data: 'phoneNumber'},
                {data: 'addedBy'}
            ],
            columnDefs: [
                {
                    targets: 4,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <div class="symbol symbol-35px symbol-circle" title="${row.addedByName}">
				<img alt="Pic" src="${row.addedByPhoto}" />
			    </div>
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacients-prospects-scheduled-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function (date) {
            initDatatable(date);
            handleSearchDatatable();
        }
    };
}();

// Datatable for pacients prospects offers
var KTDatatablesPacientsProspectsOffers = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function (date) {
        dt = $("#kt_datatable_pacients_prospects_offers").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacients/prospects/offers",
                data: {
                    'date': date
                }
            },
            columns: [
                {data: 'offerSentAt'},
                {data: 'relationName'},
                {data: 'beneficiaryName'},
                {data: 'phoneNumber'},
                {data: 'offerPrice'},
                {data: 'addedBy'}
            ],
            columnDefs: [
                {
                    targets: 5,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <div class="symbol symbol-35px symbol-circle" title="${row.addedByName}">
				<img alt="Pic" src="${row.addedByPhoto}" />
			    </div>
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pacients-prospects-offers-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function (date) {
            initDatatable(date);
            handleSearchDatatable();
        }
    };
}();

// Class for pacients prospects sources
var KTPacientsProspectsSources = function () {
    // Private functions
    var initPieChart = function () {
        // GOOGLE CHARTS INIT
        google.load('visualization', '1', {
            packages: ['corechart', 'bar', 'line']
        });

        google.setOnLoadCallback(function () {
            loadAndDrawPieChartData();
        });
    };

    var loadAndDrawPieChartData = function () {
        $.ajax({
            url: '/dashboard/ajax/prospects/sources',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var data = google.visualization.arrayToDataTable(response);

                var options = {
                    title: 'Sursa lead-uri'
                };

                var chart = new google.visualization.PieChart(document.getElementById('kt_prospects_sources_google_chart_pie'));
                chart.draw(data, options);
            },
            error: function () {
                KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
            }
        });
    };

    // Public methods
    return {
        init: function () {
            initPieChart();
        }
    };
}();

// Class for pacient prospect
var KTPacientProspect = function () {
    // Shared variables
    var pacientProspectForm = $('#kt_pacient_prospect_form');

    // Init edit pacient prospect
    var initEditPacientProspect = (uuid) => {
        // Live edit handler
        pacientProspectForm.on('change', 'input, select, textarea', function () {
            $.ajax({
                url: '/dashboard/ajax/prospect/' + uuid + '/update',
                method: 'POST',
                dataType: 'json',
                data: pacientProspectForm.serialize(),
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            initEditPacientProspect(uuid);
        }
    };
}();

// Class for pacient prospect offer
var KTPacientProspectOffer = function () {
    var to = document.querySelector("#to");
    var cc = document.querySelector("#cc");
    var bcc = document.querySelector("#bcc");
    var tagifyTo;
    var tagifyCc;
    var tagifyBcc;

    var initLoadOfferTemplate = (uuid) => {
        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/prospect/' + uuid + '/offer/template',
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                if (!tinymce.get('message')) {
                    initEditor();
                }
                tinymce.activeEditor.setContent('');
                // add loading indication
                tinymce.activeEditor.setProgressState(true);
            },
            success: function (response) {
                if (response.success) {
                    tinymce.activeEditor.setContent(response.content);
                } else {
                    KTSwal.showDefaultErrorSwal();
                }
            },
            error: function () {
                KTSwal.showDefaultErrorSwal();
            },
            complete: function () {
                tinymce.activeEditor.setProgressState(false);
            }
        });
    };

    // init editors
    var initEditor = function () {
        tinymce.init({
            selector: "#message",
            height: "600",
            convert_urls: false
        });
    };

    // var init Tagify
    var initTagify = function () {
        tagifyTo = new Tagify(to, {
            pattern: /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,
            placeholder: "Introdu mai multe adrese de email, separate prin virgula"
        });
        tagifyCc = new Tagify(cc, {
            pattern: /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,
            placeholder: "Introdu mai multe adrese de email, separate prin virgula"
        });
        tagifyBcc = new Tagify(bcc, {
            pattern: /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,
            placeholder: "Introdu mai multe adrese de email, separate prin virgula"
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            initLoadOfferTemplate(uuid);
            initTagify();
        }
    };
}();


// Datatable for admissions
var KTDatatablesAdmissions = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function () {
        dt = $("#kt_datatable_admissions").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacients/admissions"
            },
            columns: [
                {data: 'admissionDate'},
                {data: 'name'},
                {data: 'pacientType'},
                {data: 'pacientMobility'},
                {data: 'requiresDiapers'},
                {data: 'diet'},
                {data: 'requiresMedicalBed'},
                {data: 'medic'}
            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return `<a href="/dashboard/pacient/${row.uid}/overview" class="fw-bold">${data}</a>`
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        if (data) {
                            return "da";
                        }

                        return "nu";
                    }
                },
                {
                    targets: 6,
                    render: function (data, type, row) {
                        if (data) {
                            return "da";
                        }

                        return "nu";
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-admissions-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
        }
    };
}();

// Datatable for discharges
var KTDatatablesDischarges = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function () {
        dt = $("#kt_datatable_discharges").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacients/discharges"
            },
            columns: [
                {data: 'dischargeDate'},
                {data: 'name'},
                {data: 'clinicalDiagnostic'},
                {data: 'paraclinicalDiagnostic'},
                {data: 'epicrisis'},
                {data: 'dischargeReason'}
            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return `<a href="/dashboard/pacient/${row.pacientUuid}/overview" class="fw-bold">${data}</a>`
                    }
                },
                {
                    targets: 6,
                    orderable: false,
                    render: function (data, type, row) {
                        return `<a href="/dashboard/pacients/readmission/${row.pacientUuid}" class="btn btn-light btn-active-light-primary btn-sm">Readmite</a>
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-discharges-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
        }
    };
}();

// Datatable for nursing homes
var KTDatatablesNursingHomes = function () {
    // Shared variables
    var table;
    var dt;
    const element = document.getElementById('kt_modal_add_nursing_home');
    var form, modal;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_nursing_home_form');
        modal = new bootstrap.Modal(element);
    }

    // Private functions
    var initDatatable = function () {
        dt = $("#kt_datatable_nursing_homes").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'asc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/nursing-homes"
            },
            columns: [
                {data: 'name'},
                {data: 'officialName'},
                {data: 'county'},
                {data: 'city'},
                {data: 'address'},
                {data: 'createdAt'}
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, row) {
                        return `<a href="/dashboard/nursing-homes/${row.uid}/edit" class="fw-bold">${data}</a>`;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, row) {
                        return data ? data : '-';
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, row) {
                        return data ? data : '-';
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-nursing-homes-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Init add nursing home
    var initAddNursingHome = () => {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(form,
            {
                fields: {
                    'nursing_home_form[name]': {
                        validators: {
                            notEmpty: {
                                message: 'Acest câmp este obligatoriu.'
                            }
                        }
                    },
                    'nursing_home_form[officialName]': {
                        validators: {
                            notEmpty: {
                                message: 'Acest câmp este obligatoriu.'
                            }
                        }
                    },
                    'nursing_home_form[dpoEmail]': {
                        validators: {
                            emailAddress: {
                                message: 'Formatul adresei de email este invalid.'
                            }
                        }
                    },
                    'nursing_home_form[address]': {
                        validators: {
                            notEmpty: {
                                message: 'Acest câmp este obligatoriu.'
                            }
                        }
                    },
                    'nursing_home_form[county]': {
                        validators: {
                            notEmpty: {
                                message: 'Acest câmp este obligatoriu.'
                            }
                        }
                    },
                    'nursing_home_form[city]': {
                        validators: {
                            notEmpty: {
                                message: 'Acest câmp este obligatoriu.'
                            }
                        }
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-nursing-home-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status === 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/nursing-homes/add-nursing-home',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    KTSwal.showSwal(response.message, "success");
                                    setTimeout(function (e) {
                                        window.location.reload();
                                    }, 400);
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-nursing-home-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-nursing-home-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            initAddNursingHome();
        }
    };
}();


// Datatable for pacient relations
var KTDatatablesPacientRelations = function () {
    // Shared variables
    var table;
    var dt;
    const element = document.getElementById('kt_modal_add_pacient_relation');
    var form, modal;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_pacient_relation_form');
        modal = new bootstrap.Modal(element);
    }

    // Private functions
    var initDatatable = function (uuid) {
        dt = $("#kt_datatable_pacient_relations").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'asc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacient/" + uuid + "/relations"
            },
            columns: [
                {data: 'name'},
                {data: 'email'},
                {data: 'phoneNumber'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                Actiuni
                                <span class="svg-icon svg-icon-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px py-2" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                   <a href="/dashboard/user/${data}/view" class="menu-link px-3">
                                        Editare
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-relations-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Init add pacient relation
    var initAddRelation = (uuid, followRedirect) => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'email': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci o adresa de email'
                            },
                            emailAddress: {
                                message: 'Te rugam sa introduci o adresa de email valida'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-pacient-relations-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/' + uuid + '/quick-add-relation',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    if (followRedirect) {
                                        window.location.href = response.redirect;
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-pacient-relations-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-pacient-relations-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    // Public methods
    return {
        init: function (uuid, followRedirect) {
            initDatatable(uuid);
            handleSearchDatatable();
            initAddRelation(uuid, followRedirect);
        }
    };
}();

// Datatable for medical visits
var KTDatatablesPacientDailyMonitoringMedical = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function (uuid) {
        dt = $("#kt_datatable_visits").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pacient/" + uuid + "/daily-monitoring/medical"
            },
            columns: [
                {data: 'createdAt'},
                {data: null},
                {data: 'temperature'},
                {data: 'saturation'},
                {data: null},
                {data: 'heartRate'},
                {data: 'glucose'},
                {data: 'infusion'},
                {data: 'observations'}
            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return row.firstName + ' ' + row.lastName;
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        if (row.systolicBloodPressure && row.diastolicBloodPressure) {
                            return row.systolicBloodPressure + '/' + row.diastolicBloodPressure;
                        }
                        return '';
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-visits-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            initDatatable(uuid);
            handleSearchDatatable();
        }
    };
}();

// Class definition
var KTUsersUpdatePassword = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_update_password');
    var form, modal;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_update_password_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add schedule modal
    var initUpdatePassword = (uuid) => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'change_password_form[new_password][first]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci parola noua'
                            },
                            callback: {
                                message: 'Te rugam sa introduci o parola valida',
                                callback: function (input) {
                                    if (input.value.length > 0) {
                                        return validatePassword();
                                    }
                                }
                            }
                        }
                    },
                    'change_password_form[new_password][second]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa confirmi parola noua'
                            },
                            identical: {
                                compare: function () {
                                    return form.querySelector('[name="new_password"]').value;
                                },
                                message: 'Parolele introduse nu coincid'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Close button handler
        const closeButton = element.querySelector('[data-kt-users-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form	
            modal.hide(); // Hide modal	
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-users-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form	
            modal.hide(); // Hide modal	
        });

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-users-modal-action="submit"]');
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/user/' + uuid + '/update-password',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "OK",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then(function (result) {
                                        if (result.isConfirmed) {
                                            modal.hide();
                                        }
                                    });
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });
                    }
                });
            }
        });
    }

    return {
        // Public functions
        init: function (uuid) {
            initUpdatePassword(uuid);
        }
    };
}();

// Class definition
var KTUsersUpdatePersonalData = function () {
    // Shared variables
    const form = $('#kt_update_personal_data_form, #kt_create_admission_form');

    // Init update personal data
    var initUpdatePersonalData = (uuid) => {
        // Live edit event handler
        form.on('change', 'input, select, textarea', function () {
            $.ajax({
                url: '/dashboard/ajax/user/' + uuid + '/update-personal-data',
                method: 'POST',
                dataType: 'json',
                data: $(form).serialize(),
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Init upload profile photo
    var uploadProfilePhoto = (uuid) => {
        $('input[name="avatar"]').change(function () {
            //on change event  
            var formData = new FormData();
            var photo;
            if ($(this).prop('files').length > 0) {
                photo = $(this).prop('files')[0];
                formData.append("photo", photo);

                $.ajax({
                    url: '/dashboard/ajax/user/' + uuid + '/upload-profile-photo',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            KTToasts.appendToast('bg-success', response.message);
                        } else {
                            KTToasts.appendToast('bg-danger', response.message);
                        }
                    },
                    error: function () {
                        KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                    }
                });
            }
        });
    };

    // Init delete profile photo
    var deleteProfilePhoto = (uuid) => {
        $('span[data-kt-image-input-action="remove"]').click(function () {
            $.ajax({
                url: '/dashboard/ajax/user/' + uuid + '/remove-profile-photo',
                type: "GET",
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Init date picker
    var initDatePicker = () => {
        $("#dateOfBirth").flatpickr();
    };


    return {
        // Public functions
        init: function (uuid) {
            initUpdatePersonalData(uuid);
            initDatePicker();
            uploadProfilePhoto(uuid);
            deleteProfilePhoto(uuid);
        }
    };
}();

// Class definition
var KTPacientUpdatePersonalData = function () {
    // Shared variables
    const form = $('#kt_update_personal_data_form, #kt_create_admission_form');

    // Init update personal data
    var initUpdatePersonalData = (uuid) => {
        // Live edit event handler
        form.on('change', 'input, select, textarea', function () {
            $.ajax({
                url: '/dashboard/ajax/pacient/' + uuid + '/update-personal-data',
                method: 'POST',
                dataType: 'json',
                data: $(form).serialize(),
                success: function (response) {
                    if (response.success) {
                        KTToasts.appendToast('bg-success', response.message);
                    } else {
                        KTToasts.appendToast('bg-danger', response.message);
                    }
                },
                error: function () {
                    KTToasts.appendToast('bg-danger', 'A intervenit o eroare neprevazuta, te rugam sa incerci din nou mai tarziu');
                }
            });
        });
    };

    // Init date picker
    var initDatePicker = () => {
        $("#dateOfBirth").flatpickr();
    };

    return {
        // Public functions
        init: function (uuid) {
            initUpdatePersonalData(uuid);
            initDatePicker();
        }
    };
}();

// Quick add user
var KTUsersAddUser = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_add_user');
    var form, modal;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_user_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add schedule modal
    var initAddUser = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'email': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci o adresa de email'
                            },
                            emailAddress: {
                                message: 'Te rugam sa introduci o adresa de email valida'
                            }
                        }
                    },
                    'nursing-home': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un camin'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-users-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/quick-add-user',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.href = response.redirect;
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-users-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-users-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    return {
        // Public functions
        init: function () {
            initAddUser();
        }
    };
}();

// Quick add pacient
var KTUsersAddPacient = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_add_pacient');
    var form, modal;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_pacient_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add schedule modal
    var initAddPacient = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'lastName': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un nume valid'
                            }
                        }
                    },
                    'firstName': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un prenume valid'
                            }
                        }
                    },
                    'nursing-home': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un camin'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-pacients-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/quick-add-pacient',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-pacients-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-pacients-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    return {
        // Public functions
        init: function () {
            initAddPacient();
        }
    };
}();

// Class for pacient files
var KTPacientFile = function () {
    // Shared variables
    const addPacientFileElement = document.getElementById('kt_modal_add_pacient_file');
    const editPacientFileElement = document.getElementById('kt_modal_edit_pacient_file');
    var addPacientFileForm, addPacientFileModal, editPacientFileForm, editPacientFileModal;

    if (typeof (addPacientFileElement) != 'undefined' && addPacientFileElement != null) {
        addPacientFileForm = addPacientFileElement.querySelector('#kt_modal_add_pacient_file_form');
        addPacientFileModal = new bootstrap.Modal(addPacientFileElement);
    }

    if (typeof (editPacientFileElement) != 'undefined' && editPacientFileElement != null) {
        editPacientFileForm = editPacientFileElement.querySelector('#kt_modal_edit_pacient_file_form');
        editPacientFileModal = new bootstrap.Modal(editPacientFileElement);
    }

    // Init add pacient file modal
    var initAddPacientFile = (uuid) => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            addPacientFileForm,
            {
                fields: {
                    'fileName': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un nume valid'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = addPacientFileElement.querySelector('[data-kt-pacient-file-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/' + uuid + '/add-file',
                            method: 'POST',
                            dataType: 'json',
                            data: $(addPacientFileForm).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = addPacientFileElement.querySelector('[data-kt-pacient-file-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            addPacientFileForm.reset(); // Reset form			
            addPacientFileModal.hide();
        });

        // Close button handler
        const closeButton = addPacientFileElement.querySelector('[data-kt-pacient-file-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            addPacientFileForm.reset(); // Reset form			
            addPacientFileModal.hide();
        });

        // pass group to modal input
        $('#kt_modal_add_pacient_file').on('show.bs.modal', function (e) {
            var group = $(e.relatedTarget).data('pacient-file-group');
            $(e.currentTarget).find('input[name="group"]').val(group);
        });
    };

    // Init edit pacient file modal
    var initEditPacientFile = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            editPacientFileForm,
            {
                fields: {
                    'pacient_file_form[fileName]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un nume valid'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = editPacientFileElement.querySelector('[data-kt-edit-pacient-file-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;
                        // get pacient file uuid value
                        var pacientFileUuid = $('#pacient_file_form_uid').val();

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/file/' + pacientFileUuid + '/update',
                            method: 'POST',
                            dataType: 'json',
                            data: $(editPacientFileForm).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = editPacientFileElement.querySelector('[data-kt-edit-pacient-file-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            editPacientFileForm.reset(); // Reset form			
            editPacientFileModal.hide();
        });

        // Close button handler
        const closeButton = editPacientFileElement.querySelector('[data-kt-edit-pacient-file-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            editPacientFileForm.reset(); // Reset form			
            editPacientFileModal.hide();
        });

        // get pacient file data on modal show
        $('#kt_modal_edit_pacient_file').on('show.bs.modal', function (e) {
            var fileUuid = $(e.relatedTarget).data('pacient-file-uuid');
            var pacientFileFormWrapper = $('#kt_modal_edit_pacient_file_scroll');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/file/' + fileUuid,
                method: 'GET',
                dataType: 'html',
                beforeSend: function () {
                    pacientFileFormWrapper.html('');
                },
                success: function (response) {
                    pacientFileFormWrapper.html(response);
                    $(".datepicker").flatpickr();
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    };

    // Init delete pacient file
    var initDeletePacientFile = () => {
        // Delete button handler
        $(document).on('click', "[data-kt-pacient-file='delete']", function (e) {
            e.preventDefault();
            var button = $(this);
            var pacientFileUuid = button.data('pacient-file-uuid');

            Swal.fire({
                text: "Esti sigur ca vrei sa stergi acest document?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/file/' + pacientFileUuid + '/delete',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            button.attr('disabled', 'disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            button.removeAttr('disabled');
                        }
                    });
                }
            });
        });
    };

    // Init track pacient file view
    var initTrackPacientFileView = () => {
        // View button handler
        $('.track-pacient-file-view').click(function (e) {
            //e.preventDefault();
            var pacientFileUuid = $(this).data('pacient-file-uuid');
            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/file/' + pacientFileUuid + '/view',
                method: 'GET',
                dataType: 'json'
            });
        });
    };

    return {
        // Public functions
        init: function (uuid) {
            initAddPacientFile(uuid);
            initEditPacientFile();
            initDeletePacientFile();
            initTrackPacientFileView();
        }
    };
}();

// Datatable for documents
var KTDatatablesDocuments = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function (type) {
        dt = $("#kt_datatable_documents").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'asc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/documents/" + type
            },
            columns: [
                {data: 'fileName'},
                {data: 'fileNumber'},
                {data: 'fileDate'},
                {data: 'fileDetails'},
                {data: 'responsibleName'},
                {data: 'uploaderName'},
                {data: 'status'},
                {data: 'views'}
            ],
            columnDefs: []
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-documents-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function (type) {
            initDatatable(type);
            handleSearchDatatable();
        }
    };
}();

// Datatable for visits
var KTDatatablesRelationVisits = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function () {
        dt = $("#kt_datatable_visits").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/visits",
                data: function (d) {
                    // Check if the select element exists
                    const filterElement = document.querySelector('select[name="filter_type"]');

                    // If the select element exists, set d.filter to its value; otherwise, set d.filter to null or an empty string
                    d.filter = filterElement ? filterElement.value : null;

                    return d;
                }
            },
            columns: [
                {data: 'date'},
                {data: 'hour'},
                {data: 'name'},
                {data: 'relation'},
                {data: 'observations'},
                {data: 'status'}
            ],
            columnDefs: []
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-visits-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    var handleFilterDatatable = function () {
        document.querySelector('select[name="filter_type"]').addEventListener('change', function () {
            dt.ajax.reload();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            handleFilterDatatable();
        }
    };
}();

// Datatable for users
var KTDatatablesCookMenus = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function () {
        dt = $("#kt_datatable_cook_menus").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[4, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/cook-menus"
            },
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'startDate'},
                {data: 'endDate'},
                {data: 'createdAt'},
                {data: 'uid'}
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                Actiuni
                                <span class="svg-icon svg-icon-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-250px py-2" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/cook/menu/${data}/edit" class="menu-link px-3" data-kt-cook-menus-table-filter="edit_row">
                                        Editare
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-cook-menus-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
        }
    };
}();

// Class for visits
var KTVisits = function () {
    // Private variables
    var element;
    var resultsElement;
    var wrapperElement;
    var emptyElement;
    var searchObject;
    var visitDate, visitHour, slot, locationUuid;
    var $searchResultsWrapper = $('#kt_modal_pacients_search_results_wrapper');

    // Private functions
    var processs = function (search) {
        $.ajax({
            url: '/dashboard/ajax/pacients/search',
            method: 'GET',
            data: {'q': searchObject.getQuery()},
            dataType: 'json',
            beforeSend: function () {
                $searchResultsWrapper.html('');
            },
            success: function (users) {
                if (users.length) {
                    $.each(users, function (key, user) {
                        $searchResultsWrapper.append(`
                                <!--begin::User-->
                                <div class="rounded d-flex flex-stack bg-active-lighten p-4">
                                    <!--begin::Details-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-35px symbol-circle">
                                            <img alt="Pic" src="${user.photo}" />
                                        </div>
                                        <!--end::Avatar-->
                                        <!--begin::Details-->
                                        <div class="ms-5">
                                            <span class="fs-5 fw-bold text-gray-900 mb-2">${user.name}</span>
                                            <div class="fw-semibold text-muted">${user.email}</div>
                                        </div>
                                        <!--end::Details-->
                                    </div>
                                    <!--end::Details-->
                                    <!--begin::Access menu-->
                                    <div class="ms-2 w-100px">
                                        <button type="submit" class="btn btn-primary add-pacient" data-user-uuid="${user.uid}">Adauga</button>
                                    </div>
                                    <!--end::Access menu-->
                                </div>
                                <!--end::User-->`
                        );
                        if (key < users.length - 1) {
                            $searchResultsWrapper.append('<div class="border-bottom border-gray-300 border-bottom-dashed"></div>');
                        }
                    });

                    // Show results
                    resultsElement.classList.remove('d-none');
                    // Hide empty message 
                    emptyElement.classList.add('d-none');
                } else {
                    // Hide results
                    resultsElement.classList.add('d-none');
                    // Show empty message 
                    emptyElement.classList.remove('d-none');
                }

                // Complete search
                search.complete();
            },
            error: function () {
                // Hide results
                resultsElement.classList.add('d-none');
                // Show empty message 
                emptyElement.classList.remove('d-none');
                // Complete search
                search.complete();
            }
        });
    };

    var clear = function (search) {
        // Hide results
        resultsElement.classList.add('d-none');
        // Hide empty message 
        emptyElement.classList.add('d-none');
    };

    var addRelationPacientVisit = function (uuid) {
        $(document).on('click', '.add-pacient', function (e) {
            e.preventDefault();
            var userUuid = $(this).data('user-uuid');

            var data = {
                date: visitDate,
                hour: visitHour,
                slot: slot,
                locationUuid: locationUuid,
                userUuid: userUuid
            };

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/visit/pacient/add',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        KTSwal.showSwal(response.message, 'error');
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    };

    var handleFormChange = function () {
        var $pacientForm = $('#pacient_visit_form');
        $('#visit_date').on('change', function () {
            $pacientForm.submit();
        });
        $('#visit_location').on('change', function () {
            $pacientForm.submit();
        });
    };

    // get pacient file data on modal show
    $('#kt_modal_pacients_search').on('show.bs.modal', function (e) {
        var $relatedTarget = $(e.relatedTarget);
        visitDate = $relatedTarget.data('visit-date');
        visitHour = $relatedTarget.data('visit-hour');
        slot = $relatedTarget.data('slot');
        locationUuid = $relatedTarget.data('location-uuid');
    });

    // Public methods
    return {
        init: function () {
            // Elements
            element = document.querySelector('#kt_modal_pacients_search_handler');

            if (!element) {
                return;
            }

            wrapperElement = element.querySelector('[data-kt-search-element="wrapper"]');
            resultsElement = element.querySelector('[data-kt-search-element="results"]');
            emptyElement = element.querySelector('[data-kt-search-element="empty"]');

            // Initialize search handler
            searchObject = new KTSearch(element);

            // Search handler
            searchObject.on('kt.search.process', processs);

            // Clear handler
            searchObject.on('kt.search.clear', clear);

            // Add relation pacient visit
            addRelationPacientVisit();

            // Handle form inputs change
            handleFormChange();
        }
    };
}();

// Class definition
var KTModalUserSearch = function () {
    // Private variables
    var element;
    var resultsElement;
    var wrapperElement;
    var emptyElement;
    var searchObject;
    var $searchResultsWrapper = $('#kt_modal_users_search_results_wrapper');
    var fileUuid;

    // Private functions
    var processs = function (search) {
        $.ajax({
            url: '/dashboard/ajax/users/search',
            method: 'GET',
            data: {'q': searchObject.getQuery()},
            dataType: 'json',
            beforeSend: function () {
                $searchResultsWrapper.html('');
            },
            success: function (users) {
                if (users.length) {
                    $.each(users, function (key, user) {
                        $searchResultsWrapper.append(`
                                <!--begin::User-->
                                <div class="rounded d-flex flex-stack bg-active-lighten p-4">
                                    <!--begin::Details-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-35px symbol-circle">
                                            <img alt="Pic" src="${user.photo}" />
                                        </div>
                                        <!--end::Avatar-->
                                        <!--begin::Details-->
                                        <div class="ms-5">
                                            <span class="fs-5 fw-bold text-gray-900 mb-2">${user.name}</span>
                                            <div class="fw-semibold text-muted">${user.email}</div>
                                        </div>
                                        <!--end::Details-->
                                    </div>
                                    <!--end::Details-->
                                    <!--begin::Access menu-->
                                    <div class="ms-2 w-100px">
                                        <button type="submit" class="btn btn-primary update-responsible-user" data-user-uuid="${user.uid}">Adauga</button>
                                    </div>
                                    <!--end::Access menu-->
                                </div>
                                <!--end::User-->`
                        );
                        if (key < users.length - 1) {
                            $searchResultsWrapper.append('<div class="border-bottom border-gray-300 border-bottom-dashed"></div>');
                        }
                    });

                    // Show results
                    resultsElement.classList.remove('d-none');
                    // Hide empty message 
                    emptyElement.classList.add('d-none');
                } else {
                    // Hide results
                    resultsElement.classList.add('d-none');
                    // Show empty message 
                    emptyElement.classList.remove('d-none');
                }

                // Complete search
                search.complete();
            },
            error: function () {
                // Hide results
                resultsElement.classList.add('d-none');
                // Show empty message 
                emptyElement.classList.remove('d-none');
                // Complete search
                search.complete();
            }
        });
    };

    var clear = function (search) {
        // Hide results
        resultsElement.classList.add('d-none');
        // Hide empty message 
        emptyElement.classList.add('d-none');
    };

    // get pacient file data on modal show
    $('#kt_modal_users_search').on('show.bs.modal', function (e) {
        fileUuid = $(e.relatedTarget).data('pacient-file-uuid');
    });

    var updateDigitalRecordResponsibleUser = function () {
        $(document).on('click', '.update-responsible-user', function (e) {
            e.preventDefault();

            var userUuid = $(this).data('user-uuid');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/file/' + fileUuid + '/responsible/' + userUuid + '/update',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        KTSwal.showSwal(response.message, 'error');
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    };

    // Public methods
    return {
        init: function () {
            // Elements
            element = document.querySelector('#kt_modal_users_search_handler');

            if (!element) {
                return;
            }

            wrapperElement = element.querySelector('[data-kt-search-element="wrapper"]');
            resultsElement = element.querySelector('[data-kt-search-element="results"]');
            emptyElement = element.querySelector('[data-kt-search-element="empty"]');

            // Initialize search handler
            searchObject = new KTSearch(element);

            // Search handler
            searchObject.on('kt.search.process', processs);

            // Clear handler
            searchObject.on('kt.search.clear', clear);

            // Update responsible user
            updateDigitalRecordResponsibleUser();
        }
    };
}();

// Class definition
var KTModalPacientSearch = function () {
    // Private variables
    var element;
    var resultsElement;
    var wrapperElement;
    var emptyElement;
    var searchObject;
    var $searchResultsWrapper = $('#kt_modal_pacients_search_results_wrapper');

    // Private functions
    var processs = function (search) {
        $.ajax({
            url: '/dashboard/ajax/pacients/search',
            method: 'GET',
            data: {'q': searchObject.getQuery()},
            dataType: 'json',
            beforeSend: function () {
                $searchResultsWrapper.html('');
            },
            success: function (users) {
                if (users.length) {
                    $.each(users, function (key, user) {
                        $searchResultsWrapper.append(`
                                <!--begin::User-->
                                <div class="rounded d-flex flex-stack bg-active-lighten p-4">
                                    <!--begin::Details-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-35px symbol-circle">
                                            <img alt="Pic" src="${user.photo}" />
                                        </div>
                                        <!--end::Avatar-->
                                        <!--begin::Details-->
                                        <div class="ms-5">
                                            <span class="fs-5 fw-bold text-gray-900 mb-2">${user.name}</span>
                                            <div class="fw-semibold text-muted">${user.email}</div>
                                        </div>
                                        <!--end::Details-->
                                    </div>
                                    <!--end::Details-->
                                    <!--begin::Access menu-->
                                    <div class="ms-2 w-100px">
                                        <button type="submit" class="btn btn-primary add-pacient" data-user-uuid="${user.uid}">Adauga</button>
                                    </div>
                                    <!--end::Access menu-->
                                </div>
                                <!--end::User-->`
                        );
                        if (key < users.length - 1) {
                            $searchResultsWrapper.append('<div class="border-bottom border-gray-300 border-bottom-dashed"></div>');
                        }
                    });

                    // Show results
                    resultsElement.classList.remove('d-none');
                    // Hide empty message 
                    emptyElement.classList.add('d-none');
                } else {
                    // Hide results
                    resultsElement.classList.add('d-none');
                    // Show empty message 
                    emptyElement.classList.remove('d-none');
                }

                // Complete search
                search.complete();
            },
            error: function () {
                // Hide results
                resultsElement.classList.add('d-none');
                // Show empty message 
                emptyElement.classList.remove('d-none');
                // Complete search
                search.complete();
            }
        });
    };

    var clear = function (search) {
        // Hide results
        resultsElement.classList.add('d-none');
        // Hide empty message 
        emptyElement.classList.add('d-none');
    };

    var addPacientToSummary = function (uuid) {
        $(document).on('click', '.add-pacient', function (e) {
            e.preventDefault();

            var userUuid = $(this).data('user-uuid');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/summary/' + uuid + '/pacient/' + userUuid + '/add',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        KTSwal.showSwal(response.message, 'error');
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    };

    // Public methods
    return {
        init: function (uuid) {
            // Elements
            element = document.querySelector('#kt_modal_pacients_search_handler');

            if (!element) {
                return;
            }

            wrapperElement = element.querySelector('[data-kt-search-element="wrapper"]');
            resultsElement = element.querySelector('[data-kt-search-element="results"]');
            emptyElement = element.querySelector('[data-kt-search-element="empty"]');

            // Initialize search handler
            searchObject = new KTSearch(element);

            // Search handler
            searchObject.on('kt.search.process', processs);

            // Clear handler
            searchObject.on('kt.search.clear', clear);

            // Add pacient to current summary
            addPacientToSummary(uuid);
        }
    };
}();

// Class for summary pacient
var KTPacientSummary = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_edit_summary_pacient');
    var form, modal;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_edit_summary_pacient_form');
        modal = new bootstrap.Modal(element);
    }

    // Init edit summary pacient modal
    var initEditSummaryPacient = (reload) => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'summary_pacient_form[observations]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi acest camp'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-edit-summary-pacient-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;
                        // get pacient file uuid value
                        var uuid = $('#summary_pacient_form_uid').val();

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/summary/pacient/' + uuid + '/update',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    if (reload) {
                                        window.location.reload();
                                    } else {
                                        KTSwal.showSwal(response.message, "success");
                                        modal.hide();
                                    }
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-edit-summary-pacient-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-edit-summary-pacient-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // get pacient file data on modal show
        $('#kt_modal_edit_summary_pacient').on('show.bs.modal', function (e) {
            var uuid = $(e.relatedTarget).data('summary-pacient-uuid');
            var summaryPacientFormWrapper = $('#kt_modal_edit_summary_pacient_scroll');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/summary/pacient/' + uuid,
                method: 'GET',
                dataType: 'html',
                beforeSend: function () {
                    summaryPacientFormWrapper.html('');
                },
                success: function (response) {
                    summaryPacientFormWrapper.html(response);
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    };

    // Init delete summary pacient
    var initDeleteSummaryPacient = (reload) => {
        // Delete button handler
        $(document).on('click', "[data-kt-summary-pacient='delete']", function (e) {
            e.preventDefault();
            var button = $(this);
            var summaryPacientUuid = button.data('summary-pacient-uuid');

            Swal.fire({
                text: "Esti sigur ca vrei sa stergi acest borderou?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/summary/pacient/' + summaryPacientUuid + '/delete',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            button.addClass('disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                if (reload) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "success");
                                }
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            button.removeClass('disabled');
                        }
                    });
                }
            });
        });
    };

    return {
        // Public functions
        init: function (reload) {
            initEditSummaryPacient(reload);
            initDeleteSummaryPacient(reload);
        }
    };
}();

// Class for pacient sample
var KTPacientSample = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_request_pacient_sample');
    var form, modal, pacientUuid;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_request_pacient_sample_form');
        modal = new bootstrap.Modal(element);
    }

    // Init edit summary pacient modal
    var initAddPacientSampleRequest = () => {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'date': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi data recoltarii'
                            }
                        }
                    },
                    'description': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa adaugi analizele necesare'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-request-pacient-sample-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/' + pacientUuid + '/sample-request',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    KTSwal.showSwal(response.message, "success");
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 2500);
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-request-pacient-sample-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-request-pacient-sample-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // get pacient file data on modal show
        $('#kt_modal_request_pacient_sample').on('show.bs.modal', function (e) {
            pacientUuid = $(e.relatedTarget).data('pacient-uuid');
        });
    };

    return {
        // Public functions
        init: function () {
            initAddPacientSampleRequest();
        }
    };
}();

// Class for summary settings
var KTSummarySettings = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_summary_settings');
    const pacientSampleElement = document.getElementById('kt_modal_edit_pacient_sample');
    var form, nursingHomeForm, pacientSampleForm, modal, pacientSampleModal, submitSettingsButton, pacientSampleUuid;
    var floors = [];
    var shifts = [];
    var nursingHomeElement = $('#nursing-home');
    var floorTitleTemplate = `<p class="fw-semibold fs-6 mb-2">Etaj</p>`;
    var shiftTitleTemplate = `<p class="fw-semibold fs-6 mb-2">Tura</p>`;
    var checkboxTemplate = `<label class="form-check form-check-custom form-check-solid mb-2">
                            <input class="form-check-input" type="checkbox" name={name} value="{value}" data-label="{label}" />
                            <span class="form-check-label">
                                {label}
                            </span>
                        </label>`;
    var headingTemplate = `<p class="fw-semibold fs-4">Se afiseaza pacientii de la etajele {floors} pentru tura {shifts}</p>`;
    var accordionItemTemplate = `<div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button fs-4 fw-semibold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{accordion_item_id}">
                {accordion_item_title}
            </button>
        </h2>
        <div id="{accordion_item_id}" class="accordion-collapse collapse" data-bs-parent="#kt_accordion">
            <div class="accordion-body">
                {accordion_item_body}
            </div>
        </div>
    </div>`;
    var pacientWrapper = `<div class="pacient-wrapper" id="{uid}">`;
    var pacientTemplate = `<p class="fw-bold fs-3 mb-2">{completed_icon}{name} <a href="#" class="fw-bold text-hover-primary mb-1 fs-6" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_summary_pacient" data-summary-pacient-uuid="{uuid}">Actualizeaza observatiile</a></p>`;
    var completedIconTemplate = `<span class="svg-icon svg-icon-primary svg-icon-2x"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" d="M10.3 14.3L11 13.6L7.70002 10.3C7.30002 9.9 6.7 9.9 6.3 10.3C5.9 10.7 5.9 11.3 6.3 11.7L10.3 15.7C9.9 15.3 9.9 14.7 10.3 14.3Z" fill="currentColor"/>
                                    <path d="M22 12C22 17.5 17.5 22 12 22C6.5 22 2 17.5 2 12C2 6.5 6.5 2 12 2C17.5 2 22 6.5 22 12ZM11.7 15.7L17.7 9.70001C18.1 9.30001 18.1 8.69999 17.7 8.29999C17.3 7.89999 16.7 7.89999 16.3 8.29999L11 13.6L7.70001 10.3C7.30001 9.89999 6.69999 9.89999 6.29999 10.3C5.89999 10.7 5.89999 11.3 6.29999 11.7L10.3 15.7C10.5 15.9 10.8 16 11 16C11.2 16 11.5 15.9 11.7 15.7Z" fill="currentColor"/>
                                    </svg>
                                 </span>`;
    var shiftTemplate = `<p class="fw-bold fs-4 mt-4 mb-2">{shift}</p>`;
    var medicationTitleTemplate = `<p class="fw-semibold fs-5 mt-4 mb-2 text-uppercase">Administrare medicamentatie</p>`;
    var medicationTemplate = `<span class="fs-7">{drug} ({dose}) {observations}</span>`;
    var medicalMonitoringTitleTemplate = `<p class="fw-semibold fs-5 mt-4 mb-2 text-uppercase">Functii vitale</p>`;
    var medicalMonitoringTableTemplate = `<table class="table gy-3">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800 border-bottom border-top border-start border-end">
                        <th>Tensiune arteriala</th>
                        <th>Puls</th>
                        <th>Temperatura</th>
                        <th>Saturatie</th>
                        <th>Glicemie</th>
                        <th>Perfuzabile</th>
                    </tr>
                </thead>
                <tbody>
                    {tbody}
                </tbody>
            </table>`;
    var medicalMonitoringTableBodyTemplate = `<tr class="border-bottom border-top border-start border-end">
                        <td>{systolicBloodPressure}/{diastolicBloodPressure}</td>
                        <td>{heartRate}</td>
                        <td>{temperature}</td>
                        <td>{saturation}</td>
                        <td>{glucose}</td>
                        <td>{infusion}</td>
                    </tr>`;
    var medicalMonitoringTableBodyEmptyTemplate = `<tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>`;
    var medicalMonitoringAddTemplate = `<div>
                    <a href="#" class="fw-bold text-hover-primary mb-1 fs-6" data-bs-toggle="modal" data-bs-target="#kt_modal_add_pacient_monitoring_medical" data-pacient-uuid="{uuid}" data-monitoring-date="{date}">Adauga</a>
                </div>`;
    var medicalMonitoringMapping = {
        'bloodPressure': 'Tensiunea arteriala',
        'heartRate': 'Puls',
        'temperature': 'Temperatura',
        'saturation': 'Saturatie',
        'glucose': 'Glicemie',
        'infusion': 'Perfuzabile'
    };
    var medicalMonitoringActionTemplate = `<span>{key}: {value}</span>`;
    var sampleTitleTemplate = `<p class="fw-semibold fs-5 mt-4 mb-2 text-uppercase">Recoltare</p>`;
    var editSampleTemplate = `<div>
                    <a href="#" class="fw-bold text-hover-primary mb-1 fs-6" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_pacient_sample" data-pacient-sample-uuid="{uuid}">{description}</a> <span class="badge badge-light">{status}</span>
                </div>`;
    var physicalMonitoringTitleTemplate = `<p class="fw-semibold fs-5 mt-4 mb-2 text-uppercase">Proceduri de kinetoterapie</p>`;
    var procedureTemplate = `<span>{procedure}</span>`;
    var procedureDividerTemplate = `<span class="d-inline-block pe-1">,</span>`;
    var physicalMonitoringAddTemplate = `<div>
                    <a href="#" class="fw-bold text-hover-primary mb-1 fs-6" data-bs-toggle="modal" data-bs-target="#kt_modal_add_pacient_monitoring_physical" data-pacient-uuid="{uuid}" data-monitoring-date="{date}">Adauga</a>
                </div>`;
    var proceduresMapping = {
        "bodyRepositioningImmobilizedPatients": "Reposturare corporala pacienti imobilizati",
        "correctingBodyPostureAndAlignment": "Corectarea posturii si aliniamentului corpului",
        "groupExercises": "Exercitii de grup",
        "groupExercisesBodyBalanceAndCoordination": "Exercitii de grup - pentru cresterea echilibrului si coordonarii corpului",
        "groupExercisesJointMobility": "Exercitii de grup - pentru cresterea mobilitatii articulare",
        "groupExercisesResistanceAndMuscleStrength": "Exercitii de grup - pentru cresterea rezistentei si fortei musculare",
        "increasingBodyCoordinationAndBalance": "Cresterea coordonarii si echilibrului corpului",
        "increasingJointMobility": "Cresterea mobilitatii articulare",
        "increasingJointMobilityActive": "Cresterea mobilitatii articulare - mobilizari active",
        "increasingJointMobilityActiveVoluntary": "Cresterea mobilitatii articulare - mobilizari active voluntare",
        "increasingJointMobilityAutoPassive": "Cresterea mobilitatii articulare - mobilizari autopasive",
        "increasingJointMobilityPassive": "Cresterea mobilitatii articulare - mobilizari pasive",
        "increasingJointMobilityPassiveActive": "Cresterea mobilitatii articulare - mobilizari pasive-active",
        "increasingMuscleStrengthAndEndurance": "Cresterea fortei si a rezistentei musculare",
        "massage": "Masaj",
        "multifunctionalDevice": "Scripetoterapie - aparatul multifunctional",
        "rocherCage": "Scripetoterapie - cusca Rocher",
        "scriptotherapy": "Scripetoterapie",
        "stretching": "Intinderi",
        "tappingMassage": "Tapotaj",
        "therapeuticMassage": "Masaj terapeutic",
        "trellisExercises": "Exercitii la spalier",
        "walkingExercises": "Exercitii de mers",
        "walkingExercisesBicycle": "Exercitii de mers - pedalatul la bicicleta",
        "walkingExercisesSteps": "Exercitii de mers - mersul pe trepte",
        "walkingExercisesSupport": "Exercitii de mers - mersul cu mijloace de sustinere",
        "walkingExercisesWalkingLane": "Exercitii de mers - banda de mers",
        "refusal": "Refuz",
        "medicalProblem": "Problema medicala",
        "observations": "Observații"
    };
    var orderlyMonitoringTitleTemplate = `<p class="fw-semibold fs-5 mt-4 mb-2 text-uppercase">Monitorizare</p>`;
    var actionTemplate = `<span>{action}</span>`;
    var actionDividerTemplate = `<span class="d-inline-block pe-1">,</span>`;
    var orderlyMonitoringAddTemplate = `<div>
                    <a href="#" class="fw-bold text-hover-primary mb-1 fs-6" data-bs-toggle="modal" data-bs-target="#kt_modal_add_pacient_monitoring_orderly" data-pacient-uuid="{uuid}" data-monitoring-date="{date}">Adauga</a>
                </div>`;
    var actionsMapping = {
        "hydrationFood": "Hidratare/masa",
        "diuresis": "Diureza",
        "stool": "Scaun",
        "bathing": "Baie",
        "diapers": "Pampers"
    };
    var cookFoodTitleTemplate = `<p class="fw-semibold fs-5 mt-4 mb-2 text-uppercase">Bucatarie</p>`;
    var cookFoodAddTemplate = `<div>
                    <a href="#" class="fw-bold text-hover-primary mb-1 fs-6" data-bs-toggle="modal" data-bs-target="#kt_modal_pacient_cook_food" data-pacient-uuid="{uuid}" data-cook-food-id="new">Adauga</a>
                </div>`;
    var editCookFoodTemplate = `<div>
                    <span class="fw-bold fs-6">({date}) {option} {observations}</span> <a href="#" class="fw-bold text-hover-primary mb-1 fs-6" data-bs-toggle="modal" data-bs-target="#kt_modal_pacient_cook_food" data-pacient-uuid="{uuid}" data-cook-food-id="{id}">Editeaza</a>
                </div>`;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_summary_settings_form');
        nursingHomeForm = element.querySelector('#kt_modal_nursing_home_form');
        submitSettingsButton = element.querySelector('[data-kt-summary-settings-modal-action="submit"]');
        modal = new bootstrap.Modal(element);
    }

    if (typeof (pacientSampleElement) != 'undefined' && pacientSampleElement != null) {
        pacientSampleForm = pacientSampleElement.querySelector('#kt_modal_edit_pacient_sample_form');
        pacientSampleModal = new bootstrap.Modal(pacientSampleElement);
    }

    // handle nursing home location change
    var handleNursingHomeLocationChange = () => {
        nursingHomeElement.on('change', function () {
            $('#nursing-home-uuid').val($(this).val());
        });
    };

    // handle nursing home form submit 
    var handleNursingHomeFormSubmit = (uuid) => {
        var validator = FormValidation.formValidation(
            nursingHomeForm,
            {
                fields: {
                    'nursing-home': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un camin'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        const submitButton = document.getElementById('nursing_home_form_submit');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/summary/' + uuid + '/settings',
                            method: 'POST',
                            dataType: 'json',
                            data: $(nursingHomeForm).serialize(),
                            beforeSend: function () {
                                $('#summary_settings_floors').html('');
                                $('#summary_settings_shift').html('');
                            },
                            success: function (response) {
                                if (response.success) {
                                    $('#summary_settings_floors').append(floorTitleTemplate);
                                    if (response.shifts.length > 0) {
                                        $('#summary_settings_shift').append(shiftTitleTemplate);
                                    }
                                    var floorsTemplate = '';
                                    var shiftsTemplate = '';
                                    $.each(response.floors, function (key, data) {
                                        floorsTemplate += checkboxTemplate
                                            .replaceAll('{name}', 'floors[]')
                                            .replaceAll('{label}', data)
                                            .replaceAll('{value}', data);
                                    });
                                    $.each(response.shifts, function (key, data) {
                                        shiftsTemplate += checkboxTemplate
                                            .replaceAll('{name}', 'shifts[]')
                                            .replaceAll('{label}', data)
                                            .replaceAll('{value}', key);
                                    });
                                    $('#summary_settings_floors').append(floorsTemplate);
                                    $('#summary_settings_shift').append(shiftsTemplate);
                                    $('button[data-kt-summary-settings-modal-action="submit"]').removeAttr('disabled');
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });
    };

    // handle summary settings form submit
    var handleSummarySettingsFormSubmit = (uuid, type) => {
        // Submit button handler
        submitSettingsButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            submitSettingsButton.setAttribute('data-kt-indicator', 'on');
            // Disable button to avoid multiple click 
            submitSettingsButton.disabled = true;

            handleSettingsChange();

            var data = $(form).serialize();
            handleSettingsAjaxCall(uuid, data, type, true);
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-summary-settings-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-summary-settings-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    var handleSettingsAjaxCall = (uuid, data, type, onClick) => {
        // send AJAX call to backend

        var ajaxUrl = '/dashboard/ajax/summary/' + uuid + '/' + type;

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function () {
                $('#kt_accordion').html('');
                $('#heading').html('');
            },
            success: function (response) {
                if (response.success) {
                    var dataTemplate = '';
                    var dataHeadingTemplate = '';
                    if (onClick) {
                        dataHeadingTemplate = headingTemplate
                            .replaceAll('{floors}', floors.join(', '))
                            .replaceAll('{shifts}', shifts.join(', '));
                        $('#heading').append(dataHeadingTemplate);
                    }
                    $.each(response.data, function (i, room) {
                        var bodyTemplate = '';
                        var pacientsList = [];
                        var pacientsCount = Object.keys(room.pacients).length;

                        if (pacientsCount > 0) {
                            $.each(room.pacients, function (j, pacient) {
                                pacientsList.push(pacient.name);
                                bodyTemplate += pacientWrapper
                                    .replace('{uid}', pacient.uid);
                                bodyTemplate += pacientTemplate
                                    .replace('{name}', pacient.name)
                                    .replace('{completed_icon}', pacient.completed ? completedIconTemplate : '')
                                    .replace('{uuid}', pacient.summaryPacientUid);

                                $.each(pacient.shifts, function (shift, item) {
                                    bodyTemplate += shiftTemplate
                                        .replace('{shift}', shift);

                                    if (type == 'assistance-medical') {
                                        if (item.medication.length > 0) {
                                            bodyTemplate += medicationTitleTemplate;

                                            $.each(item.medication, function (k, medication) {
                                                bodyTemplate += medicationTemplate
                                                    .replace('{drug}', medication.drug)
                                                    .replace('{dose}', medication.dose)
                                                    .replace('{observations}', medication.observations);
                                                if (k < item.length - 1) {
                                                    bodyTemplate += ', ';
                                                }
                                            });
                                        }

                                        bodyTemplate += medicalMonitoringTitleTemplate;
                                        bodyTemplate += medicalMonitoringAddTemplate
                                            .replace('{uuid}', pacient.uid)
                                            .replace('{date}', item.start);
                                        if (item.monitoring.length > 0) {
                                            var medicalMonitoringTableBody = '';
                                            $.each(item.monitoring, function (l, monitoring) {
                                                medicalMonitoringTableBody += medicalMonitoringTableBodyTemplate
                                                    .replace('{systolicBloodPressure}', monitoring.systolicBloodPressure || '-')
                                                    .replace('{diastolicBloodPressure}', monitoring.diastolicBloodPressure || '-')
                                                    .replace('{heartRate}', monitoring.heartRate || '-')
                                                    .replace('{temperature}', monitoring.temperature || '-')
                                                    .replace('{saturation}', monitoring.saturation || '-')
                                                    .replace('{glucose}', monitoring.glucose || '-')
                                                    .replace('{infusion}', monitoring.infusion || '-');
                                            });
                                            medicalMonitoringTableBody += medicalMonitoringTableBodyEmptyTemplate;
                                            bodyTemplate += medicalMonitoringTableTemplate
                                                .replace('{tbody}', medicalMonitoringTableBody);
                                        }
                                        if (item.samples.length > 0) {
                                            bodyTemplate += sampleTitleTemplate;
                                            $.each(item.samples, function (m, sample) {
                                                bodyTemplate += editSampleTemplate
                                                    .replace('{uuid}', sample.uid)
                                                    .replace('{description}', sample.description)
                                                    .replace('{status}', sample.status);
                                            });
                                        }
                                    }

                                    if (type == 'physical-therapy') {
                                        bodyTemplate += physicalMonitoringTitleTemplate;
                                        bodyTemplate += physicalMonitoringAddTemplate
                                            .replace('{uuid}', pacient.uid)
                                            .replace('{date}', item.start);
                                        if (item.physical.length > 0) {
                                            let procedures = item.physical.map(object => Object.keys(object).filter(key => object[key] == true));
                                            let k = 0;
                                            while (k < procedures.length) {
                                                let l = 0;
                                                while (l < procedures[k].length) {
                                                    bodyTemplate += procedureTemplate
                                                        .replace('{procedure}', proceduresMapping[procedures[k][l]]);
                                                    if (l < procedures[k].length - 1) {
                                                        bodyTemplate += procedureDividerTemplate;
                                                    }
                                                    l++;
                                                }
                                                bodyTemplate += "<br/>";
                                                k++;
                                            }
                                        }
                                    }

                                    if (type == 'orderly') {
                                        bodyTemplate += orderlyMonitoringTitleTemplate;
                                        bodyTemplate += orderlyMonitoringAddTemplate
                                            .replace('{uuid}', pacient.uid)
                                            .replace('{date}', item.start);
                                        if (item.orderly.length > 0) {
                                            let actions = item.orderly.map(object => Object.keys(object).filter(key => object[key] == true));
                                            let k = 0;
                                            while (k < actions.length) {
                                                let l = 0;
                                                while (l < actions[k].length) {
                                                    bodyTemplate += actionTemplate
                                                        .replace('{action}', actionsMapping[actions[k][l]]);
                                                    if (l < actions[k].length - 1) {
                                                        bodyTemplate += actionDividerTemplate;
                                                    }
                                                    l++;
                                                }
                                                bodyTemplate += "<br/>";
                                                k++;
                                            }
                                        }
                                    }

                                    if (type == 'cook') {
                                        bodyTemplate += cookFoodTitleTemplate;
                                        bodyTemplate += cookFoodAddTemplate
                                            .replace('{uuid}', pacient.uid)
                                            .replace('{date}', item.start);
                                        if (item.cook.length > 0) {
                                            $.each(item.cook, function (k, cook) {
                                                bodyTemplate += editCookFoodTemplate
                                                    .replace('{id}', cook.id)
                                                    .replace('{uuid}', pacient.uid)
                                                    .replace('{option}', cook.foodOption)
                                                    .replace('{date}', cook.date)
                                                    .replace('{observations}', cook.observations || '');
                                            });
                                        }
                                    }
                                });
                                bodyTemplate += '<div class="pacient-separator mb-2">&nbsp;</div></div>';
                            });
                        }

                        if (pacientsCount > 0) {
                            var accordionItemTitle = room.room;
                            if (pacientsList.length > 0) {
                                accordionItemTitle += ' - ';
                                accordionItemTitle += pacientsList.join(', ');
                            }
                            dataTemplate += accordionItemTemplate
                                .replaceAll('{accordion_item_id}', 'accordion_item_' + i)
                                .replaceAll('{accordion_item_title}', accordionItemTitle)
                                .replaceAll('{accordion_item_body}', bodyTemplate);
                        }
                    });
                    $('#kt_accordion').append(dataTemplate);

                    if (onClick) {
                        modal.hide();
                    }
                } else {
                    KTSwal.showSwal(response.message, "error");
                }
            },
            error: function () {
                KTSwal.showDefaultErrorSwal();
            },
            complete: function () {
                if (onClick) {
                    // Remove loading indication
                    submitSettingsButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    submitSettingsButton.disabled = false;
                }
            }
        });
    };

    var handleSettingsChange = () => {
        floors = [];
        shifts = [];
        var floorsCheckboxes = document.querySelectorAll('input[name^=floors]:checked');
        var shiftsCheckboxes = document.querySelectorAll('input[name^=shifts]:checked');

        for (var i = 0; i < floorsCheckboxes.length; i++) {
            floors.push(floorsCheckboxes[i].value);
        }
        for (var i = 0; i < shiftsCheckboxes.length; i++) {
            shifts.push(shiftsCheckboxes[i].dataset.label);
        }
    };

    // handle pacient sample form submit 
    var handlePacientSampleFormSubmit = () => {

        const submitButton = document.getElementById('pacient_sample_form_submit');
        submitButton.addEventListener('click', e => {
            e.preventDefault();
            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');
            // Disable button to avoid multiple click 
            submitButton.disabled = true;
            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/sample/' + pacientSampleUuid + '/update',
                method: 'POST',
                dataType: 'json',
                data: $(pacientSampleForm).serialize(),
                success: function (response) {
                    if (response.success) {
                        KTSwal.showSwal(response.message, "success");
                        pacientSampleModal.hide();
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    submitButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    submitButton.disabled = false;
                }
            });

        });

        // Cancel button handler
        const cancelButton = pacientSampleElement.querySelector('[data-kt-edit-pacient-sample-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();
            pacientSampleModal.hide();
        });

        // Close button handler
        const closeButton = pacientSampleElement.querySelector('[data-kt-edit-pacient-sample-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();
            pacientSampleModal.hide();
        });
    };

    // get pacient sample data on modal show
    $('#kt_modal_edit_pacient_sample').on('show.bs.modal', function (e) {
        pacientSampleUuid = $(e.relatedTarget).data('pacient-sample-uuid');
        var pacientSampleFormWrapper = $('#kt_modal_edit_pacient_sample_form_items');

        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/pacient/sample/' + pacientSampleUuid,
            method: 'GET',
            dataType: 'html',
            beforeSend: function () {
                pacientSampleFormWrapper.html('');
            },
            success: function (response) {
                pacientSampleFormWrapper.html(response);
            },
            error: function () {
                KTSwal.showDefaultErrorSwal();
            }
        });
    });

    // get pacient physical data on modal show
    $('#kt_modal_add_pacient_monitoring_physical').on('show.bs.modal', function (e) {
        var pacientPhysicalFormWrapper = $('#kt_modal_add_pacient_physical_form_items');

        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/pacient/physical/template',
            method: 'GET',
            dataType: 'html',
            beforeSend: function () {
                pacientPhysicalFormWrapper.html('');
            },
            success: function (response) {
                pacientPhysicalFormWrapper.html(response);
            },
            error: function () {
                KTSwal.showDefaultErrorSwal();
            }
        });
    });

    // get pacient orderly data on modal show
    $('#kt_modal_add_pacient_monitoring_orderly').on('show.bs.modal', function (e) {
        var pacientOrderlyFormWrapper = $('#kt_modal_add_pacient_orderly_form_items');
        var monitoringDate = $(e.relatedTarget).data('monitoring-date');

        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/pacient/orderly/template',
            method: 'GET',
            dataType: 'html',
            beforeSend: function () {
                pacientOrderlyFormWrapper.html('');
            },
            success: function (response) {
                pacientOrderlyFormWrapper.html(response);
                $('#pacient_monitoring_orderly_form_date').val(monitoringDate);
                KTDatePickers.init();
            },
            error: function () {
                KTSwal.showDefaultErrorSwal();
            }
        });
    });

    return {
        // Public functions
        init: function (uuid, extraDataQuery, type) {
            if (extraDataQuery) {
                handleSettingsAjaxCall(uuid, extraDataQuery, type, false);
            }
            handleNursingHomeLocationChange();
            handleNursingHomeFormSubmit(uuid);
            handleSummarySettingsFormSubmit(uuid, type);
            if (type == 'assistance-medical') {
                handlePacientSampleFormSubmit();
            }
        },
        getEditCookFoodTemplate: function () {
            return editCookFoodTemplate;
        },
        getProcedureDividerTemplate: function () {
            return procedureDividerTemplate;
        },
        getProcedureTemplate: function () {
            return procedureTemplate;
        },
        getProceduresMapping: function () {
            return proceduresMapping;
        },
        getActionsMapping: function () {
            return actionsMapping;
        },
        getActionDividerTemplate: function () {
            return actionDividerTemplate;
        },
        getActionTemplate: function () {
            return actionTemplate;
        },
        getMedicalMonitoringMapping: function () {
            return medicalMonitoringMapping;
        },
        getMedicalMonitoringActionTemplate: function () {
            return medicalMonitoringActionTemplate;
        }
    };
}();

// Class for pacient diagnosis
var KTPacientDiagnosis = function () {
    // Shared variables
    const addPacientMedicationElement = document.getElementById('kt_modal_add_pacient_medication');
    const editPacientModalElement = document.getElementById('kt_modal_edit_pacient_diagnosis');
    var addPacientMedicationModal, addPacientMedicationForm, editPacientDiagnosisForm, editPacientDiagnosisModal,
        diagnosisId, selectedPacientUuid;

    if (typeof (addPacientMedicationElement) != 'undefined' && addPacientMedicationElement != null) {
        addPacientMedicationForm = addPacientMedicationElement.querySelector('#kt_modal_add_pacient_medication_form');
        addPacientMedicationModal = new bootstrap.Modal(addPacientMedicationElement);
    }
    if (typeof (editPacientModalElement) != 'undefined' && editPacientModalElement != null) {
        editPacientDiagnosisForm = editPacientModalElement.querySelector('#kt_modal_edit_pacient_diagnosis_form');
        editPacientDiagnosisModal = new bootstrap.Modal(editPacientModalElement);
    }

    var initAddPacientMedication = (pacientUuid) => {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            addPacientMedicationForm,
            {
                fields: {
                    'diagnosis': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un diagnostic'
                            }
                        }
                    },
                    'drug': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi un medicament'
                            }
                        }
                    },
                    'dose': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi dozajul'
                            }
                        }
                    },
                    'startDate': {
                        validators: {
                            callback: {
                                message: 'Te rugam sa selectezi o data de start',
                                callback: function (input) {
                                    var element = input.element;
                                    var asNecessary = document.getElementById("as_necessary");
                                    return (asNecessary.checked) ? true : (element.value !== '');
                                }
                            }
                        }
                    },
                    'endDate': {
                        validators: {
                            callback: {
                                message: 'Te rugam sa selectezi o data de final',
                                callback: function (input) {
                                    var element = input.element;
                                    var asNecessary = document.getElementById("as_necessary");
                                    return (asNecessary.checked) ? true : (element.value !== '');
                                }
                            }
                        }
                    },
                    'asNecessaryObservations': {
                        validators: {
                            callback: {
                                message: 'Te rugam sa completezi observatiile',
                                callback: function (input) {
                                    var element = input.element;
                                    var asNecessary = document.getElementById("as_necessary");
                                    return (asNecessary.checked) ? (element.value !== '') : true;
                                }
                            }
                        }
                    },
                    hour: {
                        selector: '.fv-hour',
                        validators: {
                            callback: {
                                message: 'Te rugam sa completezi ora',
                                callback: function (input) {
                                    var element = input.element;
                                    return (element.disabled) ? true : (element.value !== '');
                                }
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = addPacientMedicationElement.querySelector('[data-kt-add-pacient-medication-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            if (null === pacientUuid) {
                pacientUuid = selectedPacientUuid;
            }

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;
                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/' + pacientUuid + '/medication/add',
                            method: 'POST',
                            dataType: 'json',
                            data: $(addPacientMedicationForm).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = addPacientMedicationElement.querySelector('[data-kt-add-pacient-medication-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            addPacientMedicationForm.reset(); // Reset form			
            addPacientMedicationModal.hide();
            resetMedicationDetails();
        });

        // Close button handler
        const closeButton = addPacientMedicationElement.querySelector('[data-kt-add-pacient-medication-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();
            addPacientMedicationModal.hide();
            addPacientMedicationForm.reset();
            resetMedicationDetails();
        });
    };

    var initStopPacientMedication = (pacientUuid) => {
        $(document).on('click', '.stop-medication-plan', function () {
            var $this = $(this);
            var pacientMedicationId = $this.data('medication-id');
            Swal.fire({
                text: "Esti sigur ca vrei sa intrerupi aceasta schema de tratament?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/' + pacientUuid + '/medication/' + pacientMedicationId + '/stop',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            $this.attr('disabled', 'disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            $this.removeAttr('disabled');
                        }
                    });
                }
            });
        });
    };

    var initEditPacientDiagnosis = () => {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            editPacientDiagnosisForm,
            {
                fields: {
                    'diagnosis': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un diagnostic'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = editPacientModalElement.querySelector('[data-kt-edit-pacient-diagnosis-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;
                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/diagnosis/' + diagnosisId + '/update',
                            method: 'POST',
                            dataType: 'json',
                            data: $(editPacientDiagnosisForm).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = editPacientModalElement.querySelector('[data-kt-edit-pacient-diagnosis-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();
            editPacientDiagnosisForm.reset(); // Reset form			
            editPacientDiagnosisModal.hide();
        });

        // Close button handler
        const closeButton = editPacientModalElement.querySelector('[data-kt-edit-pacient-diagnosis-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();
            editPacientDiagnosisForm.reset();
            editPacientDiagnosisModal.hide();
        });
    };

    var initPacientDiagnosisForm = (diagnosisId) => {
        $.ajax({
            url: '/dashboard/ajax/pacient/diagnosis/' + diagnosisId + '/form',
            method: 'GET',
            dataType: 'html',
            success: function (response) {
                $('#kt_modal_edit_pacient_diagnosis_scroll').html(response);
            }
        });
    };

    var resetMedicationDetails = () => {
        $('input.form-allow-details').prop('checked', false);
        $('.fv-hour, .fv-observations').prop('disabled', 'disabled');
    };

    var initDeletePacientDiagnosis = () => {
        $(document).on('click', '.delete-diagnosis', function () {
            var $this = $(this);
            var diagnosisId = $this.data('diagnosis-id');
            Swal.fire({
                text: "Esti sigur ca vrei sa stergi acest diagnostic?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/diagnosis/' + diagnosisId + '/delete',
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function () {
                            // Disable button to avoid multiple click 
                            $this.attr('disabled', 'disabled');
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        },
                        complete: function () {
                            // Enable button
                            $this.removeAttr('disabled');
                        }
                    });
                }
            });
        });
    };

    // Init checkbox change on medication
    var initChangeCheckboxes = () => {
        $('input.form-allow-details').change(function () {
            if (this.checked) {
                $(this).closest('.row-allow-details').find('input[type=text]').removeAttr('disabled');
            } else {
                $(this).closest('.row-allow-details').find('input[type=text]').attr('disabled', 'disabled');
            }
        });

        $('#as_necessary').change(function () {
            if (this.checked) {
                $('#scheduled_wrapper').hide();
                $('#as_necessary_wrapper').show();
            } else {
                $('#scheduled_wrapper').show();
                $('#as_necessary_wrapper').hide();
            }

        });
    };

    $('#kt_modal_add_pacient_medication').on('show.bs.modal', function (e) {
        var $relatedTarged = $(e.relatedTarget);
        var medicationId = $relatedTarged.data('medication-id');
        selectedPacientUuid = $relatedTarged.data('pacient-uuid');
        diagnosisId = $relatedTarged.data('diagnosis-id');
        KTUtilities.initDiagnoses(selectedPacientUuid, diagnosisId);
        if (medicationId) {
            $('#medication-id').val(medicationId);
            KTUtilities.initMedicationData(medicationId);
        }
    });

    $('#kt_modal_edit_pacient_diagnosis').on('show.bs.modal', function (e) {
        diagnosisId = $(e.relatedTarget).data('diagnosis-id');
        initPacientDiagnosisForm(diagnosisId);

    });

    return {
        // Public functions
        init: function (pacientUuid) {
            initAddPacientMedication(pacientUuid);
            initStopPacientMedication(pacientUuid);
            initChangeCheckboxes();
            if (null !== pacientUuid) {
                initEditPacientDiagnosis();
                initDeletePacientDiagnosis();
            }
        }
    };
}();

// Class for summary notification
var KTSummaryNotification = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_add_summary_notification');
    var modal, form;
    var $diagnosis = $('#diagnosis');

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_summary_notification_form');
        modal = new bootstrap.Modal(element);
    }

    var initAddSummaryNotification = (pacientUuid) => {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'notificationDate': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi o data'
                            }
                        }
                    },
                    'notificationObservations': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi observatiile'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-add-summary-notification-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/' + pacientUuid + '/summary-notification/add',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-add-summary-notification-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form		
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-add-summary-notification-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();
            modal.hide();  // Reset form
            form.reset();
        });
    };

    // Init get pacient diagnoses
    var initGetPacientDiagnoses = (pacientUuid) => {
        $.ajax({
            url: '/dashboard/ajax/pacient/' + pacientUuid + '/diagnoses',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $.each(response.data, function (key, item) {
                        $diagnosis.append("<option value='" + item.id + "'>" + item.diagnosis + "</option>");
                    });
                }
            }
        });
    };

    return {
        // Public functions
        init: function (pacientUuid) {
            initAddSummaryNotification(pacientUuid);
            initGetPacientDiagnoses(pacientUuid);
        }
    };
}();

var KTNursingHomeRoom = function () {
    // Shared variables
    let modal, form, modalPacients;
    let closeModal, cancelModal, closeButton, cancelButton;

    let modalBody = $('#kt_modal_add_nursing_home_room_scroll');

    const element = document.getElementById('kt_modal_add_nursing_home_room');
    const modalPacient = document.getElementById('kt_modal_add_nursing_home_pacient');

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_nursing_home_room');

        modal = new bootstrap.Modal(element);

        closeButton = element.querySelector('[data-kt-nursing-home-room-modal-action="close"]');
        cancelButton = element.querySelector('[data-kt-nursing-home-room-modal-action="cancel"]');
    }


    if (typeof (modalPacient) != 'undefined' && modalPacient != null) {
        modalPacients = new bootstrap.Modal(modalPacient);

        closeModal = modalPacient.querySelector('[data-kt-nursing-home-pacient-modal-action="close"]');
        cancelModal = modalPacient.querySelector('[data-kt-nursing-home-pacient-modal-action="cancel"]');
    }


    form = document.getElementById('kt_modal_add_nursing_home_room_form');


    // init pacient medication details id
    if (element) {
        modal = new bootstrap.Modal(element);

        element.addEventListener('show.bs.modal', function (e) {
            let nursingUuid = $(e.relatedTarget).data('nursing-home-location-room-uuid');
            let title = $('.modal_title');

            $.ajax({
                url: '/dashboard/ajax/nursing-home/' + nursingUuid + '/room/load-form',
                method: 'GET',
                dataType: 'html',
                beforeSend: function () {
                    modalBody.html('');
                },
                success: function (response) {
                    nursingUuid === 0 ? title.text('Adaugă camera') : title.text('Editează camera');
                    modalBody.html(response);
                    $('.pacient-select').select2({placeholder: "Select an option"});
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    }

    // Init add nursing home location room modal
    var initAddNursingHomeRoom = (nursingUuid) => {
        // Submit button handler
        const submitButton = form.querySelector('[data-kt-nursing-home-room-modal-action="submit"]');

        submitButton.addEventListener('click', e => {
            e.preventDefault();
            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(form, {
                fields: {
                    'nursing_home_room_form[roomNumber]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci numarul camerei'
                            }
                        }
                    },
                    'nursing_home_room_form[floor]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci etajul camerei'
                            }
                        }
                    },
                    'nursing_home_room_form[numberOfBeds]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci numarul de locuri'
                            }
                        }
                    },
                    'nursing_home_room_form[roomType]': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi tipul camerei'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            });

            let uidElement = form.querySelector('[name="nursing_home_room_form[uid]"]');

            let roomUuid = 0;
            if (uidElement) {
                let uuidVal = uidElement.value.trim();

                // Check empty value
                if (uuidVal !== '') {
                    roomUuid = uuidVal;
                }
            }

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/nursing-home/' + nursingUuid + '/room/' + roomUuid + '/actions',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });
                    }
                });
            }
        });

        // Cancel button handler
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form
            modal.hide();
        });

        // Close button handler
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form
            modal.hide();
        });
    };


    var initAddNursingHomePacients = (nursingUuid) => {
        $('.add_pacients').on('click', function (e) {
            e.preventDefault();
            let select = $('#pacient-select');

            // Obține valorile de la data-* din buton
            let roomUuid = $(this).data('nursing-home-location-room-uuid');

            // Trimite cererea AJAX către backend
            $.ajax({
                url: '/dashboard/ajax/nursing-home/' + nursingUuid + '/room/' + roomUuid + '/load-pacients',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    // Golește opțiunile anterioare din select
                    select.empty();

                    // Populează selectul cu pacienții primiți din răspuns
                    $.each(response.rows, function (index, item) {
                        let option = $('<option></option>')
                            .val(item.uid)
                            .text(item.fullName);

                        // Marchează opțiunile care au selected: true
                        if (item.selected == "1") {
                            option.prop('selected', item.selected);
                        }

                        // Adaugă opțiunea la select
                        select.append(option);
                    });

                    // Inițializează Select2 după ce opțiunile sunt adăugate
                    select.select2({
                        maximumSelectionLength: response.numberOfBeds,
                        placeholder: "Selectează un pacient",
                        allowClear: false,
                        multiple: true
                    });

                    modalPacients.show();
                    $('#nursing_room').val(roomUuid);
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });

        $('.submit_pacient').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: '/dashboard/ajax/nursing-home/' + nursingUuid + '/room/add-pacients',
                method: 'POST',
                data: {
                    pacients: $('#pacient-select').val(),
                    nursing_room: $('#nursing_room').val()
                },
                cache: false,
                success: function (response) {
                    modalPacients.hide();
                    KTSwal.showSwal(response.message, response.success ? 'success' : 'error');

                    if (response.success) {
                        setTimeout(function (e) {
                            window.location.reload();
                        }, 800)
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });

        // Cancel button handler
        cancelModal.addEventListener('click', e => {
            e.preventDefault();
            modalPacients.hide();
        });

        // Close button handler
        closeModal.addEventListener('click', e => {
            e.preventDefault();
            modalPacients.hide();
        });
    };

    return {
        // Public functions
        init: function (uuid) {
            initAddNursingHomeRoom(uuid);
            initAddNursingHomePacients(uuid);
        }
    };
}();

var KTPacientRoom = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_assign_pacient_room');
    var form, modal;
    var nursingHomeLocationElement = $('#nursing_home_location');
    var nursingHomeLocationRoomElement = $('#nursing_home_location_room');
    var pacientUuid;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_assign_pacient_room_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add nursing home location room modal
    var initAssignPacientRoom = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'createdAt': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi o data'
                            }
                        }
                    },
                    'location': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi o locatie'
                            }
                        }
                    },
                    'roomNumber': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi numarul camerei'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-assign-pacient-room-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/' + pacientUuid + '/room/assign',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-assign-pacient-room-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-assign-pacient-room-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    var getNursingHomes = () => {
        $.ajax({
            url: '/dashboard/ajax/utilities/nursing-homes',
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                nursingHomeLocationElement.html('');
            },
            success: function (nursingHomeLocations) {
                $.each(nursingHomeLocations, function (key, item) {
                    nursingHomeLocationElement.append("<option value='" + item.uid + "'>" + item.name + "</option>");
                });
                if (nursingHomeLocations.length > 0) {
                    getRooms(nursingHomeLocations[0].uid);
                }
            }
        });
    };

    var getNursingHomeRooms = () => {
        nursingHomeLocationElement.on('change', function () {
            var nursingHomeUuid = $(this).val();
            getRooms(nursingHomeUuid);
        });
    };

    var getRooms = (nursingHomeUuid) => {
        $.ajax({
            url: '/dashboard/ajax/nursing-home/' + nursingHomeUuid + '/rooms',
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                nursingHomeLocationRoomElement.html('');
            },
            success: function (locations) {
                $.each(locations, function (key, item) {
                    nursingHomeLocationRoomElement.append("<option value='" + item.id + "'>" + item.roomNumber + "</option>");
                });
            }
        });
    };

    $('#kt_modal_assign_pacient_room').on('show.bs.modal', function (e) {
        pacientUuid = $(e.relatedTarget).data('pacient-uuid');
    });

    return {
        // Public functions
        init: function () {
            initAssignPacientRoom();
            getNursingHomes();
            getNursingHomeRooms();
        }
    };
}();

var KTDrafts = function () {
    var $form = $('#documents_draft_filter_form');

    // show Swal
    var initHandleMonthChange = function () {
        $('input[name="date"]').change(function () {
            $form.submit();
        });
    };

    return {
        // Public functions
        init: function () {
            initHandleMonthChange();
        }
    };
}();

var KTAddPacientFileDeadline = function () {
    const element = document.getElementById('kt_modal_add_pacient_file_deadline');
    var form, modal, fileUuid;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_pacient_file_deadline_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add pacient file deadline modal
    var initAddPacientFileDeadline = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'uploadDeadline': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci o data'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-pacient-file-deadline-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/file/' + fileUuid + '/deadline',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-pacient-file-deadline-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-pacient-file-deadline-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    // get file uuid & deadline
    $('#kt_modal_add_pacient_file_deadline').on('show.bs.modal', function (e) {
        var $relatedTarget = $(e.relatedTarget);
        fileUuid = $relatedTarget.data('pacient-file-uuid');
        $(e.currentTarget).find('input[name="uploadDeadline"]').val($relatedTarget.data('pacient-file-deadline'));
    });

    return {
        // Public functions
        init: function () {
            initAddPacientFileDeadline();
        }
    };
}();

// Class for editors
var KTDocuments = function () {
    const element = document.getElementById('kt_modal_pacient_document_data');
    var form, modal;
    var documentModalButton = $('#trigger_document_modal');
    var generatePDFButton = $('#generate_pdf_document');
    var textTemplate = `<div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{field_label}</label>
                            <input type="text" name="{field_name}" class="form-control form-control-solid mb-3 mb-lg-0 {field_classes}" value="{field_value}" placeholder="{field_placeholder}" data-pattern="{field_pattern}" />
                        </div>`;
    var textareaTemplate = `<div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{field_label}</label>
                            <textarea name="{field_name}" class="form-control form-control-solid mb-3 mb-lg-0 {field_classes}" placeholder="{field_placeholder}" data-pattern="{field_pattern}" rows="4">{field_value}</textarea>
                        </div>`;
    var entityTemplate = `<div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{field_label}</label>
                            <select id="{field_id}" name="{field_name}" data-name="{field_data_name}" class="form-control form-select form-select-solid form-select-lg mb-3 mb-lg-0 {field_classes}" data-placeholder="{field_placeholder}" data-value="{field_value}" data-pattern="{field_pattern}" data-trigger-id="{field_trigger_id}">
                                <option value="">{field_placeholder}</option>
                            </select>
                        </div>`;
    var editorContent = "";
    var fieldsCount = 0;
    var completedFieldsCount = 0;
    var blankFieldsCount = 0;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_pacient_document_data_form');
        modal = new bootstrap.Modal(element);
    }

    // handle filter form submit 
    var handleFormFilterSubmit = (form, slug) => {
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'nursing-home': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un camin'
                            }
                        }
                    },
                    'pacient': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un pacient'
                            }
                        }
                    },
                    'relation': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa selectezi un apartinator'
                            }
                        }
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        const submitButton = document.getElementById('document_filter_form_submit');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/document/' + slug + '/generate',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            beforeSend: function () {
                                documentModalButton.addClass('invisible');
                                generatePDFButton.addClass('invisible');
                                // init WYSIWYG editor if is not initialized already
                                if (!tinymce.get('kt_docs')) {
                                    initEditor();
                                }
                                // empty content
                                tinymce.activeEditor.setContent('');
                                // add loading indication
                                tinymce.activeEditor.setProgressState(true);
                                fieldsCount = 0;
                                completedFieldsCount = 0;
                                blankFieldsCount = 0;
                            },
                            success: function (response) {
                                if (response.success) {
                                    // generate form
                                    var formTemplate = '';
                                    var data = response.data;

                                    $.each(data, function (className, items) {
                                        $.each(items, function (classKey, item) {
                                            formTemplate += `<h3 class="fw-bold mb-5">${item.label}</h3>`;
                                            var variables = item.variables;
                                            $.each(variables, function (key, variable) {
                                                fieldsCount++;
                                                if (variable.type == 'text' || variable.type == 'textarea') {
                                                    var template = '';

                                                    switch (variable.type) {
                                                        case 'text':
                                                            template = textTemplate;
                                                            break;
                                                        case 'textarea':
                                                            template = textareaTemplate;
                                                            break;
                                                        default:
                                                            template = textTemplate;
                                                            break;
                                                    }

                                                    formTemplate += template
                                                        .replaceAll('{field_label}', variable.label)
                                                        .replaceAll('{field_name}', `fields[${className}][${item.id}][${variable.field}]`)
                                                        .replaceAll('{field_value}', null == variable.value ? '' : variable.value)
                                                        .replaceAll('{field_placeholder}', variable.placeholder)
                                                        .replaceAll('{field_classes}', variable.classes)
                                                        .replaceAll('{field_pattern}', variable.pattern);
                                                    if (variable.value) {
                                                        completedFieldsCount++;
                                                    } else {
                                                        blankFieldsCount++;
                                                    }
                                                } else if (variable.type == 'entity') {
                                                    formTemplate += entityTemplate
                                                        .replaceAll('{field_label}', variable.label)
                                                        .replaceAll('{field_name}', `fields[${className}][${item.id}][${variable.field}]`)
                                                        .replaceAll('{field_data_name}', variable.field)
                                                        .replaceAll('{field_value}', null == variable.value ? '' : variable.value)
                                                        .replaceAll('{field_placeholder}', variable.placeholder)
                                                        .replaceAll('{field_classes}', variable.classes)
                                                        .replaceAll('{field_id}', variable.id)
                                                        .replaceAll('{field_trigger_id}', variable.trigger_id)
                                                        .replaceAll('{field_pattern}', variable.pattern);
                                                    if (variable.value) {
                                                        completedFieldsCount++;
                                                    } else {
                                                        blankFieldsCount++;
                                                    }
                                                }

                                            });
                                        });
                                    });

                                    $('#kt_modal_pacient_document_data_scroll').html(formTemplate);
                                    $('#document_slug').val(slug);
                                    $('#pacient_uuid').val(response.pacientUuid);
                                    documentModalButton.text('Vezi datele - completate (' + completedFieldsCount + ') / necompletate (' + blankFieldsCount + ')').removeClass('invisible');
                                    if (fieldsCount == completedFieldsCount) {
                                        documentModalButton.addClass('invisible');
                                        generatePDFButton.removeClass('invisible');
                                    }
                                    // update WYSISWYG editor content
                                    tinymce.activeEditor.setContent(response.content);
                                    var initWidgetsSlugs = ['contract', 'ancheta-sociala', 'fisa-evaluare-initiala', 'bilet-externare'];
                                    if (initWidgetsSlugs.includes(slug)) {
                                        // init datepickers
                                        $(".datepicker").flatpickr();
                                        // init counties/cities
                                        KTUtilities.initCounties();
                                    }
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                tinymce.activeEditor.setProgressState(false);
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });
    };

    // handle document data form submit 
    var handleFormSubmit = () => {
        const submitButton = element.querySelector('[data-kt-pacient-document-data-modal-action="submit"]');
        var $form = $(form);
        form.addEventListener('submit', e => {
            e.preventDefault();
            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/global-update',
                method: 'POST',
                dataType: 'json',
                data: $form.serialize(),
                beforeSend: function () {
                    // Show loading indication
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    // Disable button to avoid multiple click 
                    submitButton.disabled = true;
                    // Get content from editor
                    editorContent = tinymce.activeEditor.getContent();

                    fieldsCount = 0;
                    completedFieldsCount = 0;
                    blankFieldsCount = 0;
                },
                success: function (response) {
                    if (response.success) {
                        $form.find('.form-control').each(function () {
                            fieldsCount++;
                            var $this = $(this);
                            var pattern = $this.data('pattern');
                            var value = "";
                            if (!$this.is('select')) {
                                value = $this.val();
                            } else {
                                value = $this.find('option:selected').text();
                            }

                            if (value) {
                                editorContent = editorContent.replaceAll(pattern, value);
                                completedFieldsCount++;
                            } else {
                                blankFieldsCount++;
                            }
                        });

                        if (fieldsCount == completedFieldsCount) {
                            documentModalButton.addClass('invisible');
                            generatePDFButton.removeClass('invisible');
                        }

                        tinymce.activeEditor.setContent(editorContent);
                        modal.hide();
                        KTSwal.showSwal(response.message, "success");
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    submitButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    submitButton.disabled = false;
                }
            });
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-pacient-document-data-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-pacient-document-data-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();
            modal.hide();
        });
    };

    // handle generate PDF from editor 
    var handleGeneratePDF = (slug) => {
        const generatePDFButton = document.getElementById('generate_pdf_document');
        var fileName = 'Document';
        var extension = '.pdf';

        switch (slug) {
            case 'gdpr':
                fileName = 'Acord GDPR';
                break;
            case 'contract':
                fileName = 'Contract pentru acordarea de servicii sociale';
                break;
            default:
                break;
        }

        generatePDFButton.addEventListener('click', e => {
            e.preventDefault();
            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pdf/generate',
                method: 'POST',
                cache: false,
                data: {
                    'html': tinymce.activeEditor.getContent(),
                    'name': fileName,
                    'slug': slug
                },
                xhrFields: {
                    responseType: 'blob'
                },
                beforeSend: function () {
                    // Show loading indication
                    generatePDFButton.setAttribute('data-kt-indicator', 'on');
                    // Disable button to avoid multiple click 
                    generatePDFButton.disabled = true;
                },
                success: function (data) {
                    var blob = new Blob([data]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = fileName + extension;
                    link.click();
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    generatePDFButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    generatePDFButton.disabled = false;
                }
            });
        });
    };

    // init editors
    var initEditor = function () {
        tinymce.init({
            selector: "#kt_docs",
            height: "600",
            convert_urls: false
        });
    };

    return {
        // Public functions
        init: function (form, slug) {
            handleFormFilterSubmit(form, slug);
            handleFormSubmit();
            handleGeneratePDF(slug);
        }
    };
}();

// Class for pacient medication details
var KTPacientMedicationDetails = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_update_pacient_medication_details');
    var form, modal, pacientMedicationDetailsId;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_update_pacient_medication_details_form');
        modal = new bootstrap.Modal(element);
    }

    // Init update pacient medication details
    var initUpdatePacientMedicationDetails = () => {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'status': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un status'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-update-pacient-medication-details-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/pacient/medication-details/' + pacientMedicationDetailsId + '/update',
                            method: 'POST',
                            dataType: 'json',
                            data: $(form).serialize(),
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-update-pacient-medication-details-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-update-pacient-medication-details-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // init pacient medication details id
        $('#kt_modal_update_pacient_medication_details').on('show.bs.modal', function (e) {
            pacientMedicationDetailsId = $(e.relatedTarget).data('pacient-medication-details-id');
            var pacientMedicationDetailsFormWrapper = $('#kt_modal_update_pacient_medication_details_form_wrapper');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/medication-details/' + pacientMedicationDetailsId + '/form',
                method: 'GET',
                dataType: 'html',
                beforeSend: function () {
                    pacientMedicationDetailsFormWrapper.html('');
                },
                success: function (response) {
                    pacientMedicationDetailsFormWrapper.html(response);
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    };

    // Init update pacient medication details
    var initUpdateMedicationData = () => {
        $('#btn_mark_complete').on('click', function (e) {
            e.preventDefault();
            let medications = $("input:checkbox[name^=medications]:checked")
                .map(function () {
                    return $(this).val();
                }).toArray();
            if (medications.length) {
                $.ajax({
                    url: '/dashboard/ajax/pacient/medication-details/bulk-update',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        'medications': medications
                    },
                    success: function (response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            KTSwal.showSwal(response.message, "error");
                        }
                    },
                    error: function () {
                        KTSwal.showDefaultErrorSwal();
                    },
                    complete: function () {
                        // Remove loading indication
                        submitButton.removeAttribute('data-kt-indicator');
                        // Enable button
                        submitButton.disabled = false;
                    }
                });
            } else {
                KTSwal.showSwal('Te rugam sa selectezi cel putin un medicament', "error");
            }
        });
    };

    // Public methods
    return {
        init: function () {
            initUpdatePacientMedicationDetails();
            initUpdateMedicationData();
        }
    };
}();

// Class for pacient monitoring medical
var KTPacientMonitoringMedical = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_add_pacient_monitoring_medical');
    var form, modal, pacientUuid, monitoringDate, relatedTarget;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_pacient_monitoring_medical_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add pacient monitoring medical
    var initAddPacientMonitoringMedical = (reload) => {
        // Submit button handler
        const submitButton = element.querySelector('[data-kt-add-pacient-monitoring-medical-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            submitButton.disabled = true;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + pacientUuid + '/monitoring/medical/add',
                method: 'POST',
                dataType: 'json',
                data: $(form).serialize(),
                success: function (response) {
                    if (response.success) {
                        if (reload) {
                            window.location.reload();
                        } else {
                            KTSwal.showSwal(response.message, "success");
                            form.reset(); // Reset form			
                            modal.hide();

                            var medicalMonitoringMapping = KTSummarySettings.getMedicalMonitoringMapping();
                            let actionTemplate = KTSummarySettings.getMedicalMonitoringActionTemplate();
                            let actionDividerTemplate = KTSummarySettings.getActionDividerTemplate();

                            let data = response.data;
                            let template = '';

                            for (let key in data) {
                                if (data.hasOwnProperty(key)) {
                                    template += actionTemplate
                                        .replace('{key}', medicalMonitoringMapping[key])
                                        .replace('{value}', data[key]);
                                    template += actionDividerTemplate;
                                }
                            }

                            var targetWrapper = relatedTarget.parent();
                            $('<br/>').insertAfter(targetWrapper);
                            $(template).insertAfter(targetWrapper);
                        }
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    submitButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    submitButton.disabled = false;
                }
            });
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-add-pacient-monitoring-medical-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-add-pacient-monitoring-medical-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // init pacient medication details id
        $('#kt_modal_add_pacient_monitoring_medical').on('show.bs.modal', function (e) {
            relatedTarget = $(e.relatedTarget);
            pacientUuid = relatedTarget.data('pacient-uuid');
            monitoringDate = relatedTarget.data('monitoring-date');
            $('#monitoring_date').val(monitoringDate);
        });
    };

    // Public methods
    return {
        init: function (reload) {
            initAddPacientMonitoringMedical(reload);
        }
    };
}();

// Class for pacient monitoring physical
var KTPacientMonitoringPhysical = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_add_pacient_monitoring_physical');
    var form, modal, pacientUuid, monitoringDate, relatedTarget;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_pacient_monitoring_physical_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add pacient physical medical
    var initAddPacientMonitoringPhysical = (reload, createdAt) => {
        // Submit button handler
        const submitButton = element.querySelector('[data-kt-add-pacient-monitoring-physical-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            submitButton.disabled = true;

            // Append createdAt
            document.querySelector('input[name="summary_created_at"]').value = createdAt;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + pacientUuid + '/monitoring/physical/add',
                method: 'POST',
                dataType: 'json',
                data: $(form).serialize(),
                success: function (response) {
                    if (response.success) {
                        if (reload) {
                            window.location.reload();
                        } else {
                            KTSwal.showSwal(response.message, "success");
                            form.reset(); // Reset form			
                            modal.hide();

                            let data = response.data;
                            let procedures = Object.keys(data).filter(key => data[key]);
                            let i = 0;
                            let procedureTemplate = KTSummarySettings.getProcedureTemplate();
                            let proceduresMapping = KTSummarySettings.getProceduresMapping();
                            let procedureDividerTemplate = KTSummarySettings.getProcedureDividerTemplate();
                            let template = '';
                            while (i < procedures.length) {
                                template += procedureTemplate
                                    .replace('{procedure}', proceduresMapping[procedures[i]]);
                                if (i < procedures.length - 1) {
                                    template += procedureDividerTemplate;
                                }
                                i++;
                            }

                            var targetWrapper = relatedTarget.parent();
                            $('<br/>').insertAfter(targetWrapper);
                            $(template).insertAfter(targetWrapper);
                        }
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    submitButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    submitButton.disabled = false;
                }
            });
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-add-pacient-monitoring-physical-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-add-pacient-monitoring-physical-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // init pacient physical details id
        $('#kt_modal_add_pacient_monitoring_physical').on('show.bs.modal', function (e) {
            relatedTarget = $(e.relatedTarget);
            pacientUuid = relatedTarget.data('pacient-uuid');
            monitoringDate = relatedTarget.data('monitoring-date');
            $('#monitoring_date').val(monitoringDate);
        });
    };

    // Public methods
    return {
        init: function (reload, createdAt) {
            initAddPacientMonitoringPhysical(reload, createdAt);
        }
    };
}();

// Class for pacient monitoring orderly
var KTPacientMonitoringOrderly = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_add_pacient_monitoring_orderly');
    var form, modal, pacientUuid, relatedTarget;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_pacient_monitoring_orderly_form');
        modal = new bootstrap.Modal(element);
    }

    // Init add pacient monitoring orderly
    var initAddPacientMonitoringOrderly = (reload) => {
        // Submit button handler
        const submitButton = element.querySelector('[data-kt-add-pacient-monitoring-orderly-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            submitButton.disabled = true;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + pacientUuid + '/monitoring/orderly/add',
                method: 'POST',
                dataType: 'json',
                data: $(form).serialize(),
                success: function (response) {
                    if (response.success) {
                        if (reload) {
                            window.location.reload();
                        } else {
                            KTSwal.showSwal(response.message, "success");
                            form.reset(); // Reset form			
                            modal.hide();

                            let data = response.data;
                            let actions = Object.keys(data).filter(key => data[key]);
                            let i = 0;
                            let actionTemplate = KTSummarySettings.getActionTemplate();
                            let actionsMapping = KTSummarySettings.getActionsMapping();
                            let actionDividerTemplate = KTSummarySettings.getActionDividerTemplate();
                            let template = '';
                            while (i < actions.length) {
                                template += actionTemplate
                                    .replace('{action}', actionsMapping[actions[i]]);
                                if (i < actions.length - 1) {
                                    template += actionDividerTemplate;
                                }
                                i++;
                            }

                            var targetWrapper = relatedTarget.parent();
                            $('<br/>').insertAfter(targetWrapper);
                            $(template).insertAfter(targetWrapper);
                        }
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    submitButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    submitButton.disabled = false;
                }
            });
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-add-pacient-monitoring-orderly-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-add-pacient-monitoring-orderly-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // init pacient orderly details id
        $('#kt_modal_add_pacient_monitoring_orderly').on('show.bs.modal', function (e) {
            relatedTarget = $(e.relatedTarget);
            pacientUuid = relatedTarget.data('pacient-uuid');
        });
    };

    // Public methods
    return {
        init: function (reload) {
            initAddPacientMonitoringOrderly(reload);
        }
    };
}();

// Class for cook food
var KTPacientCookFood = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_pacient_cook_food');
    var form, modal, pacientUuid, cookFoodId;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_pacient_cook_food_form');
        modal = new bootstrap.Modal(element);
    }

    // Init handle pacient cook food form submit
    var handlePacientCookFoodSubmit = () => {
        // Submit button handler
        const submitButton = element.querySelector('[data-kt-pacient-cook-food-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click 
            submitButton.disabled = true;

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/pacient/' + pacientUuid + '/cook/food/' + cookFoodId,
                method: 'POST',
                dataType: 'json',
                data: $(form).serialize(),
                success: function (response) {
                    if (response.success) {
                        KTSwal.showSwal(response.message, "success");
                        form.reset(); // Reset form			
                        modal.hide();

                        if (cookFoodId == 'new') {
                            var data = response.data;
                            var editCookFoodTemplate = KTSummarySettings.getEditCookFoodTemplate();
                            var template = editCookFoodTemplate
                                .replace('{id}', data.id)
                                .replace('{uuid}', pacientUuid)
                                .replace('{option}', data.foodOption)
                                .replace('{date}', data.date)
                                .replace('{observations}', data.observations || '');
                            var pacientWrapper = $('#' + pacientUuid);
                            pacientWrapper.find('.pacient-separator').remove();
                            pacientWrapper.append(template);
                            pacientWrapper.append('<div class="pacient-separator mb-2">&nbsp;</div>');
                        }
                    } else {
                        KTSwal.showSwal(response.message, "error");
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    // Remove loading indication
                    submitButton.removeAttribute('data-kt-indicator');
                    // Enable button
                    submitButton.disabled = false;
                }
            });
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-pacient-cook-food-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-pacient-cook-food-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // get pacient data on modal show
        $('#kt_modal_pacient_cook_food').on('show.bs.modal', function (e) {
            pacientUuid = $(e.relatedTarget).data('pacient-uuid');
            cookFoodId = $(e.relatedTarget).data('cook-food-id');
            var cookFoodFormWrapper = $('#kt_modal_pacient_cook_food_items');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/form/cook/food/' + cookFoodId,
                method: 'GET',
                dataType: 'html',
                beforeSend: function () {
                    cookFoodFormWrapper.html('');
                },
                success: function (response) {
                    cookFoodFormWrapper.html(response);
                    $(".datepicker").flatpickr();
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                }
            });
        });
    };

    // Public methods
    return {
        init: function () {
            handlePacientCookFoodSubmit();
        }
    };
}();

// Class definition
var KTAppCalendar = function () {
    // Shared variables
    // Calendar variables
    var calendar;
    var data = {
        id: '',
        userUuid: '',
        eventName: '',
        eventDescription: '',
        eventLocation: '',
        startDate: '',
        endDate: '',
        type: 'relation',
        color: ''
    };
    var $location = $('#visit_location');

    // Search variables
    var searchElement;
    var resultsElement;
    var wrapperElement;
    var emptyElement;
    var searchObject;
    var $searchResultsWrapper = $('#kt_modal_pacients_search_results_wrapper');

    // Add event variables
    var location = $location.val();
    var eventName;
    var eventPacientUuid;
    var eventDescription;
    var eventLocation;
    var eventType;
    var startDatepicker;
    var startFlatpickr;
    var endDatepicker;
    var endFlatpickr;
    var startTimepicker;
    var startTimeFlatpickr;
    var endTimepicker;
    var endTimeFlatpickr;
    var modal;
    var modalTitle;
    var form;
    var validator;
    var addButtons;
    var addType;
    var submitButton;
    var cancelButton;
    var closeButton;
    var action = '';

    // View event variables
    var viewEventName;
    var viewEventDescription;
    var viewEventLocation;
    var viewStartDate;
    var viewEndDate;
    var viewModal;
    var viewEditButton;
    var viewDeleteButton;

    // Private functions
    var initCalendarApp = function () {
        // Define variables
        var calendarEl = document.getElementById('kt_calendar_app');
        var todayDate = moment().startOf('day');
        var YM = todayDate.format('YYYY-MM');
        var TODAY = todayDate.format('YYYY-MM-DD');

        // Init calendar --- more info: https://fullcalendar.io/docs/initialize-globals
        calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            initialDate: TODAY,
            navLinks: true, // can click day/week names to navigate views
            selectable: true,
            selectMirror: true,

            // Select dates action --- more info: https://fullcalendar.io/docs/select-callback
            select: function (arg) {
                formatArgs(arg);
                handleNewEvent();
            },

            // Click event --- more info: https://fullcalendar.io/docs/eventClick
            eventClick: function (arg) {
                formatArgs({
                    id: arg.event.id,
                    userUuid: arg.event.extendedProps.userUuid,
                    title: arg.event.title,
                    description: arg.event.extendedProps.description,
                    location: arg.event.extendedProps.location,
                    startStr: arg.event.startStr,
                    endStr: arg.event.endStr,
                    color: arg.event.backgroundColor
                });

                handleViewEvent();
            },

            editable: true,
            dayMaxEvents: true, // allow "more" link when too many events
            eventSources: [
                // your event source
                {
                    url: '/dashboard/ajax/visits/calendar',
                    extraParams: function () { // a function that returns an object
                        return {
                            location: location
                        };
                    }
                }
            ],
            // Handle changing calendar views --- more info: https://fullcalendar.io/docs/datesSet
            datesSet: function () {
                // do some stuff
            }
        });

        calendar.render();
    };

    // Init validator
    const initValidator = () => {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'search': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa adaugi un pacient'
                            }
                        }
                    },
                    'calendar_event_start_date': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi data de start'
                            }
                        }
                    },
                    'calendar_event_end_date': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa completezi data de final'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );
    };

    // Initialize datepickers --- more info: https://flatpickr.js.org/
    const initDatepickers = () => {
        startFlatpickr = flatpickr(startDatepicker, {
            enableTime: false,
            dateFormat: "Y-m-d"
        });

        endFlatpickr = flatpickr(endDatepicker, {
            enableTime: false,
            dateFormat: "Y-m-d"
        });

        startTimeFlatpickr = flatpickr(startTimepicker, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i"
        });

        endTimeFlatpickr = flatpickr(endTimepicker, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i"
        });
    };

    // Handle add button
    const handleAddButton = () => {
        addButtons.forEach(function (elem) {
            elem.addEventListener('click', e => {
                addType = elem.dataset.ktVisitType;

                // Reset form data
                data = {
                    id: '',
                    userUuid: '',
                    eventName: '',
                    eventDescription: '',
                    startDate: new Date(),
                    endDate: new Date(),
                    type: addType
                };
                handleNewEvent();
            });
        });
    };

    const handleAjaxAddEvent = () => {
        // Handle submit form
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
            e.preventDefault();

            if (action !== 'add') {
                return;
            }

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Merge date & time
                        var startDateTime = moment(startFlatpickr.selectedDates[0]).format();
                        var endDateTime = moment(endFlatpickr.selectedDates[endFlatpickr.selectedDates.length - 1]).format();
                        const startDate = moment(startFlatpickr.selectedDates[0]).format('YYYY-MM-DD');
                        const endDate = startDate;
                        const startTime = moment(startTimeFlatpickr.selectedDates[0]).format('HH:mm:ss');
                        const endTime = moment(endTimeFlatpickr.selectedDates[0]).format('HH:mm:ss');

                        startDateTime = startDate + 'T' + startTime;
                        endDateTime = endDate + 'T' + endTime;

                        $.ajax({
                            url: '/dashboard/ajax/visits/calendar/add',
                            method: 'POST',
                            data: {
                                'pacientUuid': eventPacientUuid.value,
                                'locationUuid': location,
                                'observations': eventDescription.value,
                                'startDate': startDateTime,
                                'endDate': endDateTime,
                                'type': eventType.value
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                // Show loading indication
                                submitButton.setAttribute('data-kt-indicator', 'on');

                                // Disable submit button whilst loading
                                submitButton.disabled = true;
                            },
                            success: function (response) {
                                if (response.success) {
                                    modal.hide();

                                    // Add new event to calendar
                                    calendar.addEvent({
                                        id: response.uuid,
                                        userUuid: eventPacientUuid.value,
                                        title: eventName.value,
                                        description: eventDescription.value,
                                        location: eventLocation.value,
                                        start: startDateTime,
                                        end: endDateTime,
                                        allDay: false,
                                        color: addType === 'medical' ? 'gray' : 'blue'
                                    });
                                    calendar.render();

                                    // Reset form
                                    form.reset();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });
                    } else {
                        // Show popup warning 
                        Swal.fire({
                            text: "Te rugam sa completezi corect toate datele",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });
            }
        });
    };

    const handleAjaxEditEvent = () => {
        // Handle submit form
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
            e.preventDefault();

            if (action !== 'edit') {
                return;
            }

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Merge date & time
                        var startDateTime = moment(startFlatpickr.selectedDates[0]).format();
                        var endDateTime = moment(endFlatpickr.selectedDates[endFlatpickr.selectedDates.length - 1]).format();
                        const startDate = moment(startFlatpickr.selectedDates[0]).format('YYYY-MM-DD');
                        const endDate = startDate;
                        const startTime = moment(startTimeFlatpickr.selectedDates[0]).format('HH:mm:ss');
                        const endTime = moment(endTimeFlatpickr.selectedDates[0]).format('HH:mm:ss');

                        startDateTime = startDate + 'T' + startTime;
                        endDateTime = endDate + 'T' + endTime;

                        $.ajax({
                            url: '/dashboard/ajax/visits/calendar/' + data.id + '/edit',
                            method: 'POST',
                            data: {
                                'pacientUuid': eventPacientUuid.value,
                                'observations': eventDescription.value,
                                'startDate': startDateTime,
                                'endDate': endDateTime
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                // Show loading indication
                                submitButton.setAttribute('data-kt-indicator', 'on');

                                // Disable submit button whilst loading
                                submitButton.disabled = true;
                            },
                            success: function (response) {
                                if (response.success) {
                                    modal.hide();

                                    // Remove old event
                                    var oldEvent = calendar.getEventById(data.id);
                                    var oldEventColor = oldEventColor.backgroundColor;
                                    oldEvent.remove();

                                    // Add new event to calendar
                                    calendar.addEvent({
                                        id: response.uuid,
                                        userUuid: eventPacientUuid.value,
                                        title: eventName.value,
                                        description: eventDescription.value,
                                        location: eventLocation.value,
                                        start: startDateTime,
                                        end: endDateTime,
                                        allDay: false,
                                        color: oldEventColor
                                    });
                                    calendar.render();

                                    // Reset form
                                    form.reset();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });
                    } else {
                        // Show popup warning 
                        Swal.fire({
                            text: "Te rugam sa completezi corect toate datele",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });
            }
        });
    };

    // Handle add new event
    const handleNewEvent = () => {
        if (window.notAssistanceMedical) {
            // Update modal title
            modalTitle.innerText = "Adauga vizita";

            modal.show();

            populateForm(data);

            action = 'add';
        }
    };

    // Handle edit event
    const handleEditEvent = () => {
        // Update modal title
        modalTitle.innerText = "Editeaza vizita";

        modal.show();

        populateForm(data);

        action = 'edit';
    };

    // Handle view event
    const handleViewEvent = () => {
        var editable = ['blue'];
        if (editable.indexOf(data.color) > -1) {
            viewEditButton.style.display = 'flex';
            viewDeleteButton.style.display = 'flex';
        } else {
            viewEditButton.style.display = 'none';
            viewDeleteButton.style.display = 'none';
        }

        viewModal.show();

        // Detect all day event
        var startDateMod;
        var endDateMod;

        // Generate labels
        startDateMod = moment(data.startDate).format('Do MMM, YYYY - h:mm a');
        endDateMod = moment(data.endDate).format('Do MMM, YYYY - h:mm a');

        // Populate view data
        viewEventName.innerText = data.eventName;
        viewEventDescription.innerText = data.eventDescription ? data.eventDescription : '--';
        viewEventLocation.innerText = data.eventLocation ? data.eventLocation : '--';
        viewStartDate.innerText = startDateMod;
        viewEndDate.innerText = endDateMod;
    };

    // Handle delete event
    const handleDeleteEvent = () => {
        viewDeleteButton.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: "Esti sigur ca doresti sa stergi aceasta vizita?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: '/dashboard/ajax/visits/calendar/' + data.id + '/delete',
                        method: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                // Remove event
                                calendar.getEventById(data.id).remove();
                                // Hide modal	
                                viewModal.hide();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        }
                    });
                }
            });
        });
    };

    // Handle edit button
    const handleEditButton = () => {
        viewEditButton.addEventListener('click', e => {
            e.preventDefault();

            viewModal.hide();
            handleEditEvent();
        });
    };

    // Handle cancel button
    const handleCancelButton = () => {
        // Edit event modal cancel button
        cancelButton.addEventListener('click', function (e) {
            e.preventDefault();

            form.reset(); // Reset form	
            modal.hide(); // Hide modal			
        });
    };

    // Handle close button
    const handleCloseButton = () => {
        // Edit event modal close button
        closeButton.addEventListener('click', function (e) {
            e.preventDefault();

            form.reset(); // Reset form	
            modal.hide(); // Hide modal		
        });
    };

    // Reset form validator on modal close
    const resetFormValidator = (element) => {
        // Target modal hidden event --- For more info: https://getbootstrap.com/docs/5.0/components/modal/#events
        element.addEventListener('hidden.bs.modal', e => {
            if (validator) {
                // Reset form validator. For more info: https://formvalidation.io/guide/api/reset-form
                validator.resetForm(true);
            }
        });
    };

    // Populate form 
    const populateForm = () => {
        eventName.value = data.eventName ? data.eventName : '';
        eventPacientUuid.value = data.userUuid ? data.userUuid : '';
        eventDescription.value = data.eventDescription ? data.eventDescription : '';
        eventLocation.value = data.eventLocation ? data.eventLocation : $("#visit_location option:selected").text();
        eventType.value = data.type;
        startFlatpickr.setDate(data.startDate, true, 'Y-m-d');

        // Handle null end dates
        const endDate = data.endDate ? data.endDate : moment(data.startDate).format();
        endFlatpickr.setDate(endDate, true, 'Y-m-d');

        startTimeFlatpickr.setDate(data.startDate, true, 'Y-m-d H:i');
        endTimeFlatpickr.setDate(data.endDate, true, 'Y-m-d H:i');
        endFlatpickr.setDate(data.startDate, true, 'Y-m-d');

    };

    // Format FullCalendar reponses
    const formatArgs = (res) => {
        data.id = res.id;
        data.userUuid = res.userUuid;
        data.eventName = res.title;
        data.eventDescription = res.description;
        data.eventLocation = res.location;
        data.startDate = res.startStr;
        data.endDate = res.endStr;
        data.color = res.color;
    };

    var handleFilterChange = function () {
        $('#visit_date').on('change', function () {
            var date = $(this).val();
            calendar.changeView('timeGridDay', date);
        });
        $('#visit_location').on('change', function () {
            location = $(this).val();
            calendar.refetchEvents();
        });
    };

    var processs = function (search) {
        $.ajax({
            url: '/dashboard/ajax/pacients/search',
            method: 'GET',
            data: {'q': searchObject.getQuery(), 'status': 'internat'},
            dataType: 'json',
            beforeSend: function () {
                $searchResultsWrapper.html('');
            },
            success: function (users) {
                if (users.length) {
                    $.each(users, function (key, user) {
                        $searchResultsWrapper.append(`
                                <!--begin::User-->
                                <div class="rounded d-flex flex-stack bg-active-lighten p-4">
                                    <!--begin::Details-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-35px symbol-circle">
                                            <img alt="Pic" src="${user.photo}" />
                                        </div>
                                        <!--end::Avatar-->
                                        <!--begin::Details-->
                                        <div class="ms-5">
                                            <span class="fs-5 fw-bold text-gray-900 mb-2">${user.name}</span>
                                            <div class="fw-semibold text-muted">${user.email}</div>
                                        </div>
                                        <!--end::Details-->
                                    </div>
                                    <!--end::Details-->
                                    <!--begin::Access menu-->
                                    <div class="ms-2 w-100px">
                                        <button type="button" class="btn btn-primary add-pacient" data-user-uuid="${user.uid}" data-user-name="${user.name}">Adauga</button>
                                    </div>
                                    <!--end::Access menu-->
                                </div>
                                <!--end::User-->`
                        );
                        if (key < users.length - 1) {
                            $searchResultsWrapper.append('<div class="border-bottom border-gray-300 border-bottom-dashed"></div>');
                        }
                    });

                    // Show results
                    resultsElement.classList.remove('d-none');
                    // Hide empty message 
                    emptyElement.classList.add('d-none');
                } else {
                    // Hide results
                    resultsElement.classList.add('d-none');
                    // Show empty message 
                    emptyElement.classList.remove('d-none');
                }

                // Complete search
                search.complete();
            },
            error: function () {
                // Hide results
                resultsElement.classList.add('d-none');
                // Show empty message 
                emptyElement.classList.remove('d-none');
                // Complete search
                search.complete();
            }
        });
    };

    var clear = function (search) {
        // Hide results
        resultsElement.classList.add('d-none');
        // Hide empty message 
        emptyElement.classList.add('d-none');
    };

    var addPacient = function () {
        $(document).on('click', '.add-pacient', function (e) {
            e.preventDefault();
            eventPacientUuid.value = $(this).data('user-uuid');
            eventName.value = $(this).data('user-name');
            // Hide results
            resultsElement.classList.add('d-none');
        });
    };

    return {
        // Public Functions
        init: function () {
            // Define variables
            // Add event modal
            const element = document.getElementById('kt_modal_add_event');
            form = element.querySelector('#kt_modal_add_event_form');
            eventName = form.querySelector('[name="search"]');
            eventPacientUuid = form.querySelector('[name="calendar_event_pacient_uuid"]');
            eventDescription = form.querySelector('[name="calendar_event_description"]');
            eventLocation = form.querySelector('[name="calendar_event_location"]');
            eventType = form.querySelector('[name="calendar_event_type"]');
            startDatepicker = form.querySelector('#kt_calendar_datepicker_start_date');
            endDatepicker = form.querySelector('#kt_calendar_datepicker_end_date');
            startTimepicker = form.querySelector('#kt_calendar_datepicker_start_time');
            endTimepicker = form.querySelector('#kt_calendar_datepicker_end_time');
            addButtons = document.querySelectorAll('[data-kt-calendar="add"]');
            submitButton = form.querySelector('#kt_modal_add_event_submit');
            cancelButton = form.querySelector('#kt_modal_add_event_cancel');
            closeButton = element.querySelector('#kt_modal_add_event_close');
            modalTitle = form.querySelector('[data-kt-calendar="title"]');
            modal = new bootstrap.Modal(element);

            // View event modal
            const viewElement = document.getElementById('kt_modal_view_event');
            viewModal = new bootstrap.Modal(viewElement);
            viewEventName = viewElement.querySelector('[data-kt-calendar="event_name"]');
            viewEventDescription = viewElement.querySelector('[data-kt-calendar="event_description"]');
            viewEventLocation = viewElement.querySelector('[data-kt-calendar="event_location"]');
            viewStartDate = viewElement.querySelector('[data-kt-calendar="event_start_date"]');
            viewEndDate = viewElement.querySelector('[data-kt-calendar="event_end_date"]');
            viewEditButton = viewElement.querySelector('#kt_modal_view_event_edit');
            viewDeleteButton = viewElement.querySelector('#kt_modal_view_event_delete');

            // Elements
            searchElement = document.querySelector('#kt_modal_pacients_search_handler');
            wrapperElement = searchElement.querySelector('[data-kt-search-element="wrapper"]');
            resultsElement = searchElement.querySelector('[data-kt-search-element="results"]');
            emptyElement = searchElement.querySelector('[data-kt-search-element="empty"]');

            // Initialize search handler
            searchObject = new KTSearch(searchElement);

            // Search handler
            searchObject.on('kt.search.process', processs);

            // Clear handler
            searchObject.on('kt.search.clear', clear);
            addPacient();

            initCalendarApp();
            initValidator();
            initDatepickers();
            handleAjaxAddEvent();
            handleAjaxEditEvent();
            handleEditButton();
            handleAddButton();
            handleDeleteEvent();
            handleCancelButton();
            handleCloseButton();
            resetFormValidator(element);
            handleFilterChange();
        }
    };
}();

// Class for reports
var KTReports = function () {
    // Init change pacient status
    var initArchivePacient = () => {
        // Delete button handler
        $(document).on('click', "[data-kt-pacient='delete']", function (e) {
            e.preventDefault();
            var button = $(this);
            var pacientUuid = button.data('pacient-uuid');

            Swal.fire({
                text: "Esti sigur ca vrei sa arhivezi acest pacient?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Da",
                cancelButtonText: "Nu",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    // send AJAX call to backend
                    $.ajax({
                        url: '/dashboard/ajax/pacient/' + pacientUuid + '/archive',
                        method: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                KTSwal.showSwal(response.message, "error");
                            }
                        },
                        error: function () {
                            KTSwal.showDefaultErrorSwal();
                        }
                    });
                }
            });
        });
    };

    // Public methods
    return {
        init: function () {
            initArchivePacient();
        }
    };
}();


// NPS
var KTNps = function () {
    // Shared variables
    const element = document.getElementById('kt_modal_add_nps');
    var form, modal, userUuid, pacientUuid;

    if (typeof (element) != 'undefined' && element != null) {
        form = element.querySelector('#kt_modal_add_nps_form');
        modal = new bootstrap.Modal(element);
    }

    var initLoadNpsTemplate = () => {
        $('#kt_modal_add_nps').on('show.bs.modal', function (e) {
            userUuid = $(e.relatedTarget).data('user-uuid');
            pacientUuid = $(e.relatedTarget).data('pacient-uuid');

            // send AJAX call to backend
            $.ajax({
                url: '/dashboard/ajax/nps/template?userUuid=' + userUuid + '&pacientUuid=' + pacientUuid,
                method: 'GET',
                dataType: 'json',
                beforeSend: function () {
                    if (!tinymce.get('message')) {
                        initEditor();
                    }
                    tinymce.activeEditor.setContent('');
                    // add loading indication
                    tinymce.activeEditor.setProgressState(true);
                    $('#emails').html('');
                },
                success: function (response) {
                    if (response.success) {
                        tinymce.activeEditor.setContent(response.content);
                        $('#recipient').val(response.recipient);
                        $('#emails').html(response.emails);
                    } else {
                        KTSwal.showDefaultErrorSwal();
                    }
                },
                error: function () {
                    KTSwal.showDefaultErrorSwal();
                },
                complete: function () {
                    tinymce.activeEditor.setProgressState(false);
                }
            });
        });
    };

    // Init add nps modal
    var initAddNps = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'subject': {
                        validators: {
                            notEmpty: {
                                message: 'Te rugam sa introduci un subiect'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-nps-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // send AJAX call to backend
                        $.ajax({
                            url: '/dashboard/ajax/nps/add',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                'subject': $('#subject').val(),
                                'message': tinymce.activeEditor.getContent(),
                                'userUuid': userUuid,
                                'pacientUuid': pacientUuid
                            },
                            success: function (response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    KTSwal.showSwal(response.message, "error");
                                }
                            },
                            error: function () {
                                KTSwal.showDefaultErrorSwal();
                            },
                            complete: function () {
                                // Remove loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                // Enable button
                                submitButton.disabled = false;
                            }
                        });

                    } else {
                        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        KTSwal.showSwal("Datele introduse nu sunt valide, te rugam sa incerci din nou", "error");
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = element.querySelector('[data-kt-nps-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });

        // Close button handler
        const closeButton = element.querySelector('[data-kt-nps-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            form.reset(); // Reset form			
            modal.hide();
        });
    };

    // init editors
    var initEditor = function () {
        tinymce.init({
            selector: "#message",
            height: "400",
            convert_urls: false
        });
    };

    return {
        // Public functions
        init: function () {
            initAddNps();
            initLoadNpsTemplate();
        }
    };
}();

// Class for pacient overview charts
var KTPacientOverviewCharts = function () {

    var initLineChart = function (data, containerId) {
        var ctx = document.getElementById(containerId);
        // Define fonts
        var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');
        // Chart config
        const config = {
            type: 'line',
            data: data,
            options: {
                plugins: {
                    title: {
                        display: false
                    }
                },
                responsive: true
            },
            defaults: {
                global: {
                    defaultFont: fontFamily
                }
            }
        };

        // Init ChartJS -- for more info, please visit: https://www.chartjs.org/docs/latest/
        var chart = new Chart(ctx, config);
    };

    var initBarChart = function (data, containerId) {
        var ctx = document.getElementById(containerId);
        // Define fonts
        var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');
        // Chart config
        const config = {
            type: 'bar',
            data: data,
            options: {
                plugins: {
                    title: {
                        display: false
                    }
                },
                responsive: true,
                interaction: {
                    intersect: false
                },
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true
                    }
                }
            },
            defaults: {
                global: {
                    defaultFont: fontFamily
                }
            }
        };

        // Init ChartJS -- for more info, please visit: https://www.chartjs.org/docs/latest/
        var chart = new Chart(ctx, config);
    };

    return {
        // Public functions
        init: function (uuid) {
            $.getJSON("/dashboard/ajax/pacient/" + uuid + "/chart/blood-pressure", function (response) {
                if (response.success) {
                    initLineChart(response.data, 'kt_chartjs_blood_pressure');
                }
            });
            $.getJSON("/dashboard/ajax/pacient/" + uuid + "/chart/saturation", function (response) {
                if (response.success) {
                    initBarChart(response.data, 'kt_chartjs_saturation');
                }
            });
            $.getJSON("/dashboard/ajax/pacient/" + uuid + "/chart/heart-rate", function (response) {
                if (response.success) {
                    initBarChart(response.data, 'kt_chartjs_heart_rate');
                }
            });
            $.getJSON("/dashboard/ajax/pacient/" + uuid + "/chart/glucose", function (response) {
                if (response.success) {
                    initBarChart(response.data, 'kt_chartjs_glucose');
                }
            });
        }
    };
}();

// Class for toasts
var KTToasts = function () {
    // shared variables
    const toastContainer = document.getElementById('kt_toast_stack_container');
    const targetElement = document.querySelector('[data-kt-toast="stack"]');

    // Remove base element markup
    if (typeof (targetElement) != 'undefined' && targetElement != null) {
        targetElement.parentNode.removeChild(targetElement);
    }

    var appendToast = function (className, text) {
        const newToast = targetElement.cloneNode(true);
        newToast.classList.add(className);
        const toastBody = newToast.getElementsByClassName('toast-body')[0];
        toastBody.innerText = text;
        toastContainer.append(newToast);
        const toast = bootstrap.Toast.getOrCreateInstance(newToast);
        toast.show();
    };

    return {
        // Public functions
        appendToast: function (className, text) {
            appendToast(className, text);
        }
    };
}();

// Embedding form collections
var KTEmbeddedCollection = function () {

    var initEvents = function () {
        document.querySelectorAll('.add_item_link').forEach(btn => {
            btn.addEventListener("click", addFormToCollection);
        });
        document.querySelectorAll('div.items .item').forEach((item) => {
            addItemFormDeleteLink(item);
        });
    };

    // Private functions
    var addFormToCollection = function (e) {
        const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);
        const item = document.createElement('div');

        item.innerHTML = collectionHolder.dataset.prototype.replace(/__name__/g, collectionHolder.dataset.index);
        item.classList.add('item');

        collectionHolder.appendChild(item);
        collectionHolder.dataset.index++;
        addItemFormDeleteLink(item);
    };

    var addItemFormDeleteLink = (item) => {
        const removeFormButton = document.createElement('button');
        removeFormButton.innerText = 'Sterge';
        removeFormButton.classList.add('btn', 'btn-danger', 'my-3');
        item.append(removeFormButton);

        removeFormButton.addEventListener('click', (e) => {
            e.preventDefault();
            item.remove();
        });
    };

    // Public methods
    return {
        init: function () {
            initEvents();
        }
    };
}();

// Utilities
var KTUtilities = function () {
    var nursingHomesElement = $('select[name="nursing-home"]');
    var nursingHomeLocationsElement = $('select[name="nursing-home-location"]');
    var pacientsElement = $('select[name="pacient"]');
    var relationsElement = $('select[name="relation"]');
    var diagnosisElement = $('select[name="diagnosis"]');

    var getCounties = async () => {
        return await $.ajax({
            url: '/dashboard/ajax/counties',
            method: 'GET',
            dataType: 'json'
        });
    };

    var getCities = async (countyId) => {
        return await $.ajax({
            url: '/dashboard/ajax/cities/county/' + countyId,
            method: 'GET',
            dataType: 'json'
        });
    };

    // Init counties
    var initCounties = async (countyElement) => {
        countyElement.html('');
        var countyId = countyElement.data('value');
        await getCounties().then((cities) => {
            countyElement.append('<option value="">Alege un judet</option>');
            $.each(cities, function (key, item) {
                if (item.id == countyId) {
                    countyElement.append("<option value='" + item.id + "' selected>" + item.name + "</option>");
                } else {
                    countyElement.append("<option value='" + item.id + "'>" + item.name + "</option>");
                }
            });
        });
    };

    // Init cities
    var initCities = async (countyElement, cityElement) => {
        cityElement.html('');
        var countyId = countyElement.data('value');
        if (countyId) {
            await getCities(countyId).then((cities) => populateCities(cities, cityElement));
        }

        countyElement.on('change', async function () {
            countyId = $(this).val();
            // fetch new data & populate cities
            await getCities(countyId).then((cities) => populateCities(cities, cityElement));
        });
    };

    // Append cities
    var populateCities = (cities, cityElement) => {
        // remove all options and add default one
        cityElement.html('');
        cityElement.append('<option value="">Alege o localitate</option>');
        var cityId = cityElement.data('value');
        // populate options
        $.each(cities, function (key, item) {
            if (item.id == cityId) {
                cityElement.append("<option value='" + item.id + "' selected>" + item.name + "</option>");
            } else {
                cityElement.append("<option value='" + item.id + "'>" + item.name + "</option>");
            }
        });
    };

    // get nursing homes
    var getNursingHomes = () => {
        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/utilities/nursing-homes',
            method: 'GET',
            dataType: 'json',
            success: function (nursingHomes) {
                $.each(nursingHomes, function (key, item) {
                    nursingHomesElement.append("<option value='" + item.uid + "'>" + item.name + "</option>");
                });
            }
        });
    };

    // get pacients by nursing home
    var getPacientsByNursingHome = (nursingHomeUuid) => {
        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/utilities/nursing-home/' + nursingHomeUuid + '/pacients',
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                pacientsElement.html("");
                pacientsElement.append('<option value="">Alege un pacient</option>');
            },
            success: function (pacients) {
                $.each(pacients, function (key, item) {
                    pacientsElement.append("<option value='" + item.uid + "'>" + item.name + "</option>");
                });
            }
        });
    };

    // get relations by pacient
    var getRelationsByPacient = (pacientUuid, showAlertIfEmpty) => {
        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/utilities/pacient/' + pacientUuid + '/relations',
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                relationsElement.html("");
                relationsElement.append('<option value="">Alege un apartinator</option>');
            },
            success: function (relations) {
                if (showAlertIfEmpty && !relations.length) {
                    KTSwal.showSwal('Acest pacient nu are nici un apartinator! <a href="/dashboard/pacient/' + pacientUuid + '/relations" target="_blank">Adauga</a> cel putin un apartinator.', "error");
                } else {
                    $.each(relations, function (key, item) {
                        relationsElement.append("<option value='" + item.uid + "'>" + item.name + "</option>");
                    });
                }
            }
        });
    };

    // get diagnoses by pacient
    var getDiagnosesByPacient = (pacientUuid, diagnosisId) => {
        // send AJAX call to backend
        $.ajax({
            url: '/dashboard/ajax/utilities/pacient/' + pacientUuid + '/diagnoses',
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                diagnosisElement.html("");
            },
            success: function (relations) {
                $.each(relations, function (key, item) {
                    if (item.id == diagnosisId) {
                        diagnosisElement.append("<option value='" + item.id + "' selected>" + item.diagnosis + "</option>");
                    } else {
                        diagnosisElement.append("<option value='" + item.id + "'>" + item.diagnosis + "</option>");
                    }
                });
            }
        });
    };

    // get medication data by id
    var getMedicationDataById = (medicationId) => {
        $.ajax({
            url: '/dashboard/ajax/utilities/medication/' + medicationId + '/data',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var data = response.data;
                    $('#drug').val(data.drug);
                    $('#dose').val(data.dose);
                    if (data.asNecessary) {
                        $('#as_necessary').prop('checked', true);
                        $('#scheduled_wrapper').hide();
                        $('#as_necessary_wrapper').show();
                        $('#as_necessary_observations').val(data.observations);
                    } else {
                        $('#as_necessary').prop('checked', false);
                        $('#scheduled_wrapper').show();
                        $('#as_necessary_wrapper').hide();

                        $('#startDate').flatpickr().setDate(data.startDate, true);
                        $('#endDate').flatpickr().setDate(data.endDate, true);

                        var i = 0;
                        $.each(data.details, function (key, item) {
                            $('#switcher' + i).prop('checked', 'checked');
                            $('input[name="details[' + i + '][hour]"]').val(key);
                            $('input[name="details[' + i + '][observations]"]').val(item);
                            $('input[name="details[' + i + '][hour]"]').removeAttr('disabled');
                            $('input[name="details[' + i + '][observations]"]').removeAttr('disabled');
                            i++;
                        });
                    }
                }
            }
        });
    };

    // handle nursing home change
    var handleNursingHomeChange = (pacients) => {
        nursingHomesElement.on('change', function () {
            var nursingHomeUuid = $(this).val();
            // send AJAX call to backend
            if (pacients) {
                getPacientsByNursingHome(nursingHomeUuid);
            }
        });
    };

    // handle pacient change
    var handlePacientChange = () => {
        pacientsElement.on('change', function () {
            var pacientUuid = $(this).val();
            // send AJAX call to backend
            getRelationsByPacient(pacientUuid, true);
        });
    };

    // Init dropzones for digital records
    var initDropzonesDigitalRecords = () => {
        Dropzone.autoDiscover = false;

        $('.dropzone-digital-record').each(function () {
            var $this = $(this);
            var url = $this.data('kt-dropzone-upload-url');
            $this.dropzone({
                url: url, // Set the url for your upload script location
                paramName: "file", // The name that will be used to transfer the file
                maxFiles: 1,
                maxFilesize: 30, // MB
                addRemoveLinks: false,
                uploadMultiple: false,
                disablePreviews: true,
                acceptedFiles: '.jpg, .jpeg, .png, .doc, .docx, .pdf',
                complete: function () {
                    window.location.reload();
                }
            });
        });
    };

    // Init hoverable actions
    var initHoverableActions = () => {
        $(".hoverable").hover(function () {
            $(this).find(".show-on-hover").removeClass('invisible').addClass('visible');
        }, function () {
            $(this).find(".show-on-hover").removeClass('visible').addClass('invisible');
        });
    };

    return {
        // Public functions
        initCounties: function () {
            $('select[data-name="county"]').each(function (i, element) {
                var countyElement = $(element);
                var cityElement = $(countyElement.data('trigger-id'));
                initCounties(countyElement);
                initCities(countyElement, cityElement);
            });
        },
        initDefault: function () {
            initDropzonesDigitalRecords();
            initHoverableActions();
        },
        initDocumentFilters: function () {
            getNursingHomes();
            handleNursingHomeChange(true);
            handlePacientChange();
        },
        initNursingHomeFilters: function () {
            getNursingHomes();
            handleNursingHomeChange(false);
        },
        initDiagnoses: function (pacientUuid, diagnosisId) {
            getDiagnosesByPacient(pacientUuid, diagnosisId);
        },
        initMedicationData: function (medicationId) {
            getMedicationDataById(medicationId);
        }
    };
}();


// Datatable for pages
let KTDatatablePages = function () {
    var table;
    var dt;

    var initDatatable = function () {
        dt = $("#kt_datatable_pages").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[3, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/pages"
            },
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'url'},
                {data: 'createdAt'},
                {data: null} // Actions
            ],
            columnDefs: [
                {
                    targets: -1,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-brand btn-light btn-active-light-primary btn-sm rounded-0 min-w-100px rounded-0" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                Actiuni
                                <span class="svg-icon fs-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/page/${row.machineName}/edit" class="menu-link px-3">
                                          Editeaza
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/page/${row.machineName}/delete" class="menu-link px-3" data-kt-table-action="delete" data-action="/dashboard/page/${row.machineName}/delete">
                                         Delete
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-pages-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
        }
    };
}();


// Datatable for pages
let KTDatatableMenus = function () {
    var table;
    var dt;

    var initDatatable = function () {
        dt = $("#kt_datatable_menus").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[3, 'desc']],
            stateSave: false,
            ajax: {
                url: "/dashboard/ajax/menus"
            },
            columns: [
                {data: 'id'},
                {data: 'title'},
                {data: 'machineName'},
                {data: 'links'},
                {data: null},
            ],
            columnDefs: [
                {
                    targets: 2,
                    orderable: false,
                    className: 'text-center',
                    render: function (data) {
                        return `<span class="badge badge-info">${data}</span>`
                    }
                },
                {
                    targets: 3,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `<a href="/dashboard/menu/item/${row.uuid}/view">Manage links</a>`
                    }
                },
                {
                    targets: -1,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-brand btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                Actions
                                <span class="svg-icon fs-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/menu/${row.uuid}/edit" class="menu-link px-3">
                                        Edit
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/dashboard/menu/${row.uuid}/delete" class="menu-link px-3" data-kt-table-action="delete" data-action="/dashboard/menu/${row.uuid}/delete">
                                        Delete
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                    }
                }
            ]
        });

        table = dt.$;

        dt.on('draw', function () {
            KTMenu.createInstances();
        });
    };
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-menus-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTUtilities.initDefault();
});