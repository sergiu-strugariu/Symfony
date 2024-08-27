$(document).ready(function () {
    if ($('.favorites').length) {
        /**
         * Declare global variables
         */
        let currentPage = 1;
        let favoriteItems = window.favoriteItems;
        let removeFavoriteItemUrl = window.removeFavoriteItemUrl;
        let toggleState = 0;
        let type = '';
        let sortName = 'id'; // Default sort name
        let sortOrder = 'desc'; // Default sort order
        let limit = 3;
        let firstLoad = true; // Track if it's the first load

        /**
         * Handle DOM queries
         */
        let favoritesList = $('.listing');
        let favoriteFilters = $(".contract-filter");
        let errorMessage = $('.error-message');
        let noItemsMessage = $('.no-items-message');

        let sortingButtons = $(".sorting-buttons");
        let sortingButton = sortingButtons.find("h3");
        let spans = sortingButtons.find("h3 span");
        let sortDate = $('.sort-date');
        
        let loadMoreButton = $(".load-more a");

        /**
         * Listen for events
         */
        async function handleEvents() {
            await fetchFavorites(favoriteItems, currentPage, type, sortName, sortOrder, favoritesList, true);

            if (favoriteFilters.length) {
                favoriteFilters.select2({
                    allowClear: true,
                });
                favoriteFilters.on('change', async function () {
                    currentPage = 1; // Reset currentPage to 1
                    type = this.value === 'all' ? '' : this.value;
                    sortOrder = 'desc'; // Reset sortOrder to "desc"
                    sortName = 'id'; // Optionally reset sortName to default
                    // Remove active class from sorting buttons
                    sortingButton.removeClass('active');
                    spans.removeClass('active');
                    await fetchFavorites(favoriteItems, currentPage, type, sortName, sortOrder, favoritesList, true);
                });
            }

            if (sortDate.length) {
                sortDate.on('click', async function (e) {
                    // Prevent default if there are no items or only one item
                    if ($('.listing__item.favorite-card').length <= 1) {
                        e.preventDefault();
                        return;
                    }
                    await sortHandler(this);
                });
            }

            $('body').on('click', '.tooltip', function () {
                resetBookmarkToggle();
            });

            $('body').on('click', '.favorites .bookmark-item', function () {
                resetBookmarkToggle();
                $(this).closest('.toggle-favorite').find('.tooltip-text').toggleClass('show-tooltip');
                const bookmarkImages = $(this).closest('.toggle-favorite').find('.bookmark-image img');
                bookmarkImages.first().toggleClass('d-none');
                bookmarkImages.last().toggleClass('d-none');
            });

            $('body').on('click', '.favorites .favorite-delete-item', function () {
                let item = $(this).closest('.listing__item');
                let uuid = $(this).data('uuid');
                removeFavoriteItem(removeFavoriteItemUrl, favoritesList, item, uuid);
            });

            if (loadMoreButton.length) {
                loadMoreButton.on('click', async function (e) {
                    e.preventDefault();
            
                    // Disable the button to prevent multiple clicks
                    loadMoreButton.prop('disabled', true);
                    currentPage++;
                    
                    try {
                        // Await the fetchFavorites function to ensure it's fully executed before any further clicks
                        await fetchFavorites(favoriteItems, currentPage, type, sortName, sortOrder, favoritesList, false);
                    } finally {
                        // Re-enable the button after the request is completed
                        loadMoreButton.prop('disabled', false);
                    }
                });
            }
        }

        async function removeFavoriteItem(removeItemUrl, itemsContainer, item, uuid) {
            $.ajax({
                url: removeItemUrl,
                data: {
                    uuid: uuid,
                },
                cache: false,
                success: function (response) {
                    errorMessage.html('');

                    if (response.status) {
                        itemsContainer.addClass('show-loader');
                        item.remove();
                        currentPage = 1;
                        fetchFavorites(favoriteItems, currentPage, type, sortName, sortOrder, favoritesList, true);
                    } else {
                        errorMessage.html(response.message);
                    }
                },
                complete: function () {
                    setTimeout(function () {
                        itemsContainer.removeClass('show-loader');
                    }, 400);
                },
                error: function () {
                    errorMessage.html(window.translations.dashboard.common.error_message); // Use translation
                }
            });
        }

        async function fetchFavorites(itemsURL, currentPage, type, sortName, sortOrder, itemsContainer, clearItems) {
            $.ajax({
                url: itemsURL,
                data: {
                    locale: window.locale,
                    limit: limit,
                    page: currentPage,
                    type: type,
                    sortName: sortName,
                    sortOrder: sortOrder,
                },
                cache: false,
                success: function (response) {
                    if (response.status) {
                        if (clearItems) {
                            itemsContainer.html('');
                        }
                        itemsContainer.addClass('show-loader');
                        
                        // Check if there are no items and handle the no items message and load more button
                        if (response.rows.length === 0) {
                            loadMoreButton.hide();
                            noItemsMessage.text(window.translations.dashboard.favorites.no_items).show(); // Use translation
                            if (firstLoad) {
                                favoriteFilters.prop('disabled', true); // Disable filter select only on first load
                            }
                        } else {
                            noItemsMessage.hide();
                            favoriteFilters.prop('disabled', false); // Enable filter select
                            response.rows.forEach((item) => {
                                let path = '';
                                let imagePath = '';

                                switch (item.type) {
                                    case window.locationTypeProvider:
                                        path = window.providerPath + `/${item.slug}`;
                                        imagePath = window.companyImagePath + item.image;
                                        break;
                                    case window.locationTypeCare:
                                        path = window.companyPath + `/${item.slug}`;
                                        imagePath = window.companyImagePath + item.image;
                                        break;
                                    case window.jobType:
                                        path = window.jobPath + `/${item.slug}`;
                                        imagePath = window.jobImagePath + item.image;
                                        break;
                                    case window.courseType:
                                        path = window.coursePath + `/${item.slug}`;
                                        imagePath = window.trainingImagePath + item.image;
                                        break;
                                }
                                let favoriteItem = `
                                    <div class="listing__item favorite-card" data-slug="${item.slug}">
                                        <div class="item-info">
                                            <div class="data-image" style="background-image: url(${imagePath}); width: 100px; height: 100px;"></div>
                                            <a href="${path}" target="_blank" class="favorite-description">
                                                <p class="favorite-card-title text-md-start"><strong>${item.name}</strong></p>
                                                <p class="favorite-card-text text-md-start">${item.address}</p>
                                            </a>
                                        </div>
                                        <div class="date-favorite">
                                            <p class="favorite-card-text">${item.createdAt}</p>
                                        </div>
                                        <div class="toggle-favorite">
                                            <div class="bookmark-item">
                                                <div class="bookmark-image text-center">
                                                    <img src='../assets/dashboard/media/dashboard/heart-bookmark-icon-bookmarked.svg' class="bookmarked" alt="${window.translations.dashboard.common.bookmark}" width="24" height="24"> <!-- Use translation -->
                                                    <img src="../assets/dashboard/media/dashboard/heart-bookmark-icon-bookmarked1.svg" class="bookmarked d-none" alt="${window.translations.dashboard.common.bookmark}" width="24" height="24"> <!-- Use translation -->
                                                </div>
                                            </div>
                                            <div class="tooltip-head">
                                                <div class="tooltip">
                                                    <div class="tooltip-text">
                                                        <a data-uuid="${item.uuid}" class="favorite-delete-item">${window.translations.dashboard.favorites.delete_favorite}</a> <!-- Use translation -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;
                                itemsContainer.append(favoriteItem);
                            });

                            if (currentPage === response.totalPages) {
                                loadMoreButton.hide();
                            } else {
                                loadMoreButton.text(window.translations.dashboard.actions.load_more).show(); // Use translation
                            }
                        }
                    }
                },
                complete: function () {
                    setTimeout(function () {
                        itemsContainer.removeClass('show-loader');
                    }, 400);
                    firstLoad = false; // Set firstLoad to false after initial load
                }
            });
        }

        async function sortHandler(currentItem) {
            sortingButton.removeClass('active');
            $(currentItem).addClass('active');
        
            // Keep sortOrder consistent
            sortOrder = $(currentItem).data('sort') === 'asc' ? 'desc' : 'asc';
            $(currentItem).data('sort', sortOrder);
        
            sortName = $(currentItem).data('name');
            currentPage = 1; // Reset to the first page when sorting
            await fetchFavorites(favoriteItems, currentPage, type, sortName, sortOrder, favoritesList, true);
        
            spans.removeClass("active");
            $(currentItem).find('span').eq(toggleState).addClass("active");
            toggleState = (toggleState + 1) % 2;
        }

        function resetBookmarkToggle() {
            $('.bookmark-image img:first-of-type').removeClass('d-none');
            $('.bookmark-image img:last-of-type').addClass('d-none');
            $('.tooltip-text').removeClass('show-tooltip');
        }

        handleEvents();
    }
});