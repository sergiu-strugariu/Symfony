class KTCardDraggable {
    constructor(config = {}) {
        this.config = config;

        // Wait for the document to be fully loaded
        document.addEventListener("DOMContentLoaded", () => {
            let containers = document.querySelectorAll(`.${this.config.container}`);
            if (containers.length === 0) {
                return false;
            }

            // Initialize Sortable
            let swappable = new Sortable.default(containers, this.config.init);

            // Add listener for when the order changes
            this.handleDraggable(swappable);
        });
    }

    /**
     * @param swappable
     */
    handleDraggable(swappable) {
        swappable.on('sortable:stop', function (event) {
            setTimeout(function (e) {
                // Get the container where sorting happened
                let items = event.data.dragEvent.sourceContainer.children;

                // Log or process the new order
                items.forEach((item, index) => {
                    let orderInput = item.querySelector('input[type="hidden"]');

                    if (orderInput) {
                        item.repeaterIndex = index + 1;
                        orderInput.value = index + 1;
                    }
                });
            }, 400)
        });
    }
}