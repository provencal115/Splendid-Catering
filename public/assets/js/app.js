/**
 * Splendid Catering — Application JavaScript
 */

const APP = {
    base: window.APP_BASE || '',
    api(path) { return this.base + '/api/' + path.replace(/^\//, ''); }
};

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSidebar();
    initNotifications();
    initGlobalSearch();
    initSidebarSectionHighlight();

    if (typeof AOS !== 'undefined') AOS.init({ duration: 800, once: true, offset: 50 });

    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 50));
        const path = window.location.pathname.replace(APP.base, '') || '/';
        nav.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href')?.replace(APP.base, '') || '';
            if (href === path || (path === '/' && href.endsWith('/'))) link.classList.add('active');
        });
    }

    initChatWidget();
    initBookingWizard();
    initQuoteCalculator();
    initDietFilters();
    initAIPanels();
    if (typeof initDashboardCharts === 'function') initDashboardCharts();
});

/* ─── Theme ─── */
function initTheme() {
    const apply = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('sc-theme', theme);
    };

    const toggle = () => apply(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');

    document.getElementById('themeToggle')?.addEventListener('click', toggle);
    document.getElementById('themeTogglePublic')?.addEventListener('click', toggle);
}

/* ─── Sidebar ─── */
function initSidebar() {
    const layout = document.getElementById('dashboardLayout');
    const sidebar = document.getElementById('dashboardSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    const collapse = document.getElementById('sidebarCollapse');

    toggle?.addEventListener('click', () => {
        sidebar?.classList.add('open');
        backdrop?.classList.add('show');
    });
    backdrop?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('show');
    });
    collapse?.addEventListener('click', () => layout?.classList.toggle('sidebar-collapsed'));
}

function initSidebarSectionHighlight() {
    const links = document.querySelectorAll('.sidebar-link[data-section]');
    if (!links.length) return;

    const setActive = () => {
        const hash = location.hash.replace('#', '') || 'overview';
        links.forEach(l => l.classList.toggle('active', l.dataset.section === hash));
    };
    setActive();
    window.addEventListener('hashchange', setActive);
}

/* ─── Notifications ─── */
async function initNotifications() {
    const list = document.getElementById('notificationList');
    if (!list) return;

    try {
        const res = await fetch(APP.api('notifications'));
        const data = await res.json();
        if (!data.notifications?.length) {
            list.innerHTML = '<div class="text-center text-muted py-3 small">No notifications</div>';
            return;
        }
        list.innerHTML = data.notifications.map(n => `
            <a href="${n.link || '#'}" class="notification-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
                <strong>${escapeHtml(n.title)}</strong>
                <p>${escapeHtml(n.message)}</p>
                <small>${escapeHtml(n.time)}</small>
            </a>
        `).join('');
    } catch {
        list.innerHTML = '<div class="text-center text-muted py-3 small">Unable to load</div>';
    }

    document.getElementById('markAllRead')?.addEventListener('click', async () => {
        await fetch(APP.api('notifications?action=mark_all_read'), { method: 'POST' });
        initNotifications();
    });
}

/* ─── Search ─── */
function initGlobalSearch() {
    const input = document.getElementById('globalSearch');
    const results = document.getElementById('searchResults');
    if (!input || !results) return;

    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) { results.hidden = true; return; }
        timer = setTimeout(async () => {
            try {
                const res = await fetch(APP.api('search?q=' + encodeURIComponent(q)));
                const data = await res.json();
                if (!data.results?.length) {
                    results.innerHTML = '<div class="search-empty">No results</div>';
                } else {
                    results.innerHTML = data.results.map(r => `
                        <a href="${r.url}" class="search-result-item">
                            <span class="search-type">${escapeHtml(r.type)}</span>
                            <strong>${escapeHtml(r.label)}</strong>
                            <small>${escapeHtml(r.sub)}</small>
                        </a>
                    `).join('');
                }
                results.hidden = false;
            } catch { results.hidden = true; }
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !results.contains(e.target)) results.hidden = true;
    });
}

/* ─── Chat Widget ─── */
function initChatWidget() {
    const toggle = document.getElementById('chatToggle');
    const panel = document.getElementById('chatPanel');
    const close = document.getElementById('chatClose');
    const input = document.getElementById('chatInput');
    const send = document.getElementById('chatSend');
    const messages = document.getElementById('chatMessages');
    if (!toggle) return;

    toggle.addEventListener('click', () => panel.classList.toggle('open'));
    close?.addEventListener('click', () => panel.classList.remove('open'));

    const sendMessage = async () => {
        const text = input.value.trim();
        if (!text) return;
        appendMessage(text, 'user');
        input.value = '';
        try {
            const res = await fetch(APP.api('ai-assistant'), {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();
            appendMessage(data.reply, 'bot');
        } catch {
            appendMessage('Sorry, I am temporarily unavailable.', 'bot');
        }
    };

    send?.addEventListener('click', sendMessage);
    input?.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });

    function appendMessage(text, type) {
        const div = document.createElement('div');
        div.className = `chat-msg ${type}`;
        div.innerHTML = `<p>${escapeHtml(text)}</p>`;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }
}

/* ─── Booking Wizard ─── */
function initBookingWizard() {
    const wizard = document.getElementById('bookingWizard');
    if (!wizard) return;

    let currentStep = 1;
    const form = wizard.querySelector('form');

    wizard.querySelectorAll('[data-next]').forEach(btn => {
        btn.addEventListener('click', () => { if (validateStep(currentStep)) { currentStep++; updateWizard(); } });
    });
    wizard.querySelectorAll('[data-prev]').forEach(btn => {
        btn.addEventListener('click', () => { currentStep--; updateWizard(); });
    });

    function updateWizard() {
        wizard.querySelectorAll('.wizard-step').forEach((step, i) => {
            step.classList.toggle('active', i + 1 === currentStep);
            step.classList.toggle('completed', i + 1 < currentStep);
        });
        wizard.querySelectorAll('.wizard-pane').forEach((pane, i) => {
            pane.style.display = i + 1 === currentStep ? 'block' : 'none';
        });
    }

    function validateStep(step) {
        const pane = wizard.querySelector(`.wizard-pane[data-step="${step}"]`);
        let valid = true;
        pane.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) { field.classList.add('is-invalid'); valid = false; }
            else field.classList.remove('is-invalid');
        });
        return valid;
    }

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        try {
            const res = await fetch(APP.api('booking'), {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.fromEntries(new FormData(form)))
            });
            const result = await res.json();
            if (result.success) window.location.href = result.redirect || APP.base + '/dashboard/customer';
            else { alert(result.message || 'Booking failed'); btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Booking & Pay Deposit'; }
        } catch {
            alert('An error occurred'); btn.disabled = false;
        }
    });
    updateWizard();
}

function initQuoteCalculator() {
    const calc = document.getElementById('quoteCalculator');
    if (!calc) return;
    const form = document.querySelector('#bookingWizard form');
    const guestsInput = form?.querySelector('[name="guest_count"]');
    const promoInput = form?.querySelector('[name="promo_code"]');

    const update = async () => {
        const guests = parseInt(guestsInput?.value) || 0;
        const checkedPackage = form?.querySelector('[name="package_id"]:checked');
        const packageId = checkedPackage?.value;
        if (!guests || !packageId) return;
        try {
            const res = await fetch(APP.api('quotation'), {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ guest_count: guests, package_id: packageId, promo_code: promoInput?.value })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('quoteSubtotal').textContent = data.subtotal;
                document.getElementById('quoteTax').textContent = data.tax;
                document.getElementById('quoteTotal').textContent = data.total;
                document.getElementById('quoteDeposit').textContent = data.deposit;
            }
        } catch {}
    };
    guestsInput?.addEventListener('input', update);
    form?.querySelectorAll('[name="package_id"]').forEach(r => r.addEventListener('change', update));
    promoInput?.addEventListener('blur', update);
}

function initDietFilters() {
    document.querySelectorAll('.diet-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.diet-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;
            document.querySelectorAll('.menu-card-item').forEach(item => {
                if (filter === 'all') item.style.display = '';
                else {
                    const key = filter === 'glutenFree' ? 'glutenFree' : filter;
                    item.style.display = item.dataset[key] === '1' ? '' : 'none';
                }
            });
        });
    });
}

function initAIPanels() {
    document.getElementById('aiBudgetBtn')?.addEventListener('click', async () => {
        const res = await fetch(APP.api('ai-budget'), {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ budget: document.getElementById('aiBudget')?.value, guests: document.getElementById('aiGuests')?.value })
        });
        const data = await res.json();
        document.getElementById('aiBudgetResult').innerHTML = data.html || data.message;
    });
    document.getElementById('aiMenuBtn')?.addEventListener('click', async () => {
        const res = await fetch(APP.api('ai-menu'), {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                budget: document.getElementById('menuBudget')?.value,
                guests: document.getElementById('menuGuests')?.value,
                event_type: document.getElementById('menuEventType')?.value,
                preferences: Array.from(document.querySelectorAll('[name="preferences[]"]:checked')).map(c => c.value)
            })
        });
        document.getElementById('aiMenuResult').innerHTML = (await res.json()).html || '';
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function processPayment(bookingId, method, amount) {
    const res = await fetch(APP.api('payment'), {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ booking_id: bookingId, method, amount })
    });
    return res.json();
}
window.processPayment = processPayment;

/* ─── Chart helper ─── */
function createThemedChart(canvas, config) {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(100,116,139,0.1)';

    if (config.options?.scales) {
        Object.values(config.options.scales).forEach(scale => {
            scale.ticks = { ...scale.ticks, color: textColor };
            scale.grid = { ...scale.grid, color: gridColor };
        });
    }
    if (config.options?.plugins?.legend?.labels) {
        config.options.plugins.legend.labels.color = textColor;
    }
    return new Chart(canvas, config);
}
window.createThemedChart = createThemedChart;
