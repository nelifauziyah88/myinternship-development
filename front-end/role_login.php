<?php
error_reporting(0);
session_start();

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Select Login Role - MyInternship</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css?v=<?php echo time(); ?>" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo time(); ?>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .role-container {
            max-width: 420px;
            width: 100%;
        }

        .role-card {
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .role-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .role-card h2 {
            font-weight: 700;
            font-size: 26px;
            color: #333333;
            margin-bottom: 12px;
            text-align: center;
        }

        .role-card .subtitle {
            color: #555;
            font-size: 14px;
            text-align: center;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .role-btn {
            width: 100%;
            padding: 14px 22px;
            font-size: 14px;
            font-weight: 600;
            border: 2px solid;
            border-radius: 8px;
            margin-bottom: 18px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Student Button */
        .btn-student {
            background: white;
            color: #3b82f6;
            border: 1.5px solid rgba(37, 99, 235, 0.5);
        }

        .btn-student:hover {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        /* Lecturer Button */
        .btn-lecturer {
            background: white;
            color: #22c55e;
            border: 1.5px solid rgba(22, 163, 74, 0.5);
        }

        .btn-lecturer:hover {
            background: #16a34a;
            color: white;
            border-color: #16a34a;
        }

        /* Industry Partner Button */
        .btn-industry {
            background: white;
            color: #60a5fa;
            border: 1.5px solid rgba(90, 169, 255, 0.5);
        }

        .btn-industry:hover {
            background: #5aa9ff;
            color: white;
            border-color: #5aa9ff;
        }

        /* Admin Button */
        .btn-admin {
            background: white;
            color: #374151;
            border: 1.5px solid rgba(17, 24, 39, 0.5);
        }

        .btn-admin:hover {
            background: #111827;
            color: white;
            border-color: #111827;
        }

        /* Home Button */
        .btn-home {
            background: white;
            color: #6b7280;
            border: 1.5px solid rgba(107, 114, 128, 0.4);
            padding: 13px 22px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 25px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-home:hover {
            background: #6b7280;
            color: white;
            border-color: #6b7280;
            box-shadow: 0 3px 10px rgba(107, 114, 128, 0.3);
        }
    </style>
</head>

<body>
    <div class="role-container">
        <div class="role-card">
            <h2>Welcome to MyInternship</h2>
            <p class="subtitle">Hi! Before we continue, please choose your role below to access the system.</p>

            <h5 class="section-title">Select your login role :</h5>

            <!-- Student -->
            <form action="login_student.php" method="get">
                <button type="submit" class="role-btn btn-student">
                    I'm a Student
                </button>
            </form>

            <p style="text-align:center; margin-top:8px; font-size:14px; color:#555;">
                or <a href="registrasi.php" style="color:#777; font-weight:600; text-decoration:none;">
                    Register as Student
                </a>
            </p>

            <!-- Lecturer -->
            <form action="login_lecturer.php" method="get">
                <button type="submit" class="role-btn btn-lecturer">
                    I'm a Lecturer
                </button>
            </form>

            <!-- Industry Partner -->
            <form action="login_company.php" method="get">
                <button type="submit" class="role-btn btn-industry">
                    I'm an Industry Partner
                </button>
            </form>

            <!-- CDC Admin -->
            <form action="login_cdcadmin.php" method="get">
                <button type="submit" class="role-btn btn-admin">
                    I'm a CDC Administrator
                </button>
            </form>

            <!-- Home -->
            <form action="landing_page.php" method="get">
                <button type="submit" class="btn-home">
                    <i class="fas fa-home"></i>
                    Go to Home
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js?v=<?php echo time(); ?>"></script>
</body>

</html>