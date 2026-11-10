<?php

function sd_user_header(string $title, string $description, string $active): void
{
    ?>
    <!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sd_user_e($title); ?> | MindMerge SmartCampus</title>
    <link rel="stylesheet" href="../../assets/css/global.css"><link rel="stylesheet" href="../../assets/css/layout.css"><link rel="stylesheet" href="../../assets/css/components.css"><link rel="stylesheet" href="../../assets/css/portals.css"><link rel="stylesheet" href="../../assets/css/material-theme.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    .sd-user-wrap{max-width:1320px;margin:0 auto}.sd-user-hero{position:relative;overflow:hidden;border-radius:22px;padding:30px;color:#fff;background:radial-gradient(circle at 90% 10%,#22d3ee55,transparent 35%),linear-gradient(135deg,#312e81,#7a00ff 58%,#0891b2);box-shadow:0 18px 45px #4c1d9540}.sd-user-hero:after{content:"";position:absolute;width:190px;height:190px;border:1px solid #ffffff44;border-radius:50%;right:-35px;bottom:-80px}.sd-user-hero h1{margin:0 0 7px;font-size:30px}.sd-user-hero p{margin:0;color:#e0e7ff;max-width:740px}.sd-user-nav{display:flex;gap:8px;flex-wrap:wrap;margin:22px 0}.sd-user-nav a{background:var(--card);border:1px solid var(--border-color);border-radius:11px;padding:10px 14px;text-decoration:none;color:var(--text-primary)}.sd-user-nav a.active{background:#7a00ff;color:#fff;border-color:#7a00ff}.sd-user-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;margin:22px 0}.sd-user-card{background:var(--card);border:1px solid var(--border-color);border-radius:17px;padding:20px;box-shadow:0 8px 26px #0f172a0b}.sd-user-card h2,.sd-user-card h3{margin:0 0 9px}.sd-user-value{font-size:30px;font-weight:800;color:#7a00ff}.sd-user-table{width:100%;border-collapse:collapse;min-width:650px}.sd-user-table th,.sd-user-table td{padding:13px;border-bottom:1px solid var(--border-color);text-align:left}.sd-user-table th{color:var(--text-secondary);font-size:12px;text-transform:uppercase}.sd-user-table-wrap{overflow:auto}.sd-user-toolbar{display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin:18px 0}.sd-user-toolbar label{display:grid;gap:5px;font-size:12px;font-weight:700;color:var(--text-secondary)}.sd-user-toolbar select,.sd-user-toolbar input{padding:10px;border:1px solid var(--border-color);border-radius:9px;background:var(--surface);color:var(--text-primary)}.sd-user-muted{color:var(--text-secondary);font-size:13px}.sd-user-badge{display:inline-block;padding:4px 9px;border-radius:999px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:700}.sd-user-section{margin:22px 0}@media(max-width:700px){.sd-user-hero h1{font-size:24px}.sd-user-hero{padding:23px}.sd-user-toolbar>*{width:100%}}
    .sd-user-alert{padding:12px 15px;border-radius:11px;margin:18px 0;background:#ecfdf5;color:#065f46;font-weight:600}.sd-user-alert.error{background:#fef2f2;color:#991b1b}.sd-user-apply{justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border-color)}.sd-user-apply:last-child{border-bottom:0}
    </style></head><body><div class="app-layout"><?php include('../../partials/sidebar.php'); ?><div class="main-content"><?php include('../../partials/topbar.php'); ?><main class="page-content"><div class="sd-user-wrap">
    <section class="sd-user-hero"><h1><?php echo sd_user_e($title); ?></h1><p><?php echo sd_user_e($description); ?></p></section>
    <nav class="sd-user-nav"><?php foreach(['index.php'=>['Overview','fa-house','overview'],'clubs.php'=>['Clubs','fa-people-group','clubs'],'sports.php'=>['Sports','fa-futbol','sports'],'achievements.php'=>['Achievements','fa-award','achievements'],'programmes.php'=>['NCC / NSS','fa-shield-halved','programmes']] as $href=>$item): ?><a class="<?php echo $active === $item[2] ? 'active' : ''; ?>" href="<?php echo $href; ?>"><i class="fa-solid <?php echo $item[1]; ?>"></i> <?php echo $item[0]; ?></a><?php endforeach; ?></nav>
    <?php $flash = sd_user_take_flash(); if ($flash): ?><div class="sd-user-alert <?php echo ($flash['type'] ?? '') === 'error' ? 'error' : ''; ?>"><?php echo sd_user_e($flash['message']); ?></div><?php endif; ?>
    <?php
}

function sd_user_footer(): void
{
    ?></div></main></div></div><script src="../../assets/js/common.js"></script><script>
    (() => {
        document.querySelectorAll('.sd-user-wrap form[method="post"]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (!window.fetch || form.dataset.ajax === 'off') return;
                event.preventDefault();
                const button = form.querySelector('button[type="submit"],button:not([type])');
                if (button) { button.disabled = true; button.dataset.label = button.textContent; button.textContent = 'Submitting...'; }
                try {
                    const response = await fetch(form.action || window.location.href, {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
                    });
                    const result = await response.json();
                    const alert = document.createElement('div');
                    alert.className = 'sd-user-alert' + (result.success ? '' : ' error');
                    alert.textContent = result.message || 'Request completed.';
                    form.parentNode.insertBefore(alert, form);
                    if (result.success) form.remove();
                    window.setTimeout(() => alert.remove(), 4500);
                } catch (error) {
                    form.submit();
                } finally {
                    if (button) { button.disabled = false; button.textContent = button.dataset.label || 'Submit'; }
                }
            });
        });
    })();
    </script></body></html><?php
}
