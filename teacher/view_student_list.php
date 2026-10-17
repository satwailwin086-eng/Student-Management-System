<?php
session_start();
include '../db.php';

// Teacher ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../unauthorized.php");
    exit();
}

$teacher_name = $_POST['teacher_name'] ?? $_SESSION['username'] ?? '';
$selected_class = "";
$selected_arm = "";
$students_result = null;

// Class များကို Dropdown အတွက် ဆွဲထုတ်ခြင်း
$classes_result = $conn->query("SELECT * FROM classes ORDER BY id ASC");

// Class Arm များကို Dropdown အတွက် ဆွဲထုတ်ခြင်း
$arms_result = $conn->query("SELECT class_arms.*, classes.class_name FROM class_arms JOIN classes ON class_arms.class_id = classes.id ORDER BY class_arms.id ASC");

// View Students ခလုတ်နှိပ်သောအခါ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['view_students'])) {
    $selected_class = $_POST['class_id'];
    $selected_arm = $_POST['class_arm_id'];

    if (!empty($selected_class) && !empty($selected_arm)) {
        // မှတ်ချက်။ ။ students ဇယား (သို့မဟုတ် classes/class_arms ဇယား) ထဲတွင် academic_year ကော်လံ ရှိရပါမည်။
        $stmt = $conn->prepare("
            SELECT students.*, classes.class_name, class_arms.class_arm_name 
            FROM students 
            JOIN classes ON students.class_id = classes.id 
            JOIN class_arms ON students.class_arm_id = class_arms.id 
            WHERE students.class_id = ? AND students.class_arm_id = ?
            ORDER BY students.roll_no ASC
        ");
        $stmt->bind_param("ii", $selected_class, $selected_arm);
        $stmt->execute();
        $students_result = $stmt->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student List - Teacher</title>
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

    <div class="sidebar py-2">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <div class="menu-category">Students Management</div>
        <a href="view_student_list.php" class="active"><i class="fa-solid fa-users"></i> View Student List</a>
        <div class="menu-category">Attendance</div>
        <a href="manage_student_attendance.php"><i class="fa-regular fa-calendar-check"></i> Manage Monthly Student Attendance</a>
        <div class="menu-category">Academics</div>
        <a href="upload_course.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Course</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">View Student List</h3>
        </div>

        <div class="card-box">
            <div class="card-header-custom">Filter Students</div>
            <div class="p-4">
                <form method="POST">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-muted">Teacher Name</label>
                            <input type="text" name="teacher_name" class="form-control" placeholder="Enter Teacher Name" value="<?php echo htmlspecialchars($teacher_name); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-semibold text-muted">Select Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="" selected disabled>--Select Class--</option>
                                <?php 
                                $classes_result->data_seek(0);
                                while($c_row = $classes_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $c_row['id']; ?>" <?php echo ($selected_class == $c_row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c_row['class_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold text-muted">Select Section *</label>
                            <select name="class_arm_id" class="form-select" required>
                                <option value="" selected disabled>--Select Section--</option>
                                <?php 
                                $arms_result->data_seek(0);
                                while($arm_row = $arms_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $arm_row['id']; ?>" <?php echo ($selected_arm == $arm_row['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($arm_row['class_arm_name'] . ' (' . $arm_row['class_name'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="submit" name="view_students" class="btn btn-primary w-100" style="background-color: #3b5998; border-color: #3b5998;">
                                <i class="fa-solid fa-magnifying-glass me-2"></i> View
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-box">
            <div class="card-header-custom">All Students in Selected Class</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
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
                                <th>Academic Year</th> <!-- ထည့်သွင်းထားသော Academic Year Header -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students_result !== null): ?>
                                <?php if ($students_result->num_rows > 0): ?>
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
                                            <td><?php echo htmlspecialchars($row['academic_year'] ?? ''); ?></td> <!-- ထည့်သွင်းထားသော Academic Year Data -->
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center text-danger py-4">No students found.</td>
                                    </tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php // colspan ကို ၁၂ သို့ ပြောင်းထားပါသည် ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">Please select Class and Class Arm, then click View.</td>
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