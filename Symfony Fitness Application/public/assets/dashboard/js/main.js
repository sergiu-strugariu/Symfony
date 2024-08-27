/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('#cache-clear').click(function (event) {
    event.preventDefault();

    $.ajax({
        url: window.ajaxCacheClear,
        type: "POST",
        cache: false,
        success: function (response) {
            KTSwal.showSwal(response.message, response.success ? 'success' : 'error');
        },
        error: function (err) {
            console.log(err);
            KTSwal.showSwal(response.message, response.success ? 'success' : 'error');
        }
    });
});

$(document).ready(function () {
    if ($(".tinymce").length) {
        tinymce.init({
            selector: '.tinymce',
            plugins: 'advlist autolink lists preview code',
            extended_valid_elements: 'span',
            toolbar_mode: 'floating',
            height: 300,
            branding: false
        });
    }
});

if (document.querySelector('.counties') && document.querySelector('.cities')) {
    let county = $('.counties');
    let city = $('.cities');

    county.on('change', function () {
        $.ajax({
            method: "GET",
            url: window.countyCitiesPath,
            data: {'id': $(this).val()},
            cache: false,
            success: function (response) {
                city.empty();

                let optionTpl = `<option value="{{id}}">{{name}}</option>`;
                if (response.status) {
                    response.cities.forEach(function (item) {
                        let newTpl = optionTpl.replace('{{name}}', item.name)
                            .replace('{{id}}', item.id)
                            .replace('{{name}}', item.name);
                        $(newTpl).appendTo(city);
                    });
                }
            }
        });
    });
}

$('#education_price').on('change', function(){
    let price = Number($('#education_price').val());
    let vat = Number($('#education_vat').val());

    $('#education_finalPrice').val(price + ((vat)/100 * price));
})

$('#education_vat').on('change', function(){
    let price = Number($('#education_price').val());
    let vat = Number($('#education_vat').val());

    $('#education_finalPrice').val(price + ((vat)/100 * price));
})