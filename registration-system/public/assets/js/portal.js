(() => {
    'use strict';

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Continue?')) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-geolocation-form]').forEach((form) => {
        const submit = form.querySelector('[data-checkin-button]');
        const status = form.querySelector('[data-location-message]');
        const latitude = form.querySelector('[name="latitude"]');
        const longitude = form.querySelector('[name="longitude"]');

        form.addEventListener('submit', (event) => {
            if (form.dataset.locationReady === 'true') return;
            event.preventDefault();

            if (!navigator.geolocation) {
                if (status) status.textContent = 'This browser cannot read device location. Open the site in a current browser to check in.';
                return;
            }

            if (submit) {
                submit.disabled = true;
                submit.querySelector('span').textContent = 'Getting your location…';
            }
            if (status) status.textContent = 'Waiting for location permission…';

            navigator.geolocation.getCurrentPosition((position) => {
                latitude.value = position.coords.latitude.toFixed(7);
                longitude.value = position.coords.longitude.toFixed(7);
                form.dataset.locationReady = 'true';
                if (status) status.textContent = 'Location found. Saving your site arrival…';
                form.requestSubmit(submit);
            }, (error) => {
                if (submit) {
                    submit.disabled = false;
                    submit.querySelector('span').textContent = 'Check in at site';
                }
                const messages = {
                    1: 'Location access was denied. Allow location access to record a site check-in.',
                    2: 'Your location is unavailable. Move to an open area and try again.',
                    3: 'Location took too long. Check your connection and try again.',
                };
                if (status) status.textContent = messages[error.code] || 'Could not read your location. Please try again.';
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        });
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) return;
            const button = event.submitter || form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            if (form.matches('[data-geolocation-form]') && form.dataset.locationReady !== 'true') return;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (button.dataset.checkinButton !== undefined) button.querySelector('span').textContent = 'Saving arrival…';
        });
    });

    const navLinks = [...document.querySelectorAll('.rail-nav a[href^="#"]')];
    const sections = navLinks.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
    if ('IntersectionObserver' in window && sections.length) {
        const observer = new IntersectionObserver((entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
            if (!visible) return;
            navLinks.forEach((link) => {
                const current = link.getAttribute('href') === `#${visible.target.id}`;
                link.classList.toggle('is-current', current);
                if (current) link.setAttribute('aria-current', 'location');
                else link.removeAttribute('aria-current');
            });
        }, { rootMargin: '-20% 0px -65% 0px', threshold: [0, 0.2, 0.5] });
        sections.forEach((section) => observer.observe(section));
    }
})();
