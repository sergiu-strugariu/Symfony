let menuItems = null;

/**
 * Handle the menu items page
 */
class MenuItems {
    constructor() {

        // Variables Setup
        this.init();

        // Prepare HTML templates that will be used
        this.prepareTemplates();

        // DOM Parsing and preparing
        this.handleDOM();

        // Prepare all of the event listeners
        this.handleEvents();
    }

    /**
     * All instance-wide vars go here
     */
    init() {
        this.menuItemsTreeInstance = null;
        this.menuItemsTreeData = null;
    }

    /**
     * HTML Templates
     */
    prepareTemplates() {
        this.menuItemOptionTpl = `
      <option value="{value}" class="menu-item-option">{level} {name}</option>
    `;
    }

    /**
     * Store all of the DOM objects
     */
    handleDOM() {
        // Menu items Form
        this.menuItemsForm = $("#menu-items-form");
        this.menuItemsFormSelectParent = this.menuItemsForm.find(".menu-item-parent").first().find('optgroup');
        this.menuItemsFormAddTitle = this.menuItemsForm.find('.add-menu-item-title');
        this.menuItemsFormEditTitle = this.menuItemsForm.find('.edit-menu-item-title');

        // Form fields
        this.menuItemIdInput = this.menuItemsForm.find('.menu-item-id');
        this.menuItemIcon = this.menuItemsForm.find('.icon-preview');
        this.menuItemLinkTextInput = this.menuItemsForm.find('.link-text');
        this.menuItemUrlInput = this.menuItemsForm.find('.link');
        this.menuIcon = this.menuItemsForm.find('.menu-icon');
        this.menuItemCssClassInput = this.menuItemsForm.find('.css-class');
        this.menuItemDescriptionInput = this.menuItemsForm.find('.description');

        this.menuItemParent = this.menuItemsForm.find('.menu-item-parent');

        // Form Buttons
        this.addMenuItemButtons = this.menuItemsForm.find('.add-menu-item-buttons');
        this.editMenuItemButtons = this.menuItemsForm.find('.edit-menu-item-buttons');
        this.addMenuItemSaveButton = $("#add-menu-item-save");
        this.editMenuItemSaveButton = $("#edit-menu-item-save");
        this.removeMenuItemButton = $("#edit-menu-item-delete");

        // Edit MenuItem structure
        this.menuItemsContainer = $(".menu-items-hierarchy");
        this.menuItemsTree = this.menuItemsContainer.find(".menu-item-tree");
    }

    /**
     * Event listeners for the DOM elements stored earlier
     */
    handleEvents() {
        this.drawMenuItemsTree();

        this.addMenuItemSaveButton.on('click', e => {
            e.preventDefault();
            this.handleAddMenuItem();
        });

        this.editMenuItemSaveButton.on("click", e => {
            e.preventDefault();
            this.handleEditMenuItem();
        });

        this.removeMenuItemButton.on("click", e => {
            e.preventDefault();
            if (confirm("Are you sure you want to remove this item?")) {
                this.handleDeleteMenuItem();
            }
        });
    }

    /**
     * Draw the menu items tree and the form dropdown
     */
    drawMenuItemsTree() {
        this.addMenuItemSaveButton.prop('disabled', 'disabled');
        // Get the menu items Data
        $.get(window.getMenuItemsEndpoint, (response) => {
            // Parse the menu items hierarchically
            this.menuItemsTreeData = this.parseMenuItemsResponse(response.data, null);

            // Fill the select on the "add menu items" form
            this.populateMenuItemsForm();

            // Draw and populate JS Tree
            this.populateMenuItemsTree();

            this.addMenuItemSaveButton.removeProp('disabled');
            this.addMenuItemSaveButton.removeAttr('disabled');
        });
    }

    /**
     * Fill the select on the "add menu items" form
     */
    populateMenuItemsForm() {
        // Store the structured data generated from the tree
        let selectOptionsData = [];

        /**
         * Flatten the structured data - recursive
         *
         * @param elements
         * @param level
         */
        let recursiveDraw = function (elements, level) {
            elements.forEach(elt => {
                selectOptionsData.push({
                    level: level,
                    name: elt.text,
                    id: elt.id
                });
                if (elt.children) {
                    recursiveDraw(elt.children, level + 1);
                }
            });
        };

        // Parse the data
        recursiveDraw(this.menuItemsTreeData, 0);

        // Remove old menu items
        this.menuItemsFormSelectParent.find('.menu-item-option').remove();

        selectOptionsData.forEach(optionData => {
            let elt = $(this.menuItemOptionTpl
                .replace(/{value}/g, optionData.id)
                .replace(/{level}/g, "-".repeat(optionData.level))
                .replace(/{name}/g, optionData.name)
            );

            elt.appendTo(this.menuItemsFormSelectParent);
        });
    }

    /**
     * Draw and populate JS Tree
     */
    populateMenuItemsTree() {
        // If there already is an instance, destroy it
        if (this.menuItemsTreeInstance) this.menuItemsTree.jstree('destroy');

        // Create jsTree instance
        this.menuItemsTreeInstance = this.menuItemsTree
            .on('select_node.jstree', (e, data) => {
                this.handlePopulateEditForm(data.node.original);
            })
            .on('deselect_node.jstree', (e, data) => {
                this.resetEditForm();
                this.resetSelectParent(data.node.original, false);
            })
            .on('move_node.jstree', (e, data, d) => {
                this.handleChangeNodeParent(data.node.original, data.instance.get_node(data.parent), data.new_instance.get_json());
            })
            .jstree({
                "core": {
                    "themes": {
                        "responsive": false
                    },
                    multiple: false,
                    // so that create works
                    "check_callback": true,
                    "data": [{
                        "text": window.menuTitle,
                        "state": {
                            "opened": true,
                            "disabled": true,
                        },
                        "children": this.menuItemsTreeData
                    }]
                },
                "types": {
                    "default": {
                        "icon": "fa fa-folder text-success"
                    },
                    "file": {
                        "icon": "fa fa-file text-success"
                    }
                },
                "checkbox": {
                    "whole_node": true,
                    "three_state": false
                },
                "plugins": ["dnd", "types", "checkbox"]
            });
    }

    /**
     * Parse the response data from the server - recursive function
     *
     * @param data
     * @param parentId
     * @returns {Array}
     */
    parseMenuItemsResponse(data, parentId) {
        let parsed = [];
        data.forEach(item => {
            if (item.parentId !== parentId) return true;
            parsed.push({
                "id": item.id,
                "parentId": item.parentId,
                "text": item.linkText,
                "link": item.link,
                "cssClass": item.cssClass,
                "description": item.description,
                "iconImg": item.icon,
                "state": {"opened": true},
                "children": this.parseMenuItemsResponse(data, item.id)
            });
        });

        return parsed;
    }

    /**
     * Add menu item to DB
     */
    handleAddMenuItem() {
        let formData = new FormData(this.menuItemsForm[0]);

        // Remove existing error messages
        $('.form-text.text-danger').remove();

        fetch(window.addMenuItemEndpoint, {
            method: "POST",
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                // Set alert message
                showSwalFire(data.success ? 'success' : 'error', data.message);

                if (data.success) {
                    this.drawMenuItemsTree();
                    this.resetEditForm();
                    this.addMenuItemSaveButton.removeProp('disabled');
                    this.addMenuItemSaveButton.removeAttr('disabled');
                } else {
                    if (Object.keys(data.errors).length > 0) {
                        this.parseBackendError(data.errors);
                    }
                }
            })
            .catch(error => {
                this.addMenuItemSaveButton.removeProp('disabled');
                this.addMenuItemSaveButton.removeAttr('disabled');
            });
    }

    /**
     * Edit menu item to DB
     */
    handleEditMenuItem() {
        let formData = new FormData(this.menuItemsForm[0]);
        fetch(window.editMenuItemEndpoint, {
            method: "POST",
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                // Set alert message
                showSwalFire(data.success ? 'success' : 'error', data.message);

                if (data.success) {
                    this.drawMenuItemsTree();
                    this.resetEditForm();
                    this.addMenuItemSaveButton.removeProp('disabled');
                    this.addMenuItemSaveButton.removeAttr('disabled');
                } else {
                    if (Object.keys(data.errors).length > 0) {
                        this.parseBackendError(data.errors);
                    }
                }
                this.addMenuItemSaveButton.removeProp('disabled');
                this.addMenuItemSaveButton.removeAttr('disabled');
            })
            .catch(error => {
                this.addMenuItemSaveButton.removeProp('disabled');
                this.addMenuItemSaveButton.removeAttr('disabled');
            });
    }

    /**
     * Populate edit form
     *
     * @param data
     */
    handlePopulateEditForm(data) {
        this.menuItemIdInput.val(data.id);
        this.menuItemLinkTextInput.val(data.text);
        this.menuItemUrlInput.val(data.link);
        this.menuItemCssClassInput.val(data.cssClass);
        this.menuItemDescriptionInput.val(data.description);
        this.menuItemIcon.find('img').remove();

        if (data.iconImg){
            this.menuItemIcon.append(`<img src="${window.menuIconPath}${data.iconImg}" class="w-45px h-45px" alt="${data.text}">`);
        }

        this.resetSelectParent(data, true);

        this.addMenuItemButtons.addClass('d-none');
        this.menuItemsFormAddTitle.addClass('d-none');

        this.editMenuItemButtons.removeClass('d-none');
        this.menuItemsFormEditTitle.removeClass('d-none');
    }

    /**
     * Change the node parent - drag n drop from the jstree instance
     *
     * @param node
     * @param parentNode
     */
    handleChangeNodeParent(node, parentNode, rawData) {
        let order = 0;
        let parsedRawData = [];
        let parseRawData = (data, first) => {
            data.forEach(item => {
                if (!first) {
                    parsedRawData.push({
                        id: item.id,
                        order: order,
                    });
                    order++;
                }
                if (item.children) {
                    item.children.forEach(child => {
                        parsedRawData.push({
                            "id": child.id,
                            "order": order
                        });
                        order++;
                        if (child.children) {
                            parseRawData(child.children, false);
                        }
                    })
                }
            })
        };

        parseRawData(rawData, true);

        $.post(window.editMenuItemParentEndpoint, {
            "menu-item-id": node.id,
            "menu-item-parent": parentNode.original.id || null,
            "order": parsedRawData
        }, (response) => {
            // Set alert message
            showSwalFire(response.success ? 'success' : 'error', response.message);
            if (response.success) {
                this.drawMenuItemsTree();
                this.resetEditForm();

                this.addMenuItemButtons.removeClass('d-none');
                this.menuItemsFormAddTitle.removeClass('d-none');

                this.editMenuItemButtons.addClass('d-none');
                this.menuItemsFormEditTitle.addClass('d-none');
            }
        });
    }

    /**
     * Delete menu item from DB
     */
    handleDeleteMenuItem() {
        let formData = this.menuItemsForm.serializeArray();
        $.post(window.deleteMenuItemEndpoint, formData, (response) => {
            // Set alert message
            showSwalFire(response.success ? 'success' : 'error', response.message);
            if (response.success) {
                this.drawMenuItemsTree();
                this.resetEditForm();
            }
        });
    }

    /**
     * Reset edit form and show add form
     */
    resetEditForm() {
        this.menuItemIdInput.val('0');
        this.menuItemLinkTextInput.val("");
        this.menuItemUrlInput.val("");
        this.menuIcon.val("");
        this.menuIcon.parent().find('label').text("Select icon");
        this.menuItemCssClassInput.val("");
        this.menuItemDescriptionInput.val("");
        this.menuItemParent.val("0");
        this.menuItemIcon.find('img').remove();

        this.addMenuItemButtons.removeClass('d-none');
        this.menuItemsFormAddTitle.removeClass('d-none');

        this.editMenuItemButtons.addClass('d-none');
        this.menuItemsFormEditTitle.addClass('d-none');
    }

    /**
     * Change select values
     * @param data
     * @param checked
     */
    resetSelectParent(data, checked) {
        this.menuItemParent.val(data.parentId || "0");

        this.menuItemsFormSelectParent.find('option').each((index, item) => {
            $(item).removeProp('disabled');
            $(item).removeAttr('disabled');
        });

        // Disable the menu item that is being edited
        this.menuItemsFormSelectParent.find('option').each((index, item) => {
            if (parseInt($(item).attr('value')) === parseInt(data['id'])) {
                if (checked) {
                    $(item).prop('disabled', 'disabled');
                }
            }
        });
    }

    /**
     * @param fields
     */
    parseBackendError(fields) {
        $.each(fields, function (key, value) {
            // Get field by name
            let field = $('[name="menu_item_form[' + key + ']"]');

            // Check existing field in form
            if (field.length > 0) {
                // Set error message
                field.after(`<span class="form-text text-danger text-left">${value}</span>`);
            }
        });
    };
}

$('document').ready(() => {
    menuItems = new MenuItems();
});
