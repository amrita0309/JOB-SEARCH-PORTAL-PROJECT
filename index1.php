<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'job_portal';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$valid_recruiter_username = 'recruiter';
$valid_recruiter_password = 'recruiter123';

$login_error = '';
$errors = [];
$success = '';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index1.php");
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $valid_recruiter_username && $password === $valid_recruiter_password) {
        $_SESSION['recruiter_loggedin'] = true;
        $_SESSION['recruiter_username'] = $username;
        header("Location: index1.php");
        exit();
    } else {
        $login_error = 'Invalid username or password.';
    }
}

// Handle job vacancy submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_job']) && isset($_SESSION['recruiter_loggedin']) && $_SESSION['recruiter_loggedin'] === true) {
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $salary_input = trim($_POST['salary'] ?? '');
    if ($salary_input === '' || !is_numeric($salary_input)) {
        $salary = null;
    } else {
        $salary = floatval($salary_input);
    }
    $number_of_candidates = trim($_POST['number_of_candidates'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$title) $errors[] = "Job title is required.";
    if (!$company) $errors[] = "Company name is required.";
    if (!$location) $errors[] = "Location is required.";
    if (!$description) $errors[] = "Job description is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO recruiter_jobs (title, company, location, salary, number_of_candidates, type, experience, skills, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($salary === null) {
            $stmt->bind_param("sssisssss", $title, $company, $location, $salary, $number_of_candidates, $type, $experience, $skills, $description);
        } else {
            $stmt->bind_param("sssisssss", $title, $company, $location, $salary, $number_of_candidates, $type, $experience, $skills, $description);
        }

        if ($stmt->execute()) {
            $success = "Job vacancy added successfully.";
        } else {
            $errors[] = "Failed to add job vacancy. Please try again.";
        }
        $stmt->close();
    }
}

$conn->close();

if (!isset($_SESSION['recruiter_loggedin']) || $_SESSION['recruiter_loggedin'] !== true):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recruiter Login - JobPortal Pro</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357ABD;
            --accent-color: #4a90e2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('https://wallpapercave.com/wp/wp2019265.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--light-color);
        }

        .login-wrapper {
            background: rgba(0, 0, 0, 0.6);
            padding: 3rem 2.5rem 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            width: 360px;
            text-align: center;
            position: relative;
        }

        .login-wrapper::before {
            content: '';
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 80px;
            background: var(--gradient);
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2.5rem;
            color: white;
            font-weight: 700;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper h2 {
            margin-top: 50px;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 1px;
            color: var(--light-color);
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-align: left;
            color: var(--light-color);
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            border-radius: 12px;
            border: 1px solid #999;
            font-size: 1rem;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
            outline: none;
            background-color: #222;
            color: var(--light-color);
        }

        input[type="text"]:focus, input[type="password"]:focus {
            box-shadow: 0 0 8px 2px var(--primary-color);
            border-color: var(--primary-color);
            background-color: #222;
            color: var(--light-color);
        }

        button {
            width: 100%;
            padding: 0.85rem;
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            color: var(--light-color);
            transition: background 0.3s ease;
            letter-spacing: 1px;
        }

        button:hover {
            background: var(--secondary-color);
            color: var(--light-color);
        }

        .error {
            color: #ff6b6b;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="logo">
            <i class="fas fa-briefcase"></i> 
            <span style="font-weight: 900; background: linear-gradient(135deg, #4a90e2 0%, #357ABD 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                JobPortal Pro
            </span>
        </div>
        <h2>Recruiter Login</h2>
        <?php if ($login_error): ?>
            <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        <form method="POST" action="index1.php" autocomplete="off">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus placeholder="Enter your username" />
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password" />
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
<?php
else:
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recruiter Dashboard - Add Job Vacancy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357ABD;
            --accent-color: #4a90e2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
            color: var(--dark-color);
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: var(--dark-color);
        }

        form input[type="text"],
        form textarea,
        form select {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            resize: vertical;
        }

        form textarea {
            min-height: 100px;
        }

        form button {
            margin-top: 20px;
            background: var(--gradient);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            transition: background 0.3s ease;
        }

        form button:hover {
            background: var(--secondary-color);
        }

        .error {
            background: #ff6b6b;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success {
            background: var(--success-color);
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .logout-link {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        header {
            background: white;
            padding: 1rem 0;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-briefcase"></i> JobPortal Pro
            </div>
            <a href="index1.php?action=logout" class="logout-link" style="color: #dc3545; font-weight: 700; margin-left: 20px;">Logout</a>
            <a href="applications.php" style="margin-left: 20px; padding: 10px 15px; background-color: var(--primary-color); color: white; border-radius: 8px; font-weight: 600; text-decoration: none;">View Applicants</a>
        </div>
    </header>
    <div class="container">
        <h1>Add New Job Vacancy</h1>
        <?php if ($errors): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="index1.php" autocomplete="off">
            <label for="title">Job Title *</label>
            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" />

            <label for="company">Company *</label>
            <input type="text" id="company" name="company" required value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>" />

            <label for="location">Location *</label>
            <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" />

            <label for="salary">Salary</label>
            <input type="text" id="salary" name="salary" value="<?php echo htmlspecialchars($_POST['salary'] ?? ''); ?>" />

            <label for="number_of_candidates">Number of Candidates Required</label>
            <input type="number" id="number_of_candidates" name="number_of_candidates" min="1" value="<?php echo htmlspecialchars($_POST['number_of_candidates'] ?? ''); ?>" />

            <label for="type">Job Type</label>
            <select id="type" name="type">
                <option value="">Select Type</option>
                <option value="Full-time" <?php echo (($_POST['type'] ?? '') === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                <option value="Part-time" <?php echo (($_POST['type'] ?? '') === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                <option value="Contract" <?php echo (($_POST['type'] ?? '') === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                <option value="Freelance" <?php echo (($_POST['type'] ?? '') === 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
            </select>

            <label for="experience">Experience</label>
            <input type="text" id="experience" name="experience" value="<?php echo htmlspecialchars($_POST['experience'] ?? ''); ?>" />

            <label for="skills">Skills (comma separated)</label>
            <input type="text" id="skills" name="skills" value="<?php echo htmlspecialchars($_POST['skills'] ?? ''); ?>" />

            <label for="description">Job Description *</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

            <button type="submit" name="submit_job">Add Job</button>
        </form>
    </div>
</body>
</html>
<?php
endif;
?>
