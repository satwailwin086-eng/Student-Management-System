<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../unauthorized.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Top Navbar Style */
        .top-navbar {
            height: 60px;
            background-color: #3b5998;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        /* Sidebar Style */
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            overflow-y: auto;
            z-index: 1020;
        }
        .sidebar .menu-category {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            padding: 10px 15px 5px 15px;
            font-weight: bold;
        }
        .sidebar a {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            color: #333;
            text-decoration: none;
            transition: 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #eaedf1;
            color: #3b5998;
            font-weight: 500;
        }
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        /* Main Content Area */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 25px;
        }
        /* Dashboard Card Boxes */
        .stat-card {
            background: white;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-left: 4px solid #3b5998;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

    <!-- TOP NAVBAR -->
    <nav class="top-navbar d-flex align-items-center px-4 justify-content-between">
        <div class="d-flex align-items-center">
            <h5 class="m-0 fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i>  Student Information and Academic Management System</h5>
        </div>
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <div class="sidebar py-3">
        <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>

        <div class="menu-category">Class & Course</div>
        <a href="manage_class.php"><i class="fa-solid fa-school"></i> Manage Class</a>
        <a href="manage_class_arms.php"><i class="fa-solid fa-layer-group"></i> Manage Section</a> <!-- ဤလင့်ခ်ကို ထည့်ပါ -->
        <a href="manage_course.php"><i class="fa-solid fa-book-open"></i> Manage Course</a>
        

        <div class="menu-category">Staff & Students</div>
        <a href="manage_teacher.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Teacher</a>
        <a href="manage_student.php"><i class="fa-solid fa-user-graduate"></i> Manage Student</a>
        <a href="manage_payfee.php"><i class="fa-solid fa-file-invoice-dollar"></i> Manage Payment</a>

        <div class="menu-category">Attendance</div>
        
        <a href="view_student_attendance.php"><i class="fa-solid fa-calendar-check"></i> View Monthly Student Attendance</a>

        <div class="menu-category">Exam Management</div>
        <a href="manage_academic_year.php"><i class="fa-solid fa-calendar-days"></i> Manage Academic Year</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Administrator Dashboard</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>

        <!-- DASHBOARD CARDS (Boxes များ အပြည့်အစုံ) -->
        <div class="row g-4">
            <!-- Students Box -->
            <div class="col-md-3">
                <div class="stat-card border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase fs-7">Students</h6>
                            <h3 class="fw-bold mb-0">15</h3>
                        </div>
                        <i class="fa-solid fa-user-graduate fs-1 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Teachers Box -->
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #198754 !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase fs-7">Teachers</h6>
                            <h3 class="fw-bold mb-0">6</h3>
                        </div>
                        <i class="fa-solid fa-chalkboard-user fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Classes Box -->
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #ffc107 !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase fs-7">Classes</h6>
                            <h3 class="fw-bold mb-0">10</h3>
                        </div>
                        <i class="fa-solid fa-school fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Departments Box -->
            

            <!-- Courses Box (အသစ်ထည့်သွင်းခြင်း) -->
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #6610f2 !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase fs-7">Courses</h6>
                            <h3 class="fw-bold mb-0">6</h3>
                        </div>
                        <i class="fa-solid fa-book-open fs-1 text-purple opacity-50" style="color: #6610f2;"></i>
                    </div>
                </div>
            </div>

            <!-- Generate Reports Box (အသစ်ထည့်သွင်းခြင်း) -->
           
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
