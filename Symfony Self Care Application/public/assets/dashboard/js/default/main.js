/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (document.querySelector('.dropzone_gallery')) {
    /**
     * @param id
     * @param path
     * @param btn
     */
    function initDropZone(id, path, btn) {
        Dropzone.autoDiscover = false;
        let success = false;
        let message = '';

        // Upload new file to storage
        const dropzone = new Dropzone(id, {
            url: path,
            paramName: 'file',
            maxFiles: 20,
            parallelUploads: 20,
            maxFilesize: 3,
            autoProcessQueue: false,
            addRemoveLinks: true,
            acceptedFiles: '.jpeg,.jpg,.png',
            init: function () {
                this.on("addedfile", function () {
                    // Detect files exist
                    btn.classList.remove("d-none");
                });
                this.on("success", function (file, data) {
                    // Remove prev files
                    dropzone.removeFile(file);
                    success = data.success;
                    message = data.message;

                    if (success) {
                        // Create a jQuery object from the HTML string
                        let mediaDiv = $(createMediaItem(data));

                        // Get the container and the first child element
                        let $container = $('#galleries');

                        // Check if the container has any children
                        if ($container.children().length > 0) {
                            // Get the first child element
                            let $firstChild = $container.children().first();

                            // Insert the new mediaDiv before the first child element
                            mediaDiv.insertBefore($firstChild);
                        } else {
                            // If no children, append the new mediaDiv to the container
                            $container.append(mediaDiv);
                        }
                    }
                });
                this.on("queuecomplete", function () {
                    // Set message popup
                    showSwalFire(success ? 'success' : 'error', message);

                    btn.classList.add("d-none");
                });
            }
        });

        // Button upload files
        btn.addEventListener("click", function () {
            // Upload all files for loop
            dropzone.processQueue();
        });
    }

    /**
     * Remove item
     */
    $('#galleries').on('click', '.btn', function (e) {
        e.preventDefault();
        let item = $(this);

        Swal.fire({
            text: "Are you sure you want to delete this file?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    method: "GET",
                    url: window.removeItemGallery,
                    data: {'id': item.attr('data-id')},
                    cache: false,
                    success: function (response) {
                        // Remove item
                        if (response.success) {
                            item.closest('.item').remove();
                        } else {
                            setTimeout(function (e) {
                                window.location.reload();
                            }, 1500);
                        }

                        // Set message popup
                        showSwalFire(response.success ? 'success' : 'error', response.message);
                    }
                });
            }
        });
    });

    /**
     * @param media
     * @returns {string}
     */
    function createMediaItem(media) {
        return `<div class="col-md-2 mb-5 item">
                    <img src="${media.fileUrl}" alt="file-${media.id}" class="lozad rounded img-thumbnail"/>
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-id="${media.id}">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                </div>`;
    }
}

if (document.querySelector('.category-cares') && document.querySelector('.category-services')) {
    $(document).ready(function () {
        let categoryCareWrapper = $('.category-cares');
        let categoryServiceWrapper = $('.category-services');

        /**
         * @param type
         */
        function displayCategory(type) {
            // Remove classes
            categoryCareWrapper.removeClass('d-none');
            categoryServiceWrapper.removeClass('d-none');

            // Change classes
            if (type === window.careType) {
                categoryServiceWrapper.addClass('d-none');
            }

            // Change classes
            if (type === window.providerType) {
                categoryCareWrapper.addClass('d-none');
            }
        }

        // Display default category
        displayCategory(window.locationType);
    });
}