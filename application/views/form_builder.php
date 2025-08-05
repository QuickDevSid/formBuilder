<?php include('header.php'); ?>
<style type="text/css">
    .error {
        color: red;
        float: left;
    }
    .chosen-container {
        font-size: 14px;
    }
</style>
<div class="main-content" id="project-list">
    <div class="container-fluid p-0">
        <div class="card p-4 mb-4">
            <div class="client-header">
                <div class="client-title">
                    <h3 class="mb-3">Module Creation</h3>
                </div>
            </div>
            <div class="client-body p-3">
                <form class="rt-form" method="post" name="module_form" id="module_form">
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-12 mb-3 form-group">
                                <label class="form-label text-sm-medium text-tertiary">Module Name <b style="color:red;">*</b></label>
                                <input type="text" class="form-control text-md text-secondary" placeholder="Enter Module Name" name="module_name" id="module_name" value="">
                                <span class="" id="module_name_error"></span>
                            </div>
                            <div class="col-md-12 mb-3 form-group">
                                <label class="form-label text-sm-medium text-tertiary">Module Description</label>
                                <textarea class="form-control text-md text-secondary" placeholder="Enter Module Description" name="description" id="description"></textarea>
                                <span class="" id="description_error"></span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-12">
                                <label class="form-label text-sm-medium text-tertiary">Fields <b style="color:red;">*</b></label>
                                <table class="table table-bordered" id="field_table">
                                    <thead>
                                        <tr>
                                            <th>Label Name <b style="color:red;">*</b></th>
                                            <th>Field Type <b style="color:red;">*</b></th>
                                            <th>Length <b style="color:red;">*</b></th>
                                            <th>Is Required? <b style="color:red;">*</b></th>
                                            <th>Handle Unique? <b style="color:red;">*</b></th>
                                            <th>Dependent Module</th>
                                            <th>Dependent Module Field</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="field_rows"></tbody>
                                </table>
                                <button type="button" class="btn btn-secondary btn-sm" id="add_field_btn"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3 form-group">
                                <button type="submit" id="submit_btn" class="save-btn me-3 button button-primary text-md">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
<script>
    var BASE_URL = '<?=base_url(); ?>';
    $(document).ready(function () {
        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || /^\s/.test(value) === false;
        });

        $.validator.addMethod("noNumbers", function (value, element) {
            return this.optional(element) || !/\d/.test(value);
        });

        $.validator.addMethod("noSpecialChars", function (value, element) {
            return this.optional(element) || /^[a-zA-Z0-9\s\-]+$/.test(value);
        });

        let fieldIndex = 0;

        function getFieldRow(index) {
            const isFirstRow = index === 0;
            return `
            <tr>
                <td>
                    <input type="text" name="fields[${index}][label_name]" class="form-control label_name" required>
                </td>
                <td>
                    <select name="fields[${index}][data_type]" class="form-control data_type chosen-select" required>
                        <option value="">Select Field Type</option>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="email">Email</option>
                        <option value="password">Password</option>
                        <option value="mobile">Mobile</option>
                        <option value="date">Date</option>
                        <option value="time">Time</option>
                        <option value="datetime">Date & Time</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio Button</option>
                        <option value="file">File Upload</option>
                        <option value="url">URL</option>
                        <option value="color">Color Picker</option>
                        <option value="range">Range Slider</option>
                        <option disabled value="hidden">Hidden</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="dropdown-dependent">Dependent Dropdown</option>
                        <option value="textarea">Textarea</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="fields[${index}][length]" class="form-control length" min="1" required>
                </td>
                <td>
                    <select name="fields[${index}][is_required]" class="form-control is_required chosen-select" required>
                        <option value="">Select Option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </td>
                <td>
                    <select name="fields[${index}][is_unique]" class="form-control is_unique chosen-select" required>
                        <option value="">Select Option</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </td>
                <td>
                    <select name="fields[${index}][dependent_module]" class="form-control dependent_module chosen-select" data-index="${index}"></select>
                </td>
                <td>
                    <select name="fields[${index}][dependent_module_field]" class="form-control dependent_module_field chosen-select" data-index="${index}"></select>
                </td>
                <td>
                    ${isFirstRow ? '' : '<button type="button" class="btn btn-danger btn-sm remove_field">Remove</button>'}
                </td>
            </tr>`;
        }

        $('#add_field_btn').click(function () {
            $('#field_rows').append(getFieldRow(fieldIndex));

            $(`input[name="fields[${fieldIndex}][label_name]"]`).rules("add", {
                required: true,
                noSpaceAtStart: true,
                noSpecialChars: true,
                messages: {
                    required: "Please Enter Label Name !",
                    noSpaceAtStart: "No leading space !",
                    noSpecialChars: "No special characters !"
                }
            });

            $(`select[name="fields[${fieldIndex}][data_type]"]`).rules("add", {
                required: true,
                messages: {
                    required: "Please Select Field Type !"
                }
            });

            $(`input[name="fields[${fieldIndex}][length]"]`).rules("add", {
                required: true,
                number: true,
                min: 1,
                messages: {
                    required: "Please Enter Length !",
                    number: "Must be number !",
                    min: "Min value is 1 !"
                }
            });

            $(`select[name="fields[${fieldIndex}][is_required]"]`).rules("add", {
                required: true,
                messages: {
                    required: "Please Select Option !"
                }
            });

            $(`select[name="fields[${fieldIndex}][is_unique]"]`).rules("add", {
                required: true,
                messages: {
                    required: "Please Select Option !"
                }
            });

            let moduleSelect = $(`select[name="fields[${fieldIndex}][dependent_module]"]`);

            fetchModules(moduleSelect);

            initChosen();

            fieldIndex++;
        });

        $(document).on('click', '.remove_field', function () {
            $(this).closest('tr').remove();
        });

        $('#module_form').validate({
            ignore: "hidden",
            rules: {
                module_name: {
                    required: true,
                    noSpaceAtStart: true,
                    noNumbers: true,
                    noSpecialChars: true,
                },
            },
            messages: {
                module_name: {
                    required: 'Please Enter Module Name !',
                    noNumbers: 'Name Must Not Contain Numbers !',
                    noSpaceAtStart: 'First Letter Can Not Be Space !',
                    noSpecialChars: "Special Characters Are Not Allowed !",
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group, td').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                form.submit();
                $('#submit_btn').remove();
            }            
        });

        function initChosen() {
            $('.chosen-select').chosen({
                width: '100%',
                allow_single_deselect: true,
                placeholder_text_single: 'Select an option',
                disable_search_threshold: 10
            });
        }

        initChosen();

        $('#add_field_btn').trigger('click');

        $(document).on('change', '.dependent_module', function () {
            let moduleId = $(this).val();
            let index = $(this).data('index');
            let $fieldDropdown = $(`select[name="fields[${index}][dependent_module_field]"]`);

            fetchModuleFieldsById($fieldDropdown, moduleId);

            if (moduleId !== '') {
                $fieldDropdown.rules("add", {
                    required: true,
                    messages: {
                        required: "Please select a dependent module field!"
                    }
                });
            } else {
                $fieldDropdown.rules("remove", "required");
            }
        });
    });

    function fetchModules($element) {
        $element.empty();
        $.ajax({
            type: "POST",
            url: BASE_URL + "Form_builder_ajax_controller/get_project_modules_data",
            dataType: 'json',
            data: { project: '' },
            success: function(opts) {
                if (opts.length > 0) {
                    $element.append('<option value="">Select Dependent Module</option>');
                    $.each(opts, function(i, d) {
                        $element.append(`<option value="${d.id}">${d.module_name}</option>`);
                    });
                }
                $element.trigger('chosen:updated');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }

    function fetchModuleFieldsById($element, moduleId) {
        $element.empty();
        $.ajax({
            type: "POST",
            url: BASE_URL + "Form_builder_ajax_controller/get_project_module_fields_data",
            data: {
                project: '',
                module: moduleId,
                is_unique: '',
                is_dependent: 'No'
            },
            success: function(data) {
                const opts = $.parseJSON(data);
                if (opts.length > 0) {
                    $element.append('<option value="">Select Dependent Module Field</option>');
                    $.each(opts, function(i, d) {
                        $element.append(`<option value="${d.id}">${d.label}</option>`);
                    });
                }
                $element.trigger('chosen:updated');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
</script>