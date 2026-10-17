<?php
session_start();
include '../db.php';

// Teacher/Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'teacher' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: ../unauthorized.php");
    exit();
}

// Filter variables
$filter_class = $_GET['class_id'] ?? '';
$filter_class_arm = $_GET['class_arm'] ?? '';
$filter_date = $_GET['date'] ?? '';

$attendance_list = null;

// Filter ခလုတ်နှိပ်ပြီးမှသာ Query ကို Run မည်
if (isset($_GET['view_attendance'])) {
    $query = "SELECT student_attendance.*, students.student_name, classes.class_name FROM student_attendance 
              JOIN students ON student_attendance.student_id = students.id 
              JOIN classes ON student_attendance.class_id = classes.id WHERE 1=1";

    if (!empty($filter_class)) {
        $query .= " AND student_attendance.class_id = " . intval($filter_class);
    }
    if (!empty($filter_class_arm)) {
        $query .= " AND student_attendance.class_arm = '" . $conn->real_escape_string($filter_class_arm) . "'";
    }
    if (!empty($filter_date)) {
        $query .= " AND student_attendance.attendance_date = '" . $conn->real_escape_string($filter_date) . "'";
    }

    $query .= " ORDER BY student_attendance.id DESC";
    $attendance_list = $conn->query($query);
}

// Classes list for dropdown
$classes = $conn->query("SELECT * FROM classes");
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-navbar { height: 60px; background-color: #3b5998; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1030; }
        .sidebar { width: 250px; background-color: #ffffff; position: fixed; top: 60px; bottom: 0; left: 0; box-shadow: 2px 0 5px rgba(0,0,0,0.05); overflow-y: auto; z-index: 1020; }
        .sidebar .menu-category { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; padding: 12px 15px 4px 15px; font-weight: bold; }
        .sidebar a { padding: 10px 15px; display: flex; align-items: center; color: #333; text-decoration: none; transition: none; }
        .sidebar a:hover, .sidebar a.active { background-color: #eaedf1; color: #3b5998; font-weight: 500; }
        .sidebar a i { margin-right: 10px; width: 20px; text-align: center; }
        .main-content { margin-left: 250px; margin-top: 60px; padding: 25px; }
        .card-box { background: white; border-radius: 6px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-bottom: 25px; border-top: 3px solid #3b5998; }
        .card-header-custom { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; font-weight: 600; color: #3b5998; }
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

    <!-- Sidebar (Updated to match Admin design) -->
    <div class="sidebar py-3">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        
        <div class="menu-category">Class & Course</div>
        <a href="manage_class.php"><i class="fa-solid fa-school"></i> Manage Class</a>
        <a href="manage_class_arms.php"><i class="fa-solid fa-layer-group"></i> Manage Section</a>
        <a href="manage_course.php"><i class="fa-solid fa-book"></i> Manage Course</a>

        <div class="menu-category">Staff & Students</div>
        <a href="manage_teacher.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Teacher</a>
        <a href="manage_student.php"><i class="fa-solid fa-user-graduate"></i> Manage Student</a>
        <a href="manage_payfee.php"><i class="fa-solid fa-file-invoice-dollar"></i> Manage Payment</a>

        <div class="menu-category">Attendance</div>
        <a href="view_student_attendance.php" class="active"><i class="fa-solid fa-calendar-check"></i> View Monthly Student Attendance</a>

        <div class="menu-category">Exam Management</div>
        <a href="manage_academic_year.php"><i class="fa-solid fa-calendar-days"></i>Manage Academic Year</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">View Student Attendance</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">View Attendance</li>
                </ol>
            </nav>
        </div>

        <!-- Filter Form Card -->
        <div class="card-box">
            <div class="card-header-custom">Filter Attendance</div>
            <div class="p-4">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="" disabled <?php echo empty($filter_class) ? 'selected' : ''; ?>>-- Select Class --</option>
                                <?php while($row = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo ($filter_class == $row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['class_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Section *</label>
                            <select name="class_arm" class="form-select" required>
                                <option value="" disabled <?php echo empty($filter_class_arm) ? 'selected' : ''; ?>>-- Select Section --</option>
                                <option value="Section-A" <?php echo ($filter_class_arm == 'Section-A') ? 'selected' : ''; ?>>Section-A</option>
                                <option value="Section-B" <?php echo ($filter_class_arm == 'Section-B') ? 'selected' : ''; ?>>Section-B</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Date *</label>
                            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="view_attendance" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">
                        <i class="fa-solid fa-filter me-1"></i> View Attendance
                    </button>
                    <a href="view_student_attendance.php" class="btn btn-secondary px-3 ms-2">Reset</a>
                </form>
            </div>
        </div>

        <!-- Attendance List Table Card -->
        <div class="card-box">
            <div class="card-header-custom">Student Attendance List</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Course No</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Attendance (%)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            if ($attendance_list === null): 
                            ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-3">Please select filter criteria and click "View Attendance" to display records.</td>
                                </tr>
                            <?php elseif ($attendance_list->num_rows > 0):
                                while($row = $attendance_list->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['class_arm']); ?></td>
                                    <td><?php echo htmlspecialchars($row['course_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['present_val']); ?></td>
                                    <td><?php echo htmlspecialchars($row['absent_val']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($row['attendance_percentage'] >= 75) ? 'success' : 'warning'; ?>">
                                            <?php echo number_format($row['attendance_percentage'], 1); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-3">No attendance records found for the selected criteria.</td>
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