function fetchZones(elementID, dropSource = ''){
    $(elementID).empty();
    $.ajax({
        type: "POST",
        url: BASE_URL + "admin/Ajax_controller/get_project_zones_data",
        data: {
            'project_id': PROJECT_ID,
            'packages': [$('#selected_package').val()],
            'source': SOURCE,
            'dropSource': dropSource
        },
        success: function(data) {
            const opts = $.parseJSON(data);
            if(opts.length > 0){
                $(elementID).append('<option value="" data-img="" data-label="Select Zone">Select Zone</option>');
                $.each(opts, function(i, d) {
                    const labelText = d.zone_name;

                    $(elementID).append(
                        `<option value="${d.id}" data-img="" data-label="${labelText}">${labelText}</option>`
                    );
                });
            }

            $(elementID).select2({
                placeholder: 'Select Zone',
                templateResult: formatOption,
                templateSelection: formatOption,
                allowHtml: true,
                width: '100%'
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}    

function fetchFix(elementID, dropSource = ''){
    $(elementID).empty();
    $.ajax({
        type: "POST",
        url: BASE_URL + "admin/Ajax_controller/get_project_fix_data",
        data: {
            'project_id': PROJECT_ID,
            'packages': [$('#selected_package').val()],
            'zones': $('#zone_edit').val(),
            'source': SOURCE,
            'dropSource': dropSource
        },
        success: function(data) {
            const opts = $.parseJSON(data);
            if(opts.length > 0){
                $(elementID).append('<option value="" data-img="" data-label="Select Fix">Select Fix</option>');
                $.each(opts, function(i, d) {
                    const labelText = d.fix_name;

                    $(elementID).append(
                        `<option value="${d.id}" data-img="" data-label="${labelText}">${labelText}</option>`
                    );
                });
            }

            $(elementID).select2({
                placeholder: 'Select Fix',
                templateResult: formatOption,
                templateSelection: formatOption,
                allowHtml: true,
                width: '100%'
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function fetchTrade(elementID, dropSource = ''){
    $(elementID).empty();
    $.ajax({
        type: "POST",
        url: BASE_URL + "admin/Ajax_controller/get_project_trade_data",
        data: {
            'project_id': PROJECT_ID,
            'packages': [$('#selected_package').val()],
            'zones': $('#zone_edit').val(),
            'source': SOURCE,
            'dropSource': dropSource
        },
        success: function(data) {
            const opts = $.parseJSON(data);
            if(opts.length > 0){
                $(elementID).append('<option value="" data-img="" data-label="Select Trade">Select Trade</option>');
                $.each(opts, function(i, d) {
                    const labelText = d.trade_name;

                    $(elementID).append(
                        `<option value="${d.id}" data-img="" data-label="${labelText}">${labelText}</option>`
                    );
                });
            }

            $(elementID).select2({
                placeholder: 'Select Trade',
                templateResult: formatOption,
                templateSelection: formatOption,
                allowHtml: true,
                width: '100%'
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function formatOption(state) {
    if (!state.id) return state.text;

    const label = $(state.element).data('label');

    if (!label) return state.text;

    return $(`
        <span style="display: flex; align-items: center;">
            ${label}
        </span>
    `);
}

function fetchProjectPackages(elementID, dropSource = ''){
    $(elementID).empty();
    $.ajax({
        type: "POST",
        url: BASE_URL + "admin/Ajax_controller/get_project_packages_data",
        data: {
            'project_id': PROJECT_ID,
            'dropSource': dropSource
        },
        success: function(data) {
            const opts = $.parseJSON(data);
            $(elementID).append('<option value="" data-img="" data-label="Select Package">Select Package</option>');
            if(opts.length > 0){
                $.each(opts, function(i, d) {
                    const labelText = d.package_name;

                    $(elementID).append(
                        `<option value="${d.id}" data-img="" data-label="${labelText}">${labelText}</option>`
                    );
                });
            }

            $(elementID).select2({
                placeholder: 'Select Package',
                templateResult: formatOption,
                templateSelection: formatOption,
                allowHtml: true,
                width: '100%'
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function fetchDoerRoles(elementID, dropSource = ''){
    $(elementID).empty();
    $.ajax({
        type: "POST",
        url: BASE_URL + "admin/Ajax_controller/get_project_doer_roles_data",
        data: {
            'project_id': PROJECT_ID,
            'packages': [$('#selected_package').val()],
            'zones': $('#zone_edit').val(),
            'source': SOURCE,
            'dropSource': dropSource
        },
        success: function(data) {
            const opts = $.parseJSON(data);
            if(opts.length > 0){
                $(elementID).append('<option value="" data-img="" data-label="Select Role">Select Role</option>');
                $.each(opts, function(i, d) {
                    const labelText = d.role_name;

                    $(elementID).append(
                        `<option value="${d.id}" data-img="" data-label="${labelText}">${labelText}</option>`
                    );
                });
            }

            $(elementID).select2({
                placeholder: 'Select Role',
                templateResult: formatOption,
                templateSelection: formatOption,
                allowHtml: true,
                width: '100%'
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function fetchDoers(elementID, dropSource = ''){
    $(elementID).empty();
    $.ajax({
        type: "POST",
        url: BASE_URL + "admin/Ajax_controller/get_project_doers_data",
        data: {
            'project_id': PROJECT_ID,
            'packages': [$('#selected_package').val()],
            'zones': $('#zone_edit').val(),
            'source': SOURCE,
            'dropSource': dropSource
        },
        success: function(data) {
            const opts = $.parseJSON(data);
            if(opts.length > 0){
                $(elementID).append('<option value="" data-img="" data-label="Select Doer">Select Doer</option>');
                $.each(opts, function(i, d) {
                    const labelText = '(' + d.emp_code + ') ' + d.d.person_name;

                    $(elementID).append(
                        `<option value="${d.id}" data-img="" data-label="${labelText}">${labelText}</option>`
                    );
                });
            }

            $(elementID).select2({
                placeholder: 'Select Doer',
                templateResult: formatOption,
                templateSelection: formatOption,
                allowHtml: true,
                width: '100%'
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}

function fetchFilterUniqueValues(elementID, table, column, project_column, label, dropSource = ''){
    $(elementID).empty();
    $.ajax({
        type: "POST",
        url: BASE_URL + "admin/Ajax_controller/get_project_filters_unique_values_data",
        data: {
            'project_id': PROJECT_ID,
            'table': table,
            'column': column,
            'project_column': project_column,
            'source': SOURCE,
            'dropSource': dropSource
        },
        success: function(data) {
            const opts = $.parseJSON(data);
            if(opts.length > 0){
                $(elementID).append('<option value="" data-img="" data-label="Select Role">Select ' + label + '</option>');
                $.each(opts, function(i, d) {
                    const labelText = d[column];

                    $(elementID).append(
                        `<option value="${labelText}" data-img="" data-label="${labelText}">${labelText}</option>`
                    );
                });
            }

            $(elementID).select2({
                placeholder: 'Select ' + label,
                templateResult: formatOption,
                templateSelection: formatOption,
                allowHtml: true,
                width: '100%'
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}