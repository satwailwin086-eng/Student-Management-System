<?php
session_start();
include '../db.php';

// Student ဟုတ်မဟုတ် စစ်ဆေးခြင်း (အကယ်၍ student role စစ်ဆေးလိုပါက ဖွင့်သုံးနိုင်ပါသည်)
/*
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../unauthorized.php");
    exit();
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-navbar { height: 60px; background-color: #3b5998; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1030; }
        .sidebar { width: 250px; background-color: #ffffff; position: fixed; top: 60px; bottom: 0; left: 0; box-shadow: 2px 0 5px rgba(0,0,0,0.05); overflow-y: auto; z-index: 1020; }
        .sidebar .menu-category { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; padding: 15px 15px 5px 15px; font-weight: bold; }
        .sidebar a { padding: 10px 15px; display: flex; align-items: center; color: #333; text-decoration: none; transition: 0.2s; }
        .sidebar a:hover, .sidebar a.active { background-color: #eaedf1; color: #3b5998; font-weight: 500; border-left: 3px solid #3b5998; }
        .sidebar a i { margin-right: 10px; width: 20px; text-align: center; }
        .main-content { margin-left: 250px; margin-top: 60px; padding: 25px; }
        .card-box { background: white; border-radius: 6px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-bottom: 25px; border-top: 3px solid #3b5998; }
        .card-header-custom { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; font-weight: 600; color: #3b5998; font-size: 1.1rem; }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="top-navbar d-flex align-items-center px-4 justify-content-between">
        <h5 class="m-0 fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i> Student Information and Academic Management System</h5>
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Student'); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar (Admin ပုံစံတူ စီစဉ်ထားခြင်း) -->
    <div class="sidebar py-2">
        <a href="student_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        
        <div class="menu-category">Academics</div>
        <a href="view_course.php"><i class="fa-solid fa-book-open"></i> View Course</a>
        <a href="view_exam_date.php"><i class="fa-solid fa-calendar-days"></i> View Exam Date</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Student Dashboard</h3>
        </div>

        <div class="row">
            <!-- Quick Link / Welcome Box -->
            <div class="col-md-12">
                <div class="card-box p-4">
                    <h4 class="text-primary fw-bold mb-3">Welcome to Student Portal</h4>
                    <p class="text-muted">You can access courses and exam dates from here</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="view_course.php" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">
                            <i class="fa-solid fa-book-open me-2"></i> View Courses
                        </a>
                        <a href="view_exam_date.php" class="btn btn-outline-primary px-4">
                            <i class="fa-solid fa-calendar-days me-2"></i> View Exam Dates
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>