(() => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const loader = document.querySelector('#page-loader');
    const menu = document.querySelector('#site-menu');
    const panels = {
        login: document.querySelector('#login-modal'),
        contact: document.querySelector('#contact-modal'),
    };
    const actionToast = document.querySelector('#action-toast');
    const openOverlays = new Set();
    let returnFocus = null;
    let menuReturnFocus = null;
    let toastTimer = 0;

    const dismissToast = () => {
        if (!actionToast) return;
        window.clearTimeout(toastTimer);
        actionToast.classList.remove('is-visible');
        actionToast.setAttribute('aria-hidden', 'true');
    };
    const showToast = () => {
        if (!actionToast) return;
        actionToast.setAttribute('aria-hidden', 'false');
        actionToast.classList.add('is-visible');
        toastTimer = window.setTimeout(dismissToast, 7000);
    };
    actionToast?.querySelector('[data-toast-close]')?.addEventListener('click', dismissToast);

    const spring = (element, target, config = {}) => {
        const tension = config.tension ?? 210;
        const friction = config.friction ?? 24;
        const state = element._springState ?? { opacity: 1, y: 0, scale: 1 };
        const velocity = { opacity: 0, y: 0, scale: 0 };
        const destination = { opacity: target.opacity ?? state.opacity, y: target.y ?? state.y, scale: target.scale ?? state.scale };
        if (element._springFrame) cancelAnimationFrame(element._springFrame);
        let lastTime = performance.now();

        const tick = (now) => {
            const dt = Math.min((now - lastTime) / 1000, 0.032);
            lastTime = now;
            let settled = true;
            for (const key of Object.keys(destination)) {
                const acceleration = (destination[key] - state[key]) * tension - velocity[key] * friction;
                velocity[key] += acceleration * dt;
                state[key] += velocity[key] * dt;
                if (Math.abs(destination[key] - state[key]) > 0.001 || Math.abs(velocity[key]) > 0.006) settled = false;
            }
            element.style.opacity = String(state.opacity);
            element.style.transform = `translateY(${state.y}px) scale(${state.scale})`;
            if (settled) {
                Object.assign(state, destination);
                element.style.opacity = String(state.opacity);
                element.style.transform = `translateY(${state.y}px) scale(${state.scale})`;
                element._springFrame = 0;
            } else {
                element._springFrame = requestAnimationFrame(tick);
            }
        };
        element._springState = state;
        element._springFrame = requestAnimationFrame(tick);
    };

    const syncScrollLock = () => {
        const locked = openOverlays.size > 0 || !document.documentElement.classList.contains('page-ready');
        document.body.classList.toggle('is-locked', locked);
    };
    syncScrollLock();

    const focusableWithin = (element) => [...element.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')]
        .filter((item) => !item.hasAttribute('hidden') && item.getAttribute('aria-hidden') !== 'true');

    const focusFirst = (element, selector) => {
        window.setTimeout(() => {
            const target = selector ? element.querySelector(selector) : focusableWithin(element)[0];
            target?.focus({ preventScroll: true });
        }, 120);
    };

    const closeMenu = (restoreFocus = true) => {
        if (!menu?.classList.contains('is-open')) return;
        menu.classList.remove('is-open');
        menu.setAttribute('aria-hidden', 'true');
        menu.inert = true;
        document.querySelector('[data-menu-open]')?.setAttribute('aria-expanded', 'false');
        openOverlays.delete('menu');
        syncScrollLock();
        if (restoreFocus) menuReturnFocus?.focus({ preventScroll: true });
    };

    const openMenu = (trigger) => {
        if (!menu) return;
        menuReturnFocus = trigger ?? document.querySelector('[data-menu-open]');
        menu.inert = false;
        menu.setAttribute('aria-hidden', 'false');
        menu.classList.add('is-open');
        openOverlays.add('menu');
        document.querySelector('[data-menu-open]')?.setAttribute('aria-expanded', 'true');
        syncScrollLock();
        focusFirst(menu, '[data-menu-close]:not(.menu-shade)');
    };

    const closePanel = (name, restoreFocus = true) => {
        const panel = panels[name];
        if (!panel?.classList.contains('is-open')) return;
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        window.setTimeout(() => { panel.inert = true; }, 280);
        openOverlays.delete(name);
        syncScrollLock();
        if (restoreFocus) returnFocus?.focus({ preventScroll: true });
        window.setTimeout(() => {
            const form = panel.querySelector('form');
            if (form && name === 'contact' && !document.body.dataset.openPanel) form.reset();
        }, 350);
    };

    const openPanel = (name, trigger) => {
        const panel = panels[name];
        if (!panel) return;
        const wasOpenedFromMenu = Boolean(menu?.classList.contains('is-open') && trigger?.closest('.menu-panel'));
        if (menu?.classList.contains('is-open')) closeMenu(false);
        Object.entries(panels).forEach(([key]) => { if (key !== name) closePanel(key, false); });
        returnFocus = wasOpenedFromMenu ? menuReturnFocus : (trigger ?? document.activeElement);
        panel.inert = false;
        panel.setAttribute('aria-hidden', 'false');
        panel.classList.add('is-open');
        openOverlays.add(name);
        syncScrollLock();
        focusFirst(panel, name === 'login' ? '#login-username' : '#contact-name');
    };

    document.querySelectorAll('[data-close-panel]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const host = button.closest('.modal-backdrop');
            const name = Object.keys(panels).find((key) => panels[key] === host);
            if (name) closePanel(name);
        });
    });
    document.querySelectorAll('[data-menu-open]').forEach((button) => {
        button.addEventListener('click', () => openMenu(button));
    });
    document.querySelectorAll('[data-menu-close]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            closeMenu();
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : event.target?.parentElement;
        if (!target) return;
        const opener = target.closest('[data-open-panel]');
        if (opener) {
            event.preventDefault();
            openPanel(opener.dataset.openPanel, opener);
            return;
        }
        if (target.closest('[data-go-register]')) {
            closePanel('login', false);
            document.querySelector('#register')?.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
            document.querySelector('#firstName')?.focus({ preventScroll: true });
        }
    });

    document.querySelectorAll('[data-menu-link]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const destination = document.querySelector(link.getAttribute('href'));
            if (!destination) return;
            event.preventDefault();
            closeMenu(false);
            destination.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
            history.replaceState(null, '', link.getAttribute('href'));
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const topPanel = [...Object.keys(panels)].reverse().find((name) => panels[name]?.classList.contains('is-open'));
            if (topPanel) closePanel(topPanel);
            else closeMenu();
        }
        if (event.key !== 'Tab') return;
        const active = [...Object.keys(panels)].reverse().find((name) => panels[name]?.classList.contains('is-open'));
        const container = active ? panels[active] : menu?.classList.contains('is-open') ? menu.querySelector('.menu-panel') : null;
        if (!container) return;
        const items = focusableWithin(container);
        if (!items.length) return;
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });

    /* Word-by-word hero reveal, held behind the opening curtain. */
    const heroTitle = document.querySelector('[data-hero-reveal]');
    if (heroTitle && !reducedMotion) {
        const walker = document.createTreeWalker(heroTitle, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);
        let wordIndex = 0;
        textNodes.forEach((node) => {
            const fragment = document.createDocumentFragment();
            node.textContent.split(/(\s+)/).filter(Boolean).forEach((part) => {
                if (/^\s+$/.test(part)) fragment.append(document.createTextNode(part));
                else {
                    const clip = document.createElement('span');
                    clip.className = 'hero-word-clip';
                    const word = document.createElement('span');
                    word.className = 'hero-word';
                    word.style.setProperty('--word-delay', `${wordIndex * 140}ms`);
                    word.textContent = part;
                    clip.append(word);
                    fragment.append(clip);
                    wordIndex += 1;
                }
            });
            node.replaceWith(fragment);
        });
    }

    /* Scroll-in reveals with stagger values declared on the elements. */
    const revealItems = document.querySelectorAll('.reveal-on-view');
    if ('IntersectionObserver' in window && !reducedMotion) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -35px 0px' });
        revealItems.forEach((element, index) => {
            if (element.matches('.solution-row')) {
                const rowIndex = [...element.parentElement.children].indexOf(element);
                element.style.setProperty('--reveal-delay', `${rowIndex * 90}ms`);
            } else if (element.matches('.impact-item')) {
                element.style.setProperty('--reveal-delay', `${[...element.parentElement.children].indexOf(element) * 110}ms`);
            } else if (element.matches('.question-card')) {
                element.style.setProperty('--reveal-delay', `${index % 3 * 120}ms`);
            }
            revealObserver.observe(element);
        });
    } else {
        revealItems.forEach((element) => element.classList.add('is-visible'));
    }

    /* Hero's rotating solar highlights. */
    const featureSlides = [
        { label: '01 / SOLAR PANELS', title: 'Let daylight do more.', body: 'A solar plan shaped around your roof and your everyday energy use.' },
        { label: '02 / ENERGY STORAGE', title: 'Keep energy close.', body: 'Ask about storage options for power beyond daylight.' },
        { label: '03 / PERSONAL SUPPORT', title: 'Get a human answer.', body: 'A team to help you understand each step of your solar project.' },
    ];
    const featureCard = document.querySelector('[data-feature-card]');
    const featureDots = [...document.querySelectorAll('[data-feature-dot]')];
    let featureIndex = 0;
    let featureTimer = 0;
    const showFeature = (nextIndex) => {
        if (!featureCard || nextIndex === featureIndex) return;
        featureIndex = (nextIndex + featureSlides.length) % featureSlides.length;
        const update = () => {
            const slide = featureSlides[featureIndex];
            featureCard.querySelector('[data-feature-label]').textContent = slide.label;
            featureCard.querySelector('[data-feature-title]').textContent = slide.title;
            featureCard.querySelector('[data-feature-body]').textContent = slide.body;
            featureCard.querySelector('[data-feature-count]').textContent = `0${featureIndex + 1}`;
            featureDots.forEach((dot, index) => dot.setAttribute('aria-current', String(index === featureIndex)));
            spring(featureCard, { opacity: 1, y: 0, scale: 1 }, { tension: 210, friction: 24 });
        };
        spring(featureCard, { opacity: 0, y: 12, scale: .97 }, { tension: 210, friction: 24 });
        window.setTimeout(update, 220);
    };
    const startFeatureTimer = () => {
        window.clearInterval(featureTimer);
        if (!reducedMotion && document.documentElement.classList.contains('page-ready')) {
            featureTimer = window.setInterval(() => showFeature(featureIndex + 1), 3800);
        }
    };
    featureDots.forEach((dot) => dot.addEventListener('click', () => showFeature(Number(dot.dataset.featureDot))));
    featureCard?.addEventListener('mouseenter', () => window.clearInterval(featureTimer));
    featureCard?.addEventListener('mouseleave', startFeatureTimer);
    featureCard?.addEventListener('focusin', () => window.clearInterval(featureTimer));
    featureCard?.addEventListener('focusout', startFeatureTimer);
    startFeatureTimer();

    /* Principles carousel replays the large-word reveal for each selected theme. */
    const principles = [
        { words: ['Solar', 'made', 'personal', 'for home'], caption: 'Thoughtful design. A clear plan. Power from the sun.' },
        { words: ['Energy', 'that', 'starts', 'with you'], caption: 'Your goals lead the conversation, from the first visit onward.' },
        { words: ['A brighter', 'way', 'to power', 'every day'], caption: 'Practical solar choices, explained in a way that makes sense.' },
    ];
    const principleStage = document.querySelector('[data-principle-carousel]');
    const principleWords = [...document.querySelectorAll('[data-principle-word]')];
    const principleCaption = document.querySelector('[data-principle-caption]');
    const principleDots = [...document.querySelectorAll('[data-principle-dot]')];
    let principleIndex = 0;
    let principleTimer = 0;
    const showPrinciple = (nextIndex) => {
        if (!principleStage) return;
        principleIndex = (nextIndex + principles.length) % principles.length;
        if (reducedMotion) {
            updatePrinciple();
            return;
        }
        principleStage.classList.add('is-switching');
        window.setTimeout(updatePrinciple, 280);
    };
    const updatePrinciple = () => {
        const slide = principles[principleIndex];
        principleWords.forEach((word, index) => { word.textContent = slide.words[index]; });
        principleCaption.textContent = slide.caption;
        principleDots.forEach((dot, index) => dot.setAttribute('aria-current', String(index === principleIndex)));
        principleStage.classList.remove('is-switching');
    };
    document.querySelector('[data-principle-prev]')?.addEventListener('click', () => showPrinciple(principleIndex - 1));
    document.querySelector('[data-principle-next]')?.addEventListener('click', () => showPrinciple(principleIndex + 1));
    principleDots.forEach((dot) => dot.addEventListener('click', () => showPrinciple(Number(dot.dataset.principleDot))));
    principleStage?.addEventListener('mouseenter', () => window.clearInterval(principleTimer));
    principleStage?.addEventListener('mouseleave', () => {
        window.clearInterval(principleTimer);
        if (!reducedMotion) principleTimer = window.setInterval(() => showPrinciple(principleIndex + 1), 6200);
    });
    if (!reducedMotion) principleTimer = window.setInterval(() => showPrinciple(principleIndex + 1), 6200);

    /* Gentle hero image parallax. */
    const heroArt = document.querySelector('.hero-solar-art');
    let parallaxFrame = 0;
    const updateParallax = () => {
        parallaxFrame = 0;
        if (!heroArt || reducedMotion) return;
        const hero = heroArt.closest('.hero-card');
        const bounds = hero.getBoundingClientRect();
        const progress = Math.min(1, Math.max(0, -bounds.top / bounds.height));
        heroArt.style.transform = `translate3d(0, ${progress * 3.5}%, 0)`;
    };
    window.addEventListener('scroll', () => {
        if (!parallaxFrame) parallaxFrame = requestAnimationFrame(updateParallax);
    }, { passive: true });

    /* Registration input behavior, including employee-only department field. */
    const roleInput = document.querySelector('#account-role');
    const departmentField = document.querySelector('#department-field');
    const departmentInput = document.querySelector('#department');
    const syncRequiredIndicator = (input) => {
        const label = input.labels?.[0];
        if (!label) return;
        let indicator = label.querySelector('.required-indicator');
        if (input.required && !indicator) {
            indicator = document.createElement('span');
            indicator.className = 'required-indicator';
            indicator.textContent = 'Required';
            label.append(indicator);
        } else if (!input.required) {
            indicator?.remove();
        }
    };
    document.querySelectorAll('.field input, .field textarea').forEach(syncRequiredIndicator);
    document.querySelectorAll('[data-role-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const role = button.dataset.roleChoice;
            document.querySelector('.role-error')?.remove();
            if (roleInput) roleInput.value = role;
            document.querySelectorAll('[data-role-choice]').forEach((option) => {
                const selected = option === button;
                option.classList.toggle('is-selected', selected);
                option.setAttribute('aria-pressed', String(selected));
            });
            if (departmentField && departmentInput) {
                const isEmployee = role === 'employee';
                departmentField.hidden = !isEmployee;
                departmentInput.required = isEmployee;
                syncRequiredIndicator(departmentInput);
                if (!isEmployee) departmentInput.value = '';
            }
        });
    });
    const registerForm = document.querySelector('#register-form');
    const passwordInput = document.querySelector('#password');
    const confirmInput = document.querySelector('#confirmPassword');
    const checkPasswords = () => {
        if (!confirmInput) return;
        confirmInput.setCustomValidity(confirmInput.value && confirmInput.value !== passwordInput?.value ? 'Passwords do not match.' : '');
        if (confirmInput.validity.valid) {
            clearFieldError(confirmInput);
            clearServerFieldError(confirmInput);
        } else if (confirmInput.dataset.validationShown) {
            showFieldError(confirmInput);
        }
    };
    const updateCustomConstraint = (input) => {
        if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement)) return;
        let message = '';
        const value = input.value.trim();
        const inputType = input instanceof HTMLTextAreaElement ? 'textarea' : input.type;
        if (input.required && ['text', 'email', 'tel', 'textarea'].includes(inputType) && value === '') {
            message = 'This field is required.';
        } else if (input.id === 'phone' && value && !/^[0-9+()\s-]{7,20}$/.test(value)) {
            message = 'Use 7–20 digits, spaces, parentheses, +, or -.';
        } else if (input.id === 'username' && value && !/^[A-Za-z0-9_.-]{3,50}$/.test(value)) {
            message = 'Use 3–50 letters, numbers, dots, dashes, or underscores.';
        } else if (input.id === 'password' && input.value.length > 0 && input.value.length < 8) {
            message = 'Use at least 8 characters.';
        } else if (input.id === 'confirmPassword' && input.value && input.value !== passwordInput?.value) {
            message = 'Passwords do not match.';
        } else if (input.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            message = 'Enter a valid email address.';
        }
        input.setCustomValidity(message);
    };
    const clientErrorFor = (input) => {
        const field = input.closest('.field');
        if (!field) return null;
        let error = field.querySelector('[data-client-error]');
        if (!error) {
            error = document.createElement('small');
            error.className = 'field-error';
            error.dataset.clientError = '';
            error.id = `client-error-${input.id}`;
            field.append(error);
        }
        return error;
    };
    const showFieldError = (input) => {
        const error = clientErrorFor(input);
        if (!error) return;
        error.textContent = input.validationMessage || 'Check this field and try again.';
        input.dataset.validationShown = 'true';
        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('aria-describedby', error.id);
    };
    const clearFieldError = (input) => {
        input.closest('.field')?.querySelector('[data-client-error]')?.remove();
        input.removeAttribute('aria-invalid');
        input.removeAttribute('aria-describedby');
        delete input.dataset.validationShown;
    };
    const clearServerFieldError = (input) => {
        input.closest('.field')?.querySelector('.field-error:not([data-client-error])')?.remove();
    };
    document.addEventListener('invalid', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement)) return;
        updateCustomConstraint(input);
        showFieldError(input);
    }, true);
    document.querySelectorAll('.field input, .field textarea').forEach((input) => {
        input.addEventListener('input', () => {
            updateCustomConstraint(input);
            if (input.validity.valid) {
                clearFieldError(input);
                clearServerFieldError(input);
            }
            else if (input.dataset.validationShown) showFieldError(input);
        });
        input.addEventListener('change', () => {
            updateCustomConstraint(input);
            if (input.validity.valid) {
                clearFieldError(input);
                clearServerFieldError(input);
            }
            else if (input.dataset.validationShown) showFieldError(input);
        });
    });
    passwordInput?.addEventListener('input', checkPasswords);
    confirmInput?.addEventListener('input', checkPasswords);

    document.querySelectorAll('form[method="post"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            checkPasswords();
            if (!form.checkValidity()) return;
            const submit = form.querySelector('button[type="submit"]');
            if (!submit) return;
            form.classList.add('is-submitting');
            form.setAttribute('aria-busy', 'true');
            submit.disabled = true;
            if (form.classList.contains('modal-form') && form.closest('#contact-modal')) {
                submit.firstChild.textContent = 'Sending message ';
            } else if (form.id === 'register-form') {
                submit.firstChild.textContent = 'Creating account ';
            } else if (form.closest('#login-modal')) {
                submit.firstChild.textContent = 'Logging in ';
            }
        });
    });
    if (registerForm?.querySelector('.notice-error, .field-error')) {
        document.querySelector('#register')?.scrollIntoView({ behavior: 'auto', block: 'start' });
    }

    /* Opening curtain has a short reduced-motion path and a load timeout fallback. */
    const finishLoading = () => {
        if (!loader || loader.classList.contains('is-ready')) return;
        document.documentElement.classList.add('page-ready');
        document.querySelector('[data-hero-reveal]')?.classList.add('is-ready');
        loader.classList.add('is-ready');
        syncScrollLock();
        startFeatureTimer();
        window.setTimeout(() => {
            loader.remove();
            showToast();
        }, reducedMotion ? 10 : 850);
    };
    const beginMinimumDisplay = () => {
        if (beginMinimumDisplay.started) return;
        beginMinimumDisplay.started = true;
        window.setTimeout(finishLoading, reducedMotion ? 200 : 1400);
    };
    if (document.readyState === 'complete') beginMinimumDisplay();
    else window.addEventListener('load', beginMinimumDisplay, { once: true });
    window.setTimeout(beginMinimumDisplay, reducedMotion ? 400 : 2600);

    /* Reopen forms after server-side validation or a successful submission. */
    const requestedPanel = document.body.dataset.openPanel;
    const hashPanel = window.location.hash === '#login' ? 'login' : window.location.hash === '#contact' ? 'contact' : '';
    if (requestedPanel || hashPanel) {
        const name = panels[requestedPanel] ? requestedPanel : hashPanel;
        if (name) window.setTimeout(() => openPanel(name, document.querySelector(`[data-open-panel="${name}"]`)), 50);
    }
})();
