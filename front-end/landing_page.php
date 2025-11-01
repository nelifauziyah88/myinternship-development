<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MyInternship</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Atlantis -->
    <link rel="stylesheet"
        href="https://themekita.com/demo-atlantis-bootstrap/livepreview/examples/assets/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://themekita.com/demo-atlantis-bootstrap/livepreview/examples/assets/css/atlantis.min.css">

    <!-- Icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/style_landing.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="landing_page.php">
                <img src="assets/img/logo.png" alt="MyInternship Logo">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="#hero">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#footer">Contact Us</a></li>
                    <li class="nav-item"><a class="btn btn-login" href="role_login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section id="hero" class="hero">
        <div class="hero-content">
            <h1>Manage your internship experience more easily<br>with <span>MyInternship</span></h1>
            <p>Supporting industrial learning through structured internships</p>
            <a href="#about" class="btn btn-outline-primary">Get Started</a>
            <a href="registrasi.php" class="btn btn-outline-primary">Student Registration</a>
        </div>
        <div>
            <img src="assets/img/index.png" alt="Hero Image">
        </div>
    </section>

    <!-- Why Choose -->
    <section class="why-section text-center">
        <div class="container">
            <h5 class="why-title">Why Choose MyInternship?</h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="why-box">
                        <h6>Easy Monitoring</h6>
                        <p>Track student progress in real time through digital internship reports.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-box">
                        <h6>Integrated Collaboration</h6>
                        <p>Unify communication between students, lecturers, and industry mentors.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-box">
                        <h6>Transparent Evaluation</h6>
                        <p>Assessment process is clearer, structured, and accessible to all parties.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container-fluid px-0">
            <div class="row align-items-center" style="padding-left: 6%; padding-right: 6%;">
                <div class="col-md-7">
                    <h2>About MyInternship</h2>
                    <p>MyInternship is an internship management application that helps organize every stage of the
                        internship process, from registration and implementation to final evaluation. As of October 5,
                        2022,
                        MyInternship has been used by more than 6,000 students, involving 300 industry mentors and 200
                        academic supervisors at the Polytechnic. In addition, MyInternship also serves as a
                        communication
                        platform between students, lecturers, and mentors.</p>
                    <p>Higher education institutions can assign academic supervisors, while industry partners can also
                        appoint mentors for each student at their respective internship placements. With MyInternship,
                        monitoring and evaluating student internships becomes easier for both polytechnics and
                        industries.
                        Student performance can be reported regularly through progress reports. From these reports,
                        academic supervisors can monitor the learning progress of students throughout the internship
                        program.</p>
                </div>
                <div class="col-md-5 text-center">
                    <img src="assets/img/about.png" alt="About MyInternship Illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center justify-content-center">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-box">
                        <h3>6000+</h3>
                        <p>Students actively participate<br>in the program</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-box">
                        <h3>1600</h3>
                        <p>Industries have established<br>partnerships</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-box">
                        <h3>500</h3>
                        <p>Lecturers are involved<br>in mentoring</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-box">
                        <h3>7</h3>
                        <p>University lecturers collaborate<br>with MyInternship</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section text-center py-5" style="font-family: 'Open Sans', sans-serif;">
        <div class="container">
            <h2 class="fw-bold mb-5" style="color: #3498db; font-size: 32px;">Features</h2>

            <!-- Gambar Ilustrasi -->
            <div class="feature-image mb-5">
                <img src="assets/img/features.png" alt="Features Illustration" class="feature-img">
            </div>

            <!-- Features Cards -->
            <div class="row gy-4 justify-content-center">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <h4>Progress Monitoring</h4>
                        <p>Lecturers and industry mentors can monitor student activities directly in real time.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <h4>Feedback & Evaluation</h4>
                        <p>The evaluation process is more structured with direct feedback from supervisors.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <h4>Digital Portfolio</h4>
                        <p>Students can compile their work results into a digital portfolio.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Features Section -->

    <!-- FAQ Section -->
    <section id="faq" class="faq-section">
        <h2>FAQ</h2>
        <p>Frequently Asked Questions</p>

        <div class="faq-item">
            <div class="faq-question">
                <span>❓</span> Who can use MyInternship?
            </div>
            <div class="faq-answer">
                Students, lecturers, and industry partners can use MyInternship. Students can manage their
                internship activities, lecturers serve as supervisors, and industry partners act as mentors
                and
                evaluators.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>❓</span> How can companies register?
            </div>
            <div class="faq-answer">
                Companies can register through Polibatam CDC to obtain a special MyInternship account for
                industry.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>❓</span> How can students register for internships?
            </div>
            <div class="faq-answer">
                Students can register through the Polibatam Talent Hub or through the MyInternship dashboard
                in the
                internship registration menu.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>❓</span> How can I manage internship administration?
            </div>
            <div class="faq-answer">
                Internship administration such as attendance, logbooks, and reports can be managed directly
                through
                the MyInternship dashboard for students, lecturers, and industry mentors.
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="footer" style="font-family: 'Open Sans', sans-serif;">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3 class="footer-title">MyInternship</h3>
                    <p class="footer-text">
                        If you have any further questions about this website, please don’t hesitate to contact us directly during office hours or through the contact information provided below.
                    </p>
                </div>

                <div class="footer-column">
                    <h3 class="footer-title">Hubungi Kami</h3>
                    <div class="footer-contact">
                        <p class="footer-text">
                            <i class="bi bi-geo-alt"></i> Gedung Mohamad Nasir Lt.12, Kompleks Politeknik Negeri Batam,
                            Batam Centre, 29461
                        </p>
                        <p class="footer-text"><i class="bi bi-envelope"></i> pblifpagi3a@gmail.com</p>
                        <p class="footer-text"><i class="bi bi-telephone"></i> +62 813-7853-5706</p>
                    </div>
                </div>

                <div class="footer-column">
                    <h3 class="footer-title">Temukan Kami di</h3>
                    <div class="footer-social">
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>© 2025 MyInternship | Made with <span style="color: #3498db;">❤</span> by
                    <a href="#" target="_blank" class="footer-link">
                        <strong>PBLIFPagi3A-3</strong>
                    </a>
                </p>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <script src="https://themekita.com/demo-atlantis-bootstrap/livepreview/examples/assets/js/core/jquery.3.2.1.min.js"></script>
    <script src="https://themekita.com/demo-atlantis-bootstrap/livepreview/examples/assets/js/core/bootstrap.min.js"></script>
    <script>
        // ===== Navbar Scroll Effect =====
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // ===== FAQ Toggle =====
        const faqItems = document.querySelectorAll(".faq-item");
        faqItems.forEach(item => {
            const q = item.querySelector(".faq-question");
            const a = item.querySelector(".faq-answer");
            a.style.maxHeight = "0px";
            q.addEventListener("click", () => {
                item.classList.toggle("active");
                if (item.classList.contains("active")) {
                    a.style.maxHeight = a.scrollHeight + "px";
                } else {
                    a.style.maxHeight = "0px";
                }
            });
        });

        // ===== Smooth Scroll for Navbar Links =====
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    window.scrollTo({
                        top: target.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // ===== Floating Hero Image Animation =====
        const heroImg = document.querySelector('.hero img');
        if (heroImg) {
            heroImg.style.transition = 'transform 2s ease-in-out';
            let direction = 1;
            setInterval(() => {
                heroImg.style.transform = `translateY(${direction * 15}px)`;
                direction *= -1;
            }, 2000);
        }

        // ===== Fade-in Animation (Revised) =====
        const fadeEls = document.querySelectorAll("section, .faq-item, .feature-box, .why-box");

        fadeEls.forEach(el => {
            el.style.opacity = 0;
            el.style.transform = "translateY(30px)";
            el.style.transition = "all 0.8s ease-out";
        });

        // Saat halaman pertama kali dimuat
        window.addEventListener("load", () => {
            fadeEls.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight - 50) {
                    el.style.opacity = 1;
                    el.style.transform = "translateY(0)";
                }
            });
        });

        // Saat di-scroll ke bawah
        window.addEventListener("scroll", () => {
            fadeEls.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    el.style.opacity = 1;
                    el.style.transform = "translateY(0)";
                }
            });
        });

        // ===== Button Hover Animation =====
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                btn.style.transform = 'scale(1.1)';
                btn.style.boxShadow = '0 5px 15px rgba(52,152,219,0.3)';
                btn.style.transition = 'all 0.2s ease';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'scale(1)';
                btn.style.boxShadow = 'none';
            });
        });

        // ===== Card Hover Animation (Soft Glow Version) =====
        const cardElements = document.querySelectorAll('.feature-box, .why-box, .stats-box');

        cardElements.forEach(card => {
            card.style.transition = 'transform 0.4s ease, box-shadow 0.4s ease';
            card.style.willChange = 'transform, box-shadow';

            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-12px) scale(1.05)';
                card.style.boxShadow = '0 12px 25px rgba(52, 152, 219, 0.3)';
                card.style.border = '1px solid rgba(52, 152, 219, 0.3)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
                card.style.boxShadow = 'none';
                card.style.border = 'none';
            });
        });
    </script>

</body>

</html>