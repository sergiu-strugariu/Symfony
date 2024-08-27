"use strict";

// Class definition
var KTCardsWidget4 = function () {
    // Private methods
    var initChart = function () {
        var el = document.getElementById("kt_card_widget_4_chart");

        if (!el) {
            return;
        }

        var options = {
            size: el.getAttribute("data-kt-size") ? parseInt(el.getAttribute("data-kt-size")) : 70,
            lineWidth: el.getAttribute("data-kt-line") ? parseInt(el.getAttribute("data-kt-line")) : 5,
            rotate: el.getAttribute("data-kt-rotate") ? parseInt(el.getAttribute("data-kt-rotate")) : 0,
        }

        var canvas = document.createElement("canvas");
        var span = document.createElement("span");

        if (typeof (G_vmlCanvasManager) !== "undefined") {
            G_vmlCanvasManager.initElement(canvas);
        }

        var ctx = canvas.getContext("2d");
        canvas.width = canvas.height = options.size;

        el.appendChild(span);
        el.appendChild(canvas);

        ctx.translate(options.size / 2, options.size / 2); // change center
        ctx.rotate((-1 / 2 + options.rotate / 180) * Math.PI); // rotate -90 deg

        var radius = (options.size - options.lineWidth) / 2;

        var drawCircle = function (color, lineWidth, percent) {
            percent = Math.min(Math.max(0, percent || 1), 1);
            ctx.beginPath();
            ctx.arc(0, 0, radius, 0, Math.PI * 2 * percent, false);
            ctx.strokeStyle = color;
            ctx.lineCap = "round"; // butt, round or square
            ctx.lineWidth = lineWidth;
            ctx.stroke();
        };

        // Dynamic values from hidden inputs
        var coursesValue = document.getElementById("courses").value;
        var workshopsValue = document.getElementById("workshops").value;
        var conventionsValue = document.getElementById("conventions").value;

        // Total sum to normalize the values
        var total = parseInt(coursesValue) + parseInt(workshopsValue) + parseInt(conventionsValue);

        // Normalize values
        var coursesPercent = parseInt(coursesValue) / total;
        var workshopsPercent = parseInt(workshopsValue) / total;
        var conventionsPercent = parseInt(conventionsValue) / total;

        // Draw base circle
        drawCircle("#E4E6EF", options.lineWidth, 1);

        // Draw individual segments
        var startAngle = 0;
        var endAngle = startAngle + (coursesPercent * 2 * Math.PI);
        drawSegment(KTUtil.getCssVariableValue("--bs-danger"), startAngle, endAngle, options.lineWidth);

        startAngle = endAngle;
        endAngle = startAngle + (workshopsPercent * 2 * Math.PI);
        drawSegment(KTUtil.getCssVariableValue("--bs-primary"), startAngle, endAngle, options.lineWidth);

        startAngle = endAngle;
        endAngle = startAngle + (conventionsPercent * 2 * Math.PI);
        drawSegment(KTUtil.getCssVariableValue("--bs-secondary"), startAngle, endAngle, options.lineWidth);

        function drawSegment(color, startAngle, endAngle, lineWidth) {
            ctx.beginPath();
            ctx.arc(0, 0, radius, startAngle, endAngle, false);
            ctx.strokeStyle = color;
            ctx.lineCap = "round";
            ctx.lineWidth = lineWidth;
            ctx.stroke();
        }
    }

    // Public methods
    return {
        init: function () {
            initChart();
        }
    }
}();

// Webpack support
if (typeof module !== "undefined") {
    module.exports = KTCardsWidget4;
}