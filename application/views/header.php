<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Form Builder</title>
        <link href="<?= base_url() ?>admin_assets/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?= base_url() ?>admin_assets/css/dataTables.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="<?= base_url() ?>admin_assets/css/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@latest/dist/plugins/monthSelect/style.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet">
        <link rel="stylesheet" href="<?= base_url() ?>admin_assets/css/select2.min.css">
        <link rel="stylesheet" href="<?= base_url() ?>admin_assets/css/select2_one.css">
        <link rel="stylesheet" href="<?= base_url() ?>admin_assets/css/dashboard.css">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" rel="stylesheet" />
    </head>
    <body>
        <div class="loader_div">
            <span class="loader"></span>
        </div>
        <div class="header">
            <div class="logo">
                <img src="<?= base_url() ?>admin_assets/images/blank.png" alt="No Image" srcset="">
            </div>
            <div class="d-flex align-items-center faq-icons me-3">
                <div class="dropdown ml-2">
                    <a class="rounded-circle " href="#" role="button" id="dropdownUser" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-md avatar-indicators avatar-online">
                            <img alt="avatar" src="<?= base_url() ?>admin_assets/images/blank.png"
                                class="rounded-circle avatar">
                        </div>
                    </a>
                    <!-- <div class="dropdown-menu pb-2" aria-labelledby="dropdownUser">
                        <div class="dropdown-item">
                            <div class="d-flex gap-2 py-2">
                                <div class="ml-3 lh-1">
                                    <h5 class="text-md text-primary mb-0">Annette Black</h5>
                                    <p class="text-sm-regular text-tertiary mb-0">annette@company.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-md text-primary" href="<?= base_url() ?>logout" id="sign_out"
                            onclick="return confirm('Are you sure you want to sign out ?');">
                            <span class="mr-3 span-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-power">
                                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                    <line x1="12" y1="2" x2="12" y2="12"></line>
                                </svg>
                            </span>Sign Out
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="sidebar">
            <div class="menu">
                <div class="side-header">
                    <a href="#" id="toggle-sidebar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M2 12C2 8.25027 2 6.3754 2.95491 5.06107C3.26331 4.6366 3.6366 4.26331 4.06107 3.95491C5.3754 3 7.25027 3 11 3H13C16.7497 3 18.6246 3 19.9389 3.95491C20.3634 4.26331 20.7367 4.6366 21.0451 5.06107C22 6.3754 22 8.25027 22 12C22 15.7497 22 17.6246 21.0451 18.9389C20.7367 19.3634 20.3634 19.7367 19.9389 20.0451C18.6246 21 16.7497 21 13 21H11C7.25027 21 5.3754 21 4.06107 20.0451C3.6366 19.7367 3.26331 19.3634 2.95491 18.9389C2 17.6246 2 15.7497 2 12Z"
                                stroke="#74798B" stroke-width="1.8" stroke-linejoin="round" />
                            <path d="M9.5 3.5L9.5 20.5" stroke="#74798B" stroke-width="1.8" stroke-linejoin="round" />
                            <path d="M5 7C5 7 5.91421 7 6.5 7" stroke="#74798B" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M5 11H6.5" stroke="#74798B" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path
                                d="M17 10L15.7735 11.0572C15.2578 11.5016 15 11.7239 15 12C15 12.2761 15.2578 12.4984 15.7735 12.9428L17 14"
                                stroke="#74798B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
                <a href="<?= base_url(); ?>" class="menu-item active">
                    <span class="nav-icons">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9.5L12 3L21 9.5V20C21 20.5523 20.5523 21 20 21H16C15.4477 21 15 20.5523 15 20V16C15 15.4477 14.5523 15 14 15H10C9.44772 15 9 15.4477 9 16V20C9 20.5523 8.55228 21 8 21H4C3.44772 21 3 20.5523 3 20V9.5Z" stroke="#74798B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="nav-content">Home</span>
                </a>
                <a href="<?= base_url(); ?>creation" class="menu-item active">
                    <span class="nav-icons">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 20h9" stroke="#74798B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z" stroke="#74798B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="nav-content">Module Creation</span>
                </a>
                <a href="<?= base_url(); ?>list" class="menu-item active">
                    <span class="nav-icons">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="8" y1="6" x2="21" y2="6" stroke="#74798B" stroke-width="2"/>
                            <line x1="8" y1="12" x2="21" y2="12" stroke="#74798B" stroke-width="2"/>
                            <line x1="8" y1="18" x2="21" y2="18" stroke="#74798B" stroke-width="2"/>
                            <circle cx="3.5" cy="6" r="1.5" fill="#74798B"/>
                            <circle cx="3.5" cy="12" r="1.5" fill="#74798B"/>
                            <circle cx="3.5" cy="18" r="1.5" fill="#74798B"/>
                        </svg>
                    </span>
                    <span class="nav-content">Created Modules List</span>
                </a>
            </div>
        </div>       
   