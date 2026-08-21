document.addEventListener('DOMContentLoaded', () => {
    AOS.init({ duration: 800, once: true, offset: 50 });

    // Navbar scroll effect
    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        });
        // Active nav link
        const path = window.location.pathname;
        nav.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === path || (path === '/' && link.getAttribute('href')?.endsWith('/'))) {
                link.classList.add('active');
            }
        });
    }

    // AI Chat Widget
    initChatWidget();

    // Booking wizard
    initBookingWizard();

    // Quote calculator
    initQuoteCalculator();

    // Diet filters
    initDietFilters();

    // AI panels
    initAIPanels();
});

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
            const res = await fetch('/api/ai-assistant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();
            appendMessage(data.reply, 'bot');
        } catch {
            appendMessage('Sorry, I am temporarily unavailable. Please call us or use the contact form.', 'bot');
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

function initBookingWizard() {
    const wizard = document.getElementById('bookingWizard');
    if (!wizard) return;

    let currentStep = 1;
    const totalSteps = 4;
    const form = wizard.querySelector('form');

    wizard.querySelectorAll('[data-next]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                currentStep++;
                updateWizard();
            }
        });
    });

    wizard.querySelectorAll('[data-prev]').forEach(btn => {
        btn.addEventListener('click', () => {
            currentStep--;
            updateWizard();
        });
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
        const required = pane.querySelectorAll('[required]');
        let valid = true;
        required.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        return valid;
    }

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            const res = await fetch('/api/booking', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                window.location.href = result.redirect || '/dashboard/customer';
            } else {
                alert(result.message || 'Booking failed. Please try again.');
            }
        } catch {
            alert('An error occurred. Please try again.');
        }
    });

    updateWizard();
}

function initQuoteCalculator() {
    const calc = document.getElementById('quoteCalculator');
    if (!calc) return;

    const form = document.querySelector('#bookingWizard form');
    const guestsInput = form?.querySelector('[name="guest_count"]');
    const packageSelect = form?.querySelector('[name="package_id"]');
    const promoInput = form?.querySelector('[name="promo_code"]');

    const update = async () => {
        const guests = parseInt(guestsInput?.value) || 0;
        const checkedPackage = form?.querySelector('[name="package_id"]:checked');
        const packageId = checkedPackage?.value || packageSelect?.value;
        if (!guests || !packageId) return;

        try {
            const res = await fetch('/api/quotation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    guest_count: guests,
                    package_id: packageId,
                    promo_code: promoInput?.value
                })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('quoteSubtotal').textContent = data.subtotal;
                document.getElementById('quoteTax').textContent = data.tax;
                document.getElementById('quoteTotal').textContent = data.total;
                document.getElementById('quoteDeposit').textContent = data.deposit;
            }
        } catch (e) { /* silent */ }
    };

    guestsInput?.addEventListener('input', update);
    packageSelect?.addEventListener('change', update);
    form?.querySelectorAll('[name="package_id"]').forEach(r => r.addEventListener('change', update));
    promoInput?.addEventListener('blur', update);
}

function initDietFilters() {
    const filters = document.querySelectorAll('.diet-filter-btn');
    const items = document.querySelectorAll('.menu-card-item');

    filters.forEach(btn => {
        btn.addEventListener('click', () => {
            filters.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;

            items.forEach(item => {
                if (filter === 'all') {
                    item.style.display = '';
                } else {
                    item.style.display = item.dataset[filter] === '1' ? '' : 'none';
                }
            });
        });
    });
}

function initAIPanels() {
    const budgetBtn = document.getElementById('aiBudgetBtn');
    if (budgetBtn) {
        budgetBtn.addEventListener('click', async () => {
            const budget = document.getElementById('aiBudget')?.value;
            const guests = document.getElementById('aiGuests')?.value;
            if (!budget || !guests) return;

            const res = await fetch('/api/ai-budget', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ budget, guests })
            });
            const data = await res.json();
            document.getElementById('aiBudgetResult').innerHTML = data.html || data.message;
        });
    }

    const menuBtn = document.getElementById('aiMenuBtn');
    if (menuBtn) {
        menuBtn.addEventListener('click', async () => {
            const res = await fetch('/api/ai-menu', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    budget: document.getElementById('menuBudget')?.value,
                    guests: document.getElementById('menuGuests')?.value,
                    event_type: document.getElementById('menuEventType')?.value,
                    preferences: Array.from(document.querySelectorAll('[name="preferences[]"]:checked')).map(c => c.value)
                })
            });
            const data = await res.json();
            document.getElementById('aiMenuResult').innerHTML = data.html || '';
        });
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Dashboard sidebar toggle
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.querySelector('.dashboard-sidebar')?.classList.toggle('open');
});

// Payment simulation
async function processPayment(bookingId, method, amount) {
    const res = await fetch('/api/payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ booking_id: bookingId, method, amount })
    });
    return res.json();
}

window.processPayment = processPayment;
