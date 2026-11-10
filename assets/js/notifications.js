/**
 * MindMerge SmartCampus — Topbar notification bell
 */
document.addEventListener('DOMContentLoaded', () => {

const wrap = document.getElementById('notifBellWrap');

if (!wrap) {
    return;
}

const btn       = document.getElementById('notifBellBtn');
const dropdown  = document.getElementById('notifDropdown');
const badge     = document.getElementById('notifBellBadge');
const listEl    = document.getElementById('notifDropdownList');
const markAllBtn = document.getElementById('notifMarkAllBtn');

const basePath = wrap.dataset.base || '../notifications/';

function setBadge(count) {
    if (!badge) {
        return;
    }

    const n = parseInt(count, 10) || 0;

    if (n > 0) {
        badge.textContent = n > 99 ? '99+' : String(n);
        badge.classList.add('visible');
    } else {
        badge.textContent = '';
        badge.classList.remove('visible');
    }
}

function renderList(items) {
    if (!listEl) {
        return;
    }

    if (!items || items.length === 0) {
        listEl.innerHTML = `
            <div class="notif-dropdown-empty">
                <i class="fa-solid fa-bell-slash"></i>
                <p>No notifications yet</p>
            </div>`;
        return;
    }

    listEl.innerHTML = items.map(item => {
        const unreadClass = item.is_read ? '' : ' unread';
        const iconStyle = `background:${item.bg};color:${item.color}`;
        return `
            <a href="${basePath}view.php?id=${item.id}"
               class="notif-dropdown-item${unreadClass}">
                <div class="notif-dropdown-icon" style="${iconStyle}">
                    <i class="fa-solid ${item.icon}"></i>
                </div>
                <div class="notif-dropdown-body">
                    <div class="notif-dropdown-title">${escapeHtml(item.title)}</div>
                    <div class="notif-dropdown-meta">
                        <span>${escapeHtml(item.type_label)}</span>
                        &middot;
                        <span>${escapeHtml(item.time_ago)}</span>
                    </div>
                </div>
            </a>`;
    }).join('');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function loadBellData() {
    fetch(basePath + 'api/bell.php', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            return;
        }
        setBadge(data.unread_count);
        renderList(data.notifications);
    })
    .catch(() => {});
}

function toggleDropdown(forceOpen) {
    const isOpen = dropdown.classList.contains('open');
    const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !isOpen;

    if (shouldOpen) {
        dropdown.classList.add('open');
        loadBellData();
    } else {
        dropdown.classList.remove('open');
    }
}

if (btn) {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
    });
}

document.addEventListener('click', (e) => {
    if (!wrap.contains(e.target)) {
        toggleDropdown(false);
    }
});

if (markAllBtn) {
    markAllBtn.addEventListener('click', (e) => {
        e.preventDefault();

        fetch(basePath + 'api/mark_all_read.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                setBadge(0);
                loadBellData();
            }
        })
        .catch(() => {});
    });
}

loadBellData();

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        loadBellData();
    }
});

setInterval(() => {
    if (!document.hidden) {
        loadBellData();
    }
}, 30000);

});
