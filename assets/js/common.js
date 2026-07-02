/* ========================================
   SIDEBAR
======================================== */

const sidebar =
document.getElementById('sidebar');

const openSidebar =
document.getElementById('openSidebar');

const closeSidebar =
document.getElementById('closeSidebar');

const collapseSidebar =
document.getElementById('sidebarCollapseToggle');

function applySidebarState(){

const collapsed =
localStorage.getItem('mindmerge-sidebar-collapsed') === 'yes';

if(collapsed){
document.body.classList.add('sidebar-collapsed');
}
else{
document.body.classList.remove('sidebar-collapsed');
}

}

applySidebarState();

/* OPEN SIDEBAR */

if(openSidebar){

openSidebar.addEventListener('click',()=>{

sidebar.classList.add('active');

});

}

/* CLOSE SIDEBAR */

if(closeSidebar){

closeSidebar.addEventListener('click',()=>{

sidebar.classList.remove('active');

});

}

if(collapseSidebar){

collapseSidebar.addEventListener('click',()=>{

const collapsed =
document.body.classList.toggle('sidebar-collapsed');

localStorage.setItem(
'mindmerge-sidebar-collapsed',
collapsed ? 'yes' : 'no'
);

});

}


/* ========================================
   THEME
======================================== */
document.addEventListener("DOMContentLoaded", () => {

const btn =
document.getElementById("themeToggle");

function applyTheme(){

const theme =
localStorage.getItem(
"mindmerge-theme"
) || "light";

if(theme === "dark"){

document.body.classList.add("dark-mode");
document.documentElement.classList.add("dark-mode");

}
else{

document.body.classList.remove("dark-mode");
document.documentElement.classList.remove("dark-mode");


}

if(btn){

const icon =
btn.querySelector("i");

if(theme === "dark"){

icon.className =
"fa-solid fa-sun";

}
else{

icon.className =
"fa-solid fa-moon";

}

}

}

applyTheme();

if(btn){

btn.addEventListener("click", () => {

const currentTheme =
localStorage.getItem(
"mindmerge-theme"
) || "light";

const newTheme =
currentTheme === "dark"
? "light"
: "dark";

localStorage.setItem(
"mindmerge-theme",
newTheme
);

applyTheme();

});

}

});

/* =========================================
   TABLE SORTING
========================================= */

document.addEventListener('DOMContentLoaded', () => {

document.querySelectorAll('.custom-table').forEach(table => {

const headers = table.querySelectorAll('th');

headers.forEach((header,index)=>{

if(header.dataset.sort !== 'true'){
return;
}

header.style.cursor = 'pointer';

if(!header.querySelector('i')){

header.innerHTML +=
' <i class="fa-solid fa-sort"></i>';

}

let asc = true;

header.addEventListener('click',()=>{

const rows =
Array.from(
table.querySelectorAll('tbody tr')
);

rows.sort((a,b)=>{

const aCell =
a.children[index];

const bCell =
b.children[index];

const aValue =
aCell.dataset.value ||
aCell.innerText.trim().toLowerCase();

const bValue =
bCell.dataset.value ||
bCell.innerText.trim().toLowerCase();

const aNum = Number(aValue);
const bNum = Number(bValue);

if(
!isNaN(aNum) &&
!isNaN(bNum)
){

return asc
? aNum - bNum
: bNum - aNum;

}

return asc
? aValue.localeCompare(bValue)
: bValue.localeCompare(aValue);

});

asc = !asc;

rows.forEach(row=>{

table
.querySelector('tbody')
.appendChild(row);

});

});

});

});

});

/* ==========================================
   GLOBAL FORM VALIDATION
========================================== */

document.addEventListener('DOMContentLoaded', () => {

    // Mobile Number Validation
    document.querySelectorAll(
    'input[name*="phone"], input[name*="mobile"], input[type="tel"]'
).forEach(field => {

        field.addEventListener('input', function () {

            // Allow digits only
            this.value = this.value.replace(/\D/g, '');

            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }

        });

        field.addEventListener('blur', function () {

            if (
                this.value.trim() !== '' &&
                !/^[6-9]\d{9}$/.test(this.value)
            ) {
                this.setCustomValidity(
                    'Enter a valid 10-digit mobile number'
                );
            } else {
                this.setCustomValidity('');
            }

        });

    });


    // Email Validation
    document.querySelectorAll('input[type="email"]').forEach(field => {

        field.addEventListener('blur', function () {

            const emailRegex =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (
                this.value.trim() !== '' &&
                !emailRegex.test(this.value)
            ) {
                this.setCustomValidity(
                    'Enter a valid email address'
                );
            } else {
                this.setCustomValidity('');
            }

        });

    });


    // Prevent Invalid Form Submission
    document.querySelectorAll('form').forEach(form => {

        form.addEventListener('submit', function (e) {

            let valid = true;

            form.querySelectorAll(
                'input[type="tel"], .mobile-field'
            ).forEach(field => {

                if (
                    field.value.trim() !== '' &&
                    !/^[6-9]\d{9}$/.test(field.value)
                ) {
                    valid = false;
                    field.reportValidity();
                }

            });

            form.querySelectorAll(
                'input[type="email"]'
            ).forEach(field => {

                const emailRegex =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (
                    field.value.trim() !== '' &&
                    !emailRegex.test(field.value)
                ) {
                    valid = false;
                    field.reportValidity();
                }

            });

            if (!valid) {
                e.preventDefault();
            }

        });

    });

});
function showFieldError(field, message) {

    let error = field.parentNode.querySelector('.field-error');

    if (!error) {
        error = document.createElement('small');
        error.className = 'field-error';
        field.parentNode.appendChild(error);
    }

    error.textContent = message;
}

/* ==========================================
   DASHBOARD STAT ANIMATION
========================================== */

document.addEventListener('DOMContentLoaded', () => {

    const values = document.querySelectorAll('.stat-value');

    if (!values.length || !('IntersectionObserver' in window)) {
        return;
    }

    const parseValue = (text) => {
        const cleaned = text.replace(/,/g, '');
        const match = cleaned.match(/-?\d+(\.\d+)?/);

        if (!match) {
            return null;
        }

        return {
            value: Number(match[0]),
            decimals: (match[0].split('.')[1] || '').length,
            prefix: text.slice(0, match.index),
            suffix: text.slice((match.index || 0) + match[0].length)
        };
    };

    const formatValue = (number, decimals, prefix, suffix) => {
        const formatted = number.toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });

        return `${prefix}${formatted}${suffix}`;
    };

    const animate = (element) => {
        const parsed = parseValue(element.textContent.trim());

        if (!parsed || element.dataset.animated === 'true') {
            return;
        }

        element.dataset.animated = 'true';
        const duration = 900 + Math.min(600, Math.abs(parsed.value) * 8);
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = parsed.value * eased;

            element.textContent = formatValue(current, parsed.decimals, parsed.prefix, parsed.suffix);

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                element.textContent = formatValue(parsed.value, parsed.decimals, parsed.prefix, parsed.suffix);
            }
        };

        requestAnimationFrame(tick);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animate(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.35 });

    values.forEach(value => observer.observe(value));
});

/* ========================================
   AUTO HIDE FLASH / LOGOUT MESSAGES
======================================== */

document.addEventListener('DOMContentLoaded', () => {

    const flashMessages = document.querySelectorAll(
        '.logout-message, .logout-alert, .auth-alert, .alert, .success-message'
    );

    flashMessages.forEach(message => {

        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
        }, 1800);

        setTimeout(() => {
            message.remove();
        }, 2300);

    });

});
