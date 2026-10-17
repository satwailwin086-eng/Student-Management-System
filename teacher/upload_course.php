<?php
session_start();
include '../db.php';

// Teacher ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../unauthorized.php");
    exit();
}

$success_msg = "";
$error_msg = "";
$edit_mode = false;
$edit_id = "";
$edit_teacher_name = "";
$edit_course_name = "";
$edit_class_id = "";
$edit_description = "";

// Search keyword ဖမ်းယူခြင်း (+ များကို space သို့ အတိအကျ ပြောင်းရန်)
$search_teacher = "";
if (isset($_GET['search_teacher'])) {
    $search_teacher = trim($_GET['search_teacher']);
    // URL ထဲပါလာသော '+' နိမိတ်လက္ခဏာများကို Space အဖြစ် အစားထိုးခြင်း
    $search_teacher = str_replace(['+', '%20'], ' ', $search_teacher);
}

// 1. Delete Course
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: upload_course.php" . (!empty($search_teacher) ? "?search_teacher=" . urlencode($search_teacher) : ""));
        exit();
    }
}

// 2. Edit Fetch Data
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_teacher_name = $row['teacher_name'];
        $edit_course_name = $row['course_name'];
        $edit_class_id = $row['class_id'];
        $edit_description = $row['description'] ?? '';
    }
    $stmt->close();
}

// 3. Update Course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_course'])) {
    $id = $_POST['course_id'];
    $teacher_name = trim($_POST['teacher_name']);
    $course_name = trim($_POST['course_name']);
    $class_id = $_POST['class_id'] ?? '';
    $description = trim($_POST['description']);

    if (!empty($teacher_name) && !empty($course_name) && !empty($class_id)) {
        $stmt = $conn->prepare("UPDATE courses SET teacher_name = ?, course_name = ?, class_id = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $teacher_name, $course_name, $class_id, $description, $id);
        if ($stmt->execute()) {
            header("Location: upload_course.php" . (!empty($search_teacher) ? "?search_teacher=" . urlencode($search_teacher) : ""));
            exit();
        } else {
            $error_msg = "Error updating course!";
        }
        $stmt->close();
    } else {
        $error_msg = "All required fields must be filled!";
    }
}

// Fetch Classes for Dropdown
$classes_result = $conn->query("SELECT * FROM classes ORDER BY id ASC");

// Fetch Uploaded Courses List (Search လုပ်ထားမှသာ ဒေတာပေါ်စေရန်)
if (!empty($search_teacher)) {
    $stmt_courses = $conn->prepare("SELECT courses.*, COALESCE(classes.class_name, 'No Class') AS class_name FROM courses LEFT JOIN classes ON courses.class_id = classes.id WHERE courses.teacher_name LIKE ? ORDER BY courses.id DESC");
    $like_teacher = "%" . $search_teacher . "%";
    $stmt_courses->bind_param("s", $like_teacher);
    $stmt_courses->execute();
    $courses_result = $stmt_courses->get_result();
} else {
    // Search မလုပ်ရသေးပါက ဒေတာ လုံးဝ မပြရန် false သတ်မှတ်ခြင်း
    $courses_result = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Course - Teacher</title>
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
        <h5 class="m-0 fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i>  Student Information and Academic Management System</h5>
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
    <div class="sidebar py-2">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <div class="menu-category">Students Management</div>
        <a href="view_student_list.php"><i class="fa-solid fa-users"></i> View Student List</a>
        <div class="menu-category">Attendance</div>
        <a href="manage_student_attendance.php"><i class="fa-regular fa-calendar-check"></i>  Manage Monthly Student Attendance</a>
        <div class="menu-category">Academics</div>
        <a href="upload_course.php" class="active"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Course</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Upload Course</h3>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Edit Form Box -->
        <?php if ($edit_mode): ?>
            <div class="card-box mb-4">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span>Edit Course Details</span>
                    <a href="upload_course.php<?php echo !empty($search_teacher) ? '?search_teacher=' . urlencode($search_teacher) : ''; ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i> Close Form</a>
                </div>
                <div class="p-4">
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $edit_id; ?>">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted">Teacher Name *</label>
                                <input type="text" name="teacher_name" class="form-control" value="<?php echo htmlspecialchars($edit_teacher_name); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted">Course Name *</label>
                                <input type="text" name="course_name" class="form-control" value="<?php echo htmlspecialchars($edit_course_name); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted">Class *</label>
                                <select name="class_id" class="form-select" required>
                                    <option value="" disabled>-- Select Class --</option>
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
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-muted">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Enter course description..."><?php echo htmlspecialchars($edit_description); ?></textarea>
                            </div>
                        </div>

                        <div>
                            <button type="submit" name="update_course" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">Update Course</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="card-box mb-4">
            <div class="card-header-custom">Search Courses by Teacher Name</div>
            <div class="p-3">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search_teacher" class="form-control" placeholder="Enter Teacher Name to search..." value="<?php echo htmlspecialchars($search_teacher); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #3b5998; border-color: #3b5998;"><i class="fa-solid fa-search me-1"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Uploaded Courses / Search Results List Table -->
        <div class="card-box">
            <div class="card-header-custom">
                <?php echo !empty($search_teacher) ? 'Search Results for "' . htmlspecialchars($search_teacher) . '"' : 'Uploaded Courses List'; ?>
            </div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">#</th>
                                <th>Teacher Name</th>
                                <th>Course Name</th>
                                <th>Class</th>
                                <th>Description</th>
                                <th width="7%" class="text-center">Edit</th>
                                <th width="7%" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($search_teacher)): ?>
                                <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                                    <?php $count = 1; while($row = $courses_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $count++; ?></td>
                                            <td><?php echo htmlspecialchars($row['teacher_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($row['description'] ?? '')); ?></td>
                                            <td class="text-center">
                                                <a href="upload_course.php?edit=<?php echo $row['id']; ?>&search_teacher=<?php echo urlencode($search_teacher); ?>" class="text-decoration-none text-primary fw-medium">
                                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <a href="upload_course.php?delete=<?php echo $row['id']; ?>&search_teacher=<?php echo urlencode($search_teacher); ?>" class="text-decoration-none text-danger fw-medium" onclick="return confirm('Are you sure you want to delete this course?');">
                                                    <i class="fa-solid fa-trash me-1"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">
                                            No courses found for "<?php echo htmlspecialchars($search_teacher); ?>".
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        Please enter a teacher's name above to search and view courses.
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