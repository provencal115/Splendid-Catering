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

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: reducedMotion ? 0 : 750,
            once: true,
            offset: 60,
            easing: 'ease-out-cubic',
            disable: reducedMotion ? true : false
        });
    }

    const nav = document.getElementById('mainNav');
    if (nav) {
        const onScroll = () => {
            nav.classList.toggle('scrolled', window.scrollY > 40);
            nav.classList.toggle('nav-solid', window.scrollY > 40);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        const path = window.location.pathname.replace(APP.base, '') || '/';
        nav.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href')?.replace(APP.base, '') || '';
            if (href === path || (path !== '/' && href.endsWith(path))) link.classList.add('active');
            else if (path === '/' && (href === '/' || href.endsWith('/'))) link.classList.add('active');
        });
    }

    initPublicSite();
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
        document.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
    };

    const stored = localStorage.getItem('sc-theme');
    const preferred = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', preferred);

    const toggle = () => apply(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');

    document.getElementById('themeToggle')?.addEventListener('click', toggle);
    document.getElementById('themeTogglePublic')?.addEventListener('click', toggle);
    document.getElementById('themeToggleAuth')?.addEventListener('click', toggle);

    document.addEventListener('themechange', () => {
        if (typeof window.refreshDashboardCharts === 'function') {
            window.refreshDashboardCharts();
        }
    });
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
                <div class="notification-item-body">
                    <strong>${escapeHtml(n.title)}</strong>
                    <p>${escapeHtml(n.message)}</p>
                    <small>${escapeHtml(n.time)}</small>
                </div>
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
            const show = i + 1 === currentStep;
            pane.style.display = show ? 'block' : 'none';
            if (show) {
                pane.style.animation = 'none';
                pane.offsetHeight;
                pane.style.animation = '';
            }
        });
        wizard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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

/* ─── Public site animations & interactions ─── */
function initPublicSite() {
    if (document.body.classList.contains('dashboard-body')) return;

    if (document.body.classList.contains('auth-page-body')) {
        initAuthPage();
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reducedMotion) {
        document.documentElement.classList.add('js-animations');
    }

    revealElementsInViewport();

    initHeroVideo();
    initCinematicHero();
    initParallaxLayers();
    initMaskReveals();
    initScrollReveal();
    initAnimatedCounters();
    initGalleryLightbox();
    initGalleryFilters();
    initGalleryReveal();
    initTestimonialCarousel();
    initInteractiveButtons();
    initFooterReveal();
    initPageTransition();
    initCustomCursor();
    initMobileNavEnhance();
    initBusinessMaps();
    initHomeGalleryFeatured();

    if (!reducedMotion) {
        window.setTimeout(forceRevealAllHidden, 3000);
    }
}

function revealElementsInViewport() {
    const viewportPad = Math.min(window.innerHeight * 0.08, 80);

    const isInViewport = (el) => {
        const rect = el.getBoundingClientRect();
        return rect.top < window.innerHeight - viewportPad && rect.bottom > viewportPad;
    };

    document.querySelectorAll('[data-reveal], [data-mask-reveal], .footer-reveal').forEach((el) => {
        if (!isInViewport(el)) return;
        const delay = Number(el.dataset.revealDelay || 0);
        if (delay > 0) {
            window.setTimeout(() => el.classList.add('is-visible'), delay);
        } else {
            el.classList.add('is-visible');
        }
    });
}

function forceRevealAllHidden() {
    document.querySelectorAll('[data-reveal]:not(.is-visible), [data-mask-reveal]:not(.is-visible), .footer-reveal:not(.is-visible)').forEach((el) => {
        el.classList.add('is-visible');
    });
}

function initHeroVideo() {
    const hero = document.querySelector('.hero-cinematic--video');
    const video = document.querySelector('[data-hero-video]');
    if (!hero) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const narrowMobile = window.matchMedia('(max-width: 767.98px)').matches;
    const saveData = navigator.connection?.saveData === true;

    if (narrowMobile || saveData) {
        hero.classList.add('hero-cinematic--mobile-poster');
    }

    if (!video || reducedMotion) {
        hero.classList.add('is-poster-only', 'is-video-paused');
        return;
    }

    const showPoster = () => {
        hero.classList.add('is-video-paused');
    };

    video.addEventListener('error', showPoster);
    video.addEventListener('stalled', showPoster);

    const tryPlay = () => {
        const playPromise = video.play();
        if (playPromise && typeof playPromise.then === 'function') {
            playPromise.catch(showPoster);
        }
    };

    if (video.readyState >= 2) {
        tryPlay();
    } else {
        video.addEventListener('loadeddata', tryPlay, { once: true });
    }
}

function initCinematicHero() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    document.querySelectorAll('[data-cinematic-hero]').forEach(container => {
        const slides = [...container.querySelectorAll('.hero-cinematic__slide')];
        if (slides.length <= 1) return;
        let index = 0;
        setInterval(() => {
            slides[index].classList.remove('is-active');
            index = (index + 1) % slides.length;
            slides[index].classList.add('is-active');
        }, 7000);
    });
}

function initParallaxLayers() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const layers = document.querySelectorAll('[data-parallax-layer]');
    const sections = document.querySelectorAll('[data-parallax-section]');
    if (!layers.length && !sections.length) return;

    const onScroll = () => {
        layers.forEach(el => {
            const speed = parseFloat(el.dataset.parallaxLayer || '0.05');
            const rect = el.getBoundingClientRect();
            const center = rect.top + rect.height / 2 - window.innerHeight / 2;
            el.style.transform = `translateY(${center * speed}px)`;
        });
    };

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function initMaskReveals() {
    const items = document.querySelectorAll('[data-mask-reveal]');
    if (!items.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach(el => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const delay = Number(entry.target.dataset.revealDelay || 0);
            setTimeout(() => entry.target.classList.add('is-visible'), delay);
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.05, rootMargin: '40px 0px 40px 0px' });

    items.forEach(el => {
        if (!el.classList.contains('is-visible')) {
            observer.observe(el);
        }
    });
}

function initPageTransition() {
    const overlay = document.getElementById('pageTransition');
    if (!overlay || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    document.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', e => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || link.target === '_blank') return;
            if (link.hasAttribute('download')) return;
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === window.location.pathname && !url.hash) return;

            e.preventDefault();
            overlay.classList.add('is-active');
            setTimeout(() => { window.location.href = href; }, 280);
        });
    });

    window.addEventListener('pageshow', () => overlay.classList.remove('is-active'));
}

function initCustomCursor() {
    const cursor = document.getElementById('customCursor');
    const label = cursor?.querySelector('.custom-cursor__label');
    if (!cursor || window.matchMedia('(hover: none)').matches || window.innerWidth < 992) return;

    let visible = false;
    document.addEventListener('mousemove', e => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
        if (!visible) {
            cursor.classList.add('is-visible');
            visible = true;
        }
    });

    document.addEventListener('mouseleave', () => cursor.classList.remove('is-visible'));

    document.querySelectorAll('[data-cursor-label], [data-lightbox], .service-tile, .gallery-item').forEach(el => {
        const text = el.dataset.cursorLabel || (el.dataset.lightbox !== undefined ? 'View' : '');
        if (!text) return;
        el.addEventListener('mouseenter', () => { if (label) label.textContent = text; cursor.classList.add('is-visible'); });
        el.addEventListener('mouseleave', () => { if (label) label.textContent = ''; });
    });
}

function initMobileNavEnhance() {
    const nav = document.getElementById('navbarNav');
    const toggler = document.querySelector('.navbar-toggler');
    if (!nav || !toggler) return;
    nav.addEventListener('show.bs.collapse', () => nav.classList.add('show'));
    nav.addEventListener('hide.bs.collapse', () => nav.classList.remove('show'));
}

function initScrollReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach(el => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.dataset.revealDelay || 0;
                setTimeout(() => entry.target.classList.add('is-visible'), Number(delay));
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05, rootMargin: '40px 0px 40px 0px' });

    items.forEach((el, i) => {
        if (!el.dataset.revealDelay && el.closest('.stagger-grid')) {
            el.dataset.revealDelay = String((i % 6) * 80);
        }
        if (!el.classList.contains('is-visible')) {
            observer.observe(el);
        }
    });
}

function initAnimatedCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    const animate = (el) => {
        const target = parseFloat(el.dataset.counter);
        const suffix = el.dataset.counterSuffix || '';
        const prefix = el.dataset.counterPrefix || '';
        const decimals = parseInt(el.dataset.counterDecimals || '0', 10);
        if (Number.isNaN(target)) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            el.textContent = prefix + target.toFixed(decimals) + suffix;
            return;
        }

        const duration = 1400;
        const start = performance.now();
        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = target * eased;
            el.textContent = prefix + value.toFixed(decimals) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animate(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    counters.forEach(el => observer.observe(el));
}

function initGalleryLightbox() {
    const lightbox = document.getElementById('galleryLightbox');
    const image = document.getElementById('lightboxImage');
    const caption = document.getElementById('lightboxCaption');
    const counter = document.getElementById('lightboxCounter');
    const closeBtn = document.getElementById('lightboxClose');
    const prevBtn = document.getElementById('lightboxPrev');
    const nextBtn = document.getElementById('lightboxNext');
    if (!lightbox || !image) return;

    const items = [...document.querySelectorAll('[data-lightbox]')].map(el => ({
        src: el.dataset.src || el.querySelector('img')?.src,
        title: el.dataset.title || '',
        category: el.dataset.category || el.dataset.galleryCategory || ''
    })).filter(i => i.src);

    let current = 0;
    let touchStartX = 0;

    const render = (index) => {
        current = (index + items.length) % items.length;
        const item = items[current];
        image.src = item.src;
        image.alt = item.title || 'Gallery image';
        caption.textContent = [item.title, item.category].filter(Boolean).join(' · ');
        if (counter) counter.textContent = items.length > 1 ? `${current + 1} / ${items.length}` : '';
    };

    const open = (index) => {
        if (!items.length) return;
        render(index);
        lightbox.hidden = false;
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        lightbox.classList.remove('is-open');
        lightbox.hidden = true;
        document.body.style.overflow = '';
        image.src = '';
    };

    document.querySelectorAll('[data-lightbox]').forEach((el, idx) => {
        el.addEventListener('click', () => open(idx));
    });

    prevBtn?.addEventListener('click', e => { e.stopPropagation(); render(current - 1); });
    nextBtn?.addEventListener('click', e => { e.stopPropagation(); render(current + 1); });
    closeBtn?.addEventListener('click', close);
    lightbox.addEventListener('click', e => { if (e.target === lightbox) close(); });

    document.addEventListener('keydown', e => {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') render(current - 1);
        if (e.key === 'ArrowRight') render(current + 1);
    });

    lightbox.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    lightbox.addEventListener('touchend', e => {
        const diff = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(diff) < 40) return;
        render(diff > 0 ? current - 1 : current + 1);
    }, { passive: true });
}

function initGalleryFilters() {
    const buttons = document.querySelectorAll('.gallery-filter-btn');
    const items = document.querySelectorAll('[data-gallery-category]');
    if (!buttons.length || !items.length) return;

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;
            items.forEach(item => {
                const show = filter === 'all' || item.dataset.galleryCategory === filter;
                item.style.display = show ? '' : 'none';
                if (show) {
                    item.classList.remove('is-visible');
                    requestAnimationFrame(() => item.classList.add('is-visible'));
                }
            });
        });
    });
}

function initGalleryReveal() {
    const items = document.querySelectorAll('[data-gallery-reveal]');
    if (!items.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach(el => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '40px 0px 40px 0px' });

    items.forEach(el => observer.observe(el));
}

function initTestimonialCarousel() {
    const carousel = document.querySelector('[data-testimonial-carousel]');
    if (!carousel) return;

    const track = carousel.querySelector('.testimonial-carousel__track');
    const slides = carousel.querySelectorAll('.testimonial-carousel__slide');
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const dotsWrap = carousel.querySelector('.testimonial-carousel__dots');
    if (!track || !slides.length) return;

    let index = 0;
    let timer;
    let paused = false;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const useFade = carousel.hasAttribute('data-carousel-fade');

    const slidesPerView = () => {
        if (window.innerWidth >= 992) return 3;
        if (window.innerWidth >= 768) return 2;
        return 1;
    };

    const maxIndex = () => Math.max(0, slides.length - slidesPerView());

    if (useFade && slides.length) {
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === 0));
    }

    const update = () => {
        index = Math.min(index, maxIndex());
        if (useFade) {
            slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
        } else {
            const offset = (100 / slidesPerView()) * index;
            track.style.transform = `translateX(-${offset}%)`;
        }
        dotsWrap?.querySelectorAll('.testimonial-carousel__dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    };

    if (dotsWrap) {
        for (let i = 0; i <= maxIndex(); i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'testimonial-carousel__dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
            dot.addEventListener('click', () => { index = i; update(); resetAuto(); });
            dotsWrap.appendChild(dot);
        }
    }

    prev?.addEventListener('click', () => { index = index <= 0 ? maxIndex() : index - 1; update(); resetAuto(); });
    next?.addEventListener('click', () => { index = index >= maxIndex() ? 0 : index + 1; update(); resetAuto(); });

    const autoPlay = () => {
        if (reducedMotion || slides.length <= slidesPerView()) return;
        timer = setInterval(() => {
            if (paused) return;
            index = index >= maxIndex() ? 0 : index + 1;
            update();
        }, 6000);
    };
    const resetAuto = () => { clearInterval(timer); autoPlay(); };

    carousel.addEventListener('mouseenter', () => { paused = true; });
    carousel.addEventListener('mouseleave', () => { paused = false; });

    // Touch swipe
    let touchX = 0;
    carousel.addEventListener('touchstart', e => { touchX = e.changedTouches[0].screenX; }, { passive: true });
    carousel.addEventListener('touchend', e => {
        const diff = e.changedTouches[0].screenX - touchX;
        if (Math.abs(diff) < 40) return;
        index = diff > 0 ? (index <= 0 ? maxIndex() : index - 1) : (index >= maxIndex() ? 0 : index + 1);
        update();
        resetAuto();
    }, { passive: true });

    window.addEventListener('resize', update);
    update();
    autoPlay();
}

function initInteractiveButtons() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('[type="submit"].btn-interactive, [type="submit"].btn-gold, [type="submit"].auth-submit');
            if (btn && !btn.classList.contains('is-loading')) {
                btn.classList.add('is-loading');
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
            }
        });
    });
}

function initAuthPage() {
    document.querySelectorAll('[data-password-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(btn.dataset.passwordToggle);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-eye', !show);
                icon.classList.toggle('bi-eye-slash', show);
            }
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    const strengthInput = document.querySelector('[data-password-strength]');
    const strengthWrap = document.querySelector('[data-strength-bar]');
    if (strengthInput && strengthWrap) {
        const bar = strengthWrap.querySelector('span');
        const levels = ['', 'is-weak', 'is-fair', 'is-good', 'is-strong'];
        const widths = ['0%', '25%', '50%', '75%', '100%'];

        const scorePassword = (pwd) => {
            let score = 0;
            if (pwd.length >= 6) score++;
            if (pwd.length >= 10) score++;
            if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
            if (/\d/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;
            return Math.min(score, 4);
        };

        strengthInput.addEventListener('input', () => {
            const score = scorePassword(strengthInput.value);
            strengthWrap.className = 'auth-password-strength ' + (levels[score] || '');
            if (bar) bar.style.width = widths[score];
        });
    }

    const matchInput = document.querySelector('[data-password-match]');
    const matchHint = document.querySelector('[data-match-hint]');
    if (matchInput && matchHint) {
        const target = document.querySelector(matchInput.dataset.passwordMatch);
        const validate = () => {
            if (!matchInput.value) {
                matchHint.textContent = '';
                matchInput.classList.remove('is-invalid', 'is-valid');
                return;
            }
            const matches = target && matchInput.value === target.value;
            matchInput.classList.toggle('is-valid', matches);
            matchInput.classList.toggle('is-invalid', !matches);
            matchHint.textContent = matches ? 'Passwords match' : 'Passwords do not match';
            matchHint.className = 'auth-field-hint small ' + (matches ? 'text-success' : 'text-danger');
        };
        matchInput.addEventListener('input', validate);
        target?.addEventListener('input', validate);
    }

    document.querySelectorAll('[data-auth-form]').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (form.hasAttribute('data-auth-register')) {
                const password = form.querySelector('[data-password-strength]');
                const confirm = form.querySelector('[data-password-match]');
                if (password && confirm && password.value !== confirm.value) {
                    e.preventDefault();
                    confirm.classList.add('is-invalid');
                    if (matchHint) {
                        matchHint.textContent = 'Passwords do not match';
                        matchHint.className = 'auth-field-hint small text-danger';
                    }
                    return;
                }
            }
            const btn = form.querySelector('.auth-submit');
            if (btn && !btn.classList.contains('is-loading')) {
                btn.classList.add('is-loading');
                btn.disabled = true;
            }
        });
    });

    initInteractiveButtons();
}

function initHomeGalleryFeatured() {
    const featured = document.querySelector('[data-home-gallery-featured]');
    if (!featured) return;

    let slides = [];
    try {
        slides = JSON.parse(featured.dataset.slides || '[]');
    } catch (e) {
        return;
    }

    if (slides.length <= 1 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const img = featured.querySelector('.gallery-editorial__featured-img');
    const titleEl = featured.querySelector('.gallery-editorial__featured-overlay h3');
    const catEl = featured.querySelector('.gallery-editorial__featured-overlay p');
    if (!img) return;

    let index = 0;
    const fadeDuration = 900;

    const transition = () => {
        index = (index + 1) % slides.length;
        const next = slides[index];
        img.classList.add('is-fading');

        window.setTimeout(() => {
            img.src = next.src;
            img.alt = next.title || 'Gallery image';
            featured.dataset.src = next.src;
            featured.dataset.title = next.title || '';
            featured.dataset.category = next.category || '';
            if (titleEl) titleEl.textContent = next.title || '';
            if (catEl) catEl.textContent = next.category || '';
            img.classList.remove('is-fading');
        }, fadeDuration);
    };

    window.setInterval(transition, 8000);
}

const businessMapInstances = new Map();

function loadLeafletAssets() {
    if (window.L) {
        return Promise.resolve(window.L);
    }

    if (window.__leafletLoadPromise) {
        return window.__leafletLoadPromise;
    }

    window.__leafletLoadPromise = new Promise((resolve, reject) => {
        if (!document.querySelector('link[data-leaflet-css]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            link.setAttribute('data-leaflet-css', '1');
            document.head.appendChild(link);
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.async = true;
        script.onload = () => resolve(window.L);
        script.onerror = () => reject(new Error('Failed to load map library'));
        document.body.appendChild(script);
    });

    return window.__leafletLoadPromise;
}

function getMapTileLayer(L, theme) {
    if (theme === 'dark') {
        return L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 19,
        });
    }

    return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    });
}

function buildMapPopupContent(location) {
    const phoneHtml = location.phone
        ? `<div class="location-map-popup__phone"><a href="tel:${escapeHtml(String(location.phone).replace(/\s+/g, ''))}">${escapeHtml(location.phone)}</a></div>`
        : '';

    return `<div class="location-map-popup">
        <div class="location-map-popup__title">${escapeHtml(location.name)}</div>
        <div class="location-map-popup__address">${escapeHtml(location.address)}</div>
        ${phoneHtml}
    </div>`;
}

function createBusinessMarker(L, location) {
    const icon = L.divIcon({
        className: 'location-marker',
        html: '<div class="location-marker__pin"><i class="bi bi-cup-hot-fill"></i></div>',
        iconSize: [44, 44],
        iconAnchor: [22, 44],
        popupAnchor: [0, -46],
    });

    const marker = L.marker([location.latitude, location.longitude], { icon });
    marker.bindPopup(buildMapPopupContent(location), {
        className: 'location-map-popup',
        closeButton: true,
        maxWidth: 280,
    });

    return marker;
}

function initSingleBusinessMap(L, wrap) {
    const mapId = wrap.dataset.mapId;
    const container = document.getElementById(mapId);
    if (!container || businessMapInstances.has(mapId)) return;

    let location;
    try {
        location = JSON.parse(wrap.dataset.location || '{}');
    } catch (e) {
        return;
    }

    if (!location.latitude || !location.longitude) return;

    const zoom = Number(wrap.dataset.zoom || 15);
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const map = L.map(container, {
        scrollWheelZoom: false,
        zoomControl: true,
    }).setView([location.latitude, location.longitude], zoom);

    const tileLayer = getMapTileLayer(L, theme);
    tileLayer.addTo(map);

    const marker = createBusinessMarker(L, location);
    marker.addTo(map);
    marker.openPopup();

    businessMapInstances.set(mapId, { map, tileLayer, marker, location, wrap });

    wrap.classList.add('is-loaded');

    const block = wrap.closest('.location-map-block');
    if (block) block.classList.add('is-visible');

    requestAnimationFrame(() => map.invalidateSize());

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                map.invalidateSize();
                observer.disconnect();
            }
        });
    }, { threshold: 0.15 });

    observer.observe(wrap);
}

function updateBusinessMapThemes() {
    if (!window.L) return;

    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    businessMapInstances.forEach((instance) => {
        instance.map.removeLayer(instance.tileLayer);
        instance.tileLayer = getMapTileLayer(window.L, theme);
        instance.tileLayer.addTo(instance.map);
    });
}

function initBusinessMaps() {
    const wraps = document.querySelectorAll('[data-business-map]');
    if (!wraps.length) return;

    loadLeafletAssets()
        .then((L) => {
            wraps.forEach(wrap => initSingleBusinessMap(L, wrap));

            if (!window.__businessMapThemeObserver) {
                window.__businessMapThemeObserver = new MutationObserver(updateBusinessMapThemes);
                window.__businessMapThemeObserver.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['data-theme'],
                });
            }
        })
        .catch(() => {
            wraps.forEach(wrap => {
                wrap.classList.add('is-loaded');
                const loading = wrap.querySelector('.location-map__loading');
                if (loading) loading.textContent = 'Map unavailable';
            });
        });
}

function initFooterReveal() {
    const footer = document.querySelector('.footer-reveal');
    if (!footer) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        footer.classList.add('is-visible');
        return;
    }

    if (footer.classList.contains('is-visible')) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05, rootMargin: '60px 0px 0px 0px' });

    observer.observe(footer);
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
function getChartTheme() {
    const root = getComputedStyle(document.documentElement);
    return {
        text: root.getPropertyValue('--text-muted').trim() || '#718096',
        grid: root.getPropertyValue('--border-color').trim() || '#e2e8f0',
    };
}

function createThemedChart(canvas, config) {
    const { text: textColor, grid: gridColor } = getChartTheme();

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
window.getChartTheme = getChartTheme;
