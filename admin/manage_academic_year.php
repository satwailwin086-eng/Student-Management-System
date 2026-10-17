<?php
session_start();
include '../db.php';

// Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // လိုအပ်ပါက unauthorized သို့ ပို့ရန်
}

$success_msg = "";
$error_msg = "";

// Database ထဲတွင် academic_years ဇယား မရှိသေးပါက အလိုအလျောက် ဖန်တီးပေးခြင်း (Academic Year နှင့် Class Name ပါဝင်သည်)
$conn->query("CREATE TABLE IF NOT EXISTS academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year VARCHAR(100) NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 1. Academic Year အသစ်ထည့်ခြင်း (Add Academic Year)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_academic_year'])) {
    $academic_year = trim($_POST['academic_year']);
    $class_name = trim($_POST['class_name']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (!empty($academic_year) && !empty($class_name) && !empty($start_date) && !empty($end_date)) {
        $stmt = $conn->prepare("INSERT INTO academic_years (academic_year, class_name, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $academic_year, $class_name, $start_date, $end_date);
        if ($stmt->execute()) {
            $success_msg = "Academic year announcement added successfully!";
        } else {
            $error_msg = "Error adding academic year announcement!";
        }
        $stmt->close();
    } else {
        $error_msg = "Academic Year, Class Name, Start Date and End Date are required!";
    }
}

// 2. Delete Academic Year
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM academic_years WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_academic_year.php");
        exit();
    }
}

// Fetch All Academic Years
$exams_result = $conn->query("SELECT * FROM academic_years ORDER BY id ASC");
$latest_exam = $conn->query("SELECT * FROM academic_years ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Academic Year - Admin</title>
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
        
        /* အပေါ်ဆုံးတွင်ပြမည့် Date ကြီးအတွက် ဒီဇိုင်း */
        .exam-banner { background: linear-gradient(135deg, #3b5998, #4e73df); color: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .exam-date-big { font-size: 2.2rem; font-weight: 800; letter-spacing: 1px; color: #ffeb3b; text-shadow: 1px 1px 3px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="top-navbar d-flex align-items-center px-4 justify-content-between">
        <h5 class="m-0 fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i> Student Information and Academic Management System</h5>
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar py-2">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <div class="menu-category">Class & Course</div>
        <a href="manage_class.php"><i class="fa-solid fa-school"></i> Manage Class</a>
        <a href="manage_class_arms.php"><i class="fa-solid fa-layer-group"></i> Manage Section</a>
        <a href="manage_course.php"><i class="fa-solid fa-book"></i> Manage Course</a>
        <div class="menu-category">Staff & Students</div>
        <a href="manage_teacher.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Teacher</a>
        <a href="manage_student.php"><i class="fa-solid fa-user-graduate"></i> Manage Student</a>
        <a href="manage_pay_fee.php"><i class="fa-solid fa-file-invoice-dollar"></i> Manage Payment</a>
        <div class="menu-category">Attendance</div>
        <a href="view_student_attendance.php"><i class="fa-regular fa-calendar-check"></i> View Monthly Student Attendance</a>
        <div class="menu-category">Academic Management</div>
        <a href="manage_academic_year.php" class="active"><i class="fa-solid fa-calendar-days"></i>Manage Academic Year</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Manage Academic Year</h3>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- အပေါ်ဆုံးတွင် Banner အပိုင်း (Academic Year နှင့် Class Name ကို ဖော်ပြရန်) -->
        <?php if ($latest_exam): ?>
            <div class="exam-banner text-center">
                <h5 class="text-uppercase tracking-wider mb-2"><i class="fa-solid fa-bullhorn me-2"></i> Latest Academic Year Announcement</h5>
                <h3 class="fw-bold mb-2"><?php echo htmlspecialchars($latest_exam['academic_year']); ?> Academic Year</h3>
                <h4 class="text-light mb-3"><?php echo htmlspecialchars($latest_exam['class_name']); ?></h4>
                <div class="exam-date-big my-2">
                    <i class="fa-regular fa-calendar-days me-2"></i>
                    <?php echo date("d M Y", strtotime($latest_exam['start_date'])); ?> 
                    <span class="text-white px-2">to</span> 
                    <?php echo date("d M Y", strtotime($latest_exam['end_date'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Add Academic Year Form Box (Textbox ၂ ခု ပါဝင်သည်) -->
        <div class="card-box mb-4">
            <div class="card-header-custom">Announce New Academic Year</div>
            <div class="p-4">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Academic Year *</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2025-2026" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Class Name *</label>
                            <input type="text" name="class_name" class="form-control" placeholder="e.g. Semester" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">End Date *</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="add_academic_year" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">
                        <i class="fa-solid fa-paper-plane me-1"></i> Publish Announcement
                    </button>
                </form>
            </div>
        </div>

        <!-- Academic Years List Table -->
        <div class="card-box">
            <div class="card-header-custom">All Academic Years List</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Academic Year</th>
                                <th>Class Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th width="8%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($exams_result && $exams_result->num_rows > 0): ?>
                                <?php $count = 1; while($row = $exams_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['academic_year']); ?></strong></td>
                                        <td><strong><?php echo htmlspecialchars($row['class_name']); ?></strong></td>
                                        <td><span class="text-success fw-semibold"><i class="fa-regular fa-calendar me-1"></i> <?php echo date("d M Y", strtotime($row['start_date'])); ?></span></td>
                                        <td><span class="text-danger fw-semibold"><i class="fa-regular fa-calendar me-1"></i> <?php echo date("d M Y", strtotime($row['end_date'])); ?></span></td>
                                        <td class="text-center">
                                            <a href="manage_academic_year.php?delete=<?php echo $row['id']; ?>" class="text-decoration-none text-danger fw-medium" onclick="return confirm('Are you sure you want to delete this academic year announcement?');">
                                                <i class="fa-solid fa-trash me-1"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        No academic year announcements found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>