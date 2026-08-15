<?php
require_once __DIR__ . '/includes/config.php';

$page_title  = 'Contact Us';
$active_page = 'contact';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; Contact</p>
        <h1>Get in <span class="grad-text">touch</span></h1>
        <p class="lead" style="max-width:600px">Questions about admissions, programmes or student records? Send us a message and our team will get back to you.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="feature-row">
            <div>
                <div class="info-tiles" style="grid-template-columns:1fr">
                    <div class="info-tile reveal">
                        <i class="fa-solid fa-location-dot"></i>
                        <div><h4>Visit us</h4><p>University Way, Nairobi CBD, Kenya</p></div>
                    </div>
                    <div class="info-tile reveal reveal-delay-1">
                        <i class="fa-solid fa-phone"></i>
                        <div><h4>Call us</h4><p>+254 700 123 456 &middot; Mon&ndash;Fri 8:00&ndash;17:00</p></div>
                    </div>
                    <div class="info-tile reveal reveal-delay-2">
                        <i class="fa-solid fa-envelope"></i>
                        <div><h4>Email us</h4><p>info@elevatemedia.ac.ke</p></div>
                    </div>
                </div>
            </div>

            <div class="form-card reveal reveal-delay-1">
                <h3 style="margin-bottom:4px">Send a message</h3>
                <p class="text-dim" style="font-size:14px; margin-bottom:20px">We usually reply within one working day.</p>
                <form action="process_contact.php" method="post">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Full name</label>
                            <input class="input" type="text" id="name" name="name" required placeholder="Jane Doe">
                        </div>
                        <div class="field">
                            <label for="email">Email address</label>
                            <input class="input" type="email" id="email" name="email" required placeholder="you@example.com">
                        </div>
                        <div class="field full">
                            <label for="subject">Subject</label>
                            <input class="input" type="text" id="subject" name="subject" required placeholder="Admissions enquiry">
                        </div>
                        <div class="field full">
                            <label for="body">Message</label>
                            <textarea class="input" id="body" name="body" required placeholder="How can we help?"></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block mt-2" type="submit"><i class="fa-solid fa-paper-plane"></i> Send message</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
