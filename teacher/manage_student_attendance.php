<?php
session_start();
include '../db.php';

// Teacher/Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'teacher' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: ../unauthorized.php");
    exit();
}

$edit_state = false;
$edit_id = 0;
$edit_roll_no = '';
$edit_student_id = '';
$edit_class_id = '';
$edit_class_arm = '';
$edit_course_no = '';
$edit_present = '';
$edit_absent = '';
$edit_date = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_attendance'])) {
        $roll_no = $_POST['roll_no'];
        $student_id = $_POST['student_id'];
        $class_id = $_POST['class_id'];
        $class_arm = $_POST['class_arm'];
        $course_no = $_POST['course_no'];
        $present = $_POST['present'];
        $absent = $_POST['absent'];
        $date = $_POST['date'];

        // Attendance Percentage တွက်ချက်ခြင်း (တစ်လကို 16 ကြိမ် = 100%)
        $attendance_percent = ($present / 16) * 100;
        if($attendance_percent > 100) { $attendance_percent = 100; }

        $stmt = $conn->prepare("INSERT INTO student_attendance (roll_no, student_id, class_id, class_arm, course_no, present_val, absent_val, attendance_percentage, attendance_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siissddss", $roll_no, $student_id, $class_id, $class_arm, $course_no, $present, $absent, $attendance_percent, $date);
        $stmt->execute();
        header("Location: manage_student_attendance.php");
        exit();
    } elseif (isset($_POST['update_attendance'])) {
        $edit_id = $_POST['id'];
        $roll_no = $_POST['roll_no'];
        $student_id = $_POST['student_id'];
        $class_id = $_POST['class_id'];
        $class_arm = $_POST['class_arm'];
        $course_no = $_POST['course_no'];
        $present = $_POST['present'];
        $absent = $_POST['absent'];
        $date = $_POST['date'];

        $attendance_percent = ($present / 16) * 100;
        if($attendance_percent > 100) { $attendance_percent = 100; }

        $stmt = $conn->prepare("UPDATE student_attendance SET roll_no = ?, student_id = ?, class_id = ?, class_arm = ?, course_no = ?, present_val = ?, absent_val = ?, attendance_percentage = ?, attendance_date = ? WHERE id = ?");
        $stmt->bind_param("siissddssi", $roll_no, $student_id, $class_id, $class_arm, $course_no, $present, $absent, $attendance_percent, $date, $edit_id);
        $stmt->execute();
        header("Location: manage_student_attendance.php");
        exit();
    }
}

// Edit လုပ်ရန် Data ခေါ်ယူခြင်း
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_state = true;
    $record = $conn->query("SELECT * FROM student_attendance WHERE id = $edit_id")->fetch_assoc();
    if ($record) {
        $edit_roll_no = $record['roll_no'];
        $edit_student_id = $record['student_id'];
        $edit_class_id = $record['class_id'];
        $edit_class_arm = $record['class_arm'];
        $edit_course_no = $record['course_no'];
        $edit_present = $record['present_val'];
        $edit_absent = $record['absent_val'];
        $edit_date = $record['attendance_date'];
    }
}

// Delete Record
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM student_attendance WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_student_attendance.php");
    exit();
}

// Data Fetching
$students = $conn->query("SELECT * FROM students ORDER BY student_name ASC");
$classes = $conn->query("SELECT * FROM classes");
$attendance_list = $conn->query("SELECT student_attendance.*, students.student_name, classes.class_name FROM student_attendance 
                                JOIN students ON student_attendance.student_id = students.id 
                                JOIN classes ON student_attendance.class_id = classes.id ORDER BY student_attendance.id ASC");
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-navbar { height: 60px; background-color: #3b5998; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1030; }
        .sidebar { width: 250px; background-color: #ffffff; position: fixed; top: 60px; bottom: 0; left: 0; box-shadow: 2px 0 5px rgba(0,0,0,0.05); overflow-y: auto; z-index: 1020; }
        .sidebar .menu-category { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; padding: 10px 15px 5px 15px; font-weight: bold; }
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
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Teacher'); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar py-3">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <div class="menu-category">Students Management</div>
        <a href="view_student_list.php"><i class="fa-solid fa-users"></i> View Student List</a>
        
        <div class="menu-category">Attendance</div>
        <a href="manage_student_attendance.php" class="active"><i class="fa-solid fa-calendar-check"></i>  Manage Monthly Student Attendance</a>
        
        <div class="menu-category">Academics</div>
        <a href="upload_course.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Course</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Manage Student Attendance</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manage Attendance</li>
                </ol>
            </nav>
        </div>

        <!-- Form Card -->
        <div class="card-box">
            <div class="card-header-custom">
                <?php echo $edit_state ? "Edit Student Attendance" : "Take Student Attendance"; ?>
            </div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Roll No *</label>
                            <input type="text" name="roll_no" class="form-control" placeholder="Enter Roll No" value="<?php echo htmlspecialchars($edit_roll_no); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Student Name *</label>
                            <select name="student_id" class="form-select" required>
                                <option value="" disabled <?php echo !$edit_state ? 'selected' : ''; ?>>-- Select Student --</option>
                                <?php 
                                $students->data_seek(0);
                                while($row = $students->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo ($edit_student_id == $row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['student_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="" disabled <?php echo !$edit_state ? 'selected' : ''; ?>>-- Select Class --</option>
                                <?php 
                                $classes->data_seek(0);
                                while($row = $classes->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo ($edit_class_id == $row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['class_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- Class Arm ကို Dropdown (Select) ပုံစံ ပြောင်းလဲထားသည် -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Section *</label>
                            <select name="class_arm" class="form-select" required>
                                <option value="" disabled <?php echo !$edit_state ? 'selected' : ''; ?>>-- Select Section --</option>
                                <option value="Section-A" <?php echo ($edit_class_arm == 'Section-A') ? 'selected' : ''; ?>>Section-A</option>
                                <option value="Section-B" <?php echo ($edit_class_arm == 'Section-B') ? 'selected' : ''; ?>>Section-B</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Course No *</label>
                            <input type="text" name="course_no" class="form-control" placeholder="Enter Course No" value="<?php echo htmlspecialchars($edit_course_no); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Present (out of 16) *</label>
                            <input type="number" name="present" class="form-control" placeholder="e.g. 16" min="0" max="16" value="<?php echo htmlspecialchars($edit_present); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Absent *</label>
                            <input type="number" name="absent" class="form-control" placeholder="e.g. 0" min="0" max="16" value="<?php echo htmlspecialchars($edit_absent); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Date *</label>
                            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($edit_date); ?>" required>
                        </div>
                    </div>
                    <?php if ($edit_state): ?>
                        <button type="submit" name="update_attendance" class="btn btn-success px-4">Update Attendance</button>
                        <a href="manage_student_attendance.php" class="btn btn-secondary px-3 ms-2">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="save_attendance" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">Save Attendance</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card-box">
            <div class="card-header-custom">Student Attendance List</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">#</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Course No</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Attendance (%)</th>
                                <th>Date</th>
                                <th width="8%" class="text-center">Edit</th>
                                <th width="8%" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            if ($attendance_list && $attendance_list->num_rows > 0):
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
                                    <td class="text-center">
                                        <a href="manage_student_attendance.php?edit=<?php echo $row['id']; ?>" class="text-decoration-none text-primary fw-medium" title="Edit">
                                            <i class="fa-regular fa-pen-to-square me-1"></i> Edit
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="manage_student_attendance.php?delete=<?php echo $row['id']; ?>" class="text-decoration-none text-danger fw-medium" title="Delete" onclick="return confirm('Are you sure you want to delete this record?');">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-3">No attendance records found.</td>
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