<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));
    $role     = $_POST['role']; // Dropdown မှ ရွေးချယ်လိုက်သော Role (admin, teacher, student)

    if (empty($role)) {
        $error = "Please select a role!";
    } else {
        // Username, Password နှင့် Role တိုက်ဆိုင်မှု ရှိမရှိ စစ်ဆေးခြင်း
        $sql = "SELECT * FROM users WHERE username = ? AND password = ? AND role = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Role အလိုက် သက်ဆိုင်ရာ Dashboard သို့ ပို့ပေးခြင်း
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] == 'teacher') {
                header("Location: teacher/dashboard.php");
            } elseif ($user['role'] == 'student') {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Username, Password, or Selected Role!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Information and Academic Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 400px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .btn-login {
            background-color: #3a6cd9;
            border-color: #3a6cd9;
            color: white;
            font-weight: 500;
        }
        .btn-login:hover {
            background-color: #3a6cd9;
            border-color: #3a6cd9;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="card login-card p-4 bg-white">
        <h4 class="fw-bold mb-4 text-secondary text-center">System Login</h4>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter Username" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted fw-semibold">Login As</label>
                <select name="role" class="form-select" required>
                    <option value="" selected disabled>-- Select Role --</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
            </div>

            <button type="submit" class="btn btn-login w-100 py-2">Login</button>
        </form>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>