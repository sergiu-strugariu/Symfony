class Default {
    constructor(config = {}) {
        this.config = config;

        document.addEventListener('change', function (event) {
            if (event.target.matches('input[type="checkbox"]')) {
                event.target.value = event.target.checked ? 1 : 0;
            }
        });

        document.addEventListener("DOMContentLoaded", () => {
            this.initTinyMCE(`.${this.config.tinyMCE}`);
            this.appendBaseUrlToEmptyUrls(`${window.location.protocol}//${window.location.host}`);
        });
    }

    initTinyMCE(className) {
        let tinyMCE = document.querySelectorAll(`${className}`);

        if (tinyMCE.length > 0) {
            tinymce.init({
                selector: `${className}`,
                height: "400",
                plugins: 'advlist autolink lists preview code',
                toolbar_mode: 'floating',
                convert_urls: false,
                branding: false,
                valid_elements: '*[*]',
                extended_valid_elements: 'span[*]',
                forced_root_block: '',
                force_br_newlines: true,
                force_p_newlines: false
            });
        }
    }

    appendBaseUrlToEmptyUrls(baseUrl) {
        // Select all input fields of type "url"
        const urlFields = document.querySelectorAll('input[type="url"]');

        // Loop through each field
        urlFields.forEach(field => {
            // Check if the field is empty
            if (!field.value.trim()) {
                field.value = baseUrl;
            }
        });
    }
}

