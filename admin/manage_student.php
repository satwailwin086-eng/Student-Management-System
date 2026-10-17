<?php
session_start();
include '../db.php';

// Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../unauthorized.php");
    exit();
}

$success_msg = "";
$error_msg = "";
$edit_mode = false;
$edit_id = "";
$edit_roll_no = "";
$edit_student_name = "";
$edit_address = "";
$edit_phone = "";
$edit_gender = "";
$edit_dob = "";
$edit_parent_name = "";
$edit_parent_phone = "";
$edit_class_id = "";
$edit_class_arm_id = "";
$edit_academic_year = "";

// 1. Add Student (Save)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_student'])) {
    $roll_no = trim($_POST['roll_no']);
    $student_name = trim($_POST['student_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'];
    $parent_name = trim($_POST['parent_name']);
    $parent_phone = trim($_POST['parent_phone']);
    $class_id = $_POST['class_id'] ?? '';
    $class_arm_id = $_POST['class_arm_id'] ?? '';
    $academic_year = trim($_POST['academic_year']);
    
    $today = new DateTime();
    $birthDate = new DateTime($dob);
    $age = $today->diff($birthDate)->y;

    if (!empty($roll_no) && !empty($student_name) && !empty($phone) && !empty($gender) && !empty($dob) && !empty($parent_name) && !empty($parent_phone) && !empty($class_id) && !empty($class_arm_id) && !empty($academic_year)) {
        if ($birthDate > $today) {
            $error_msg = "Error: Date of Birth cannot be in the future!";
        } elseif ($age < 17) {
            $error_msg = "Error: Student must be at least 17 years old!";
        } else {
            // Class Arm တစ်ခုတည်းတွင် Roll No ထပ်နေခြင်း ရှိမရှိ စစ်ဆေးခြင်း
            $check_stmt = $conn->prepare("SELECT id FROM students WHERE roll_no = ? AND class_arm_id = ?");
            $check_stmt->bind_param("si", $roll_no, $class_arm_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error_msg = "Error: This Roll No already exists in the selected Class Arm!";
            } else {
                $stmt = $conn->prepare("INSERT INTO students (roll_no, student_name, address, phone, gender, dob, parent_name, parent_phone, class_id, class_arm_id, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssssiis", $roll_no, $student_name, $address, $phone, $gender, $dob, $parent_name, $parent_phone, $class_id, $class_arm_id, $academic_year);
                if ($stmt->execute()) {
                    $success_msg = "Student added successfully!";
                } else {
                    $error_msg = "Error adding student!";
                }
            }
            $check_stmt->close();
        }
    } else {
        $error_msg = "All required fields must be filled!";
    }
}

// 2. Delete Student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_student.php");
        exit();
    }
}

// 3. Edit Fetch Data
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_roll_no = $row['roll_no'];
        $edit_student_name = $row['student_name'];
        $edit_address = $row['address'] ?? '';
        $edit_phone = $row['phone'];
        $edit_gender = $row['gender'];
        $edit_dob = $row['dob'];
        $edit_parent_name = $row['parent_name'];
        $edit_parent_phone = $row['parent_phone'];
        $edit_class_id = $row['class_id'];
        $edit_class_arm_id = $row['class_arm_id'];
        $edit_academic_year = $row['academic_year'] ?? '';
    }
}

// 4. Update Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student'])) {
    $id = $_POST['student_id'];
    $roll_no = trim($_POST['roll_no']);
    $student_name = trim($_POST['student_name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'];
    $parent_name = trim($_POST['parent_name']);
    $parent_phone = trim($_POST['parent_phone']);
    $class_id = $_POST['class_id'] ?? '';
    $class_arm_id = $_POST['class_arm_id'] ?? '';
    $academic_year = trim($_POST['academic_year']);
    
    $today = new DateTime();
    $birthDate = new DateTime($dob);
    $age = $today->diff($birthDate)->y;

    if (!empty($roll_no) && !empty($student_name) && !empty($phone) && !empty($gender) && !empty($dob) && !empty($parent_name) && !empty($parent_phone) && !empty($class_id) && !empty($class_arm_id) && !empty($academic_year)) {
        if ($birthDate > $today) {
            $error_msg = "Error: Date of Birth cannot be in the future!";
        } elseif ($age < 17) {
            $error_msg = "Error: Student must be at least 17 years old!";
        } else {
            // Update ပြုလုပ်စဉ် တခြားကျောင်းသားတွင် ဤ Roll No နှင့် Class Arm ရှိပြီးသားဖြစ်နေခြင်း ရှိမရှိ စစ်ဆေးခြင်း
            $check_stmt = $conn->prepare("SELECT id FROM students WHERE roll_no = ? AND class_arm_id = ? AND id != ?");
            $check_stmt->bind_param("sii", $roll_no, $class_arm_id, $id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error_msg = "Error: This Roll No already exists in the selected Class Arm!";
            } else {
                $stmt = $conn->prepare("UPDATE students SET roll_no = ?, student_name = ?, address = ?, phone = ?, gender = ?, dob = ?, parent_name = ?, parent_phone = ?, class_id = ?, class_arm_id = ?, academic_year = ? WHERE id = ?");
                $stmt->bind_param("ssssssssiisi", $roll_no, $student_name, $address, $phone, $gender, $dob, $parent_name, $parent_phone, $class_id, $class_arm_id, $academic_year, $id);
                if ($stmt->execute()) {
                    header("Location: manage_student.php");
                    exit();
                } else {
                    $error_msg = "Error updating student!";
                }
            }
            $check_stmt->close();
        }
    } else {
        $error_msg = "All required fields must be filled!";
    }
}

// Fetching Dropdowns and List
$classes_result = $conn->query("SELECT * FROM classes ORDER BY id ASC");
$arms_result = $conn->query("SELECT class_arms.*, classes.class_name FROM class_arms JOIN classes ON class_arms.class_id = classes.id ORDER BY class_arms.id ASC");
$students_result = $conn->query("SELECT students.*, classes.class_name, class_arms.class_arm_name FROM students JOIN classes ON students.class_id = classes.id JOIN class_arms ON students.class_arm_id = class_arms.id ORDER BY students.id ASC");
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-navbar { height: 60px; background-color: #3b5998; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1030; }
        .sidebar { width: 250px; background-color: #ffffff; position: fixed; top: 60px; bottom: 0; left: 0; box-shadow: 2px 0 5px rgba(0,0,0,0.05); overflow-y: auto; z-index: 1020; }
        .sidebar .menu-category { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; padding: 10px 15px 5px 15px; font-weight: bold; }
        .sidebar a { padding: 10px 15px; display: flex; align-items: center; color: #333; text-decoration: none; transition: 0.2s; }
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

    <!-- Sidebar -->
    <div class="sidebar py-3">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <div class="menu-category">Class & Course</div>
        <a href="manage_class.php"><i class="fa-solid fa-school"></i> Manage Class</a>
        <a href="manage_class_arms.php"><i class="fa-solid fa-layer-group"></i> Manage Section</a>
        <a href="manage_course.php"><i class="fa-solid fa-book-open"></i> Manage Course</a>
        
        <div class="menu-category">Staff & Students</div>
        <a href="manage_teacher.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Teacher</a>
        <a href="manage_student.php" class="active"><i class="fa-solid fa-user-graduate"></i> Manage Student</a>
        <a href="manage_payfee.php"><i class="fa-solid fa-file-invoice-dollar"></i> Manage Payment</a>
        
        <div class="menu-category">Attendance</div>
        <a href="view_student_attendance.php"><i class="fa-solid fa-calendar-check"></i> View Monthly Student Attendance</a>
        
        <div class="menu-category">Exam Management</div>
        <a href="manage_academic_year.php"><i class="fa-solid fa-calendar-days"></i> Manage Academic Year</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Manage Students</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">Manage Students</li>
                </ol>
            </nav>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="card-box">
            <div class="card-header-custom">
                <?php echo $edit_mode ? "Edit Student" : "Create Students"; ?>
            </div>
            <div class="p-4">
                <form method="POST">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="student_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Roll No *</label>
                            <input type="text" name="roll_no" class="form-control" placeholder="Roll No" value="<?php echo htmlspecialchars($edit_roll_no); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Student Name *</label>
                            <input type="text" name="student_name" class="form-control" placeholder="Student Name" value="<?php echo htmlspecialchars($edit_student_name); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Address" value="<?php echo htmlspecialchars($edit_address); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Phone No *</label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone Number" value="<?php echo htmlspecialchars($edit_phone); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="" selected disabled>--Select Gender--</option>
                                <option value="Male" <?php echo ($edit_gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($edit_gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Date of Birth *</label>
                            <input type="date" name="dob" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($edit_dob); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Parent Name *</label>
                            <input type="text" name="parent_name" class="form-control" placeholder="Parent Name" value="<?php echo htmlspecialchars($edit_parent_name); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Parent Phone Number *</label>
                            <input type="text" name="parent_phone" class="form-control" placeholder="Parent Phone Number" value="<?php echo htmlspecialchars($edit_parent_phone); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Select Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="" selected disabled>--Select Class--</option>
                                <?php 
                                $classes_result->data_seek(0);
                                while($c_row = $classes_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $c_row['id']; ?>" <?php echo ($edit_class_id == $c_row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c_row['class_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Section *</label>
                            <select name="class_arm_id" class="form-select" required>
                                <option value="" selected disabled>--Select Section--</option>
                                <?php 
                                $arms_result->data_seek(0);
                                while($arm_row = $arms_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $arm_row['id']; ?>" <?php echo ($edit_class_arm_id == $arm_row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($arm_row['class_arm_name'] . ' (' . $arm_row['class_name'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Academic Year *</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2025-2026" value="<?php echo htmlspecialchars($edit_academic_year); ?>" required>
                        </div>
                    </div>

                    <?php if($edit_mode): ?>
                        <button type="submit" name="update_student" class="btn btn-primary px-4">Update</button>
                        <a href="manage_student.php" class="btn btn-secondary px-4">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="save_student" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">Save</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card-box">
            <div class="card-header-custom">All Students</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">#</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Address</th>
                                <th>Phone No</th>
                                <th>Gender</th>
                                <th>DOB</th>
                                <th>Parent Name</th>
                                <th>Parent Phone</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Academic Year</th>
                                <th width="7%" class="text-center">Edit</th>
                                <th width="7%" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students_result && $students_result->num_rows > 0): ?>
                                <?php $count = 1; while($row = $students_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['address'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($row['dob']); ?></td>
                                        <td><?php echo htmlspecialchars($row['parent_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['parent_phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['class_arm_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['academic_year'] ?? ''); ?></td>
                                        <td class="text-center">
                                            <a href="manage_student.php?edit=<?php echo $row['id']; ?>" class="text-decoration-none text-primary fw-medium" title="Edit">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="manage_student.php?delete=<?php echo $row['id']; ?>" class="text-decoration-none text-danger fw-medium" title="Delete" onclick="return confirm('Are you sure you want to delete this student?');">
                                                <i class="fa-solid fa-trash me-1"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="14" class="text-center text-muted py-3">No students found.</td>
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