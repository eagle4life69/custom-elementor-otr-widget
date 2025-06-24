jQuery(function($){
  $('.otr-tab-button').on('click', function(){
    var tab = $(this).data('tab');
    $('.otr-tab-button').removeClass('active');
    $(this).addClass('active');
    $('.otr-tab-content').hide();
    $('#' + tab).show();

    // Clear search when changing tabs
    $('.otr-search').val('');
    $('.otr-tab-content').find('tr').show();
    $('.otr-no-results').remove();
  });

  $('.play-preview').on('click', function(e){
    e.preventDefault();
    var modal = $('#' + $(this).data('modal'));
    modal.show();
  });

  $('.otr-close').on('click', function(){
    $(this).closest('.otr-modal').hide();
  });
});

function otrSearch(input) {
  const filter = input.value.toLowerCase();
  const tabs = document.querySelectorAll(".otr-tab-content");
  tabs.forEach(tab => {
    if (tab.style.display === "none") return; // only search visible tab
    const rows = tab.querySelectorAll("table tr");
    let hasResults = false;
    rows.forEach((row, index) => {
      if (index === 0) return; // Skip header
      const text = row.innerText.toLowerCase();
      const match = text.includes(filter);
      row.style.display = match ? "" : "none";
      if (match) hasResults = true;
    });

    // Remove old no-results message if any
    const existingMsg = tab.querySelector('.otr-no-results');
    if (existingMsg) existingMsg.remove();

    // Add "no results" row if nothing matches
    if (!hasResults && filter !== '') {
      const table = tab.querySelector("table");
      const row = document.createElement("tr");
      row.className = "otr-no-results";
      const cell = document.createElement("td");
      cell.colSpan = 3;
      cell.style.textAlign = "center";
      cell.textContent = "No episodes found.";
      row.appendChild(cell);
      table.appendChild(row);
    }
  });
}
