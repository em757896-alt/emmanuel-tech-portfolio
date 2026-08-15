<?php
require_once __DIR__ . '/includes/config.php';

$page_title  = 'About Us';
$active_page = 'about';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <div class="container">
        <p class="crumb"><a href="home.php">Home</a> &rsaquo; About</p>
        <h1>About <span class="grad-text">Elevate Media College</span></h1>
        <p class="lead" style="max-width:640px">A modern institution where media, technology and business education come together under one connected campus.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="feature-row">
            <div>
                <span class="eyebrow reveal">Our Story</span>
                <h2 class="reveal">Empowering the next generation of creators &amp; innovators</h2>
                <p class="text-dim reveal reveal-delay-1">
                    Founded in 2015, Elevate Media College was built on a simple belief: students learn best when
                    everything they need is connected. Our digital campus brings classrooms, timetables, the library
                    and student records into one interactive experience — while our teaching keeps media production,
                    technology and enterprise at the heart of every programme.
                </p>
                <p class="text-dim reveal reveal-delay-1">
                    From the ICT building to the media studio, every space is designed for hands-on learning with
                    real tools, real projects and real industry connections.
                </p>
            </div>
            <div class="visual-box reveal reveal-delay-1">
                <div class="mini-3d"><div class="building"></div></div>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:rgba(255,255,255,.02); border-block:1px solid var(--border);">
    <div class="container">
        <div class="section-head center reveal">
            <span class="eyebrow">Mission &amp; Vision</span>
            <h2>What drives us</h2>
        </div>
        <div class="value-grid">
            <div class="card value-card reveal">
                <div class="card-icon"><i class="fa-solid fa-bullseye"></i></div>
                <h3>Our Mission</h3>
                <p>To deliver industry-relevant education that blends creativity with technology, producing graduates ready for the modern workforce.</p>
            </div>
            <div class="card value-card reveal reveal-delay-1">
                <div class="card-icon"><i class="fa-solid fa-eye"></i></div>
                <h3>Our Vision</h3>
                <p>To be East Africa's most innovative media and technology college — a connected campus where ideas become careers.</p>
            </div>
            <div class="card value-card reveal reveal-delay-2">
                <div class="card-icon"><i class="fa-solid fa-handshake"></i></div>
                <h3>Our Values</h3>
                <p>Excellence, integrity, innovation and inclusion — practised every day by staff and students alike.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="section-head center reveal">
            <span class="eyebrow">Campus Life</span>
            <h2>A connected place to learn &amp; grow</h2>
        </div>
        <div class="info-tiles">
            <div class="info-tile reveal">
                <i class="fa-solid fa-cubes"></i>
                <div><h4>3D Interactive Campus</h4><p>Explore every building from your browser.</p></div>
            </div>
            <div class="info-tile reveal reveal-delay-1">
                <i class="fa-solid fa-clapperboard"></i>
                <div><h4>Media Studio</h4><p>Film, broadcast and digital content production.</p></div>
            </div>
            <div class="info-tile reveal reveal-delay-2">
                <i class="fa-solid fa-microchip"></i>
                <div><h4>Modern Labs</h4><p>ICT and engineering labs with real equipment.</p></div>
            </div>
            <div class="info-tile reveal reveal-delay-3">
                <i class="fa-solid fa-book-open"></i>
                <div><h4>Library &amp; Study</h4><p>A growing catalog of books and quiet study spaces.</p></div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="campus.php"><i class="fa-solid fa-cubes"></i> Explore the Campus</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
