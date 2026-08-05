<?php
$currentYear = date('Y');
?>
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-logo">P</div>
                        <strong>PBO Kenya Platform</strong>
                    </div>
                    <p>Kenya's comprehensive platform for Public Benefit Organizations — providing legal knowledge, compliance tools, AI guidance, and civic space monitoring under the PBO Act 2013.</p>
                    <p class="mt-2">
                        <small>Created by <strong class="text-white">Elevate Media Productions</strong></small>
                    </p>
                    <div class="social-links mt-3">
                        <a href="#" title="Twitter/X"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Knowledge Hub -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-col">
                    <h6>Knowledge Hub</h6>
                    <ul>
                        <li><a href="/modules/knowledge-hub/">PBO Act Summaries</a></li>
                        <li><a href="/modules/knowledge-hub/?tab=kiswahili">Kiswahili Content</a></li>
                        <li><a href="/modules/knowledge-hub/resources.php">Toolkits & Guides</a></li>
                        <li><a href="/modules/knowledge-hub/faqs.php">FAQs</a></li>
                        <li><a href="/modules/knowledge-hub/multimedia.php">Infographics & Videos</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Compliance & Tools -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-col">
                    <h6>Compliance</h6>
                    <ul>
                        <li><a href="/modules/compliance-tools/">Checklist Tool</a></li>
                        <li><a href="/modules/compliance-tools/registration.php">Registration Guide</a></li>
                        <li><a href="/modules/compliance-tools/self-assessment.php">Self-Assessment</a></li>
                        <li><a href="/modules/compliance-tools/templates.php">Templates</a></li>
                        <li><a href="/modules/chatbot/">AI Legal Assistant</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Monitoring -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-col">
                    <h6>Monitoring</h6>
                    <ul>
                        <li><a href="/modules/monitoring/">Civic Space Monitor</a></li>
                        <li><a href="/modules/monitoring/report.php">Submit Report</a></li>
                        <li><a href="/modules/monitoring/incident.php">Report Incident</a></li>
                        <li><a href="/modules/dashboard/">Data Dashboard</a></li>
                        <li><a href="/admin/">Admin Portal</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Contact & Legal -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-col">
                    <h6>Legal & Support</h6>
                    <ul>
                        <li><a href="/privacy.php">Privacy Policy</a></li>
                        <li><a href="/terms.php">Terms of Use</a></li>
                        <li><a href="/accessibility.php">Accessibility</a></li>
                        <li><a href="/about.php">About CRECO</a></li>
                        <li><a href="/contact.php">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="mt-3" style="font-size:.8rem;color:rgba(255,255,255,.5);">
                    <p><i class="fab fa-whatsapp me-1"></i>+254 775 333 673 (WhatsApp)</p>
                    <p><i class="fas fa-phone me-1"></i>+254 111 275 630</p>
                    <p><i class="fas fa-envelope me-1"></i>em757896@gmail.com</p>
                </div>
            </div>
        </div>
        
        <hr class="footer-divider">
        
        <div class="footer-bottom">
            <div>
                &copy; <?= $currentYear ?> PBO Kenya Platform by Elevate Media Productions. All rights reserved.
                <span class="ms-2">| Kenya Data Protection Act 2019 Compliant</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span>
                    <i class="fas fa-lock text-success me-1"></i>
                    <small>256-bit SSL Encrypted</small>
                </span>
                <span>
                    <i class="fas fa-universal-access text-info me-1"></i>
                    <small>WCAG 2.1 AA</small>
                </span>
                <span>
                    <i class="fas fa-mobile-alt text-warning me-1"></i>
                    <small>Mobile Optimized</small>
                </span>
            </div>
        </div>
        
        <!-- Disclaimer -->
        <div class="mt-3 p-3 rounded" style="background:rgba(255,0,0,.08);font-size:.75rem;color:rgba(255,255,255,.55);border:1px solid rgba(255,0,0,.15);">
            <strong>⚠️ DISCLAIMER:</strong> This website is a <strong>practice / portfolio project</strong> created for educational purposes only. 
            It is <strong>NOT affiliated with</strong> CRECO Kenya, the Government of Kenya, or any other organization mentioned on this site. 
            All content, data, and information presented here are <strong>fictional</strong> and should not be considered accurate or truthful. 
            Nothing on this site constitutes legal advice. This project does <strong>not impersonate</strong> any real organization.
        </div>
    </div>
</footer>