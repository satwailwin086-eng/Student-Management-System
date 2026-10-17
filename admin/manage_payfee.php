<?php
session_start();
include '../db.php';

// Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../unauthorized.php");
    exit();
}

// 1. Fee Payment ထည့်ခြင်း (သို့) ပြင်ဆင်ခြင်းအတွက် Variable များ
$edit_state = false;
$edit_id = 0;
$edit_student_id = '';
$edit_class_id = '';
$edit_amount = '';
$edit_date = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_fee'])) {
        $student_id = $_POST['student_id'];
        $class_id = $_POST['class_id'];
        $amount = $_POST['amount'];
        $date = $_POST['date'];

        $stmt = $conn->prepare("INSERT INTO pay_fees (student_id, class_id, paid_amount, payment_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $student_id, $class_id, $amount, $date);
        $stmt->execute();
        header("Location: manage_payfee.php");
        exit();
    } elseif (isset($_POST['update_fee'])) {
        $edit_id = $_POST['id'];
        $student_id = $_POST['student_id'];
        $class_id = $_POST['class_id'];
        $amount = $_POST['amount'];
        $date = $_POST['date'];

        $stmt = $conn->prepare("UPDATE pay_fees SET student_id = ?, class_id = ?, paid_amount = ?, payment_date = ? WHERE id = ?");
        $stmt->bind_param("iiisi", $student_id, $class_id, $amount, $date, $edit_id);
        $stmt->execute();
        header("Location: manage_payfee.php");
        exit();
    }
}

// Edit လုပ်ရန် Data ခေါ်ယူခြင်း
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_state = true;
    $record = $conn->query("SELECT * FROM pay_fees WHERE id = $edit_id")->fetch_assoc();
    if ($record) {
        $edit_student_id = $record['student_id'];
        $edit_class_id = $record['class_id'];
        $edit_amount = $record['paid_amount'];
        $edit_date = $record['payment_date'];
    }
}

// 2. Delete Record
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM pay_fees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_payfee.php");
    exit();
}

// Data Fetching (students ဇယားမှ academic_year ပါ တစ်ပါတည်းခေါ်ယူခြင်း)
$students = $conn->query("SELECT * FROM students");
$classes = $conn->query("SELECT * FROM classes");
$fees = $conn->query("SELECT pay_fees.*, students.student_name, students.academic_year, classes.class_name FROM pay_fees 
                    JOIN students ON pay_fees.student_id = students.id 
                    JOIN classes ON pay_fees.class_id = classes.id ORDER BY pay_fees.id ASC");
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payment</title>
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
                <i class="fa-solid fa-user-circle me-1"></i> Welcome, admin
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
        <a href="manage_student.php"><i class="fa-solid fa-user-graduate"></i> Manage Student</a>
        <a href="manage_payfee.php" class="active"><i class="fa-solid fa-file-invoice-dollar"></i> Manage Payment</a>
        
        <div class="menu-category">Attendance</div>
        <a href="view_student_attendance.php"><i class="fa-solid fa-calendar-check"></i> View Monthly Student Attendance</a>
        
        <div class="menu-category">Exam Management</div>
        <a href="manage_academic_year.php"><i class="fa-solid fa-calendar-days"></i>Manage Academic Year</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Manage Payment</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">Manage Payment</li>
                </ol>
            </nav>
        </div>

        <!-- Form Card -->
        <div class="card-box">
            <div class="card-header-custom">
                <?php echo $edit_state ? "Edit Fee Payment" : "Add Fee Payment"; ?>
            </div>
            <div class="p-4">
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold text-muted">Select Student *</label>
                            <select name="student_id" class="form-select" required>
                                <option value="" disabled <?php echo !$edit_state ? 'selected' : ''; ?>>--Select Student--</option>
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
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold text-muted">Select Class *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="" disabled <?php echo !$edit_state ? 'selected' : ''; ?>>--Select Class--</option>
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
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold text-muted">Paid Amount *</label>
                            <input type="number" name="amount" class="form-control" placeholder="Enter Amount" value="<?php echo $edit_amount; ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold text-muted">Date *</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $edit_date; ?>" required>
                        </div>
                    </div>
                    <?php if ($edit_state): ?>
                        <button type="submit" name="update_fee" class="btn btn-success px-4">Update</button>
                        <a href="manage_payfee.php" class="btn btn-secondary px-3 ms-2">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="save_fee" class="btn btn-primary px-4" style="background-color: #3b5998; border-color: #3b5998;">Save</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card-box">
            <div class="card-header-custom">Fee Payment Records</div>
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Academic Year</th>
                                <th>Paid Amount</th>
                                <th>Date</th>
                                <th width="9%" class="text-center">Edit</th>
                                <th width="9%" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            if ($fees && $fees->num_rows > 0):
                                while($row = $fees->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['academic_year'] ?? ''); ?></td>
                                    <td><?php echo number_format($row['paid_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                                    <td class="text-center">
                                        <a href="manage_payfee.php?edit=<?php echo $row['id']; ?>" class="text-decoration-none text-primary fw-medium" title="Edit">
                                            <i class="fa-regular fa-pen-to-square me-1"></i> Edit
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="manage_payfee.php?delete=<?php echo $row['id']; ?>" class="text-decoration-none text-danger fw-medium" title="Delete" onclick="return confirm('Are you sure you want to delete this record?');">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">No payment records found.</td>
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