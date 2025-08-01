        <?php if ($this->session->flashdata('success') != "") { ?>
            <div id="gen-alert" class="alert alert-success animated fadeInUp" style="color:#297401;">
                <strong style="color:#297401; "> <?= $this->session->flashdata('success') ?></strong>
            </div>
        <?php } else if ($this->session->flashdata('message') != "") { ?>
            <div id="gen-alert" class="alert alert-danger animated fadeInUp">
                <strong> <?= $this->session->flashdata('message') ?></strong>
            </div>
        <?php } elseif (validation_errors() != '') { ?>
            <div id="gen-alert" class="alert alert-danger animated fadeInUp">
                <strong> <?= validation_errors() ?></strong>
            </div>
        <?php } ?>
        <script src="<?= base_url() ?>admin_assets/js/jquery.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="<?= base_url() ?>admin_assets/js/jquery.validate.min.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/select2.min.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/dataTables.min.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/dataTables.buttons.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/buttons.dataTables.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/jszip.min.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/flatpickr.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/buttons.html5.min.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/additional-methods.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
        <script src="<?= base_url() ?>admin_assets/js/custom.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                flatpickr(".daterange", {
                    mode: "range",
                    dateFormat: "d-m-Y",
                    altInput: true,
                    altFormat: "d-m-Y",
                    allowInput: true,
                    placeholder: "Select Date Range"
                });

                $('.datepicker').flatpickr({
                    placeholder: "Select Date",
                    dateFormat: "d-m-Y",
                });

                $('.add-client-btn').click(function() {
                    $('#client-sidebar').toggleClass('show');
                });

                $('.add-project-btn').click(function() {
                    $('#project-sidebar').toggleClass('show');
                });

                $('.close-button').click(function() {
                    $('.rt-sidebar').removeClass('show');
                });

                $('.tab').click(function() {
                    $('.tab').removeClass('active');
                    $(this).addClass('active');

                    const tabIndex = $(this).index();
                    $('.tab-content').hide();
                    $('.tab-content').eq(tabIndex).show();
                });

                $('.action-btn.primary').click(function() {
                    $(this).toggleClass('active');
                    if ($(this).hasClass('active')) {
                        $(this).html('<i class="fas fa-check"></i>');
                        $(this).css('background-color', '#34A853');
                        $(this).css('color', 'white');
                    } else {
                        $(this).html('<i class="fas fa-check"></i>');
                        $(this).css('background-color', '#e8f0fe');
                        $(this).css('color', '#4285F4');
                    }
                });

                $('#toggle-fields-btn').click(function() {
                    $('#optional-fields').removeClass('d-none');
                    $(this).hide();
                });
            });

            $('.dropdown-toggle-split').click(function() {
                $(this).next('.dropdown-menu').toggleClass('show');
            })

            $(".alert").fadeTo(3000, 500).slideUp(500, function() {
                $(".alert").slideUp(500);
            });

            $(document).on('click', '.fa-star', function() {
                $(this).toggleClass('fas').toggleClass('far');
            });
        </script>
    </body>
</html>