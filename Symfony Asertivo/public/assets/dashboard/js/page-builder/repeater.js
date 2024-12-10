class PageRepeater {
    constructor(config = {}) {
        this.config = config;

        // Wait for the document to be fully loaded
        document.addEventListener("DOMContentLoaded", () => {
            // Select all the add item buttons on the page
            const addItemButtons = document.querySelectorAll(`.${this.config.addBtn}`);

            addItemButtons.forEach(button => {
                // Get the container for each button (items container)
                const itemContainer = button.closest(`.${this.config.container}`);
                if (!itemContainer) return;

                // Handle file input changes
                itemContainer.addEventListener("change", (event) => {
                    const target = event.target;

                    // Check if the target is an input of type file
                    if (target.matches('input[type="file"]')) {
                        this.handleFileChange(event, target);
                    }
                });

                // Get the initial number of items in this section
                let ItemIndex = 0;

                if (button.getAttribute('data-repeater-items') === "0") {
                    const inputName = button.dataset.inputName;
                    const machineName = button.dataset.machineName;
                    const templateName = button.dataset.templateName;

                    // Create a new item
                    const newItem = this.createItem(inputName, machineName, templateName, ItemIndex);

                    // Insert before the "Add item" button
                    itemContainer.insertBefore(newItem, button);
                }

                // Add click event for the add item button in this section
                button.addEventListener("click", (event) => {
                    this.addItem(event, itemContainer, ItemIndex, button);
                    ItemIndex++; // Increase the index for the next item
                });

                // Add click event to remove item in this section
                itemContainer.addEventListener("click", (event) => {
                    if (event.target.matches(`.${this.config.removeBtn}`)) {
                        this.removeItem(event, itemContainer, button);
                    }
                });
            });
        });
    }

    handleFileChange(event, target) {
        // Get the selected file
        const file = target.files[0];

        if (file) {
            const reader = new FileReader();

            const wrapper = target.closest(`.${this.config.imageHeaderClass}`).querySelector(`.${this.config.imageWrapperClass}`);

            // When the file is loaded, update the background
            reader.onload = function (e) {
                // Get the Base64 string
                const base64Image = e.target.result;

                if (wrapper) {
                    wrapper.style.backgroundImage = `url(${base64Image})`;
                }
            };

            // Read the file as a data URL (Base64)
            reader.readAsDataURL(file);
        }
    }

    /**
     * @param inputName
     * @param machineName
     * @param index
     */
    templateGalleryItem(inputName, machineName, index) {
        return `<div class="card card-bordered">
            <div class="card-header my-3 justify-content-center">
                <div class="image-input image-input-outline">
                    <div class="image-input-wrapper"></div>
                </div>
                <div class="my-3">
                      <input
                        type="file"
                        name="${inputName}[${this.config.galleryField}][${index}][fileName]"
                        accept="image/jpeg, image/png, image/webp"
                        class="form-control form-control-solid form-control-lg rounded-0 m-1"
                        required
                    />
                    <input
                        type="hidden"
                        name="${inputName}[${this.config.galleryField}][${index}][weight]"
                        value="${index}"
                    />
                </div>
            </div>
            <div class="card-body d-flex align-items-center">
                <input
                    type="text"
                    name="${inputName}[${this.config.galleryField}][${index}][title]"
                    placeholder="Insert title"
                    class="form-control form-control-solid form-control-lg rounded-0 m-1"
                    required
                />
                <button type="button" class="btn btn-icon btn-sm px-3 btn-light-danger ${this.config.removeBtn}" ${index === 0 ? 'disabled' : ''}>
                    <i class="far fa-trash-alt remove-repeater-btn"></i>
                </button>
                <button type="button" class="btn btn-icon btn-light-dark btn-sm px-3 mx-2 draggable-handle__gallery__${machineName}">
                    <i class="fas fa-grip-horizontal"></i>
                </button>
            </div>
        </div>`;
    }

    /**
     * @param inputName
     * @param index
     */
    templateLinkItem(inputName, index) {
        return `
            <input
                type="text"
                name="${inputName}[${this.config.linkField}][${index}][title]"
                placeholder="Insert link title"
                class="form-control form-control-solid form-control-lg rounded-0 m-1"
                required
            />
            <input
                value="${window.location.protocol}//${window.location.host}"
                type="url"
                name="${inputName}[${this.config.linkField}][${index}][url]"
                placeholder="Insert link"
                class="form-control form-control-solid form-control-lg rounded-0 m-1"
                required
            />
            <button type="button" class="btn btn-light-danger btn-icon btn-sm ${this.config.removeBtn}" ${index === 0 ? 'disabled' : ''}>
                  <i class="far fa-trash-alt px-3 remove-repeater-btn"></i>
            </button>
        `;
    }

    /**
     * Function to create a new item
     * @param inputName
     * @param machineName
     * @param templateName
     * @param index
     * @returns {HTMLDivElement}
     */
    createItem(inputName, machineName, templateName, index) {
        const item = document.createElement("div");
        let itemClassName = `${this.config.sectionClass}${machineName}`;
        item.dataset.repeaterIndex = index;

        if (this.config.linkField === templateName) {
            item.className = `d-flex align-items-center ${itemClassName}`;
            item.innerHTML = this.templateLinkItem(inputName, index);
        } else {
            item.className = `col-md-6 mb-6 ${itemClassName}`;
            item.innerHTML = this.templateGalleryItem(inputName, machineName, index);
        }

        return item;
    }

    /**
     * Function to add a new item
     * @param ev
     * @param itemContainer
     * @param ItemIndex
     * @param button
     */
    addItem(ev, itemContainer, ItemIndex, button) {
        ev.preventDefault();

        // Get the parent container of the add item button to extract inputNameBase
        const inputName = button.dataset.inputName;
        const machineName = button.dataset.machineName;
        const templateName = button.dataset.templateName;

        // Get all item elements
        const items = itemContainer.querySelectorAll(`.${this.config.sectionClass}${machineName}`);

        ItemIndex = items.length;

        // Create a new item
        const newItem = this.createItem(inputName, machineName, templateName, ItemIndex++);

        // Insert before the "Add item" button
        itemContainer.insertBefore(newItem, button);
    }

    /**
     * Function to remove item
     * @param ev
     * @param itemContainer
     * @param button
     * @param ItemIndex
     */
    removeItem(ev, itemContainer, button, ItemIndex) {
        if (ev.target.classList.contains(this.config.removeBtn)) {
            ev.preventDefault();

            const item = ev.target.closest(`.${this.config.sectionClass}${button.dataset.machineName}`);

            if (item) {
                item.remove();
                ItemIndex--;
            }
        }
    }
}