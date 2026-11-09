<?php
require_once __DIR__ . '/config/db.php';
session_start();

if (isset($_SESSION['user'])) {
    require_once __DIR__ . '/shared/helpers/portal.php';
    header('Location: ' . portal_dashboard_url());
    exit();
}

$contactStatus = $_GET['message'] ?? '';
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $contactError = 'Please fill all contact fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please enter a valid email address.';
    } else {
        mysqli_query($conn, "
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT(11) NOT NULL AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                message TEXT NOT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_contact_messages_created_at (created_at),
                KEY idx_contact_messages_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO contact_messages (name, email, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $message, $ipAddress, $userAgent);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: index.php?message=sent#contact');
                exit();
            }

            mysqli_stmt_close($stmt);
        }

        $contactError = 'Unable to save your message. Please try again.';
    }
}

?>
<?php
// Landing page for MindMerge SmartCampus ERP
// Premium, modern, fully responsive design with animations
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
if(localStorage.getItem("mindmerge-theme")==="dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>
<script>
(() => {
    if (localStorage.getItem("mindmerge-theme") === "dark") {
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>

    <title>MindMerge SmartCampus ERP - AI-Powered School Management System</title>
    <meta name="description" content="Transforming Educational Institutions with AI Powered SmartCampus ERP - Complete cloud-based school and college management system">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    
    <!-- Swiper JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.0/swiper-bundle.min.css" rel="stylesheet">
    

    <link rel="stylesheet" href="assets/css/material-theme.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
            :root{
                --primary:#7A00FF;
                --primary-light:#8b5cf6;
                --primary-dark:#5b21b6;
                --secondary:#06b6d4;
                --accent:#f59e0b;

                --light-bg:#eef0f6;
                --dark-bg:#071126;

                --text-dark:#111827;
                --text-light:#64748b;
                --white:#ffffff;

                --card:#ffffff;
                --border:#e5e7eb;
            }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            color: var(--text-dark);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Scroll Progress Indicator */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
            z-index: 1001;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        /* Notification Bar */
       .notification-bar{
    position:absolute;

    left:0;
    width:100%;

    background:rgba(0,0,0,.25);
    backdrop-filter:blur(10px);

    color:#fff;

    padding:12px 0;

    overflow:hidden;

    font-size:.95rem;

    z-index:1050;

    border-bottom:1px solid rgba(255,255,255,.12);
}


        .notification-content {
            display: flex;
            animation: slide 20s linear infinite;
            white-space: nowrap;
            gap: 40px;
        }

        .notification-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notification-item i {
            color: rgba(255, 255, 255, 0.8);
        }

        @keyframes slide {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Navbar */
        .navbar{
                background:rgba(255,255,255,.95) !important;
                backdrop-filter:blur(10px);
                box-shadow:0 2px 20px rgba(0,0,0,.08);
                padding:20px 0px 10px;
                position:sticky;
                z-index:999;
                transition:all .3s ease;
            }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 8px;
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .btn-primary, .btn-outline-primary {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 24px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
            color: var(--white);
        }

        .btn-outline-primary {
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Hero Section */
         .hero {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            padding: 80px 0 60px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            z-index: 0;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            color: var(--text-dark);
            animation: slideUp 0.8s ease-out;
        }
        .hero h1 span {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero .subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 30px;
            line-height: 1.8;
            animation: slideUp 0.8s ease-out 0.1s both;
        }

        .hero .cta-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .hero-visual {
            position: relative;
            margin-top: 40px;
            animation: slideUp 0.8s ease-out 0.3s both;
        }

            /* LANDING HERO BUTTON FIX */

.hero .btn-outline-primary{
    background:linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    border:2px solid rgba(255,255,255,.35) !important;
    color:#ffffff !important;
}

.hero .btn-outline-primary i{
    color:#ffffff !important;
}

.hero .btn-outline-primary:hover{
    background:#ffffff !important;
    color:#7A00FF !important;
    border-color:#ffffff !important;
}

.hero .btn-outline-primary:hover i{
    color:#7A00FF !important;
}

.hero .btn-primary{
    background:#7A00FF !important;
    color:#ffffff !important;
    border:2px solid #7A00FF !important;
}

.hero .btn-primary i{
    color:#ffffff !important;
}
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            z-index: 0;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            color: var(--text-dark);
            animation: slideUp 0.8s ease-out;
        }

        .hero h1 span {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero .subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 30px;
            line-height: 1.8;
            animation: slideUp 0.8s ease-out 0.1s both;
        }

        .hero .cta-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .hero-visual {
            position: relative;
            margin-top: 40px;
            animation: slideUp 0.8s ease-out 0.3s both;
        }

        .dashboard-mockup {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
        }

        .mockup-header {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .mockup-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .mockup-dot.red { background: #ef4444; }
        .mockup-dot.yellow { background: #f59e0b; }
        .mockup-dot.green { background: #10b981; }

        .mockup-content {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .mockup-card {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(6, 182, 212, 0.05) 100%);
            border-radius: 12px;
            padding: 15px;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .mockup-card-label {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .mockup-card-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        /* Company Showcase Section */
        .showcase {
            padding: 80px 0;
            background: var(--white);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 50px;
            color: var(--text-dark);
        }

        .section-title span {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .showcase-carousel {
            position: relative;
        }

        .showcase-item {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(6, 182, 212, 0.08) 100%);
            border-radius: 12px;
            overflow: hidden;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .showcase-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
            border-color: var(--primary);
        }

        .showcase-item i {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Trusted By Section */
        .trusted-by {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        }

        .logo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .logo-item {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 120px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .logo-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.15);
            border-color: var(--primary);
        }

        .logo-item img {
            max-width: 80%;
            height: auto;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .logo-item:hover img {
            opacity: 1;
        }

        /* About Section */
        .about {
            padding: 80px 0;
            background: var(--white);
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-text h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .about-text p {
            font-size: 1.05rem;
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .about-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .stat-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(6, 182, 212, 0.08) 100%);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(99, 102, 241, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: var(--text-light);
            margin-top: 10px;
            font-weight: 600;
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .feature-card {
            background: var(--white);
            padding: 40px 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(99, 102, 241, 0.1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(99, 102, 241, 0.2);
            border-color: var(--primary);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--white);
            font-size: 1.8rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .feature-description {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* ERP Diagram Section */
        .erp-diagram {
            padding: 80px 0;
            background: var(--white);
        }

        .diagram-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            border-radius: 16px;
            padding: 60px 40px;
            border: 1px solid rgba(99, 102, 241, 0.2);
            position: relative;
            overflow: hidden;
        }

        .diagram-svg {
            width: 100%;
            height: auto;
            min-height: 500px;
        }

        /* Modules Section */
        .modules {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }

        .module-card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 2px solid rgba(99, 102, 241, 0.1);
            cursor: pointer;
        }

        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.2);
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(6, 182, 212, 0.05) 100%);
        }

        .module-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .module-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Testimonials Section */
        .testimonials {
            padding: 80px 0;
            background: var(--white);
        }

        .testimonials-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(6, 182, 212, 0.08) 100%);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid rgba(99, 102, 241, 0.1);
            position: relative;
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.15);
        }

        .testimonial-stars {
            color: var(--warning);
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .testimonial-text {
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 20px;
            line-height: 1.8;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .testimonial-info h4 {
            margin: 0;
            color: var(--text-dark);
            font-weight: 700;
        }

        .testimonial-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* FAQ Section */
        .faq {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background: var(--white);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.15);
            border-color: var(--primary);
        }

        .faq-question {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-dark);
            transition: all 0.3s ease;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .faq-question:hover {
            background: rgba(99, 102, 241, 0.05);
            color: var(--primary);
        }

        .faq-question i {
            transition: transform 0.3s ease;
            color: var(--primary);
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            display: none;
            padding: 0 20px 20px;
            color: var(--text-light);
            line-height: 1.8;
            background: rgba(99, 102, 241, 0.02);
        }

        .faq-item.active .faq-answer {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Contact Section */
        .contact {
            padding: 80px 0;
            background: var(--white);
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .contact-info h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        .contact-info-item {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .contact-info-item:hover {
            transform: translateX(10px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .contact-form {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(6, 182, 212, 0.08) 100%);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            background: var(--white);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 8px;
            padding: 12px 16px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            color: var(--text-dark);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background: var(--white);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Footer */
        footer {
            background: var(--text-dark);
            color: var(--white);
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h4 {
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section li {
            margin-bottom: 12px;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--white);
            transform: translateX(5px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }

        
.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-link {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

/* Facebook */
.facebook-link:hover {
    background: #1877F2;
    transform: translateY(-3px);
}

/* Twitter */
.twitter-link:hover {
    background: #000000;
    transform: translateY(-3px);
}

/* LinkedIn */
.linkedin-link:hover {
    background: #0A66C2;
    transform: translateY(-3px);
}

/* YouTube */
.youtube-link:hover {
    background: #FF0000;
    transform: translateY(-3px);
}

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 60px 0 40px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero .cta-buttons {
                flex-direction: column;
            }

            .hero .cta-buttons .btn {
                width: 100%;
            }

            .about-content,
            .contact-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .section-title {
                font-size: 2rem;
            }

            .mockup-content {
                grid-template-columns: 1fr;
            }

            .about-stats {
                grid-template-columns: 1fr;
            }

            nav ul {
                gap: 1rem;
            }

            .nav-link {
                margin: 0 4px;
            }

            .navbar-toggler {
                border-color: var(--primary);
            }

            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%236366f1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 9h24M6 15h24M6 21h24'/%3e%3c/svg%3e");
            }

            .back-to-top {
                width: 45px;
                height: 45px;
                bottom: 20px;
                right: 20px;
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .hero h1 {
                font-size: 1.5rem;
            }

            .hero .subtitle {
                font-size: 1rem;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .feature-card,
            .module-card,
            .testimonial-card {
                padding: 20px;
            }

            .contact-form {
                padding: 20px;
            }

            .logo-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .logo-item {
                height: 100px;
            }
        }

        .feature-card,
.module-card,
.testimonial-card,
.logo-item,
.dashboard-mockup,
.contact-form,
.faq-item,
.stat-box{
    border-radius:2px !important;
    border:1px solid #e5e7eb !important;
    box-shadow:0 8px 24px rgba(15,23,42,.08) !important;
}

.feature-icon,
.contact-icon{
    border-radius:2px !important;
    background:#7A00FF !important;
    box-shadow:0 12px 24px rgba(0,0,0,.18) !important;
}

html.dark-mode body{
    background:#071126 !important;
    color:#f9fafb !important;
}

html.dark-mode .navbar,
html.dark-mode .showcase,
html.dark-mode .about,
html.dark-mode .erp-diagram,
html.dark-mode .testimonials,
html.dark-mode .contact{
    background:#071126 !important;
}

html.dark-mode .feature-card,
html.dark-mode .module-card,
html.dark-mode .testimonial-card,
html.dark-mode .logo-item,
html.dark-mode .dashboard-mockup,
html.dark-mode .contact-form,
html.dark-mode .faq-item,
html.dark-mode .stat-box{
    background:#111c31 !important;
    border-color:#334155 !important;
}

html.dark-mode h1,
html.dark-mode h2,
html.dark-mode h3,
html.dark-mode h4,
html.dark-mode .section-title,
html.dark-mode .feature-title,
html.dark-mode .module-name,
html.dark-mode .testimonial-text,
html.dark-mode .faq-question,
html.dark-mode .form-group label{
    color:#fff !important;
}

html.dark-mode p,
html.dark-mode .subtitle,
html.dark-mode .feature-description,
html.dark-mode .stat-label,
html.dark-mode .faq-answer{
    color:#cbd5e1 !important;
}

html.dark-mode .notification-bar{
    background:linear-gradient(135deg,#7A00FF 0%,#5b21b6 100%) !important;
}

html.dark-mode .navbar{
    background:#071126 !important;
    border-bottom:1px solid #334155 !important;
}

html.dark-mode .nav-link,
html.dark-mode .navbar-brand{
    color:#fff !important;
    -webkit-text-fill-color:initial !important;
}

.theme-toggle{
    width:46px;
    height:46px;

    display:flex;
    align-items:center;
    justify-content:center;

    border:none;
    border-radius:50%;

    background:#f3f4f6;
    color:#111827;

    cursor:pointer;
    transition:.25s ease;
}

.theme-toggle:hover{
    background:#7A00FF;
    color:#fff;
    transform:translateY(-2px);
}

html.dark-mode .theme-toggle{
    background:#1e293b;
    color:#fff;
}

html.dark-mode .theme-toggle:hover{
    background:#7A00FF;
}
html.dark-mode body,
html.dark-mode .showcase,
html.dark-mode .trusted-by,
html.dark-mode .about,
html.dark-mode .features,
html.dark-mode .modules,
html.dark-mode .erp-diagram,
html.dark-mode .testimonials,
html.dark-mode .faq,html.dark-mode .hero,
html.dark-mode .contact{
    background:#071126 !important;
}

html.dark-mode .navbar{
    background:#071126 !important;
    border-bottom:1px solid #334155 !important;
}

html.dark-mode .feature-card,
html.dark-mode .module-card,
html.dark-mode .testimonial-card,
html.dark-mode .logo-item,
html.dark-mode .dashboard-mockup,
html.dark-mode .mockup-card,
html.dark-mode .contact-form,
html.dark-mode .faq-item,
html.dark-mode .stat-box,
html.dark-mode .diagram-container,
html.dark-mode .showcase-item{
    background:#111c31 !important;
    border-color:#334155 !important;
}

html.dark-mode h1,
html.dark-mode h2,
html.dark-mode h3,
html.dark-mode h4,
html.dark-mode .section-title,
html.dark-mode .feature-title,
html.dark-mode .module-name,
html.dark-mode .testimonial-text,
html.dark-mode .faq-question,
html.dark-mode .form-group label,
html.dark-mode .nav-link,
html.dark-mode .auth-logo h1,
html.dark-mode .auth-logo p{
    color:#fff !important;
    -webkit-text-fill-color:initial !important;
}

html.dark-mode p,
html.dark-mode .feature-description,
html.dark-mode .stat-label,
html.dark-mode .faq-answer,
html.dark-mode .mockup-card-label,
html.dark-mode .testimonial-info p{
    color:#cbd5e1 !important;
}

.brand-logo{
    display:flex;
    align-items:center;
    gap:16px;
    text-decoration:none;
}

.brand-icon{
    width:54px;
    height:54px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:#7A00FF;
    color:#fff;

    border-radius:2px;

    font-size:22px;

    box-shadow:0 10px 25px rgba(122,0,255,.25);
}

.brand-text{
    display:flex;
    flex-direction:column;
    line-height:1;
}

.brand-text h2{
    margin:0;
    font-size:2rem;
    font-weight:700;
    color:#111827;
}

.brand-text span{
    margin-top:8px;

    font-size:.72rem;

    font-weight:700;

    letter-spacing:3px;

    color:#6b7280;
}

.hero-slider{
    position:relative;
    height:620px;
    overflow:hidden;
}

.hero-slide{
    position:absolute;
    inset:0;
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    opacity:1;
    visibility:visible;

    transition:opacity .3s ease;
    transform:translateX(-100%);

    backface-visibility:hidden;
}

.hero-slide.active{
    opacity:1;
     visibility:visible;
    transform:translateX(0);
    
}

.hero-slide.prev{
    transform:translateX(-100%);
}

.hero-overlay{
    position:absolute;
    inset:0;
    background:linear-gradient(90deg,rgba(0, 0, 6, 0.4),rgb(0, 0, 1, 0.2),rgba(0,0,0,.25));
}

.hero-slide-content{
    position:relative;
    z-index:2;
    max-width:850px;
    padding:150px 7%;
    color:#fff;
}

.hero-slide-content h1{
    font-size:52px;
    font-weight:800;
    line-height:1.15;
    color:#fff !important;
    margin-bottom:22px;
}

.hero-slide-content p{
    font-size:18px;
    line-height:1.8;
    color:#fff !important;
    max-width:850px;
}

.hero-buttons{
    display:flex;
    gap:18px;
    flex-wrap:wrap;
    margin-top:34px;
}

.hero-btn{
    padding:15px 34px;
    border-radius:4px;
    text-decoration:none;
    font-weight:700;
    transition:.25s;
}

.hero-btn.primary{
    background:#7A00FF;
    color:#fff;
}

.hero-btn.outline{
    border:1px solid rgba(255,255,255,.65);
    color:#fff;
}

.hero-btn:hover{
    transform:translateY(-3px);
}

.hero-arrow{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    z-index:5;
    width:56px;
    height:56px;
    border:none;
    border-radius:50%;
    background:rgba(0,0,0,.35);
    color:#fff;
    font-size:20px;
    cursor:pointer;
}

.hero-prev{ left:28px; }
.hero-next{ right:28px; }

.hero-dots{
    position:absolute;
    left:50%;
    bottom:32px;
    transform:translateX(-50%);
    z-index:5;
    display:flex;
    gap:10px;
}

.hero-dots button{
    width:14px;
    height:14px;
    border-radius:50%;
    border:none;
    background:rgba(255,255,255,.45);
}

.hero-dots button.active{
    background:#fff;
    outline:2px solid rgba(255,255,255,.7);
}

@media(max-width:768px){
    .hero-slider{
        height:560px;
    }

    .hero-slide-content{
        padding:120px 28px;
    }

    .hero-slide-content h1{
        font-size:34px;
    }

    .hero-arrow{
        width:44px;
        height:44px;
    }
}

.architecture-section {
    padding: 90px 5%;
    background:
        radial-gradient(circle at top left, rgba(111, 78, 255, 0.12), transparent 35%),
        #f8f9ff;
}

.architecture-heading {
    max-width: 720px;
    margin: 0 auto 45px;
    text-align: center;
}

.section-tag {
    display: inline-block;
    margin-bottom: 12px;
    color: #6547e7;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 1.8px;
}

.architecture-heading h2 {
    margin: 0 0 14px;
    color: #1f2440;
    font-size: clamp(30px, 4vw, 46px);
}

.architecture-heading p {
    margin: 0;
    color: #69708a;
    font-size: 17px;
    line-height: 1.7;
}

.architecture-container {
    max-width: 1150px;
    margin: auto;
}

.architecture-layer {
    position: relative;
    display: flex;
    gap: 25px;
    padding: 28px;
    border: 1px solid rgba(101, 71, 231, 0.15);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 12px 35px rgba(42, 34, 91, 0.08);
}

.layer-number {
    display: grid;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    place-items: center;
    border-radius: 14px;
    background: #6547e7;
    color: white;
    font-weight: 800;
}

.layer-content {
    flex: 1;
    min-width: 0;
}

.layer-content h3 {
    margin: 8px 0 20px;
    color: #242944;
    font-size: 20px;
}

.architecture-items {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
    gap: 14px;
}

.architecture-card {
    display: flex;
    min-height: 68px;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px;
    border-radius: 14px;
    background: #f1efff;
    color: #4935b5;
    font-weight: 700;
    text-align: center;
}

.architecture-card i {
    font-size: 19px;
}

.architecture-arrow {
    height: 48px;
    display: grid;
    place-items: center;
    color: #6547e7;
    font-size: 21px;
}

.core-layer .architecture-card {
    background: #eaf8ff;
    color: #087fa8;
}

.service-layer .architecture-card {
    background: #fff3e6;
    color: #bd6500;
}

.database-layer {
    border-color: rgba(12, 157, 123, 0.2);
}

.database-card {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 20px;
    border-radius: 15px;
    background: #e7faf4;
    color: #087b60;
}

.database-card i {
    font-size: 34px;
}

.database-card strong,
.database-card span {
    display: block;
}

.database-card span {
    margin-top: 5px;
    color: #58736c;
    font-size: 14px;
}

@media (max-width: 650px) {
    .architecture-section {
        padding: 65px 18px;
    }

    .architecture-layer {
        display: block;
        padding: 20px;
    }

    .layer-number {
        margin-bottom: 12px;
    }

    .architecture-items {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 420px) {
    .architecture-items {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress"></div>

    <!-- Navigation Bar -->
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light">

    <div class="container-fluid px-5">

        <!-- Logo -->
        <a href="index.php" class="brand-logo">
            <div class="brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>

            <div class="brand-text">
                <h2>MindMerge</h2>
                <span>SMARTCAMPUS</span>
            </div>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#modules">Modules</a></li>
                <li class="nav-item"><a class="nav-link" href="#architecture">ERP Diagram</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>

                <li class="nav-item ms-2">
                    <a href="./auth/login.php" class="btn btn-primary">Login</a>
                </li>

                <li class="nav-item ms-2">
                    <a href="./auth/register.php" class="btn btn-outline-primary">Register</a>
                </li>

                <li class="nav-item ms-2">
                    <button id="themeToggle" class="theme-toggle">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                </li>

            </ul>

        </div>

    </div>

</nav>

    <!-- Notification Bar -->
    <div class="notification-bar">
        <div class="notification-content">

           <div class="notification-item">
    <i class="fas fa-bullhorn"></i>
    <span>📢 Welcome to MindMerge SmartCampus ERP</span>
</div>

<div class="notification-item">
    <i class="fas fa-calendar-check"></i>
    <span>📅 Stay Updated with Timetables & Academic Events</span>
</div>

<div class="notification-item">
    <i class="fas fa-user-check"></i>
    <span>✅ Track Student Attendance in Real Time</span>
</div>

<div class="notification-item">
    <i class="fas fa-chart-line"></i>
    <span>📊 Monitor Academic Performance & Progress</span>
</div>

<div class="notification-item">
    <i class="fas fa-bell"></i>
    <span>🔔 Receive Instant School Notifications</span>
</div>

<div class="notification-item">
    <i class="fas fa-school"></i>
    <span>🏫 One Platform for Students, Teachers, Parents & Admin</span>
</div>

<div class="notification-item">
    <i class="fas fa-book-open"></i>
    <span>📚 Digital Learning & Assignment Management</span>
</div>

<div class="notification-item">
    <i class="fas fa-bus"></i>
    <span>🚌 Smart Transport & Bus Tracking System</span>
</div>

<div class="notification-item">
    <i class="fas fa-file-signature"></i>
    <span>📝 Online Examination & Result Management</span>
</div>

<div class="notification-item">
    <i class="fas fa-wallet"></i>
    <span>💳 Secure Online Fee Payment & Receipts</span>
</div>

<div class="notification-item">
    <i class="fas fa-shield-halved"></i>
    <span>🔒 Secure Role-Based Access & Data Protection</span>
</div>

<div class="notification-item">
    <i class="fas fa-brain"></i>
    <span>🤖 AI-Powered SmartCampus Experience</span>
</div>

            <div class="notification-item">
                <i class="fas fa-bell"></i>
                <span>✨ Admissions Open</span>
            </div>
            <div class="notification-item">
                <i class="fas fa-robot"></i>
                <span>🤖 AI Powered SmartCampus ERP</span>
            </div>
            <div class="notification-item">
                <i class="fas fa-graduation-cap"></i>
                <span>👨‍🎓 Student Portal</span>
            </div>
            <div class="notification-item">
                <i class="fas fa-chalkboard-user"></i>
                <span>👨‍🏫 Faculty Portal</span>
            </div>
            <div class="notification-item">
                <i class="fas fa-user"></i>
                <span>👨‍👩‍👧 Parent Portal</span>
            </div>
            <div class="notification-item">
                <i class="fas fa-credit-card"></i>
                <span>💳 Online Fee Payment</span>
            </div>
            <div class="notification-item">
                <i class="fas fa-clock"></i>
                <span>⏱️ Attendance Management</span>
            </div>
            <div class="notification-item">
                <i class="fas fa-file-alt"></i>
                <span>📋 Examination Portal</span>
            </div>

        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-slider" id="home">

    <div class="hero-slide" style="background-image: url('assets/images/hero-1.jpeg');" >
        <div class="hero-overlay"></div>
        <div class="hero-slide-content">
            <h1>MindMerge SmartCampus ERP</h1>
            <p>Manage academics, attendance, fees, exams, transport and communication from one smart platform.</p>
            <div class="hero-buttons">
                <a href="./auth/login.php" class="hero-btn primary">Get Started</a>
                <a href="#features" class="hero-btn outline">Explore Modules</a>
            </div>
        </div>
    </div>

    <div class="hero-slide active" style="background-image: url('assets/images/hero-2.jpeg');">
        <div class="hero-overlay"></div>
        <div class="hero-slide-content">
            <h1>Powerful School Management</h1>
            <p>Automate daily workflows and improve administration with a modern ERP dashboard.</p>
            <div class="hero-buttons">
                <a href="#contact" class="hero-btn primary">Request Demo</a>
                <a href="#modules" class="hero-btn outline">View Features</a>
            </div>
        </div>
    </div>

    <div class="hero-slide" style="background-image: url('assets/images/classroom.jpg');" >
        <div class="hero-overlay"></div>
        <div class="hero-slide-content">
            <h1>Connected Campus Experience</h1>
            <p>Bring students, teachers, parents and administrators together through secure portals.</p>
            <div class="hero-buttons">
                <a href="./auth/register.php" class="hero-btn primary">Register</a>
                <a href="#contact" class="hero-btn outline">Contact Us</a>
            </div>
        </div>
    </div>

    <button class="hero-arrow hero-prev" type="button">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <button class="hero-arrow hero-next" type="button">
        <i class="fa-solid fa-chevron-right"></i>
    </button>

    <div class="hero-dots">
        <button class="active" type="button"></button>
        <button type="button"></button>
        <button type="button"></button>
    </div>

</section>

    <!-- Company Showcase -->
    <section class="showcase">
        <div class="container-lg">
            <h2 class="section-title" data-aos="fade-up">
                Our <span>Capabilities</span>
            </h2>
            <div class="showcase-carousel" data-aos="fade-up" data-aos-delay="100">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="showcase-item" data-aos="zoom-in">
                            <i class="fas fa-building"></i>
                        </div>
                        <p class="text-center mt-3 fw-600">Office Environment</p>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="showcase-item" data-aos="zoom-in" data-aos-delay="100">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="text-center mt-3 fw-600">Team Collaboration</p>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="showcase-item" data-aos="zoom-in" data-aos-delay="200">
                            <i class="fas fa-code"></i>
                        </div>
                        <p class="text-center mt-3 fw-600">Development</p>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="showcase-item" data-aos="zoom-in" data-aos-delay="300">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <p class="text-center mt-3 fw-600">Awards</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By Section -->
    <section class="trusted-by">
        <div class="container-lg">
            <h2 class="section-title" data-aos="fade-up">
                Trusted By <span>Leading Institutions</span>
            </h2>
            <div class="logo-grid" data-aos="fade-up" data-aos-delay="100">
                <div class="logo-item">
                    <i class="fas fa-graduation-cap" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <div class="logo-item">
                    <i class="fas fa-school" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <div class="logo-item">
                    <i class="fas fa-book" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <div class="logo-item">
                    <i class="fas fa-university" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <div class="logo-item">
                    <i class="fas fa-laptop" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <div class="logo-item">
                    <i class="fas fa-chalkboard" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container-lg">
            <div class="about-content">
                <div>
                    <h2 data-aos="fade-up">
                        About <span style="color: var(--primary);">SmartCampus ERP</span>
                    </h2>
                    <p data-aos="fade-up" data-aos-delay="100">
                        MindMerge SmartCampus ERP is a comprehensive educational management system that brings digital transformation to educational institutions worldwide.
                    </p>
                    <p data-aos="fade-up" data-aos-delay="150">
                        Our AI-powered platform streamlines every aspect of institutional management, from student admissions and attendance to examination results and parent communication.
                    </p>
                    <p data-aos="fade-up" data-aos-delay="200">
                        With cloud-based infrastructure and advanced security measures, SmartCampus ensures your data is always protected while providing 24/7 accessibility.
                    </p>
                </div>
                <div class="about-stats">
                    <div class="stat-box" data-aos="zoom-in">
                        <div class="stat-number" data-count="100">0</div>
                        <div class="stat-label">+ Institutions</div>
                    </div>
                    <div class="stat-box" data-aos="zoom-in" data-aos-delay="100">
                        <div class="stat-number" data-count="25000">0</div>
                        <div class="stat-label">+ Students</div>
                    </div>
                    <div class="stat-box" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-number" data-count="1000">0</div>
                        <div class="stat-label">+ Faculty</div>
                    </div>
                    <div class="stat-box" data-aos="zoom-in" data-aos-delay="300">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Uptime</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container-lg">
            <h2 class="section-title" data-aos="fade-up">
                Powerful <span>Features</span>
            </h2>
            <div class="features-grid">
                <div class="feature-card" data-aos="zoom-in">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3 class="feature-title">Student Management</h3>
                    <p class="feature-description">Complete student information management with enrollment tracking</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="feature-icon"><i class="fas fa-chalkboard-user"></i></div>
                    <h3 class="feature-title">Faculty Management</h3>
                    <p class="feature-description">Efficient faculty scheduling, assignments, and performance tracking</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="feature-icon"><i class="fas fa-comments"></i></div>
                    <h3 class="feature-title">Parent Portal</h3>
                    <p class="feature-description">Real-time communication channel between parents and school</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <h3 class="feature-title">Attendance Management</h3>
                    <p class="feature-description">Automated attendance marking and analytics</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="400">
                    <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
                    <h3 class="feature-title">Assignments</h3>
                    <p class="feature-description">Digital assignment creation and submission tracking</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="500">
                    <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
                    <h3 class="feature-title">Fee Management</h3>
                    <p class="feature-description">Online fee collection and payment tracking</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="600">
                    <div class="feature-icon"><i class="fas fa-briefcase"></i></div>
                    <h3 class="feature-title">Payroll Management</h3>
                    <p class="feature-description">Automated salary processing and tax calculations</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="700">
                    <div class="feature-icon"><i class="fas fa-book"></i></div>
                    <h3 class="feature-title">Library Module</h3>
                    <p class="feature-description">Digital library management and book tracking</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="800">
                    <div class="feature-icon"><i class="fas fa-bus"></i></div>
                    <h3 class="feature-title">Transport Management</h3>
                    <p class="feature-description">Route optimization and student tracking</p>
                </div>

                <div class="feature-card" data-aos="zoom-in" data-aos-delay="1000">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="feature-title">AI Analytics</h3>
                    <p class="feature-description">Advanced analytics and predictive insights</p>
                </div>
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="1100">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="feature-title">Security</h3>
                    <p class="feature-description">Enterprise-grade security and data protection</p>
                </div>
            </div>
        </div>
    </section>

    <section class="architecture-section" id="architecture">
    <div class="architecture-heading">
        <span class="section-tag">SYSTEM DESIGN</span>
        <h2>SmartCampus Architecture</h2>
        <p>
            A centralized, role-based architecture connecting users,
            academic modules and institutional data.
        </p>
    </div>

    <div class="architecture-container">

        <div class="architecture-layer">
            <span class="layer-number">01</span>

            <div class="layer-content">
                <h3>System Users</h3>

                <div class="architecture-items">
                    <div class="architecture-card">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin</span>
                    </div>

                    <div class="architecture-card">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>Teacher</span>
                    </div>

                    <div class="architecture-card">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Student</span>
                    </div>

                    <div class="architecture-card">
                        <i class="fa-solid fa-people-roof"></i>
                        <span>Parent</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="architecture-arrow">
            <i class="fa-solid fa-arrow-down"></i>
        </div>

        <div class="architecture-layer">
            <span class="layer-number">02</span>

            <div class="layer-content">
                <h3>Role-Based Portals</h3>

                <div class="architecture-items">
                    <div class="architecture-card">Admin Dashboard</div>
                    <div class="architecture-card">Teacher Portal</div>
                    <div class="architecture-card">Student Portal</div>
                    <div class="architecture-card">Parent Portal</div>
                </div>
            </div>
        </div>

        <div class="architecture-arrow">
            <i class="fa-solid fa-arrow-down"></i>
        </div>

        <div class="architecture-layer core-layer">
            <span class="layer-number">03</span>

            <div class="layer-content">
                <h3>Core Academic Modules</h3>

                <div class="architecture-items">
                    <div class="architecture-card">Attendance</div>
                    <div class="architecture-card">Examinations</div>
                    <div class="architecture-card">Results</div>
                    <div class="architecture-card">Notifications</div>
                    <div class="architecture-card">Timetable</div>
                </div>
            </div>
        </div>

        <div class="architecture-arrow">
            <i class="fa-solid fa-arrow-down"></i>
        </div>

        <div class="architecture-layer service-layer">
            <span class="layer-number">04</span>

            <div class="layer-content">
                <h3>Application Layer</h3>

                <div class="architecture-items">
                    <div class="architecture-card">PHP Services</div>
                    <div class="architecture-card">Authentication</div>
                    <div class="architecture-card">Authorization</div>
                    <div class="architecture-card">Data Validation</div>
                </div>
            </div>
        </div>

        <div class="architecture-arrow">
            <i class="fa-solid fa-arrow-down"></i>
        </div>

        <div class="architecture-layer database-layer">
            <span class="layer-number">05</span>

            <div class="layer-content">
                <h3>Centralized Data Layer</h3>

                <div class="database-card">
                    <i class="fa-solid fa-database"></i>

                    <div>
                        <strong>MySQL Database</strong>
                        <span>Secure storage of institutional and academic data</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
    <!-- Modules Section -->
    <section class="modules" id="modules">
        <div class="container-lg">
            <h2 class="section-title" data-aos="fade-up">
                Core <span>Modules</span>
            </h2>
            <div class="modules-grid">
                <div class="module-card" data-aos="zoom-in"><i class="fas fa-graduation-cap module-icon"></i><p class="module-name">Student Portal</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="50"><i class="fas fa-chalkboard-user module-icon"></i><p class="module-name">Teacher Portal</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="100"><i class="fas fa-user-tie module-icon"></i><p class="module-name">Parent Portal</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="150"><i class="fas fa-tachometer-alt module-icon"></i><p class="module-name">Admin Dashboard</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="200"><i class="fas fa-user-secret module-icon"></i><p class="module-name">Principal Dashboard</p></div>

                <div class="module-card" data-aos="zoom-in" data-aos-delay="250"><i class="fas fa-briefcase module-icon"></i><p class="module-name">HR Management</p></div>

                <div class="module-card" data-aos="zoom-in" data-aos-delay="300"><i class="fas fa-money-bill-wave module-icon"></i><p class="module-name">Finance Module</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="350"><i class="fas fa-file-alt module-icon"></i><p class="module-name">Examination</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="400"><i class="fas fa-book module-icon"></i><p class="module-name">Library</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="450"><i class="fas fa-bus module-icon"></i><p class="module-name">Transport</p></div>
                <div class="module-card" data-aos="zoom-in" data-aos-delay="550"><i class="fas fa-robot module-icon"></i><p class="module-name">AI Analytics</p></div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container-lg">
            <h2 class="section-title" data-aos="fade-up">
                What Our Users <span>Say</span>
            </h2>
            <div class="testimonials-container">
                <div class="testimonial-card" data-aos="fade-up">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"SmartCampus has completely transformed how we manage our institution. The system is intuitive, reliable, and has saved us countless hours of administrative work."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">JP</div>
                        <div class="testimonial-info">
                            <h4>John Principal</h4>
                            <p>Principal, ABC School</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
        <div class="container-lg">
            <h2 class="section-title" data-aos="fade-up">
                Frequently Asked <span>Questions</span>
            </h2>
            <div class="faq-container" data-aos="fade-up" data-aos-delay="100">
                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>What is MindMerge SmartCampus ERP?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        MindMerge SmartCampus ERP is a comprehensive cloud-based school and college management system that streamlines administrative tasks, automates academic workflows, and improves communication between all stakeholders.
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>How secure is my data?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        We employ enterprise-grade security with 256-bit encryption, regular backups, and compliance with international data protection standards. Your data is your priority.
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>Can we customize the system for our institution?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        Yes! SmartCampus is highly customizable. We can tailor the system to match your institution's specific requirements and workflows.
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>What kind of support do you provide?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        We offer 24/7 technical support, regular training sessions, comprehensive documentation, and dedicated account management for all our clients.
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFAQ(this)">
                        <span>What is your uptime guarantee?</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        We guarantee 99.9% uptime with redundant systems, automatic failover, and geographic data replication to ensure your institution is always online.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container-lg">
            <h2 class="section-title" data-aos="fade-up">
                Get In <span>Touch</span>
            </h2>
            <div class="contact-container" data-aos="fade-up" data-aos-delay="100">
                <div>
                    <h3>Contact Information</h3>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <p style="margin: 0; font-weight: 600;">Phone</p>
                            <p style="margin: 0; color: var(--text-light);">+91 9966500016</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <p style="margin: 0; font-weight: 600;">Email</p>
                            <p style="margin: 0; color: var(--text-light);">admin@mmstinfo.com</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <p style="margin: 0; font-weight: 600;">Address</p>
                            <p style="margin: 0; color: var(--text-light);">Hyderabad & Bengaluru</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <p style="margin: 0; font-weight: 600;">Business Hours</p>
                            <p style="margin: 0; color: var(--text-light);">Mon - Fri: 10:00 AM - 06:00 PM</p>
                        </div>
                    </div>
                </div>
                <form class="contact-form" method="POST" action="index.php#contact">
                    <?php if ($contactStatus === 'sent') { ?>
                        <div class="alert alert-success" role="alert">
                            Thank you for your message! We will contact you soon.
                        </div>
                    <?php } ?>

                    <?php if ($contactError !== '') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($contactError); ?>
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" placeholder="Your message here..." required></textarea>
                    </div>
                    <button type="submit" name="contact_submit" value="1" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container-lg">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>MindMerge SmartCampus</h4>
                    <p>A leading IT solutions provider offering innovative software development, cloud services, and digital transformation solutions.</p>
                    
<div class="social-links">
    <a href="#" class="social-link facebook-link">
        <i class="fab fa-facebook-f"></i>
    </a>

    <a href="#" class="social-link twitter-link">
        <i class="fab fa-twitter"></i>
    </a>

    <a href="#" class="social-link linkedin-link">
        <i class="fab fa-linkedin-in"></i>
    </a>

    <a href="#" class="social-link youtube-link">
        <i class="fab fa-youtube"></i>
    </a>
</div>

                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Web Development</a></li>
                        <li><a href="#">App Development</a></li>
                        <li><a href="#">Cloud Computing</a></li>
                        <li><a href="#">Cyber Security</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>ERP Modules</h4>
                    <ul>
                        <li><a href="#">Student Portal</a></li>
                        <li><a href="#">Faculty Portal</a></li>
                        <li><a href="#">Parent Portal</a></li>
                        <li><a href="#">Admin Dashboard</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Career</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Mind Merge Soft Tech Private Limited. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" onclick="scrollToTop()"><i class="fas fa-arrow-up"></i></button>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.0/swiper-bundle.min.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out',
            once: true,
            offset: 100
        });

        // Scroll Progress Bar
        window.addEventListener('scroll', () => {
            const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            document.querySelector('.scroll-progress').style.transform = `scaleX(${scrolled / 100})`;
            
            // Toggle back to top button
            const backToTop = document.querySelector('.back-to-top');
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }

            // Navbar scrolled effect
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll to Top
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Toggle FAQ
        function toggleFAQ(element) {
            const item = element.parentElement;
            const isActive = item.classList.contains('active');
            
            document.querySelectorAll('.faq-item').forEach(el => {
                el.classList.remove('active');
            });
            
            if (!isActive) {
                item.classList.add('active');
            }
        }

        // Counter Animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const number = entry.target.getAttribute('data-count');
                    if (number) {
                        animateCounter(entry.target, parseInt(number));
                        observer.unobserve(entry.target);
                    }
                }
            });
        }, observerOptions);

        document.querySelectorAll('[data-count]').forEach(el => {
            observer.observe(el);
        });

        function animateCounter(element, targetNumber) {
            let currentNumber = 0;
            const step = Math.ceil(targetNumber / 50);
            const interval = setInterval(() => {
                currentNumber += step;
                if (currentNumber >= targetNumber) {
                    currentNumber = targetNumber;
                    clearInterval(interval);
                }
                element.textContent = currentNumber.toLocaleString();
            }, 30);
        }

        // Active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Smooth scroll for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    document.querySelectorAll('.navbar-collapse').forEach(collapse => {
                        collapse.classList.remove('show');
                    });
                }
            });
        });
    </script>

    <script>
const themeToggle = document.getElementById("themeToggle");
const html = document.documentElement;
const icon = themeToggle.querySelector("i");

function applyTheme(theme){
    if(theme === "dark"){
        html.classList.add("dark-mode");
        icon.className = "fa-solid fa-sun";
    }else{
        html.classList.remove("dark-mode");
        icon.className = "fa-solid fa-moon";
    }
}

const savedTheme = localStorage.getItem("mindmerge-theme") || "light";
applyTheme(savedTheme);

themeToggle.addEventListener("click", () => {
    const nextTheme = html.classList.contains("dark-mode") ? "light" : "dark";
    localStorage.setItem("mindmerge-theme", nextTheme);
    applyTheme(nextTheme);
});
</script>

<script>
const slides = document.querySelectorAll(".hero-slide");
const dots = document.querySelectorAll(".hero-dots button");
const nextBtn = document.querySelector(".hero-next");
const prevBtn = document.querySelector(".hero-prev");

let currentSlide = 0;

function showSlide(index){
    slides.forEach((slide, i) => {
        slide.classList.remove("active", "prev");

        if(i === index){
            slide.classList.add("active");
        }else if(i < index){
            slide.classList.add("prev");
        }
    });

    dots.forEach(dot => dot.classList.remove("active"));
    dots[index].classList.add("active");

    currentSlide = index;
}

function nextSlide(){
    showSlide((currentSlide + 1) % slides.length);
}

function prevSlide(){
    showSlide((currentSlide - 1 + slides.length) % slides.length);
}

nextBtn.addEventListener("click", nextSlide);
prevBtn.addEventListener("click", prevSlide);

dots.forEach((dot, index) => {
    dot.addEventListener("click", () => showSlide(index));
});

setInterval(nextSlide, 5000);
</script>

</body>
</html>
