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
$edit_teacher_name = "";
$edit_course_no = "";
$edit_course_name = "";
$edit_class_id = "";
$edit_description = "";

// 1. Add Course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_course'])) {
    $teacher_name = trim($_POST['teacher_name']);
    $course_no = trim($_POST['course_no']);
    $course_name = trim($_POST['course_name']);
    $class_id = $_POST['class_id'] ?? '';
    $description = trim($_POST['description']);

    if (!empty($teacher_name) && !empty($course_no) && !empty($course_name) && !empty($class_id)) {
        $stmt = $conn->prepare("INSERT INTO courses (teacher_name, course_no, course_name, class_id, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $teacher_name, $course_no, $course_name, $class_id, $description);
        if ($stmt->execute()) {
            $success_msg = "Course added successfully!";
        } else {
            $error_msg = "Error adding course!";
        }
        $stmt->close();
    } else {
        $error_msg = "All required fields must be filled!";
    }
}

// 2. Delete Course
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_course.php");
        exit();
    }
}

// 3. Edit Fetch Data
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_teacher_name = $row['teacher_name'];
        $edit_course_no = $row['course_no'];
        $edit_course_name = $row['course_name'];
        $edit_class_id = $row['class_id'];
        $edit_description = $row['description'];
    }
    $stmt->close();
}

// 4. Update Course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_course'])) {
    $id = $_POST['course_id'];
    $teacher_name = trim($_POST['teacher_name']);
    $course_no = trim($_POST['course_no']);
    $course_name = trim($_POST['course_name']);
    $class_id = $_POST['class_id'] ?? '';
    $description = trim($_POST['description']);

    if (!empty($teacher_name) && !empty($course_no) && !empty($course_name) && !empty($class_id)) {
        $stmt = $conn->prepare("UPDATE courses SET teacher_name = ?, course_no = ?, course_name = ?, class_id = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $teacher_name, $course_no, $course_name, $class_id, $description, $id);
        if ($stmt->execute()) {
            header("Location: manage_course.php");
            exit();
        } else {
            $error_msg = "Error updating course!";
        }
        $stmt->close();
    } else {
        $error_msg = "All required fields must be filled!";
    }
}

// Fetch Teachers for Dropdown (teachers ဇယားမှ firstname နှင့် lastname ကိုဆွဲထုတ်ရန်)
$teachers_result = $conn->query("SELECT firstname, lastname FROM teachers ORDER BY firstname ASC");

// Fetch Classes for Dropdown
$classes_result = $conn->query("SELECT * FROM classes ORDER BY id ASC");

// Fetch Courses List
$courses_result = $conn->query("SELECT courses.*, classes.class_name FROM courses LEFT JOIN classes ON courses.class_id = classes.id ORDER BY courses.id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .top-navbar { height: 60px; background-color: #3b5998; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1030; }
        .sidebar { width: 250px; background-color: #ffffff; position: fixed; top: 60px; bottom: 0; left: 0; box-shadow: 2px 0 5px rgba(0,0,0,0.05); overflow-y: auto; z-index: 1020; }
        .sidebar .menu-category { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; padding: 15px 15px 5px 15px; font-weight: bold; }
        .sidebar a { padding: 10px 15px; display: flex; align-items: center; color: #333; text-decoration: none; transition: 0.2s; }
        .sidebar a:hover, .sidebar a.active { background-color: #eaedf1; color: #3b5998; font-weight: 500; }
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
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?>
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
        <a href="manage_course.php" class="active"><i class="fa-solid fa-book-open"></i> Manage Course</a>
        
        <div class="menu-category">Staff & Students</div>
        <a href="manage_teacher.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Teacher</a>
        <a href="manage_student.php"><i class="fa-solid fa-user-graduate"></i> Manage Student</a>
        <a href="manage_payfee.php"><i class="fa-solid fa-file-invoice-dollar"></i> Manage Payment</a>
        
        <div class="menu-category">Attendance</div>
        <a href="view_student_attendance.php"><i class="fa-solid fa-calendar-check"></i> View Monthly Student Attendance</a>
        
        <div class="menu-category">Exam Management</div>
        <a href="manage_academic_year.php"><i class="fa-solid fa-calendar-days"></i> Manage Academic Year</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Manage Course</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">Manage Course</li>
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
            <div class="card-header-custom"><?php echo $edit_mode ? "Edit Course" : "Add Course"; ?></div>
            <div class="p-4">
                <form method="POST">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="course_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <!-- Row 1: Teacher Name Dropdown, Course No, Course Name, Select Class -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Teacher Name *</label>
                            <select name="teacher_name" class="form-select" required>
                                <option value="" selected disabled>--Select Teacher--</option>
                                <?php 
                                if ($teachers_result && $teachers_result->num_rows > 0) {
                                    while($t_row = $teachers_result->fetch_assoc()): 
                                        $full_name = $t_row['firstname'] . ' ' . $t_row['lastname'];
                                ?>
                                    <option value="<?php echo htmlspecialchars($full_name); ?>" <?php echo ($edit_teacher_name == $full_name) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($full_name); ?>
                                    </option>
                                <?php 
                                    endwhile; 
                                } 
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Course No *</label>
                            <input type="text" name="course_no" class="form-control" placeholder="Course No" value="<?php echo htmlspecialchars($edit_course_no); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Course Name *</label>
                            <input type="text" name="course_name" class="form-control" placeholder="Course Name" value="<?php echo htmlspecialchars($edit_course_name); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Select Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="" selected disabled>--Select Class--</option>
                                <?php 
                                while($c_row = $classes_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $c_row['id']; ?>" <?php echo ($edit_class_id == $c_row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c_row['class_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Description (Big Text Box) -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-muted">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter course description..."><?php echo htmlspecialchars($edit_description); ?></textarea>
                        </div>
                    </div>

                    <!-- Button -->
                    <div>
                        <?php if($edit_mode): ?>
                            <button type="submit" name="update_course" class="btn btn-primary px-4">Update</button>
                            <a href="manage_course.php" class="btn btn-secondary px-4">Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="save_course" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">Save</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card-box">
            <div class="card-header-custom">All Course</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">#</th>
                                <th>Teacher Name</th>
                                <th>Course No</th>
                                <th>Course Name</th>
                                <th>Class</th>
                                <th>Description</th>
                                <th width="7%" class="text-center">Edit</th>
                                <th width="7%" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                                <?php $count = 1; while($row = $courses_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($row['description'] ?? '')); ?></td>
                                        <td class="text-center">
                                            <a href="manage_course.php?edit=<?php echo $row['id']; ?>" class="text-decoration-none text-primary fw-medium">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="manage_course.php?delete=<?php echo $row['id']; ?>" class="text-decoration-none text-danger fw-medium" onclick="return confirm('Are you sure you want to delete this course?');">
                                                <i class="fa-solid fa-trash me-1"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">No courses found.</td>
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