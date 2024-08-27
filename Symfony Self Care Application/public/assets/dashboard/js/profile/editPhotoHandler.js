$(document).ready(function () {
    if ($("#previewImageModal").length !== 0) {
        let profileErrorMessage = $('.profile-error-message');
        let previewImageModal = $('#previewImageModal');
        let cancel = $('.cancel');
        let saveBtn = $('.save-photo');
        let errorMessage = $('.error-message');
        let uploadBtn = $('.upload-btn img');

        cancel.on('click', function (e) {
            e.preventDefault();
            previewImageModal.modal('hide');
        });

        previewImageModal.on('bs-hidden', function () {
            // Handle bs-hidden event
        });

        saveBtn.on('click', function (e) {
            e.preventDefault();
            // Handle save button click
        });

        let console = window.console || {
            log: function () {}
        };
        let URL = window.URL || window.webkitURL;
        let $image = $('#image');
        let $download = $('#download');
        let $dataX = $('#dataX');
        let $dataY = $('#dataY');
        let $dataHeight = $('#dataHeight');
        let $dataWidth = $('#dataWidth');
        let $dataRotate = $('#dataRotate');
        let $dataScaleX = $('#dataScaleX');
        let $dataScaleY = $('#dataScaleY');
        let options = {
            aspectRatio: 1,
            preview: '.img-preview',
            crop: function (e) {
                $dataX.val(Math.round(e.detail.x));
                $dataY.val(Math.round(e.detail.y));
                $dataHeight.val(Math.round(e.detail.height));
                $dataWidth.val(Math.round(e.detail.width));
                $dataRotate.val(e.detail.rotate);
                $dataScaleX.val(e.detail.scaleX);
                $dataScaleY.val(e.detail.scaleY);
            }
        };
        let uploadedImageName = 'cropped.jpg';
        let uploadedImageType = 'image/jpeg';
        let uploadedImageURL;

        // Cropper
        $image.on().cropper(options);

        // Download
        if (typeof $download[0].download === 'undefined') {
            $download.addClass('disabled');
        }

        // Methods
        $('.docs-buttons').on('click', '[data-method]', function (e) {
            e.preventDefault();

            let $this = $(this);
            let data = $this.data();
            let cropper = $image.data('cropper');
            let $target;
            let result;

            if ($this.prop('disabled') || $this.hasClass('disabled')) {
                return;
            }

            if (cropper && data.method) {
                data = $.extend({}, data); // Clone a new one

                if (typeof data.target !== 'undefined') {
                    $target = $(data.target);

                    if (typeof data.option === 'undefined') {
                        try {
                            data.option = JSON.parse($target.val());
                        } catch (e) {
                            console.log(e.message);
                        }
                    }
                }

                switch (data.method) {
                    case 'getCroppedCanvas':
                        if (uploadedImageType === 'image/jpeg') {
                            if (!data.option) {
                                data.option = {};
                            }

                            data.option.fillColor = '#fff';
                        }

                        break;
                }

                result = $image.cropper(data.method, data.option, data.secondOption);

                switch (data.method) {
                    case 'scaleX':
                    case 'scaleY':
                        $(this).data('option', -data.option);
                        break;

                    case 'getCroppedCanvas':
                        if (result) {
                            if (!$download.hasClass('disabled')) {
                                const file = base64ToFile(result.toDataURL(uploadedImageType), uploadedImageName);
                                const formData = new FormData();
                                formData.append('fileName', file);

                                $.ajax({
                                    url: window.profileImageUploadUrl,
                                    method: 'post',
                                    processData: false,
                                    contentType: false,
                                    cache: false,
                                    data: formData,
                                    success: function (response) {
                                        errorMessage.html('');
                                        if (response.status) {
                                            setTimeout(function () {
                                                $('.data-image').css({'background-image': 'url(' + absoluteUrl + response.fileName + ')'});

                                                let newSrc = absoluteUrl + response.fileName;
                                                // Update images with class `image-change`
                                                updateImageSrc(newSrc);
                                                previewImageModal.modal('hide');
                                            }, 200);
                                        }
                                    },
                                    error: function () {
                                        profileErrorMessage.html("${window.translation.form.messages.form_details_error}");
                                    }
                                });
                            }
                        }
                        break;
                }

                if ($.isPlainObject(result) && $target) {
                    try {
                        $target.val(JSON.stringify(result));
                    } catch (e) {
                        console.log(e.message);
                    }
                }
            }
        });

        // Import image
        let $inputImage = $('#inputImage');

        // Add click event on the .upload-btn to trigger the file input click
        uploadBtn.on('click', function() {
            $inputImage.trigger('click');
        });

        if (URL) {
            $inputImage.change(function () {
                let selectedFile = $(this).prop("files")[0];
                const validImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                let files = this.files;
                let file;

                if (validImageTypes.includes(selectedFile.type) && this.files[0].size < 2000000) {
                    if (!$image.data('cropper')) {
                        return;
                    }

                    if (files && files.length) {
                        file = files[0];

                        if (/^image\/\w+$/.test(file.type)) {
                            uploadedImageName = file.name;
                            uploadedImageType = file.type;

                            if (uploadedImageURL) {
                                URL.revokeObjectURL(uploadedImageURL);
                            }

                            uploadedImageURL = URL.createObjectURL(file);
                            $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                            $inputImage.val('');
                            previewImageModal.modal('show');
                        } else {
                            window.alert('Please choose an image file.');
                        }
                    }
                } else {
                    $('#errorMessageAvatar').html('<p>Only formats are allowed: jpeg, png, jpg and file size less than 2MB.</p>');
                }
            });
        } else {
            $inputImage.prop('disabled', true).parent().addClass('disabled');
        }

        // Transform base64 in file
        function base64ToFile(base64String, fileName) {
            // Decode base64 (split to remove the data URL part)
            const byteString = atob(base64String.split(',')[1]);

            // Extract MIME type
            const mimeString = base64String.split(',')[0].split(':')[1].split(';')[0];

            const ab = new ArrayBuffer(byteString.length);
            const ia = new Uint8Array(ab);
            for (let i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }

            return new File([ab], fileName, {type: mimeString});
        }

        function updateImageSrc(newSrc) {
            $('.image-change').each(function () {
                // Check if the element is an img and has a src attribute
                if ($(this).is('img') && $(this).attr('src')) {
                    $(this).attr('src', newSrc);
                }
            });
        }
    }
});