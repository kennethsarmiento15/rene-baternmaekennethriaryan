    <div class="menu-overlay" id="site-menu" aria-hidden="true" inert>
        <button class="menu-shade" type="button" aria-label="Close menu" data-menu-close></button>
        <div class="menu-panel" role="dialog" aria-modal="true" aria-label="Site menu">
            <div class="menu-top">
                <a class="brand brand-menu" href="#home" data-menu-link aria-label="Sun Son Solar home">
                    <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="6.5"></circle><path d="M16 2.5v4M16 25.5v4M2.5 16h4m19 0h4M6.45 6.45l2.83 2.83m13.44 13.44 2.83 2.83m0-19.1-2.83 2.83M9.28 22.72l-2.83 2.83"></path></svg></span>
                    <span>Sun Son <strong>Solar</strong></span>
                </a>
                <span class="menu-status"><span aria-hidden="true"></span> POWER, PLANNED AROUND YOU</span>
                <button class="menu-close" type="button" aria-label="Close menu" data-menu-close><span></span><span></span></button>
            </div>
            <div class="menu-content">
                <div class="menu-intro">
                    <p class="menu-overline">THE SUN SON SOLAR GUIDE</p>
                    <h2>Make more<br>of your <em>daylight.</em></h2>
                    <p>Explore solar for your space, find answers, or talk through the next step with our team.</p>
                    <span class="menu-index" aria-hidden="true">SS / <?= date('y') ?></span>
                </div>
                <nav class="menu-links" aria-label="Explore Sun Son Solar">
                    <?php foreach (($navigationGroups ?? []) as $group): ?>
                        <section class="menu-group" aria-label="<?= esc($group['eyebrow']) ?>">
                            <h3><?= esc($group['eyebrow']) ?></h3>
                            <?php foreach ($group['links'] as $link): ?>
                                <a href="<?= esc($link['href'], 'attr') ?>" data-menu-link>
                                    <span class="menu-link-copy"><strong><?= esc($link['label']) ?></strong><small><?= esc($link['description']) ?></small></span>
                                    <span class="menu-link-arrow" aria-hidden="true">&#8599;</span>
                                </a>
                            <?php endforeach; ?>
                        </section>
                    <?php endforeach; ?>
                    <a class="menu-account-link" href="#register" data-menu-link>Customer &amp; team accounts <span aria-hidden="true">&#8599;</span></a>
                </nav>
                <aside class="menu-feature" aria-label="Get a solar quote">
                    <div class="menu-solar-art" aria-hidden="true">
                        <span class="menu-sun-orbit"><span></span></span>
                        <svg viewBox="0 0 240 160"><path d="m14 110 96-68 116 70v42H14z"/><path d="m5 112 104-80 124 79-10 14-114-71-95 72z"/><g><path d="m105 65 82 2 21 39-90-2z"/><path d="m132 66 10 37m17-36 10 37m-58-23 89 2"/></g></svg>
                    </div>
                    <p class="menu-feature-kicker"><span></span> YOUR NEXT STEP</p>
                    <h3>Let's see what<br>solar can do.</h3>
                    <p>Tell us about your home or business. We'll help you find a clear place to start.</p>
                    <button type="button" class="menu-quote-button" data-open-panel="contact">Get a quote <span aria-hidden="true">&#8599;</span></button>
                    <span class="menu-feature-note">A real conversation with our team.</span>
                </aside>
            </div>
            <div class="menu-bottom"><span>Sun Son Solar <i aria-hidden="true">&#183;</i> A brighter way forward</span><a href="#questions" data-menu-link>Need a hand? Visit support <span aria-hidden="true">&#8599;</span></a></div>
        </div>
    </div>
