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
$edit_firstname = "";
$edit_lastname = "";
$edit_email = "";
$edit_phone = "";
$edit_class_id = "";
$edit_class_arm_id = "";

// 1. Add Teacher (Save)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_teacher'])) {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $class_id = $_POST['class_id'];
    $class_arm_id = $_POST['class_arm_id'];

    if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($phone) && !empty($class_id) && !empty($class_arm_id)) {
        $stmt = $conn->prepare("INSERT INTO teachers (firstname, lastname, email, phone, class_id, class_arm_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $firstname, $lastname, $email, $phone, $class_id, $class_arm_id);
        if ($stmt->execute()) {
            $success_msg = "Teacher added successfully!";
        } else {
            $error_msg = "Error adding teacher!";
        }
    } else {
        $error_msg = "All fields are required!";
    }
}

// 2. Delete Teacher
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_teacher.php");
        exit();
    }
}

// 3. Edit Fetch Data
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_firstname = $row['firstname'];
        $edit_lastname = $row['lastname'];
        $edit_email = $row['email'];
        $edit_phone = $row['phone'];
        $edit_class_id = $row['class_id'];
        $edit_class_arm_id = $row['class_arm_id'];
    }
}

// 4. Update Teacher
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_teacher'])) {
    $id = $_POST['teacher_id'];
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $class_id = $_POST['class_id'];
    $class_arm_id = $_POST['class_arm_id'];

    if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($phone) && !empty($class_id) && !empty($class_arm_id)) {
        $stmt = $conn->prepare("UPDATE teachers SET firstname = ?, lastname = ?, email = ?, phone = ?, class_id = ?, class_arm_id = ? WHERE id = ?");
        $stmt->bind_param("ssssiii", $firstname, $lastname, $email, $phone, $class_id, $class_arm_id, $id);
        if ($stmt->execute()) {
            header("Location: manage_teacher.php");
            exit();
        } else {
            $error_msg = "Error updating teacher!";
        }
    }
}

// Fetch Classes for Dropdown
$classes_result = $conn->query("SELECT * FROM classes ORDER BY id ASC");
// Fetch Class Arms for Dropdown
$arms_result = $conn->query("SELECT class_arms.*, classes.class_name FROM class_arms JOIN classes ON class_arms.class_id = classes.id ORDER BY class_arms.id ASC");

// Fetch All Teachers with Class and Class Arm Names
$teachers_query = "SELECT teachers.*, classes.class_name, class_arms.class_arm_name FROM teachers 
                    JOIN classes ON teachers.class_id = classes.id 
                    JOIN class_arms ON teachers.class_arm_id = class_arms.id 
                    ORDER BY teachers.id ASC";
$teachers_result = $conn->query($teachers_query);
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
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
        .card-box {
            background: white;
            border-radius: 6px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 25px;
            border-top: 3px solid #3b5998;
        }
        .card-header-custom {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 600;
            color: #3b5998;
        }
    </style>
</head>
<body>

    <!-- TOP NAVBAR -->
    <nav class="top-navbar d-flex align-items-center px-4 justify-content-between">
        <h5 class="m-0 fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i>  Student Information and Academic Management System</h5>
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <div class="sidebar py-3">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <div class="menu-category">Class & Course</div>
        <a href="manage_class.php"><i class="fa-solid fa-school"></i> Manage Class</a>
        <a href="manage_class_arms.php"><i class="fa-solid fa-layer-group"></i> Manage Section</a>
        <a href="manage_course.php"><i class="fa-solid fa-book-open"></i> Manage Course</a>

        <div class="menu-category">Staff & Students</div>
        <a href="manage_teacher.php" class="active"><i class="fa-solid fa-chalkboard-user"></i> Manage Teacher</a>
        <a href="manage_student.php"><i class="fa-solid fa-user-graduate"></i> Manage Student</a>
        <a href="manage_payfee.php"><i class="fa-solid fa-file-invoice-dollar"></i> Manage Payment</a>

        <div class="menu-category">Attendance</div>
        <a href="view_student_attendance.php"><i class="fa-solid fa-calendar-check"></i> View Monthly Student Attendance</a>

       <div class="menu-category">Exam Management</div>
        <a href="manage_academic_year.php"><i class="fa-solid fa-calendar-days"></i> Manage Academic Year</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Manage Teachers</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">Create Class Teachers</li>
                </ol>
            </nav>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- CREATE / EDIT TEACHER SECTION -->
        <div class="card-box">
            <div class="card-header-custom">
                <?php echo $edit_mode ? "Edit Class Teacher" : "Create Class Teachers"; ?>
            </div>
            <div class="p-4">
                <form method="POST">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="teacher_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Firstname *</label>
                            <input type="text" name="firstname" class="form-control" placeholder="Firstname" value="<?php echo htmlspecialchars($edit_firstname); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Lastname *</label>
                            <input type="text" name="lastname" class="form-control" placeholder="Lastname" value="<?php echo htmlspecialchars($edit_lastname); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="Email Address" value="<?php echo htmlspecialchars($edit_email); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Phone No *</label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone No" value="<?php echo htmlspecialchars($edit_phone); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Select Class *</label>
                            <select name="class_id" id="class_id" class="form-select" required>
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Section *</label>
                            <select name="class_arm_id" id="class_arm_id" class="form-select" required>
                                <option value="" selected disabled>--Select Section--</option>
                                <?php while($arm_row = $arms_result->fetch_assoc()): ?>
                                    <option value="<?php echo $arm_row['id']; ?>" <?php echo ($edit_class_arm_id == $arm_row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($arm_row['class_arm_name'] . ' (' . $arm_row['class_name'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <?php if($edit_mode): ?>
                        <button type="submit" name="update_teacher" class="btn btn-primary px-4">Update</button>
                        <a href="manage_teacher.php" class="btn btn-secondary px-4">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="save_teacher" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">Save</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- ALL CLASS TEACHERS TABLE SECTION -->
        <div class="card-box">
            <div class="card-header-custom">All Class Teachers</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email Address</th>
                                <th>Phone No</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Date Created</th>
                                <th width="10%" class="text-center">Edit</th>
                                <th width="10%" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($teachers_result->num_rows > 0): ?>
                                <?php $count = 1; while($row = $teachers_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['class_arm_name']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                        <td class="text-center">
                                            <a href="manage_teacher.php?edit=<?php echo $row['id']; ?>" class="text-decoration-none text-primary fw-medium" title="Edit">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="manage_teacher.php?delete=<?php echo $row['id']; ?>" class="text-decoration-none text-danger fw-medium" title="Delete" onclick="return confirm('Are you sure you want to delete this teacher?');">
                                                <i class="fa-solid fa-trash me-1"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-3">No teachers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>