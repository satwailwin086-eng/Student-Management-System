<?php
session_start();
include '../db.php';

// Student ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    // လိုအပ်ပါက login page သို့ ပို့ရန်
}

// Latest Academic Year / Exam Date ကို Database မှ ဆွဲထုတ်ရန် (Admin ဘက်က table နာမည်နဲ့ ညီအောင် academic_years သို့မဟုတ် exam_dates ကို စစ်သုံးပါ)
$latest_exam = $conn->query("SELECT * FROM academic_years ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Exam Date - Student Portal</title>
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
        .main-content { margin-left: 250px; margin-top: 60px; padding: 30px; }
        
        /* Exam Date Banner အကြီးစား ဒီဇိုင်း */
        .exam-banner { background: linear-gradient(135deg, #3b5998, #4e73df); color: white; border-radius: 12px; padding: 40px 30px; margin-top: 20px; box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
        .exam-date-big { font-size: 2.2rem; font-weight: 800; letter-spacing: 1px; color: #ffeb3b; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
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

    <!-- Sidebar -->
    <div class="sidebar py-2">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <div class="menu-category">Academics</div>
        <a href="view_course.php"><i class="fa-solid fa-book"></i> View Course</a>
        <a href="view_exam_date.php" class="active"><i class="fa-regular fa-calendar-days"></i> View Exam Date</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h3 class="fw-bold text-secondary mb-4">Exam Date Announcement</h3>

        <?php if ($latest_exam): ?>
            <div class="exam-banner text-center">
                <h5 class="text-uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-bullhorn me-2"></i> LATEST ACADEMIC YEAR ANNOUNCEMENT
                </h5>

                <h3 class="fw-bold mb-4"><?php echo htmlspecialchars($latest_exam['academic_year'] ?? ''); ?> Academic Year</h3>
                <!-- Class Name (အပေါ်မှာပြရန်) -->
                <h4 class="text-light mb-2"><?php echo htmlspecialchars($latest_exam['class_name'] ?? ''); ?></h4>
                
                <!-- Academic Year (အောက်မှာပြရန်) -->
                

                <!-- Dates -->
                <div class="exam-date-big my-3">
                    <i class="fa-solid fa-calendar-days me-2"></i>
                    <?php echo date("d M Y", strtotime($latest_exam['start_date'])); ?> 
                    <span class="text-white px-2">to</span> 
                    <?php echo date("d M Y", strtotime($latest_exam['end_date'])); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-4 fs-5">
                <i class="fa-solid fa-circle-info me-2"></i> No academic year or exam dates announced yet.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>