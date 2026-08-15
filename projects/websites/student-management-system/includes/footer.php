<?php
// footer.php - shared bottom of every page.
// Expected: $base_url ('' or '../'), optional $footer_scripts (raw HTML to output before </body>)
$base_url = $base_url ?? '';
?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <span class="logo-mark">EM</span>
                <span>
                    <strong>Elevate Media College</strong>
                    <small>Est. 2015 &middot; Nairobi, Kenya</small>
                </span>
            </div>
            <p>Empowering the next generation of media, technology and business leaders through interactive learning and modern campus life.</p>
            <div class="socials">
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter / X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Explore</h4>
            <ul>
                <li><a href="<?= $base_url ?>home.php">Home</a></li>
                <li><a href="<?= $base_url ?>campus.php">3D Campus</a></li>
                <li><a href="<?= $base_url ?>departments.php">Departments</a></li>
                <li><a href="<?= $base_url ?>students.php">Students</a></li>
                <li><a href="<?= $base_url ?>timetable.php">Timetable</a></li>
                <li><a href="<?= $base_url ?>library.php">Library</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Academics</h4>
            <ul>
                <li><a href="<?= $base_url ?>department.php?id=1">ICT &amp; Computer Science</a></li>
                <li><a href="<?= $base_url ?>department.php?id=2">Media &amp; Communication</a></li>
                <li><a href="<?= $base_url ?>department.php?id=3">Business &amp; Management</a></li>
                <li><a href="<?= $base_url ?>department.php?id=4">Engineering</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Contact Us</h4>
            <ul class="footer-contact">
                <li><i class="fa-solid fa-location-dot"></i> University Way, Nairobi CBD</li>
                <li><a href="https://wa.me/254775333673" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp: 0775 333 673</a></li>
                <li><a href="tel:+254111275630"><i class="fa-solid fa-phone"></i> Call: +254 111 275 630</a></li>
                <li><a href="mailto:em757896@gmail.com"><i class="fa-solid fa-envelope"></i> em757896@gmail.com</a></li>
                <li><i class="fa-solid fa-clock"></i> Mon &ndash; Fri, 8:00 &ndash; 17:00</li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <span>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></span>
            <span class="footer-made">This website has been created by <strong>Elevate Media Productions</strong></span>
        </div>
    </div>
</footer>

<?php if (isset($footer_scripts)): ?>
    <?= $footer_scripts ?>
<?php endif; ?>

<script src="<?= $base_url ?>assets/js/main.js"></script>
</body>
</html>
