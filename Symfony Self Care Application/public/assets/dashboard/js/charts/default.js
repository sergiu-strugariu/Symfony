if (document.querySelector('.charts')) {
    const dataLabels = ['Articles', 'Jobs', 'Courses', 'Cares', 'Providers'];
    const userLabels = ['Clients', 'Companies'];

    let chartInstances = [];
    let currentYear = new Date().getFullYear();
    let currentUser = 0;

    let userSelector = $('#user-year-filter');
    let userSelectorOption = $('#user-year-filter option');

    let dataSelector = $('#data-year-filter');
    let dataSelectorOption = $('#data-year-filter option');

    let userDataSelector = $('#data-user-filter');
    let userDataSelectorOption = $('#data-user-filter option');

    /**
     * Filter by @year
     */
    userSelector.on('change', function () {
        currentYear = $(this).val();

        // Init charts
        initChartData(currentYear, currentUser, 'kt_user_chartjs_2', window.userDataPath, userLabels, chartInstances, true);

        // Disable selected item
        disableCurrentItem(userSelectorOption, currentYear);
    });

    /**
     * Filter by @year
     */
    dataSelector.on('change', function () {
        currentYear = $(this).val();

        // Init charts
        initChartData(currentYear, currentUser, 'kt_data_chartjs_2', window.dataPath, dataLabels, chartInstances, true);

        // Disable selected item
        disableCurrentItem(dataSelectorOption, currentYear);
    });

    /**
     * Filter by @user
     */
    userDataSelector.on('change', function () {
        currentUser = $(this).val();

        // Init charts
        initChartData(currentYear, currentUser, 'kt_data_chartjs_2', window.dataPath, dataLabels, chartInstances, true);

        // Disable selected item
        disableCurrentItem(userDataSelectorOption, currentUser);
    });

    /**
     * Init Chart Data
     * @param year
     * @param userId
     * @param chartId
     * @param path
     * @param labels
     * @param chartInstances
     * @param redraw
     */
    function initChartData(year, userId, chartId, path, labels, chartInstances, redraw = false) {
        $.ajax({
            method: "GET",
            url: path,
            cache: false,
            data: {
                year: year,
                userId: userId
            },
            success: function (response) {
                const datasets = labels.map((label, index) => ({
                    label: label,
                    data: response[label.toLowerCase()].values,
                    borderColor: getColor(index),
                    backgroundColor: getColor(index),
                    fill: false
                }));

                if (redraw && chartInstances[chartId]) {
                    // Update existing chart instance
                    chartInstances[chartId].data.labels = response[labels[0].toLowerCase()].labels;
                    chartInstances[chartId].data.datasets = datasets;
                    chartInstances[chartId].update();
                } else {
                    // Initialize new chart instance
                    initChart(chartId, response[labels[0].toLowerCase()].labels, datasets, chartInstances);
                }
            }
        });
    }

    /**
     * Init chart
     * @param id
     * @param labelData
     * @param datasetData
     * @param chartInstances
     * @param typeVal
     */
    function initChart(id, labelData, datasetData, chartInstances, typeVal = 'line') {
        let ctx = document.getElementById(id).getContext('2d');

        // Define fonts
        let fontFamily = 'Arial';

        chartInstances[id] = new Chart(ctx, {
            type: typeVal,
            data: {
                labels: labelData,
                datasets: datasetData
            },
            options: {
                plugins: {
                    title: {display: false}
                },
                responsive: true,
                interaction: {intersect: false},
                scales: {
                    x: {stacked: false},
                    y: {stacked: false}
                }
            },
            defaults: {
                global: {defaultFont: fontFamily}
            }
        });
    }

    /**
     * @param select
     * @param year
     */
    function disableCurrentItem(select, year) {
        // Disable selected item
        select.prop('disabled', false).filter('[value="' + year + '"]').prop('disabled', true);
    }

    /**
     * Generate color based on index
     * @param index
     * @returns {string}
     */
    function getColor(index) {
        const colors = [
            'rgb(255, 193, 50)',
            'rgb(27, 57, 81)',
            'rgb(75, 192, 192)',
            'rgb(54, 162, 235)',
            'rgb(255, 99, 132)'
        ];
        return colors[index % colors.length];
    }

    /** Fetch data */
    initChartData(currentYear, currentUser, 'kt_user_chartjs_2', window.userDataPath, userLabels, chartInstances);
    initChartData(currentYear, currentUser, 'kt_data_chartjs_2', window.dataPath, dataLabels, chartInstances);

    /** Disable selected item */
    disableCurrentItem(dataSelectorOption, currentYear);
    disableCurrentItem(userSelectorOption, currentYear);
}