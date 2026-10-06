<?php
$registrationData = $registrationData ?? [];
$registerErrors = $registerErrors ?? [];
$contactData = $contactData ?? [];
$contactErrors = $contactErrors ?? [];
$currentUser = $currentUser ?? null;
$openPanel = $openPanel ?? '';
$regValue = static fn (string $key): string => esc((string) ($registrationData[$key] ?? ''));
$contactValue = static fn (string $key): string => esc((string) ($contactData[$key] ?? ''));
$selectedRole = (string) ($registrationData['role'] ?? 'customer');
$cssVersion = hash_file('sha256', FCPATH . 'assets/css/app.css') ?: '1';
$jsVersion = hash_file('sha256', FCPATH . 'assets/js/app.js') ?: '1';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f2f63">
    <meta name="description" content="Sun Son Solar brings thoughtful solar energy solutions and personal support to your home.">
    <title>Sun Son Solar — A brighter way to power home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . rawurlencode($cssVersion)) ?>">
</head>
<body data-open-panel="<?= esc($openPanel) ?>">
    <a class="skip-link" href="#main-content">Skip to content</a>

    <div class="loader-curtain" id="page-loader" aria-hidden="true">
        <div class="loader-brand">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="6.5"></circle><path d="M16 2.5v4M16 25.5v4M2.5 16h4m19 0h4M6.45 6.45l2.83 2.83m13.44 13.44 2.83 2.83m0-19.1-2.83 2.83M9.28 22.72l-2.83 2.83"></path></svg></span>
            <span>Sun Son <strong>Solar</strong></span>
        </div>
        <div class="loader-track"><span></span></div>
    </div>

    <?php if (! empty($actionNotice)): ?>
        <aside class="action-toast toast-<?= esc($actionNoticeType ?? 'success') ?>" id="action-toast" role="status" aria-live="polite" aria-hidden="true">
            <span class="toast-icon" aria-hidden="true">✓</span>
            <span class="toast-message"><?= esc($actionNotice) ?></span>
            <button type="button" class="toast-close" data-toast-close aria-label="Dismiss message"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
        </aside>
    <?php endif; ?>

    <main class="page-frame" id="main-content">
        <section class="hero-card" id="home" aria-labelledby="hero-title">
            <div class="hero-visual" aria-hidden="true">
                <svg class="hero-solar-art" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice">
                    <defs>
                        <linearGradient id="sky" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#5790e6"/><stop offset=".55" stop-color="#4f8eb4"/><stop offset="1" stop-color="#f2b16b"/></linearGradient>
                        <linearGradient id="house" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#ecf4ec"/><stop offset="1" stop-color="#92adad"/></linearGradient>
                        <linearGradient id="roof" x1="0" x2="1"><stop stop-color="#274765"/><stop offset="1" stop-color="#0d263f"/></linearGradient>
                        <radialGradient id="sunGlow"><stop stop-color="#fff4c8" stop-opacity=".9"/><stop offset="1" stop-color="#ffe9a2" stop-opacity="0"/></radialGradient>
                        <filter id="softBlur"><feGaussianBlur stdDeviation="24"/></filter>
                    </defs>
                    <path fill="url(#sky)" d="M0 0h1600v1000H0z"/>
                    <circle cx="1150" cy="220" r="255" fill="url(#sunGlow)"/><circle cx="1150" cy="220" r="68" fill="#ffe6a0" opacity=".92"/>
                    <path d="M0 586c225-83 375-70 573-3 183 62 300 56 471-2 209-71 379-39 556 27v392H0z" fill="#407a82" opacity=".68"/>
                    <path d="M0 710c264-111 442-99 655-15s397 88 583-3c123-60 245-62 362-16v324H0z" fill="#183f53" opacity=".9"/>
                    <g opacity=".22" fill="#e8f2eb"><path d="M125 396h96v218h-96zM237 453h69v161h-69zM1392 367h83v260h-83zM1490 434h72v193h-72z"/><path d="M132 423h83v8h-83zm0 30h83v8h-83zm0 30h83v8h-83zm127-2h59v7h-59zm1144-87h71v8h-71zm0 31h71v8h-71zm0 31h71v8h-71z"/></g>
                    <ellipse cx="830" cy="902" rx="535" ry="100" fill="#102c44" opacity=".42" filter="url(#softBlur)"/>
                    <path d="M515 553 835 330l344 240v340H515z" fill="url(#house)"/>
                    <path d="m438 584 387-301 445 310-53 67-390-267-339 263z" fill="url(#roof)"/>
                    <path d="M670 849V660h121v189m234 0V690h112v159" fill="#294b58"/>
                    <path d="M671 661h120v188H671z" fill="#6c9797" opacity=".32"/><path d="M731 661v188m-60-94h120" stroke="#d7e5db" stroke-opacity=".45" stroke-width="7"/>
                    <g transform="translate(818 394) skewX(-34)">
                        <rect width="350" height="180" rx="8" fill="#112c49" stroke="#93b7c5" stroke-width="7"/>
                        <path d="M70 0v180m70-180v180m70-180v180m70-180v180M0 60h350M0 120h350" stroke="#85b8d0" stroke-opacity=".76" stroke-width="4"/>
                        <path d="M10 10h46v48H10zm70 0h46v48H80zm70 0h46v48h-46zm70 0h46v48h-46zm70 0h46v48h-46z" fill="#5790e6" fill-opacity=".2"/>
                    </g>
                    <g fill="#153c4a"><path d="M300 843c-8-89 3-144 39-181 34 45 45 101 35 181zm70 8c-4-66 8-111 39-144 26 37 35 83 27 144zm-157 0c-2-57 8-96 31-124 24 31 33 71 27 124z"/><path d="M1320 825c-8-90 4-145 39-183 35 47 45 103 35 183zm70 25c-3-67 8-110 39-143 26 36 35 82 28 143zm-157 0c-3-57 7-96 31-125 24 32 33 73 27 125z"/></g>
                    <path d="M0 930c301-58 583-26 810 23 256 56 499 54 790-18v65H0z" fill="#0f2f43" opacity=".74"/>
                </svg>
            </div>
            <header class="site-header">
                <nav class="header-nav" aria-label="Main navigation">
                    <a href="#services">Solar solutions</a>
                    <a href="#projects">Our approach</a>
                </nav>
                <a class="brand brand-header" href="#home" aria-label="Sun Son Solar home">
                    <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="6.5"></circle><path d="M16 2.5v4M16 25.5v4M2.5 16h4m19 0h4M6.45 6.45l2.83 2.83m13.44 13.44 2.83 2.83m0-19.1-2.83 2.83M9.28 22.72l-2.83 2.83"></path></svg></span>
                    <span>Sun Son <strong>Solar</strong></span>
                </a>
                <div class="header-actions">
                    <button type="button" class="header-contact" data-open-panel="contact">Contact <span aria-hidden="true">↗</span></button>
                    <?php if ($currentUser): ?>
                        <span class="signed-in">Hi, <?= esc($currentUser['firstName'] ?? 'there') ?></span>
                        <form method="post" action="<?= site_url('logout') ?>" class="logout-form">
                            <?= csrf_field() ?>
                            <button class="header-login" type="submit">Log out</button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="header-login" data-open-panel="login">Log in</button>
                    <?php endif; ?>
                    <button type="button" class="menu-trigger" aria-label="Open menu" aria-expanded="false" aria-controls="site-menu" data-menu-open>
                        <span></span><span></span>
                    </button>
                </div>
            </header>

            <div class="hero-copy">
                <p class="eyebrow eyebrow-light"><span></span> SUN SON SOLAR · ENERGY FOR EVERYDAY</p>
                <h1 id="hero-title" class="hero-title" data-hero-reveal>Power your home<br><em>with the sun.</em></h1>
                <div class="hero-bottom">
                    <div class="hero-summary">
                        <p>Make room for a brighter kind of energy. Thoughtful solar solutions, planned around your home and the way you live.</p>
                        <div class="hero-ctas">
                            <a class="pill-button button-light" href="#services">Explore solar <span aria-hidden="true">↘</span></a>
                            <button type="button" class="hero-text-link" data-open-panel="contact">Talk to our team <span aria-hidden="true">↗</span></button>
                        </div>
                    </div>
                    <div class="hero-feature" aria-live="polite">
                        <div class="feature-card" data-feature-card>
                            <span class="feature-symbol" aria-hidden="true"><svg viewBox="0 0 48 48"><circle cx="24" cy="17" r="7"></circle><path d="M24 2v5m0 20v5M9 17h5m20 0h5M13.4 6.4 17 10m14 14 3.6 3.6M34.6 6.4 31 10M17 24l-3.6 3.6M8 40h32M13 34h22v6H13z"></path></svg></span>
                            <span class="feature-copy"><small data-feature-label>01 / SOLAR PANELS</small><strong data-feature-title>Let daylight do more.</strong><span data-feature-body>A solar plan shaped around your roof and your everyday energy use.</span></span>
                            <span class="feature-count" data-feature-count>01</span>
                        </div>
                        <div class="feature-dots" aria-label="Solar solution highlights">
                            <button type="button" aria-label="Show solar panels" aria-current="true" data-feature-dot="0"></button>
                            <button type="button" aria-label="Show energy storage" data-feature-dot="1"></button>
                            <button type="button" aria-label="Show personal support" data-feature-dot="2"></button>
                        </div>
                    </div>
                </div>
            </div>
            <span class="hero-index" aria-hidden="true">01 — A BRIGHTER START</span>
        </section>

        <section class="trust-section section-card" aria-labelledby="trust-title">
            <div class="trust-top">
                <div class="trust-stamp reveal-on-view"><span class="stamp-sun" aria-hidden="true">✳</span><strong>Energy<br>with intent</strong></div>
                <div class="trust-note reveal-on-view"><span class="index-chip">01</span><div><h2>Designed around real homes</h2><p>Every roof is different. Your solar plan should be, too.</p></div></div>
            </div>
            <div class="trust-stage" data-principle-carousel aria-live="polite">
                <h2 class="ghost-title" id="trust-title"><span data-principle-word>Solar</span><span data-principle-word>made</span><span class="word-accent" data-principle-word>personal</span><span data-principle-word>for home</span></h2>
                <div class="trust-illustration" aria-hidden="true">
                    <svg viewBox="0 0 360 430">
                        <defs><linearGradient id="panelGlow" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#6ba8dc"/><stop offset="1" stop-color="#152f59"/></linearGradient><linearGradient id="sunDisk" x1="0" x2="1"><stop stop-color="#fff0b2"/><stop offset="1" stop-color="#ffc94d"/></linearGradient></defs>
                        <circle cx="255" cy="95" r="67" fill="#f8e8b7"/><circle cx="255" cy="95" r="46" fill="url(#sunDisk)"/>
                        <path d="M25 250 177 142l164 112v150H25z" fill="#edf0e6"/><path d="m9 256 166-127 179 128-24 30-155-108L33 286z" fill="#244465"/>
                        <g transform="translate(128 174) skewX(-28)"><rect width="151" height="76" rx="5" fill="url(#panelGlow)" stroke="#a5cde0" stroke-width="4"/><path d="M38 0v76m38-76v76m38-76v76M0 25h151M0 51h151" stroke="#a8d6e7" stroke-width="2" opacity=".8"/></g>
                        <path d="M90 365v-82h58v82m108 0v-68h39v68" fill="#7ba2a0"/><path d="M90 324h58m-29-41v82" stroke="#dae5dc" stroke-width="4"/>
                        <path d="M21 390h316" stroke="#c4c9c2" stroke-width="6" stroke-linecap="round"/>
                        <g fill="#215467"><path d="M52 391c-4-46 2-77 20-98 19 25 24 55 19 98zm232 0c-4-46 2-77 20-98 18 25 24 55 19 98z"/></g>
                        <circle cx="48" cy="72" r="3" fill="#fff"/><circle cx="101" cy="110" r="2" fill="#fff"/><circle cx="308" cy="166" r="2.5" fill="#fff"/>
                    </svg>
                </div>
                <div class="trust-caption"><span class="caption-rule"></span><p data-principle-caption>Thoughtful design. A clear plan. Power from the sun.</p></div>
            </div>
            <div class="carousel-controls">
                <button class="round-arrow" type="button" aria-label="Previous solar principle" data-principle-prev><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m6 6-6-6 6-6"/></svg></button>
                <div class="carousel-dots" aria-label="Solar principles">
                    <button type="button" aria-label="Show principle 1" aria-current="true" data-principle-dot="0"></button>
                    <button type="button" aria-label="Show principle 2" data-principle-dot="1"></button>
                    <button type="button" aria-label="Show principle 3" data-principle-dot="2"></button>
                </div>
                <button class="round-arrow round-arrow-solid" type="button" aria-label="Next solar principle" data-principle-next><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></button>
            </div>
        </section>

        <section class="solutions-section section-card" id="services" aria-labelledby="solutions-title">
            <div class="section-heading reveal-on-view">
                <p class="eyebrow"><span></span> SOLAR SOLUTIONS</p>
                <h2 id="solutions-title">A clear path to<br><em>cleaner power.</em></h2>
                <p class="section-lede">From the first conversation to the final switch-on, get practical guidance at each step.</p>
            </div>
            <div class="solution-list">
                <button class="solution-row reveal-on-view" type="button" data-open-panel="contact">
                    <span class="solution-number">01</span><span class="solution-body"><strong>Home energy assessment</strong><small>Start with your roof, your usage, and the questions that matter to you.</small></span><span class="solution-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                </button>
                <button class="solution-row reveal-on-view" type="button" data-open-panel="contact">
                    <span class="solution-number">02</span><span class="solution-body"><strong>Solar system design</strong><small>Explore a setup tailored to your property and energy goals.</small></span><span class="solution-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                </button>
                <button class="solution-row reveal-on-view" type="button" data-open-panel="contact">
                    <span class="solution-number">03</span><span class="solution-body"><strong>Professional installation</strong><small>Know what to expect, with a team to guide the process.</small></span><span class="solution-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                </button>
                <button class="solution-row reveal-on-view" type="button" data-open-panel="contact">
                    <span class="solution-number">04</span><span class="solution-body"><strong>Ongoing support</strong><small>Get answers and help as you make the most of your solar system.</small></span><span class="solution-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                </button>
            </div>
        </section>

        <section class="projects-section section-card" id="projects" aria-labelledby="projects-title">
            <div class="projects-intro">
                <div class="mini-solar-mark reveal-on-view" aria-hidden="true"><svg viewBox="0 0 48 48"><circle cx="24" cy="17" r="7"/><path d="M24 2v5m0 20v5M9 17h5m20 0h5M13.4 6.4 17 10m14 14 3.6 3.6M34.6 6.4 31 10M17 24l-3.6 3.6M8 40h32M13 34h22v6H13z"/></svg></div>
                <p class="eyebrow"><span></span> FROM ROOFTOP TO SWITCH-ON</p>
                <h2 id="projects-title">Solar that fits<br><em>your everyday.</em></h2>
                <p>Good solar is more than panels. It’s a system and a plan that make sense for the people who use it.</p>
                <button type="button" class="text-link" data-open-panel="contact">Start a conversation <span aria-hidden="true">↗</span></button>
            </div>
            <div class="project-cards">
                <article class="project-card project-roof reveal-on-view">
                    <div class="project-art" aria-hidden="true"><svg viewBox="0 0 440 520"><defs><linearGradient id="roofSky" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#d7e8ed"/><stop offset="1" stop-color="#f7dca4"/></linearGradient></defs><path fill="url(#roofSky)" d="M0 0h440v520H0z"/><circle cx="333" cy="94" r="46" fill="#ffd16b"/><path d="M0 362c95-38 184-42 273-6 64 25 120 20 167-1v165H0z" fill="#9cac89"/><path d="m27 306 189-138 194 142v164H27z" fill="#ede9dc"/><path d="m0 316 216-164 224 165-21 32-203-141L23 348z" fill="#314d62"/><g transform="translate(173 195) skewX(-29)"><rect width="181" height="94" rx="5" fill="#173555" stroke="#8db6c6" stroke-width="4"/><path d="M45 0v94m45-94v94m45-94v94M0 31h181M0 63h181" stroke="#82aec7" stroke-width="2"/></g><path d="M75 474v-92h59v92m202 0v-80h42v80" fill="#b5c0b5"/><path d="M10 478h420" stroke="#758e7d" stroke-width="9"/></svg></div>
                    <div class="project-caption"><span>01 / ROOFTOP SOLAR</span><strong>Make the roof work harder.</strong><p>Designed for the shape and direction of your home.</p></div>
                </article>
                <article class="project-card project-storage reveal-on-view">
                    <div class="project-art" aria-hidden="true"><svg viewBox="0 0 440 520"><defs><linearGradient id="storeSky" x1="0" x2="0" y1="0" y2="1"><stop stop-color="#7ea4c2"/><stop offset="1" stop-color="#e8c28a"/></linearGradient></defs><path fill="url(#storeSky)" d="M0 0h440v520H0z"/><circle cx="104" cy="125" r="41" fill="#ffe6a1"/><path d="M0 323c109-67 209-72 440-12v209H0z" fill="#43656c"/><path d="M72 318 222 205l154 113v177H72z" fill="#e7e8db"/><path d="m49 325 173-140 178 138-22 30-156-112L69 356z" fill="#1a3953"/><g transform="translate(197 222) skewX(-26)"><rect width="153" height="77" rx="4" fill="#183959" stroke="#9cc7d2" stroke-width="4"/><path d="M38 0v77m38-77v77m38-77v77M0 26h153M0 52h153" stroke="#82b7cd" stroke-width="2"/></g><rect x="110" y="367" width="67" height="106" rx="8" fill="#f7f6ef" stroke="#b4c8c2" stroke-width="5"/><path d="M143 391v44m-15-14 15 15 15-15" stroke="#e4a72d" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/><path d="M99 478h91" stroke="#d1d3c9" stroke-width="8"/></svg></div>
                    <div class="project-caption"><span>02 / ENERGY STORAGE</span><strong>Keep your options open.</strong><p>Ask about storage for power beyond daylight.</p></div>
                </article>
            </div>
        </section>

        <section class="impact-section section-card" aria-labelledby="impact-title">
            <div class="impact-heading reveal-on-view"><p class="eyebrow eyebrow-light"><span></span> A BRIGHTER PERSPECTIVE</p><h2 id="impact-title">Power shaped<br>around <em>you.</em></h2></div>
            <div class="impact-grid">
                <article class="impact-item reveal-on-view"><span>01</span><h3>Use more of the daylight.</h3><p>Make solar part of your home’s daily energy picture.</p></article>
                <article class="impact-item reveal-on-view"><span>02</span><h3>Understand your system.</h3><p>Get a clear explanation of the design and how it works.</p></article>
                <article class="impact-item reveal-on-view"><span>03</span><h3>Get support from people.</h3><p>Talk to a team that can help you navigate the next step.</p></article>
            </div>
            <div class="impact-sun" aria-hidden="true">✳</div>
        </section>

        <section class="questions-section section-card" id="questions" aria-labelledby="questions-title">
            <div class="section-heading reveal-on-view">
                <p class="eyebrow"><span></span> GOOD QUESTIONS, CLEAR ANSWERS</p>
                <h2 id="questions-title">Start with what<br><em>you want to know.</em></h2>
            </div>
            <div class="question-grid">
                <article class="question-card reveal-on-view"><span class="question-icon" aria-hidden="true">01</span><h3>Will solar work for my roof?</h3><p>Roof shape, shade, and orientation all matter. A site assessment can help you understand your options.</p><button type="button" data-open-panel="contact">Ask about your roof <span aria-hidden="true">↗</span></button></article>
                <article class="question-card reveal-on-view"><span class="question-icon" aria-hidden="true">02</span><h3>What happens after sunset?</h3><p>Your setup depends on your needs. Ask our team about grid connection and available storage options.</p><button type="button" data-open-panel="contact">Ask about storage <span aria-hidden="true">↗</span></button></article>
                <article class="question-card reveal-on-view"><span class="question-icon" aria-hidden="true">03</span><h3>How do I get started?</h3><p>Tell us a little about your home and we’ll help you figure out a useful first step.</p><button type="button" data-open-panel="contact">Talk to our team <span aria-hidden="true">↗</span></button></article>
            </div>
        </section>

        <?php if (! $currentUser): ?>
            <section class="account-section section-card" id="register" aria-labelledby="register-title">
                <div class="account-intro reveal-on-view">
                    <p class="eyebrow"><span></span> YOUR SUN SON SOLAR ACCOUNT</p>
                    <h2>Make yourself<br>at <em>home.</em></h2>
                    <p>Create a customer account or an employee account to access Sun Son Solar.</p>
                    <div class="account-points"><span><b>01</b> Choose your account type</span><span><b>02</b> Add your details</span><span><b>03</b> Log in securely</span></div>
                </div>
                <div class="form-card reveal-on-view">
                    <div class="card-heading"><div><p class="card-kicker">GET STARTED</p><h2 id="register-title">Create your account</h2></div><span class="form-sun" aria-hidden="true">✳</span></div>
                    <p class="card-description">Choose an account type, then add your details.</p>
                    <?php if (! empty($registerNotice)): ?><div class="notice <?= ($registerErrors || ($registerFailed ?? false)) ? 'notice-error' : 'notice-success' ?>" role="status"><?= esc($registerNotice) ?></div><?php endif; ?>
                    <form method="post" action="<?= site_url('register') ?>" id="register-form">
                        <?= csrf_field() ?>
                        <div class="account-type" role="group" aria-label="Account type">
                            <button type="button" class="type-option <?= $selectedRole === 'customer' ? 'is-selected' : '' ?>" data-role-choice="customer" aria-pressed="<?= $selectedRole === 'customer' ? 'true' : 'false' ?>"><span class="type-icon" aria-hidden="true">⌂</span><span><strong>Customer</strong><small>Solar service account</small></span></button>
                            <button type="button" class="type-option <?= $selectedRole === 'employee' ? 'is-selected' : '' ?>" data-role-choice="employee" aria-pressed="<?= $selectedRole === 'employee' ? 'true' : 'false' ?>"><span class="type-icon" aria-hidden="true">✦</span><span><strong>Employee</strong><small>Team member account</small></span></button>
                        </div>
                        <?php if (isset($registerErrors['role'])): ?><p class="field-error role-error"><?= esc($registerErrors['role']) ?></p><?php endif; ?>
                        <input type="hidden" name="role" id="account-role" value="<?= esc($selectedRole) ?>">
                        <div class="form-grid">
                            <div class="field"><label for="firstName">First name</label><input id="firstName" name="firstName" type="text" maxlength="100" autocomplete="given-name" placeholder="Juan" value="<?= $regValue('firstName') ?>" required><?php if (isset($registerErrors['firstName'])): ?><small class="field-error"><?= esc($registerErrors['firstName']) ?></small><?php endif; ?></div>
                            <div class="field"><label for="lastName">Last name</label><input id="lastName" name="lastName" type="text" maxlength="100" autocomplete="family-name" placeholder="Dela Cruz" value="<?= $regValue('lastName') ?>" required><?php if (isset($registerErrors['lastName'])): ?><small class="field-error"><?= esc($registerErrors['lastName']) ?></small><?php endif; ?></div>
                            <div class="field"><label for="middleName">Middle name <span class="optional">Optional</span></label><input id="middleName" name="middleName" type="text" maxlength="100" autocomplete="additional-name" value="<?= $regValue('middleName') ?>"><?php if (isset($registerErrors['middleName'])): ?><small class="field-error"><?= esc($registerErrors['middleName']) ?></small><?php endif; ?></div>
                            <div class="field"><label for="birthdate">Birthdate <span class="optional">Optional</span></label><input id="birthdate" name="birthdate" type="date" max="<?= date('Y-m-d') ?>" value="<?= $regValue('birthdate') ?>"><?php if (isset($registerErrors['birthdate'])): ?><small class="field-error"><?= esc($registerErrors['birthdate']) ?></small><?php endif; ?></div>
                            <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" maxlength="150" autocomplete="email" placeholder="you@example.com" value="<?= $regValue('email') ?>" required><?php if (isset($registerErrors['email'])): ?><small class="field-error"><?= esc($registerErrors['email']) ?></small><?php endif; ?></div>
                            <div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" placeholder="0917 123 4567" value="<?= $regValue('phone') ?>" required><?php if (isset($registerErrors['phone'])): ?><small class="field-error"><?= esc($registerErrors['phone']) ?></small><?php endif; ?></div>
                            <div class="field field-wide"><span class="field-label">Gender <span class="optional">Optional</span></span><div class="choice-row"><?php foreach (['Male', 'Female', 'Prefer not to say'] as $gender): ?><label class="radio-choice"><input type="radio" name="gender" value="<?= esc($gender) ?>" <?= ($registrationData['gender'] ?? '') === $gender ? 'checked' : '' ?>><span><?= esc($gender) ?></span></label><?php endforeach; ?></div><?php if (isset($registerErrors['gender'])): ?><small class="field-error"><?= esc($registerErrors['gender']) ?></small><?php endif; ?></div>
                            <div class="field field-wide"><label for="address">Address <span class="optional">Optional</span></label><input id="address" name="address" type="text" maxlength="2000" autocomplete="street-address" placeholder="Street, city, province" value="<?= $regValue('address') ?>"><?php if (isset($registerErrors['address'])): ?><small class="field-error"><?= esc($registerErrors['address']) ?></small><?php endif; ?></div>
                            <div class="field field-wide employee-field" id="department-field" <?= $selectedRole === 'employee' ? '' : 'hidden' ?>><label for="department">Department</label><input id="department" name="department" type="text" maxlength="100" placeholder="e.g. Sales, Installation" value="<?= $regValue('department') ?>" <?= $selectedRole === 'employee' ? 'required' : '' ?>><?php if (isset($registerErrors['department'])): ?><small class="field-error"><?= esc($registerErrors['department']) ?></small><?php endif; ?></div>
                            <div class="field field-wide"><label for="username">Username</label><input id="username" name="username" type="text" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.\-]{3,50}" autocomplete="username" placeholder="Choose a username" value="<?= $regValue('username') ?>" required><?php if (isset($registerErrors['username'])): ?><small class="field-error"><?= esc($registerErrors['username']) ?></small><?php endif; ?></div>
                            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" placeholder="At least 8 characters" required><?php if (isset($registerErrors['password'])): ?><small class="field-error"><?= esc($registerErrors['password']) ?></small><?php endif; ?></div>
                            <div class="field"><label for="confirmPassword">Confirm password</label><input id="confirmPassword" name="confirmPassword" type="password" minlength="8" maxlength="72" autocomplete="new-password" placeholder="Enter it again" required><?php if (isset($registerErrors['confirmPassword'])): ?><small class="field-error"><?= esc($registerErrors['confirmPassword']) ?></small><?php endif; ?></div>
                        </div>
                        <button class="pill-button button-dark submit-button" type="submit">Create account <span aria-hidden="true">→</span></button>
                        <p class="form-footnote">Already registered? <button type="button" class="text-button" data-open-panel="login">Log in</button></p>
                    </form>
                </div>
            </section>
        <?php else: ?>
            <section class="account-section section-card signed-in-section" id="register" aria-labelledby="welcome-title">
                <div class="welcome-card"><span class="welcome-sun" aria-hidden="true">✳</span><div><p class="card-kicker">YOU'RE SIGNED IN</p><h2 id="welcome-title">Welcome, <?= esc(trim(($currentUser['firstName'] ?? '') . ' ' . ($currentUser['lastName'] ?? ''))) ?>.</h2><p><?= esc(ucfirst($currentUser['role'] ?? 'account')) ?> account · <?= esc($currentUser['username'] ?? '') ?></p></div></div>
                <?php if (! empty($actionNotice)): ?><div class="notice notice-success account-success" role="status"><?= esc($actionNotice) ?></div><?php endif; ?>
                <form method="post" action="<?= site_url('logout') ?>"><?= csrf_field() ?><button class="pill-button button-dark" type="submit">Log out <span aria-hidden="true">↗</span></button></form>
            </section>
        <?php endif; ?>

        <footer class="site-footer" id="contact">
            <div class="footer-cta"><div><p class="eyebrow eyebrow-light"><span></span> LET'S TALK SOLAR</p><h2>Ready for a<br><em>brighter home?</em></h2></div><button type="button" class="pill-button button-light" data-open-panel="contact">Contact our team <span aria-hidden="true">↗</span></button></div>
            <div class="footer-main">
                <div class="footer-brand"><a class="brand brand-footer" href="#home"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="6.5"></circle><path d="M16 2.5v4M16 25.5v4M2.5 16h4m19 0h4M6.45 6.45l2.83 2.83m13.44 13.44 2.83 2.83m0-19.1-2.83 2.83M9.28 22.72l-2.83 2.83"></path></svg></span><span>Sun Son <strong>Solar</strong></span></a><p>Thoughtful solar energy solutions for a brighter everyday.</p></div>
                <nav class="footer-links" aria-label="Explore"><strong>EXPLORE</strong><a href="#services">Solar solutions</a><a href="#projects">Our approach</a><a href="#questions">Common questions</a></nav>
                <nav class="footer-links" aria-label="Account"><strong>YOUR ACCOUNT</strong><a href="#register">Create an account</a><button type="button" data-open-panel="login">Log in</button><button type="button" data-open-panel="contact">Contact us</button></nav>
            </div>
            <div class="footer-bottom"><span>© <?= date('Y') ?> Sun Son Solar</span><span>POWERING A BRIGHTER TOMORROW <span class="footer-sun" aria-hidden="true">✳</span></span><a href="#home">Back to top ↑</a></div>
        </footer>
    </main>

    <div class="menu-overlay" id="site-menu" aria-hidden="true" inert>
        <button class="menu-shade" type="button" aria-label="Close menu" data-menu-close></button>
        <div class="menu-panel" role="dialog" aria-modal="true" aria-label="Site menu">
            <div class="menu-top"><a class="brand brand-menu" href="#home"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="6.5"></circle><path d="M16 2.5v4M16 25.5v4M2.5 16h4m19 0h4M6.45 6.45l2.83 2.83m13.44 13.44 2.83 2.83m0-19.1-2.83 2.83M9.28 22.72l-2.83 2.83"></path></svg></span><span>Sun Son <strong>Solar</strong></span></a><button class="menu-close" type="button" aria-label="Close menu" data-menu-close><span></span><span></span></button></div>
            <nav class="menu-links" aria-label="Site menu links"><a href="#services" data-menu-link>Solar solutions</a><a href="#projects" data-menu-link>Our approach</a><a href="#questions" data-menu-link>Questions</a><a href="#register" data-menu-link>Create account</a></nav>
            <div class="menu-bottom"><button type="button" class="pill-button button-light" data-open-panel="contact">Contact our team <span aria-hidden="true">↗</span></button><span>Solar, thoughtfully done.</span></div>
        </div>
    </div>

    <div class="modal-backdrop" id="login-modal" aria-hidden="true" inert>
        <button type="button" class="modal-shade" data-close-panel aria-label="Close log in"></button>
        <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="login-title">
            <button type="button" class="modal-close" data-close-panel aria-label="Close log in"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
            <p class="eyebrow"><span></span> WELCOME BACK</p><h2 id="login-title">Log in to<br><em>your account.</em></h2><p class="modal-copy">Use your customer or employee username and password.</p>
            <?php if (! empty($loginNotice)): ?><div class="notice notice-error" role="alert"><?= esc($loginNotice) ?></div><?php endif; ?>
            <form method="post" action="<?= site_url('login') ?>" class="modal-form">
                <?= csrf_field() ?>
                <div class="field"><label for="login-username">Username</label><input id="login-username" name="username" type="text" autocomplete="username" required></div>
                <div class="field"><label for="login-password">Password</label><input id="login-password" name="password" type="password" autocomplete="current-password" required></div>
                <button class="pill-button button-dark submit-button" type="submit">Log in <span aria-hidden="true">→</span></button>
            </form>
            <?php if (! $currentUser): ?><p class="form-footnote">New to Sun Son Solar? <button type="button" class="text-button" data-go-register>Register an account</button></p><?php endif; ?>
        </section>
    </div>

    <div class="modal-backdrop" id="contact-modal" aria-hidden="true" inert>
        <button type="button" class="modal-shade" data-close-panel aria-label="Close contact form"></button>
        <section class="modal-card contact-card" role="dialog" aria-modal="true" aria-labelledby="contact-title">
            <button type="button" class="modal-close" data-close-panel aria-label="Close contact form"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
            <p class="eyebrow"><span></span> LET'S START A CONVERSATION</p><h2 id="contact-title">How can we<br><em>help you?</em></h2><p class="modal-copy">Tell us what’s on your mind and the Sun Son Solar team will get back to you.</p>
            <?php if (! empty($contactNotice)): ?><div class="notice <?= ($contactErrors || ($contactFailed ?? false)) ? 'notice-error' : 'notice-success' ?>" role="status"><?= esc($contactNotice) ?></div><?php endif; ?>
            <form method="post" action="<?= site_url('contact') ?>" class="modal-form">
                <?= csrf_field() ?>
                <div class="field"><label for="contact-name">Your name</label><input id="contact-name" name="name" type="text" maxlength="150" autocomplete="name" placeholder="Your full name" value="<?= $contactValue('name') ?>" required><?php if (isset($contactErrors['name'])): ?><small class="field-error"><?= esc($contactErrors['name']) ?></small><?php endif; ?></div>
                <div class="field"><label for="contact-email">Email address</label><input id="contact-email" name="email" type="email" maxlength="150" autocomplete="email" placeholder="you@example.com" value="<?= $contactValue('email') ?>" required><?php if (isset($contactErrors['email'])): ?><small class="field-error"><?= esc($contactErrors['email']) ?></small><?php endif; ?></div>
                <div class="field"><label for="contact-message">Your message</label><textarea id="contact-message" name="message" rows="4" maxlength="5000" placeholder="Tell us a little about your home or project…" required><?= $contactValue('message') ?></textarea><?php if (isset($contactErrors['message'])): ?><small class="field-error"><?= esc($contactErrors['message']) ?></small><?php endif; ?></div>
                <button class="pill-button button-dark submit-button" type="submit">Send message <span aria-hidden="true">→</span></button>
            </form>
        </section>
    </div>

    <script src="<?= base_url('assets/js/app.js?v=' . rawurlencode($jsVersion)) ?>" defer></script>
</body>
</html>
