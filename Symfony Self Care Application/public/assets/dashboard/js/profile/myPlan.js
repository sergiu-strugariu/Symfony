$(document).ready(function () {

  if ($('.my-plan').length) {

    $('.billing-history li:gt(2)').addClass('d-none');  // :gt(3) selects all elements greater than the index 2 (0-based indexing)

    $('.view-more').on('click', function(e) {
      e.preventDefault();
      $('.billing-history li.d-none:lt(3)').removeClass('d-none').addClass('d-flex');  // :lt(3) selects the first 3 <li> elements with class .d-none
    });
  }

});
