"use strict";

// Class definition
let KTDatatableCompanies = function () {
    // Shared variables
    let table;
    let datatable;

    // Private functions
    let initDatatable = function () {
        datatable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            scrollX: true,
            scrollCollapse: true,
            order: [[0, 'desc']],
            stateSave: false,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            ajax: {url: window.companyAjaxPath},
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'email'},
                {data: 'status'},
                {data: 'fileName'},
                {data: 'createdAt'},
                {data: 'updatedAt'},
                {data: null}
            ],
            columnDefs: [
                {
                    targets: 1,
                    orderable: true,
                    className: 'text-center',
                    render: function (data, type, row) {
                        let dataValue = data;
                        if (row.status === window.published) {
                            dataValue = `<a href="${row.locationType === window.locationTypeCare ? window.singleCompanyPath : window.singleServicePath}/${row.slug}" target="blank" class="custom-link">${data}</a>`;
                        }

                        return dataValue;
                    }
                },
                {
                    targets: 3,
                    orderable: true,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `<span class="badge badge-${row.status === window.published ? 'success' : 'warning'} rounded-0">${data}</span>`;
                    }
                },
                {
                    targets: 4,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `<a href="${window.companyImagePath}${data}" target="_blank">
                                    <img src="${window.companyImagePath}${data}" alt="image" class="w-75px">
                                </a>`;
                    }
                },
                {
                    targets: 6,
                    orderable: true,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return data ? data : `<span class="badge badge-light-dark">-</span>`;
                    }
                },
                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        let template = `
                            <a href="#" class="btn btn-brand btn-light btn-active-light-primary btn-sm rounded-0 min-w-100px rounded-0" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                 ${window.translations.dashboard.actions.actions}
                                <span class="svg-icon fs-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 20 20" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column rounded-0 menu-state-bg-light-primary fw-bold fs-7 w-100px" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item">
                                    <a href="/dashboard/company/${row.uuid}/edit/${row.locationType}?locale=ro" class="menu-link px-3 color-183B51">
                                         ${window.translations.dashboard.actions.edit}
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item">
                                    <a href="#" class="menu-link px-3 color-183B51" data-kt-table-action="moderate" data-action="/dashboard/company/actions/moderate/${row.uuid}/${row.locationType}" data-name="${row.status}">
                                         ${row.status === window.published ? window.translations.dashboard.actions.draft : window.translations.dashboard.actions.publish}
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item ${window.isAdmin ? '' : 'd-none'}">
                                    <a href="#" class="menu-link px-3 color-183B51" data-kt-table-action="delete" data-action="/dashboard/company/actions/remove/${row.uuid}/${row.locationType}">
                                        ${window.translations.dashboard.actions.delete}
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->`;

                        return template;
                    }
                }
            ]
        });

        datatable.on('draw', function () {
            KTMenu.createInstances();
        });
    };

    // Search DataTable with Delay
    let handleSearchDatatable = function () {
        let delayTimer;
        const filterSearch = document.querySelector('[data-kt-table-filter="search"]');
        filterSearch.value = '';

        // Reset search data
        datatable.search(filterSearch.value).draw();

        filterSearch.addEventListener('keyup', function (e) {
            clearTimeout(delayTimer);
            delayTimer = setTimeout(function () {
                datatable.search(e.target.value).draw();
            }, 500);
        });
    };

    // Handle delete
    let handleDeleteRow = () => {
        // Delete button on click
        table.addEventListener("click", (e) => {
            let dataset = e.target.dataset;
            if (dataset.hasOwnProperty('ktTableAction') && dataset.ktTableAction === "delete") {
                e.preventDefault();

                Swal.fire({
                    text: window.translations.dashboard.common.are_you_sure_delete,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: window.translations.dashboard.common.yes,
                    cancelButtonText: window.translations.dashboard.common.no,
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        setTimeout(function () {
                            window.location.href = absoluteUrl + dataset.action;
                        }, 300);
                    }
                });
            }
        });
    };

    // Handle delete
    let handleModerateRow = () => {
        // Delete button on click
        table.addEventListener("click", (e) => {
            let dataset = e.target.dataset;
            if (dataset.hasOwnProperty('ktTableAction') && dataset.ktTableAction === "moderate") {
                e.preventDefault();

                Swal.fire({
                    text: dataset.name === 'published' ? window.translations.dashboard.common.are_you_sure_deactivate : window.translations.dashboard.common.are_you_sure_publish,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: window.translations.dashboard.common.yes,
                    cancelButtonText: window.translations.dashboard.common.no,
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        setTimeout(function () {
                            window.location.href = absoluteUrl + dataset.action;
                        }, 300);
                    }
                });
            }
        });
    };

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_datatable_companies');

            if (!table) {
                return;
            }
            initDatatable();
            handleSearchDatatable();
            handleDeleteRow();
            handleModerateRow();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatableCompanies.init();
});
