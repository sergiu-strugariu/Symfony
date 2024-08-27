if (document.querySelector('.charts')) {
    const educationLabels = ['Courses', 'Workshops'];
    const paricipantLabels = ['Courses', 'Workshops', 'Conventions'];
    const coursesLabels = ['Courses'];
    const workshopLabels = ['Workshops'];

    let chartInstances = [];
    let currentYear = new Date().getFullYear();
    let currentUser = 0;

    let participantSelector = $('#participants-year-filter');
    let participantSelectorOption = $('#participants-year-filter option');

    let dataSelector = $('#data-year-filter');
    let dataSelectorOption = $('#data-year-filter option');

    let courseSelector = $('#sum-course-filter');
    let courseSelectorOption = $('#sum-course-filter option');

    let workshopSelector = $('#sum-workshop-filter');
    let workshopSelectorOption = $('#sum-workshop-filter option');

    courseSelector.on('change', function () {
        educationId = $(this).val();

        initChartData(currentYear, educationId, 'course', 'kt_course_chartjs_2', window.incasariDataPath, coursesLabels, chartInstances, true);

        disableCurrentItem(courseSelectorOption, educationId);
    });

    workshopSelector.on('change', function () {
        educationId = $(this).val();

        initChartData(currentYear, educationId,  'workshop','kt_workshop_chartjs_2', window.incasariDataPath, workshopLabels, chartInstances, true);

        disableCurrentItem(workshopSelectorOption, educationId);
    });

    participantSelector.on('change', function () {
        currentYear = $(this).val();

        initChartData(currentYear, 'all', 'all', 'kt_participants_chartjs_2', window.participantsDataPath, paricipantLabels, chartInstances, true);

        disableCurrentItem(participantSelectorOption, currentYear);
    });

    dataSelector.on('change', function () {
        currentYear = $(this).val();

        initChartData(currentYear, 'all', 'all', 'kt_educations_chartjs_2', window.dataPath, educationLabels, chartInstances, true);

        disableCurrentItem(dataSelectorOption, currentYear);
    });

    function initChartData(year, educationId = 'all', type = 'all', chartId, path, labels, chartInstances, redraw = false) {
        $.ajax({
            method: "GET",
            url: path,
            cache: false,
            data: {
                year: year,
                educationId: educationId,
                type: type
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

        // // Define fonts
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

    initChartData(currentYear, 'all', 'all', 'kt_educations_chartjs_2', window.dataPath, educationLabels, chartInstances);
    initChartData(currentYear, 'all', 'all', 'kt_participants_chartjs_2', window.participantsDataPath, paricipantLabels, chartInstances);
    initChartData(currentYear, 'all', 'course', 'kt_course_chartjs_2', window.incasariDataPath, coursesLabels, chartInstances);
    initChartData(currentYear, 'all', 'workshop', 'kt_workshop_chartjs_2', window.incasariDataPath, workshopLabels, chartInstances);

    disableCurrentItem(dataSelectorOption, currentYear);
    disableCurrentItem(participantSelectorOption, currentYear);
    disableCurrentItem(courseSelectorOption, currentYear);
    disableCurrentItem(workshopSelectorOption, currentYear);
}
