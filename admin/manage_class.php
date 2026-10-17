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
$edit_class_name = "";

// 1. Create Class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_class'])) {
    $class_name = trim($_POST['class_name']);

    if (!empty($class_name)) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name) VALUES (?)");
        $stmt->bind_param("s", $class_name);
        if ($stmt->execute()) {
            $success_msg = "Class created successfully!";
        } else {
            $error_msg = "Error creating class!";
        }
        $stmt->close();
    } else {
        $error_msg = "Class name cannot be empty!";
    }
}

// 2. Delete Class
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_class.php");
        exit();
    }
}

// 3. Edit Fetch Data
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_class_name = $row['class_name'];
    }
    $stmt->close();
}

// 4. Update Class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_class'])) {
    $id = $_POST['class_id'];
    $class_name = trim($_POST['class_name']);

    if (!empty($class_name)) {
        $stmt = $conn->prepare("UPDATE classes SET class_name = ? WHERE id = ?");
        $stmt->bind_param("si", $class_name, $id);
        if ($stmt->execute()) {
            header("Location: manage_class.php");
            exit();
        } else {
            $error_msg = "Error updating class!";
        }
        $stmt->close();
    } else {
        $error_msg = "Class name cannot be empty!";
    }
}

// Fetch Classes List
$classes_result = $conn->query("SELECT * FROM classes ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Class - Admin</title>
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
        <a href="manage_class.php" class="active"><i class="fa-solid fa-school"></i> Manage Class</a>
       <a href="manage_class_arms.php" ><i class="fa-solid fa-layer-group"></i> Manage Section</a>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Manage Class</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">Manage Class</li>
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
            <div class="card-header-custom"><?php echo $edit_mode ? "Edit Class" : "Create Class"; ?></div>
            <div class="p-4">
                <form method="POST">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="class_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Class *</label>
                            <input type="text" name="class_name" class="form-control" placeholder="Class" value="<?php echo htmlspecialchars($edit_class_name); ?>" required>
                        </div>
                    </div>

                    <div>
                        <?php if($edit_mode): ?>
                            <button type="submit" name="update_class" class="btn btn-primary px-4">Update</button>
                            <a href="manage_class.php" class="btn btn-secondary px-4">Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="save_class" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">Submit</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card-box">
            <div class="card-header-custom">View Classes</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Class</th>
                                <th width="8%" class="text-center">Edit</th>
                                <th width="8%" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($classes_result && $classes_result->num_rows > 0): ?>
                                <?php $count = 1; while($row = $classes_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                        <td class="text-center">
                                            <a href="manage_class.php?edit=<?php echo $row['id']; ?>" class="text-decoration-none text-primary fw-medium">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="manage_class.php?delete=<?php echo $row['id']; ?>" class="text-decoration-none text-danger fw-medium" onclick="return confirm('Are you sure you want to delete this class?');">
                                                <i class="fa-solid fa-trash me-1"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No classes found.</td>
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