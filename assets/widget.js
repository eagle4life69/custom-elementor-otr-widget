jQuery(function($) {
  $('.page-directory-tab-button').on('click', function() {
    var tab = $(this).data('tab');
    $('.page-directory-tab-button').removeClass('active');
    $(this).addClass('active');
    $('.page-directory-tab-content').hide();
    $('#' + tab).show();
  });

  $('.play-preview').on('click', function(e) {
    e.preventDefault();
    var modal = $('#' + $(this).data('modal'));
    modal.show();
  });

  $('.otr-close').on('click', function() {
    $(this).closest('.otr-modal').hide();
  });
});

function otrSearch(input) {
  const filter = input.value.toLowerCase();
  const tabs = document.querySelectorAll(".page-directory-tab-content");
  tabs.forEach(tab => {
    const rows = tab.querySelectorAll("table tr");
    rows.forEach((row, index) => {
      if (index === 0) return; // Skip header
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });
}
