function createCustomDropdown(selector, options) {
  // Default options
  var defaults = {
    placeholder: "Find an option...",
    containerClass: "user-select2",
    dropdownClass: "user-select2-dropdown",
    itemClass: "content-item",
    textClass: "custom-select-content",
    selectedIconClass: "selected-icon",
    selectedIconContent: `<svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M11.0781 1.08937C11.3054 1.31661 11.3054 1.68503 11.0781 1.91227L4.66145 8.32894C4.43422 8.55617 4.0658 8.55617 3.83856 8.32894L0.921892 5.41227C0.694656 5.18503 0.694656 4.81661 0.921892 4.58937C1.14913 4.36214 1.51755 4.36214 1.74479 4.58937L4.25001 7.09459L10.2552 1.08937C10.4825 0.862136 10.8509 0.862136 11.0781 1.08937Z" fill="#74798B"/>
                            </svg>`,
    elementDataAttribute: "element",
    
  };

  // Merge defaults with provided options
  var settings = $.extend({}, defaults, options);

  // Get the select element
  var $select = $(selector);

  // Initialize Select2
  $select.select2({
    placeholder: settings.placeholder,
    minimumResultsForSearch: 0,
    templateResult: function (content) {
      return formatOption(content, $select, settings);
    },
    templateSelection: function (content) {
      return formatSelection(content, settings);
    },
    escapeMarkup: function (markup) {
      return markup;
    },
    containerCssClass: settings.containerClass,
    dropdownCssClass: settings.dropdownClass,
  });

  // Event handler for updating checkmark
  $select.on("change", function () {
    if ($(".select2-container--open").length) {
      $(this).select2("close");
      $(this).select2("open");
    }
  });

  // Return the select object for chaining if needed
  return $select;
}

// Function to format dropdown options
function formatOption(content, $select, settings) {
  if (!content.id) {
    return content.text;
  }

  var element = $(content.element).data(settings.elementDataAttribute);
  var isCurrentlySelected = $select.val() === content.id;

  var $content = $(
    '<div class="' +
      settings.itemClass +
      '">' +
      element +
      '<span class="' +
      settings.textClass +
      '">' +
      content.text +
      "</span>" +
      (isCurrentlySelected
        ? '<span class="' +
          settings.selectedIconClass +
          '">' +
          settings.selectedIconContent +
          "</span>"
        : "") +
      "</div>"
  );

  return $content;
}

// Function to format selected option
function formatSelection(content, settings) {
  if (!content.id) {
    return content.text;
  }

  var element = $(content.element).data(settings.elementDataAttribute);

  var $content = $(
    '<div class="' +
      settings.itemClass +
      '">' +
      element +
      '<span class="' +
      settings.textClass +
      '">' +
      content.text +
      "</span>" +
      "</div>"
  );

  return $content;
}
