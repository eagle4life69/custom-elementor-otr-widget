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
    $('#tab-search-results').remove();
    $(".otr-tab-button[data-tab='tab-search-results']").remove();
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

  // Remove previous result tab if any
  const oldTab = document.querySelector("#tab-search-results");
  if (oldTab) oldTab.remove();
  const oldBtn = document.querySelector(".otr-tab-button[data-tab='tab-search-results']");
  if (oldBtn) oldBtn.remove();

  if (!filter) {
    // Restore all tabs
    document.querySelectorAll(".otr-tab-content").forEach(t => t.style.display = 'none');
    const active = document.querySelector(".otr-tab-button");
    if (active) {
      active.classList.add("active");
      const tid = active.dataset.tab;
      document.getElementById(tid).style.display = 'block';
    }
    return;
  }

  const resultRows = [];
  document.querySelectorAll(".otr-tab-content").forEach(tab => {
    const rows = tab.querySelectorAll("table tr");
    rows.forEach((row, index) => {
      if (index === 0) return; // Skip header
      const text = row.innerText.toLowerCase();
      if (text.includes(filter)) {
        resultRows.push(row.cloneNode(true));
      }
    });
  });

  // Create result tab if matches found
  if (resultRows.length > 0) {
    const buttonBar = document.querySelector(".otr-widget .otr-tab-button").parentElement;
    const btn = document.createElement("span");
    btn.className = "otr-tab-button active";
    btn.dataset.tab = "tab-search-results";
    btn.textContent = "Results";

    // Deactivate other buttons
    buttonBar.querySelectorAll(".otr-tab-button").forEach(b => b.classList.remove("active"));
    buttonBar.appendChild(btn);

    // Hide all other tabs
    document.querySelectorAll(".otr-tab-content").forEach(c => c.style.display = 'none');

    // Create new tab panel
    const newTab = document.createElement("div");
    newTab.id = "tab-search-results";
    newTab.className = "otr-tab-content";
    newTab.style.display = "block";

    const table = document.createElement("table");
    table.className = "otr-episode-table";

    const header = document.createElement("tr");
    header.innerHTML = "<th>Title</th><th>Date</th><th>DL</th>";
    table.appendChild(header);
    resultRows.forEach(r => table.appendChild(r));
    newTab.appendChild(table);

    const widget = document.querySelector(".otr-widget");
    widget.appendChild(newTab);

    // Add click event
    btn.addEventListener("click", () => {
      document.querySelectorAll(".otr-tab-button").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".otr-tab-content").forEach(t => t.style.display = "none");
      btn.classList.add("active");
      newTab.style.display = "block";
    });
  } else {
    alert("No matching episodes found.");
  }
}
