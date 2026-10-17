<?php
session_start();
include '../db.php';

// Teacher ဟုတ်မဟုတ် စစ်ဆေးခြင်း (Role အပေါ်မူတည်၍ ပြင်ဆင်နိုင်ပါသည်)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../unauthorized.php");
    exit();
}

$teacher_name = $_SESSION['username'] ?? 'Teacher';

// ဥပမာ - စာရင်းဇယားအတွက် အချက်အလက်များ ထုတ်ယူရန် (လိုအပ်ပါက ချိန်ညှိနိုင်သည်)
// $students_count = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Student Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .top-navbar {
            height: 60px;
            background-color: #3b5998;
            color: white;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
        }
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            position: fixed;
            top: 60px; bottom: 0; left: 0;
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
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 25px;
        }
        /* Dashboard Card Styles (ပုံပါ ပုံစံတူ) */
        .dash-card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 20px;
            position: relative;
            margin-bottom: 20px;
            border-left: 4px solid #3b5998;
        }
        .dash-card.green { border-left-color: #198754; }
        .dash-card.warning { border-left-color: #ffc107; }
        .dash-card h6 {
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .dash-card h3 {
            font-weight: bold;
            color: #333;
            margin-top: 5px;
            margin-bottom: 0;
        }
        .dash-card .card-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2.5rem;
            opacity: 0.2;
        }
    </style>
</head>
<body>

    <!-- TOP NAVBAR -->
    <nav class="top-navbar d-flex align-items-center px-4 justify-content-between">
        <h5 class="m-0 fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i> Student Information and Academic Management System</h5>
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, <?php echo htmlspecialchars($teacher_name); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR (Teacher Menu) -->
    <div class="sidebar py-3">
        <a href="teacher_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>

        <div class="menu-category">Students Management</div>
        <a href="view_student_list.php"><i class="fa-solid fa-users"></i> View Student List</a>

        <div class="menu-category">Attendance</div>
        <a href="manage_student_attendance.php"><i class="fa-solid fa-calendar-check"></i> Manage Monthly Student Attendance</a>

        <div class="menu-category">Academics</div>
        <a href="upload_course.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Course</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Teacher Dashboard</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>

        <!-- Dashboard Widgets Row -->
        <div class="row">
            <!-- View Student List Card -->
            <div class="col-md-4">
                <div class="dash-card">
                    <h6>Students</h6>
                    <h3>View List</h3>
                    <i class="fa-solid fa-user-graduate card-icon text-primary"></i>
                    <div class="mt-3">
                        <a href="view_student_list.php" class="text-decoration-none fw-semibold small">View Details <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <!-- Manage Attendance Card -->
            <div class="col-md-4">
                <div class="dash-card green">
                    <h6>Attendance</h6>
                    <h3>Manage</h3>
                    <i class="fa-solid fa-calendar-check card-icon text-success"></i>
                    <div class="mt-3">
                        <a href="manage_student_attendance.php" class="text-decoration-none fw-semibold small text-success">Take / Edit Attendance <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <!-- Input Grade Marks Card -->
            
        </div>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>