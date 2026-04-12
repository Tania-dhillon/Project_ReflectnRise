<?php
// Homepage
// ------------------------------------------------------------
// This is the homepage of Reflect & Rise.
// It includes brief information, benefits, features, FAQ section, buttons...
// ------------------------------------------------------------

require_once __DIR__ . '/includes/header.php';
?>

<nav class="navbar navbar-expand-lg py-3 mindful-nav mindful-nav-fixed">
    <div class="container">
        <a class="navbar-brand home-brand" href="index.php">
            <span class="home-brand-icon"><i class="bi bi-book"></i></span>
            <span class="home-brand-text">
                <span class="home-brand-title">Reflect &amp; Rise</span>
                <span class="home-brand-subtitle">STUDENT WELLBEING</span>
            </span>
        </a>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="btn btn-link text-decoration-none nav-link-btn">Dashboard</a>
                <a href="logout.php" class="btn btn-mindful">Log out</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-link text-decoration-none nav-link-btn">Log in</a>
                <a href="signup.php" class="btn btn-mindful">Get started free <i class="bi bi-arrow-right-short"></i></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<section class="hero-section text-center hero-soft-panel">
    <div class="hero-bubble hero-bubble-one"></div>
    <div class="hero-bubble hero-bubble-two"></div>
    <div class="hero-bubble hero-bubble-three"></div>

    <div class="container hero-container">
        <div class="pill-badge mx-auto mb-3">
            <i class="bi bi-heart-pulse"></i> Student Wellbeing Tool
        </div>

        <h1 class="hero-title">Take care of your<br><span>mental wellbeing</span></h1>

        <p class="hero-text mx-auto">
            Reflect &amp; Rise helps university students track their mood, reflect on their experiences,
            and build healthier habits — one check-in at a time.
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
            <a href="signup.php" class="btn btn-mindful btn-lg px-4">Try it for free <i class="bi bi-arrow-right-short"></i></a>
            <a href="#how-it-works" class="btn btn-soft btn-lg px-4">See how it works</a>
        </div>

        <p class="hero-note mt-3">No credit card required · Takes 1 minute to set up</p>

        <div class="row justify-content-center g-3 mt-4 mood-cards">
            <div class="col-6 col-md-3">
                <div class="mood-card">
                    <div class="emoji">🤩</div>
                    <h6>Great</h6>
                    <div class="dots">● ● ● ●</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mood-card">
                    <div class="emoji">😊</div>
                    <h6>Good</h6>
                    <div class="dots">● ● ●</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mood-card">
                    <div class="emoji">🙂</div>
                    <h6>Okay</h6>
                    <div class="dots">● ●</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="mood-card">
                    <div class="emoji">😕</div>
                    <h6>Low</h6>
                    <div class="dots">●</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="about-box">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h2 class="section-title text-start mb-3">Built for the reality of university life</h2>
                    <p>
                        University is exciting — but it can also be overwhelming. Deadlines, social pressure,
                        financial stress, and the challenge of being away from home all take a toll on mental wellbeing.
                    </p>
                    <p>
                        Reflect &amp; Rise gives you a simple, private tool to check in with yourself regularly,
                        understand your patterns, and develop self-awareness to navigate student life with greater resilience.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3 small-benefits">
                        <div class="col-sm-6"><p><i class="bi bi-check-circle"></i> Understand what affects your mood</p></div>
                        <div class="col-sm-6"><p><i class="bi bi-check-circle"></i> Spot stress patterns before they overwhelm</p></div>
                        <div class="col-sm-6"><p><i class="bi bi-check-circle"></i> Build a reflective journaling habit</p></div>
                        <div class="col-sm-6"><p><i class="bi bi-check-circle"></i> Track your wellbeing over the term</p></div>
                        <div class="col-sm-6"><p><i class="bi bi-check-circle"></i> Get prompts to help you think clearly</p></div>
                        <div class="col-sm-6"><p><i class="bi bi-check-circle"></i> Celebrate your progress and resilience</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Everything you need</h2>
            <p class="section-subtitle">Five simple tools that work together to support your mental wellbeing.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon pink"><i class="bi bi-heart"></i></div>
                    <h5>Daily Mood Check-ins</h5>
                    <p>Track how you feel each day in under 2 minutes. Log mood, energy, stress, and sleep quality to build a clear picture of your wellbeing.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon purple"><i class="bi bi-stars"></i></div>
                    <h5>Guided Reflections</h5>
                    <p>Answer thoughtful prompts covering stress, workload, motivation, and more. Writing down your thoughts helps you process and understand yourself better.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon yellow"><i class="bi bi-pencil"></i></div>
                    <h5>Personal Journal</h5>
                    <p>A private space to write freely or respond to prompts. Free-write, gratitude, and self-reflection all in one place.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-6">
                <div class="feature-card">
                    <div class="feature-icon teal"><i class="bi bi-bullseye"></i></div>
                    <h5>Goals</h5>
                    <p>Set simple wellbeing or study goals, keep track of progress, and celebrate the habits that help you move forward.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-6">
                <div class="feature-card">
                    <div class="feature-icon green"><i class="bi bi-bar-chart"></i></div>
                    <h5>Progress Insights</h5>
                    <p>Visualise your patterns over time. Spot trends in mood, stress, and energy so you can make informed choices about your wellbeing.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space" id="how-it-works">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">How it works</h2>
            <p class="section-subtitle">Simple enough to stick to, powerful enough to make a difference.</p>
        </div>

        <div class="row g-4 process-grid">
            <div class="col-md-6">
                <div class="process-item">
                    <span>01</span>
                    <div>
                        <h6>Create your account</h6>
                        <p>Sign up for free — no card needed. It takes less than a minute.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="process-item">
                    <span>02</span>
                    <div>
                        <h6>Do your first check-in</h6>
                        <p>Log your mood in a simple guided flow. It takes about 2 minutes.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="process-item">
                    <span>03</span>
                    <div>
                        <h6>Reflect and journal</h6>
                        <p>Use guided prompts or free-write to process your thoughts and feelings.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="process-item">
                    <span>04</span>
                    <div>
                        <h6>Set goals</h6>
                        <p>Create simple goals around study, wellbeing, or habits so you stay motivated and consistent.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mx-auto">
                <div class="process-item">
                    <span>05</span>
                    <div>
                        <h6>Track your patterns</h6>
                        <p>Over time, your insights page reveals trends so you understand your wellbeing better.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">What students say</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p>“I never realised how much my sleep was affecting my mood until I saw the patterns in my insights.”</p>
                    <small>Esha, 2nd Year Psychology</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p>“The reflection prompts helped me figure out what was actually stressing me out — not just feel overwhelmed.”</p>
                    <small>James, 3rd Year Engineering</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p>“Having a private space to journal that also shows me trends over time is exactly what I needed.”</p>
                    <small>Sonia, Masters Student</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0">
    <div class="container faq-wrap">
        <div class="text-center mb-5">
            <h2 class="section-title">Common questions</h2>
        </div>

        <div class="accordion mindful-accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Is this free to use?</button></h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, completely free for all university students. No credit card required to sign up.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Is my data private?</button></h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Absolutely. Your journal entries and mood data are private to you.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">How much time does it take?</button></h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">A daily check-in takes about 2 minutes. Journaling and reflections are optional and at your own pace.</div></div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Do I need to use it every day?</button></h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Not at all — even a few check-ins a week will help you spot patterns and build self-awareness over time.</div></div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0 pb-5">
    <div class="container">
        <div class="cta-box text-center">
            <h2 class="section-title mb-3">Start your wellbeing journey today</h2>
            <p class="section-subtitle mb-4">It’s free, private, and takes under a minute to get started.</p>
            <a href="signup.php" class="btn btn-mindful btn-lg px-4">Try Reflect &amp; Rise for free <i class="bi bi-arrow-right-short"></i></a>
            <div class="hero-note mt-3">No credit card · No commitments · Just you</div>
        </div>
    </div>
</section>

<footer class="py-4 mindful-footer">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2 small text-muted">
            <span class="brand-icon small-icon"><i class="bi bi-book"></i></span>
            <span>Reflect &amp; Rise</span>
        </div>
        <div class="small text-muted">A digital wellbeing tool for university students 💚</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
