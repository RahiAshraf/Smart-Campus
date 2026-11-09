<?php

function sd_admin_header(string $title, string $description, string $active): void
{
    global $sd_admin_csrf;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo sd_admin_e($title); ?> | MindMerge SmartCampus</title>
        <link rel="stylesheet" href="../../assets/css/global.css">
        <link rel="stylesheet" href="../../assets/css/layout.css">
        <link rel="stylesheet" href="../../assets/css/components.css">
        <link rel="stylesheet" href="../../assets/css/portals.css">
        <link rel="stylesheet" href="../../assets/css/material-theme.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            .sd-admin-wrap{max-width:1400px;margin:0 auto}
            .sd-admin-hero{background:linear-gradient(135deg,#4c1d95,#7a00ff 55%,#06b6d4);color:#fff;border-radius:20px;padding:28px 32px;margin-bottom:24px}
            .sd-admin-hero h1{margin:0 0 8px;font-size:30px}.sd-admin-hero p{margin:0;color:#ede9fe;max-width:820px}
            .sd-admin-nav{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 24px}
            .sd-admin-nav a{padding:10px 14px;border:1px solid var(--border-color);border-radius:10px;text-decoration:none;color:var(--text-primary);background:var(--card)}
            .sd-admin-nav a.active{background:#7a00ff;color:#fff;border-color:#7a00ff}
            .sd-admin-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;margin-bottom:24px}
            .sd-admin-card{background:var(--card);border:1px solid var(--border-color);border-radius:16px;padding:20px}
            .sd-admin-card h2,.sd-admin-card h3{margin:0 0 8px}.sd-admin-value{font-size:30px;font-weight:800;color:#7a00ff}
            .sd-admin-table-wrap{overflow:auto;background:var(--card);border:1px solid var(--border-color);border-radius:16px}
            .sd-admin-table{width:100%;border-collapse:collapse;min-width:760px}.sd-admin-table th,.sd-admin-table td{padding:13px 15px;border-bottom:1px solid var(--border-color);text-align:left;vertical-align:top}
            .sd-admin-table th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--text-secondary);background:rgba(122,0,255,.04)}
            .sd-admin-toolbar,.sd-admin-form{display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-bottom:18px}
            .sd-admin-form label{display:grid;gap:5px;font-size:12px;font-weight:700;color:var(--text-secondary)}

            
/* Common Student Development filter toolbar */
.sd-admin-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    max-width: 100%;
    flex-wrap: wrap;
    box-sizing: border-box;
    margin-bottom: 18px;
}

/* Search input and dropdown */
.sd-admin-toolbar input,
.sd-admin-toolbar select {
    height: 44px;
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text-primary);
    font-size: 14px;
    box-sizing: border-box;
    min-width: 0;
}

/* Search input takes available space */
.sd-admin-toolbar input {
    flex: 1 1 250px;
    width: 100%;
}

/* Dropdown */
.sd-admin-toolbar select {
    flex: 0 1 190px;
    width: 190px;
    cursor: pointer;
}

/* Focus effect */
.sd-admin-toolbar input:focus,
.sd-admin-toolbar select:focus {
    outline: none;
    border-color: #7a00ff;
    box-shadow: 0 0 0 3px rgba(122, 0, 255, 0.12);
}

/* Buttons */
.sd-admin-toolbar .btn {
    height: 44px;
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    border-radius: 8px;
}

/* Mobile */
@media (max-width: 600px) {
    .sd-admin-toolbar input,
    .sd-admin-toolbar select {
        flex: 1 1 100%;
        width: 100%;
    }

    .sd-admin-toolbar .btn {
        flex: 1;
    }
}


            .sd-admin-form input,.sd-admin-form select,.sd-admin-form textarea{padding:10px 12px;border:1px solid var(--border-color);border-radius:8px;background:var(--surface);color:var(--text-primary);min-width:150px}
            .sd-admin-form textarea{min-width:260px}.sd-admin-actions{display:flex;gap:6px;flex-wrap:wrap}.sd-admin-muted{color:var(--text-secondary);font-size:13px}
            .sd-admin-alert{padding:12px 15px;border-radius:10px;margin-bottom:18px;background:#ecfdf5;color:#065f46}.sd-admin-alert.error{background:#fef2f2;color:#991b1b}
            .sd-admin-toast{position:fixed;right:24px;bottom:24px;z-index:10000;max-width:360px;padding:13px 17px;border-radius:12px;background:#065f46;color:#fff;box-shadow:0 12px 30px rgba(15,23,42,.22);font-weight:700}
            .sd-admin-toast.error{background:#991b1b}
            .sd-admin-section{margin-bottom:24px}.sd-admin-section-header{display:flex;justify-content:space-between;gap:15px;align-items:center;margin-bottom:14px}
            .sd-admin-section-header h2{margin:0}.sd-admin-badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:700}
            .sd-admin-form input.flatpickr-input,
            .sd-admin-form input[type="date"]{height:42px}
            .sd-admin-form .flatpickr-input{border-radius:11px;transition:border-color .2s ease,box-shadow .2s ease,background-color .2s ease}
            .sd-admin-form .flatpickr-input:focus{outline:none;border-color:#7a00ff;box-shadow:0 0 0 3px rgba(122,0,255,.14)}
            .flatpickr-calendar{border:1px solid rgba(122,0,255,.18);border-radius:14px;box-shadow:0 18px 45px rgba(15,23,42,.18);overflow:hidden;font-family:inherit}
            .flatpickr-months{background:linear-gradient(135deg,#5b21b6,#7a00ff);padding:7px 4px}
            .flatpickr-months .flatpickr-month,.flatpickr-current-month,.flatpickr-weekdays,.flatpickr-weekday{color:#fff}
            .flatpickr-current-month .flatpickr-monthDropdown-months,.flatpickr-current-month input.cur-year{font-weight:700;color:#fff}
            .flatpickr-current-month .flatpickr-monthDropdown-months option{color:#111827}
            .flatpickr-prev-month,.flatpickr-next-month{fill:#fff;color:#fff}
            .flatpickr-weekdays{background:#5b21b6}
            span.flatpickr-weekday{font-size:11px;font-weight:700}
            .flatpickr-day{border-radius:9px;margin:2px;width:36px;max-width:36px;height:36px;line-height:36px}
            .flatpickr-day:hover{background:#ede9fe;border-color:#ede9fe;color:#5b21b6}
            .flatpickr-day.today{border-color:#7a00ff;color:#7a00ff}
            .flatpickr-day.selected,.flatpickr-day.selected:hover{background:#7a00ff;border-color:#7a00ff;color:#fff}
            .flatpickr-day.flatpickr-disabled{color:#cbd5e1}
            html.dark-mode .flatpickr-calendar{background:#172033;border-color:#334155;color:#e2e8f0}
            html.dark-mode .flatpickr-day{color:#e2e8f0}
            html.dark-mode .flatpickr-day:hover{background:#312e81;border-color:#312e81;color:#fff}
            html.dark-mode .flatpickr-day.prevMonthDay,html.dark-mode .flatpickr-day.nextMonthDay{color:#64748b}
            html.dark-mode .flatpickr-months,html.dark-mode .flatpickr-weekdays{background:#4c1d95}
            html.dark-mode .flatpickr-current-month .flatpickr-monthDropdown-months,
            html.dark-mode .flatpickr-current-month input.cur-year{color:#fff}
            @media(max-width:600px){.flatpickr-calendar{width:calc(100vw - 28px);left:14px!important}.flatpickr-days,.dayContainer{width:100%}.flatpickr-day{width:calc((100vw - 62px)/7);max-width:none}}
            @media(max-width:700px){.sd-admin-hero{padding:22px}.sd-admin-hero h1{font-size:24px}.sd-admin-form>*{width:100%}.sd-admin-form input,.sd-admin-form select,.sd-admin-form textarea{width:100%}}
        </style>
    </head>
    <body>
    <div class="app-layout">
        <?php include('../../partials/sidebar.php'); ?>
        <div class="main-content">
            <?php include('../../partials/topbar.php'); ?>
            <main class="page-content"><div class="sd-admin-wrap">
                <section class="sd-admin-hero">
                    <h1><?php echo sd_admin_e($title); ?></h1>
                    <p><?php echo sd_admin_e($description); ?></p>
                </section>
                <?php $flash = sd_admin_take_flash(); if ($flash): ?>
                    <div class="sd-admin-alert <?php echo $flash['type'] === 'error' ? 'error' : ''; ?>"><?php echo sd_admin_e($flash['message']); ?></div>
                <?php endif; ?>
                <nav class="sd-admin-nav">
                    <?php foreach ([
                        'index.php' => ['Dashboard', 'fa-chart-pie', 'index'],
                        'clubs.php' => ['Activities & Clubs', 'fa-people-group', 'clubs'],
                        'sports.php' => ['Sports', 'fa-futbol', 'sports'],
                        'achievements.php' => ['Certifications', 'fa-award', 'achievements'],
                        'ncc.php' => ['NCC', 'fa-shield-halved', 'ncc'],
                        'nss.php' => ['NSS', 'fa-hands-helping', 'nss'],
                        'teams.php' => ['Teams & Events', 'fa-trophy', 'teams'],
                        'reports.php' => ['Reports', 'fa-file-export', 'reports'],
                        'audit.php' => ['Audit history', 'fa-clock-rotate-left', 'audit'],
                    ] as $href => [$label, $icon, $key]): ?>
                        <a class="<?php echo $active === $key ? 'active' : ''; ?>" href="<?php echo $href; ?>"><i class="fa-solid <?php echo $icon; ?>"></i> <?php echo $label; ?></a>
                    <?php endforeach; ?>
                </nav>
    <?php
}

function sd_admin_footer(): void
{
    ?>
            </div></main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script>
        (() => {
            const adminRoot = document.querySelector('.sd-admin-wrap');
            if (!adminRoot || typeof flatpickr !== 'function') {
                return;
            }

            adminRoot.querySelectorAll('input[type="date"]:not([data-flatpickr-initialized])').forEach((input) => {
                input.dataset.flatpickrInitialized = 'true';
                flatpickr(input, {
                    altInput: true,
                    altFormat: 'd-m-Y',
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    disableMobile: true,
                    animate: true,
                    monthSelectorType: 'dropdown'
                });
            });

            const showToast = (message, success) => {
                const toast = document.createElement('div');
                toast.className = `sd-admin-toast${success ? '' : ' error'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                window.setTimeout(() => toast.remove(), 3500);
            };

            adminRoot.addEventListener('submit', async (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post' || form.dataset.ajax === 'off') {
                    return;
                }

                event.preventDefault();
                const submitter = event.submitter;
                if (submitter && submitter.dataset.confirm && !window.confirm(submitter.dataset.confirm)) {
                    return;
                }

                const button = submitter || form.querySelector('button[type="submit"]');
                const actionName = form.querySelector('input[name="action"]')?.value || '';
                if (button) {
                    button.disabled = true;
                    button.dataset.originalText = button.textContent;
                    button.textContent = 'Saving...';
                }

                try {
                    const response = await fetch(form.action || window.location.href, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    const payload = await response.json();
                    showToast(payload.message || 'Operation completed.', payload.success === true && response.ok);
                    if (payload.success === true && form.dataset.ajaxReset === 'true') {
                        form.reset();
                    }
                    if (payload.success === true && form.dataset.ajaxRemove === 'true') {
                        form.closest('tr, [data-record-row]')?.remove();
                    }
                    if (payload.success === true && actionName.indexOf('delete_') === 0) {
                        form.closest('tr, [data-record-row]')?.remove();
                    }
                } catch (error) {
                    showToast('The operation could not be completed. Please try again.', false);
                } finally {
                    if (button) {
                        button.disabled = false;
                        button.textContent = button.dataset.originalText || 'Save';
                    }
                }
            });
        })();
    </script>
    <script src="../../assets/js/common.js"></script>
    </body>
    </html>
    <?php
}
