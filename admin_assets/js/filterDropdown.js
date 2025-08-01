$(document).ready(function () {
  // Dynamic filter options based on form fields
  const filterFields = [];

  // Collect all filter fields and their labels
  $(".filter-field").each(function () {
    const filterKey = $(this).data("filter");
    const label = $(this).find("label").text();
    filterFields.push({
      key: filterKey,
      label: label,
    });
  });

  // Populate filter dropdown
  function populateFilterDropdown() {
    const filterOptionsContainer = $("#filterOptions");
    filterOptionsContainer.empty();

    // Add Select All/Deselect All option at the top
    const selectAllHtml = `
      <div class="filter-option select-all-option" style="border-bottom: 1px solid #e0e0e0; margin-bottom: 8px; padding-bottom: 8px; font-weight: 600;">
        <input type="checkbox" id="selectAllFilters">
        <label for="selectAllFilters">Select All</label>
      </div>
    `;
    filterOptionsContainer.append(selectAllHtml);

    // Add individual filter options
    filterFields.forEach(function (field) {
      const optionHtml = `
        <div class="filter-option individual-filter" data-filter="${field.key}">
          <input type="checkbox" id="filter_${field.key}" data-filter="${field.key}" class="individual-filter-checkbox">
          <label for="filter_${field.key}">${field.label}</label>
        </div>
      `;
      filterOptionsContainer.append(optionHtml);
    });
  }
  

  // Initialize filter dropdown
  populateFilterDropdown();
  $(".filter-field.filter-selected").each(function () {
    const filterKey = $(this).data("filter");
    $(this).removeClass("hidden");
    $(`#filter_${filterKey}`).prop("checked", true);
  });
  
  

  // Toggle filter dropdown
  $("#filterToggle").on("click", function (e) {
    e.stopPropagation();
    $("#filterDropdown").toggleClass("show");
    $(this).toggleClass("active");
  });

  // Close dropdown when clicking outside
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".filter-sort-container").length) {
      $("#filterDropdown").removeClass("show");
      $("#filterToggle").removeClass("active");
    }
  });

  // Prevent dropdown from closing when clicking inside
  $("#filterDropdown").on("click", function (e) {
    e.stopPropagation();
  });

  // Handle Select All/Deselect All toggle
  $(document).on("change", "#selectAllFilters", function () {
    const isChecked = $(this).is(":checked");
    const selectAllLabel = $(this).next("label");
    
    // Update all individual filter checkboxes
    $(".individual-filter-checkbox").prop("checked", isChecked).trigger("change");
    
    // Update the label text
    selectAllLabel.text(isChecked ? "Deselect All" : "Select All");
  });

  // Handle individual filter checkbox changes
  $(document).on("change", ".individual-filter-checkbox", function () {
    const filterKey = $(this).data("filter");
    const isChecked = $(this).is(":checked");
    const filterField = $(`.filter-field[data-filter="${filterKey}"]`);

    if (isChecked) {
      filterField.removeClass("hidden");
    } else {
      filterField.addClass("hidden");
      // Clear the field value when hiding
      filterField.find("input, select").val("").trigger("change");
    }

    updateActiveFiltersCount();
    updateFilterButtonState();
    updateSelectAllState();

    // Ensure filter-selected fields are shown and checked on load
    $(".filter-field.filter-selected").each(function () {
      const filterKey = $(this).data("filter");
      $(this).removeClass("hidden");
      $(this).removeClass("filter-selected");
      $(`#filter_${filterKey}`).prop("checked", true).trigger("change");
    });

 
  });

  // Update Select All checkbox state based on individual selections
  function updateSelectAllState() {
    const totalIndividualFilters = $(".individual-filter-checkbox").length;
    const checkedIndividualFilters = $(".individual-filter-checkbox:checked").length;
    const selectAllCheckbox = $("#selectAllFilters");
    const selectAllLabel = selectAllCheckbox.next("label");

    if (checkedIndividualFilters === 0) {
      // No filters selected
      selectAllCheckbox.prop("checked", false);
      selectAllCheckbox.prop("indeterminate", false);
      selectAllLabel.text("Select All");
    } else if (checkedIndividualFilters === totalIndividualFilters) {
      // All filters selected
      selectAllCheckbox.prop("checked", true);
      selectAllCheckbox.prop("indeterminate", false);
      selectAllLabel.text("Deselect All");
    } else {
      // Some filters selected (indeterminate state)
      selectAllCheckbox.prop("checked", false);
      selectAllCheckbox.prop("indeterminate", true);
      selectAllLabel.text("Select All");
    }
  }

  // Update active filters count
  function updateActiveFiltersCount() {
    const activeCount = $(".individual-filter-checkbox:checked").length;
    const countElement = $("#activeFiltersCount");

    if (activeCount > 0) {
      countElement.text(activeCount).show();
      $('.filter-row').slideDown(600);
    } else {
      countElement.hide();
      $('.filter-row').slideUp(600);
    }
  }

  // Update filter button state
  function updateFilterButtonState() {
    const hasActiveFilters = $(".individual-filter-checkbox:checked").length > 0;
    const filterBtn = $("#filterToggle");

    if (hasActiveFilters) {
      filterBtn.addClass("active");
    } else {
      filterBtn.removeClass("active");
    }
  }

  // Reset filters
  $("#resetFilters").on("click", function () {
    // Uncheck all filter checkboxes (including select all)
    $('#filterOptions input[type="checkbox"]').prop("checked", false);
    $("#selectAllFilters").prop("indeterminate", false);
    $("#selectAllFilters").next("label").text("Select All");

    // Hide all filter fields
    $(".filter-field").addClass("hidden");

    // Clear all form values
    $(".filter-field input, .filter-field select").val("").trigger("change");

    // Update UI
    updateActiveFiltersCount();
    updateFilterButtonState();

    // Close dropdown
    $("#filterDropdown").removeClass("show");
  });

  // Handle filter option click (for better UX) - updated to handle both select all and individual options
  $(document).on("click", ".filter-option", function (e) {
    if (e.target.type !== "checkbox") {
      const checkbox = $(this).find('input[type="checkbox"]');
      checkbox.prop("checked", !checkbox.prop("checked")).trigger("change");
    }
  });

  // Initialize filter state
  updateActiveFiltersCount();
  updateFilterButtonState();
  updateSelectAllState();
});