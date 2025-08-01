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

    filterFields.forEach(function (field) {
      const optionHtml = `
                        <div class="filter-option" data-filter="${field.key}">
                            <input type="checkbox" id="filter_${field.key}" data-filter="${field.key}">
                            <label for="filter_${field.key}">${field.label}</label>
                        </div>
                    `;
      filterOptionsContainer.append(optionHtml);
    });
  }

  // Initialize filter dropdown
  populateFilterDropdown();

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

  // Handle filter checkbox changes
  $(document).on(
    "change",
    '#filterOptions input[type="checkbox"]',
    function () {
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
    }
  );

  // Update active filters count
  function updateActiveFiltersCount() {
    const activeCount = $(
      '#filterOptions input[type="checkbox"]:checked'
    ).length;
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
    const hasActiveFilters =
      $('#filterOptions input[type="checkbox"]:checked').length > 0;
    const filterBtn = $("#filterToggle");

    if (hasActiveFilters) {
      filterBtn.addClass("active");
    } else {
      filterBtn.removeClass("active");
    }
  }

  // Reset filters
  $("#resetFilters").on("click", function () {
    // Uncheck all filter checkboxes
    $('#filterOptions input[type="checkbox"]').prop("checked", false);

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

  // Handle filter option click (for better UX)
  $(document).on("click", ".filter-option", function (e) {
    if (e.target.type !== "checkbox") {
      const checkbox = $(this).find('input[type="checkbox"]');
      checkbox.prop("checked", !checkbox.prop("checked")).trigger("change");
    }
  });

  // Initialize filter state
  updateActiveFiltersCount();
  updateFilterButtonState();
});
