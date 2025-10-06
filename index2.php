<?php
session_start();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy the session and redirect to login page
    session_unset();
    session_destroy();
    header("Location: index2.php");
    exit();
}

$valid_username = 'admin';
$valid_password = 'admin123';

$login_error = '';

// Database connection parameters
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'job_portal';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle job application form submission
$application_success = '';
$application_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['applicant_name'])) {
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $applicant_name = $conn->real_escape_string(trim($_POST['applicant_name']));
    $applicant_email = $conn->real_escape_string(trim($_POST['applicant_email']));
    $applicant_phone = $conn->real_escape_string(trim($_POST['applicant_phone']));
    $applicant_cover_letter = $conn->real_escape_string(trim($_POST['applicant_cover_letter']));

    // Handle file upload
    if (isset($_FILES['applicant_resume']) && $_FILES['applicant_resume']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_tmp = $_FILES['applicant_resume']['tmp_name'];
        $file_name = basename($_FILES['applicant_resume']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid('resume_') . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                // Insert application data into database
                $stmt = $conn->prepare("INSERT INTO job_applications (job_id, applicant_name, applicant_email, applicant_phone, resume_path, cover_letter) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $job_id, $applicant_name, $applicant_email, $applicant_phone, $target_file, $applicant_cover_letter);

                if ($stmt->execute()) {
                    // Decrement number_of_candidates in recruiter_jobs table
                    $update_stmt = $conn->prepare("UPDATE recruiter_jobs SET number_of_candidates = number_of_candidates - 1 WHERE id = ? AND number_of_candidates > 0");
                    $update_stmt->bind_param("i", $job_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    $application_success = "Your application has been submitted successfully.";
                } else {
                    $application_error = "Failed to submit application. Please try again.";
                }
                $stmt->close();
            } else {
                $application_error = "Failed to upload resume file.";
            }
        } else {
            $application_error = "Invalid file type. Only PDF, DOC, and DOCX are allowed.";
        }
    } else {
        $application_error = "Please upload your resume.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: index2.php");
        exit();
    } else {
        $login_error = 'Invalid username or password.';
    }
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - JobPortal Pro</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: url('https://wallpapercave.com/wp/wp2019265.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
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
            background: linear-gradient(135deg, #4a90e2 0%, #357ABD 100%);
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

        .login-wrapper h1 {
            margin-top: 50px;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 1px;
            color: #f0f0f0;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-align: left;
            color: #ddd;
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
        }

        input[type="text"]:focus, input[type="password"]:focus {
            box-shadow: 0 0 8px 2px #4a90e2;
            border-color: #4a90e2;
            background-color: #222;
            color: #fff;
        }

        button {
            width: 100%;
            padding: 0.85rem;
            background: #4a90e2;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            transition: background 0.3s ease;
            letter-spacing: 1px;
        }

        button:hover {
            background: #357ABD;
            color: #fff;
        }

        .error {
            color: #ff6b6b;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #4a90e2 0%, #357ABD 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="logo"><i class="fas fa-briefcase"></i> JobPortal Pro</div>
        <h1>Login to Your Account</h1>
        <?php if ($login_error): ?>
            <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        <form method="POST" action="index2.php" autocomplete="off">
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
exit();
endif;

// Fetch job vacancies from recruiter_jobs table
$dynamic_jobs = [];
$result = $conn->query("SELECT id, title, company, location, salary, number_of_candidates, type, experience, skills, description FROM recruiter_jobs WHERE number_of_candidates > 0");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dynamic_jobs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'company' => $row['company'],
            'location' => $row['location'],
            'salary' => $row['salary'],
            'number_of_candidates' => $row['number_of_candidates'],
            'type' => $row['type'],
            'experience' => $row['experience'],
            'skills' => $row['skills'],
            'description' => $row['description'],
            'posted' => 'Just now' // or you can add a posted date column in DB
        ];
    }
}

// Fetch jobs for jobs page into $new_jobs
$new_jobs = [];
$result_new = $conn->query("SELECT id, title, company, location, salary, number_of_candidates, type, experience, skills, description FROM recruiter_jobs WHERE number_of_candidates > 0");
if ($result_new) {
    while ($row = $result_new->fetch_assoc()) {
        $new_jobs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'company' => $row['company'],
            'location' => $row['location'],
            'salary' => $row['salary'],
            'number_of_candidates' => $row['number_of_candidates'],
            'type' => $row['type'],
            'experience' => $row['experience'],
            'skills' => $row['skills'],
            'description' => $row['description'],
            'posted' => 'Just now'
        ];
    }
}

// Ensure $jobs is defined as an array before merging
if (!isset($jobs) || !is_array($jobs)) {
    $jobs = [];
}
// Merge dynamic jobs with existing jobs arrays
$jobs = array_merge($dynamic_jobs, $jobs);

$search_query = '';
$location_filter = '';
$type_filter = '';
$filtered_jobs = $jobs;

if ($_GET) {
    $search_query = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
    $location_filter = isset($_GET['location']) ? trim($_GET['location']) : '';
    $type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
    
    if (!empty($search_query) || !empty($location_filter) || !empty($type_filter)) {
        $filtered_jobs = array_filter($jobs, function($job) use ($search_query, $location_filter, $type_filter) {
            $title_match = empty($search_query) || strpos(strtolower($job['title']), $search_query) !== false;
            $skills_match = empty($search_query) || strpos(strtolower($job['skills']), $search_query) !== false;
            $company_match = empty($search_query) || strpos(strtolower($job['company']), $search_query) !== false;
            $search_match = $title_match || $skills_match || $company_match;
            
            $location_match = empty($location_filter) || strpos(strtolower($job['location']), strtolower($location_filter)) !== false;
            $type_match = empty($type_filter) || $job['type'] === $type_filter;
            
            return $search_match && $location_match && $type_match;
        });
    }
}

$total_jobs = isset($filtered_jobs) ? count($filtered_jobs) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobPortal Pro - Find Your Dream Job</title>
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

        /* Fade transition styles for sections */
        .fade {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            pointer-events: none;
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
        }

        .fade.show {
            opacity: 1;
            pointer-events: auto;
            position: relative;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            color: var(--dark-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 4rem 0;
            color: #4a90e2;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Search Section */
        .search-section {
            background: white;
            margin: -2rem auto 2rem;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow);
            max-width: 900px;
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
        }

        /* Results Section */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0 1rem;
            color: white;
        }

        .results-count {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .view-toggle {
            display: flex;
            gap: 0.5rem;
        }

        .view-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .view-btn.active,
        .view-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Job Cards */
        .jobs-container {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .jobs-grid {
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        }

        .jobs-list {
            grid-template-columns: 1fr;
        }

        .job-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .job-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .job-type {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .job-company {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .job-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .job-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .job-detail i {
            color: var(--primary-color);
            width: 16px;
        }

        .job-skills {
            margin-bottom: 1rem;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .skill-tag {
            background: #f8f9fa;
            color: var(--dark-color);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            border: 1px solid #e1e8ed;
        }

        .job-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .job-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .job-posted {
            font-size: 0.9rem;
            color: #999;
        }

        .apply-btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: white;
        }

        .more-job-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.3s ease;
        }

        .more-job-btn:hover {
            background: var(--primary-color);
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Footer */
        footer {
            background: #4a90e2;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }

            .results-header {
                flex-direction: column;
                gap: 1rem;
            }

            .job-details {
                grid-template-columns: 1fr;
            }

            .job-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }

            .hero {
                padding: 2rem 0;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .search-section {
                margin: -1rem auto 1rem;
                padding: 1rem;
            }

            .job-card {
                padding: 1rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
            color: white;
        }

        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-briefcase"></i> JobPortal Pro
                </div>
                <nav>
                    <ul class="nav-links">
                        <li><a href="#" id="homeLink" class="active">Home</a></li>
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            
                        <?php endif; ?>
                        <li><a href="#" id="jobsLink">Jobs</a></li>
                    <li><a href="#" id="aboutToggle">About</a></li>
                    <li><a href="#" id="contactToggle">Contact</a></li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li><a href="index2.php?action=logout" style="color: var(--danger-color); font-weight: 600;">Logout</a></li>
                    <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div id="homeSection">
            <section class="hero">
                <div class="container">
                    <h1>Find Your Dream Job</h1>
                    <p>Discover thousands of job opportunities from top companies</p>
                </div>
            </section>

            <div class="container">
                <div class="search-section">
                    <form class="search-form" method="GET" id="searchForm">
                        <div class="form-group">
                            <label for="search">Job Title or Keywords</label>
                            <input type="text" id="search" name="search" placeholder="e.g. Frontend Developer" 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="e.g. Mumbai" 
                                   value="<?php echo htmlspecialchars($location_filter); ?>">
                        </div>
                        <div class="form-group">
                            <label for="type">Job Type</label>
                            <select id="type" name="type">
                                <option value="">All Types</option>
                                <option value="Full-time" <?php echo $type_filter === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                <option value="Part-time" <?php echo $type_filter === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="Contract" <?php echo $type_filter === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                <option value="Freelance" <?php echo $type_filter === 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                            </select>
                        </div>
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Searching for jobs...</p>
            </div>

            <div class="results-header">
                <div class="results-count">
                    Found <?php echo $total_jobs; ?> job<?php echo $total_jobs !== 1 ? 's' : ''; ?>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" onclick="toggleView('grid')" id="gridBtn">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" onclick="toggleView('list')" id="listBtn">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <div class="jobs-container jobs-grid" id="jobsContainer">
                <?php if (empty($filtered_jobs)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h2>No jobs found</h2>
                        <p>Try adjusting your search criteria</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($filtered_jobs as $job): ?>
                        <div class="job-card">
                            <div class="job-header">
                                <div>
                                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                                    <div class="job-company"><?php echo htmlspecialchars($job['company']); ?></div>
                                </div>
                                <div class="job-type<?php if ($job['title'] === 'Cloud Solutions Architect') echo ' cloud-solutions-architect'; ?>"><?php echo htmlspecialchars(trim($job['type'])); ?></div>
                            </div>

                            <div class="job-details">
                                <div class="job-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($job['location']); ?></span>
                                </div>
                                <div class="job-detail">
                                    <i class="fas fa-rupee-sign"></i>
                                    <span><?php echo htmlspecialchars($job['salary']); ?></span>
                                </div>
                                <div class="job-detail">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo htmlspecialchars($job['number_of_candidates']); ?> candidates required</span>
                                </div>
                                <div class="job-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo htmlspecialchars($job['experience']); ?></span>
                                </div>
                            </div>

                            <div class="job-skills">
                                <strong>Required Skills:</strong>
                                <div class="skills-list">
                                    <?php 
                                    $skills = explode(', ', $job['skills']);
                                    foreach ($skills as $skill): 
                                    ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="job-description">
                                <?php echo htmlspecialchars($job['description']); ?>
                            </div>

                            <div class="job-footer">
                                <div class="job-posted">
                                    <i class="fas fa-calendar-alt"></i>
                                    Posted <?php echo htmlspecialchars($job['posted']); ?>
                                </div>
                                <button class="apply-btn" onclick="applyForJob(<?php echo $job['id']; ?>)">
                                    <i class="fas fa-paper-plane"></i> Apply Now
                                </button>
                                <button class="more-job-btn" onclick="toggleMoreDetails(<?php echo $job['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> More in Job
                                </button>
                            </div>
                            <div class="more-job-details" id="moreDetails-<?php echo $job['id']; ?>" style="display:none; margin-top: 1rem; color: #444;">
                                <strong>Full Job Description:</strong>
                                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                                <p><strong>Skills Required:</strong> <?php echo htmlspecialchars($job['skills']); ?></p>
                                <p><strong>Experience:</strong> <?php echo htmlspecialchars($job['experience']); ?></p>
                                <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="jobsSection" style="display:none;">
            <div class="container">
                <!-- Removed the "New Job Listings" heading as requested -->
                <div class="jobs-container jobs-grid" id="newJobsContainer">
                    <?php if (empty($new_jobs)): ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h2>No new jobs found</h2>
                            <p>Try adjusting your search criteria</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($new_jobs as $job): ?>
                            <div class="job-card">
                                <div class="job-header">
                                    <div>
                                        <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                                        <div class="job-company"><?php echo htmlspecialchars($job['company']); ?></div>
                                    </div>
                                    <div class="job-type"><?php echo htmlspecialchars($job['type']); ?></div>
                                </div>

                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($job['location']); ?></span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-rupee-sign"></i>
                                        <span><?php echo htmlspecialchars($job['salary']); ?></span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo htmlspecialchars($job['number_of_candidates']); ?> candidates required</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($job['experience']); ?></span>
                                    </div>
                                </div>

                                <div class="job-skills">
                                    <strong>Required Skills:</strong>
                                    <div class="skills-list">
                                        <?php 
                                        $skills = explode(', ', $job['skills']);
                                        foreach ($skills as $skill): 
                                        ?>
                                            <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="job-description">
                                    <?php echo htmlspecialchars($job['description']); ?>
                                </div>

                                <div class="job-footer">
                                    <div class="job-posted">
                                        <i class="fas fa-calendar-alt"></i>
                                        Posted <?php echo htmlspecialchars($job['posted']); ?>
                                    </div>
                                    <button class="apply-btn" onclick="applyForJob(<?php echo $job['id']; ?>)">
                                        <i class="fas fa-paper-plane"></i> Apply Now
                                    </button>
                                    <button class="more-job-btn" onclick="toggleMoreDetails(<?php echo $job['id']; ?>)">
                                        <i class="fas fa-info-circle"></i> More in Job
                                    </button>
                                </div>
                                <div class="more-job-details" id="moreDetails-<?php echo $job['id']; ?>" style="display:none; margin-top: 1rem; color: #444;">
                                    <strong>Full Job Description:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                                    <p><strong>Skills Required:</strong> <?php echo htmlspecialchars($job['skills']); ?></p>
                                    <p><strong>Experience:</strong> <?php echo htmlspecialchars($job['experience']); ?></p>
                                    <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div id="aboutBox" style="display:none; background: #f0f4f8; color: #2c3e50; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 1rem 2rem; max-width: 900px; margin: 0 auto 1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; margin-bottom: 0.5rem;">About JobPortal Pro</h2>
        <p>
            At JobPortal Pro, we connect job seekers with the best career opportunities and help employers find the perfect candidates. Our mission is to streamline the job search and hiring process through a user-friendly platform that delivers real results. Whether you're a fresh graduate looking for your first job or a seasoned professional aiming for your next big career move, we are here to support your journey every step of the way. We partner with trusted companies across industries and offer features like resume uploads, job alerts, company reviews, and easy application tracking — all designed to make your job search efficient and successful.
        </p>
    </div>

    <div id="contactBox" style="display:none; background: #f0f4f8; color: #2c3e50; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 1rem 2rem; max-width: 900px; margin: 0 auto 1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; margin-bottom: 0.5rem;">Get in Touch</h2>
        <p>Have questions, suggestions, or need support? We’re here to help!</p>
        <p>📧 Email: support@jobportalpro.com</p>
        <p>☎️ Phone: +91-8235430978</p>
        <p>📍 Address: Jamshedpur, Jharkhand</p>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 JobPortal Pro. All rights reserved. | Built with PHP, HTML, CSS & JavaScript</p>
        </div>
    </footer>

    <script>
        // Navigation toggle between Home and Jobs sections
        document.getElementById('homeLink').addEventListener('click', function(e) {
            e.preventDefault();
            const homeSection = document.getElementById('homeSection');
            const jobsSection = document.getElementById('jobsSection');
            if (homeSection.classList.contains('show')) return; // already visible

            // Fade out jobsSection, fade in homeSection
            jobsSection.classList.remove('show');
            setTimeout(() => {
                jobsSection.style.display = 'none';
                homeSection.style.display = 'block';
                setTimeout(() => {
                    homeSection.classList.add('show');
                }, 20);
            }, 500);

            document.getElementById('aboutBox').style.display = 'none';
            document.getElementById('contactBox').style.display = 'none';

            this.classList.add('active');
            document.getElementById('jobsLink').classList.remove('active');
            document.getElementById('aboutToggle').classList.remove('active');
            document.getElementById('contactToggle').classList.remove('active');
        });

        document.getElementById('jobsLink').addEventListener('click', function(e) {
            e.preventDefault();
            const homeSection = document.getElementById('homeSection');
            const jobsSection = document.getElementById('jobsSection');
            if (jobsSection.classList.contains('show')) return; // already visible

            // Fade out homeSection, fade in jobsSection
            homeSection.classList.remove('show');
            setTimeout(() => {
                homeSection.style.display = 'none';
                jobsSection.style.display = 'block';
                setTimeout(() => {
                    jobsSection.classList.add('show');
                }, 20);
            }, 500);

            document.getElementById('aboutBox').style.display = 'none';
            document.getElementById('contactBox').style.display = 'none';

            this.classList.add('active');
            document.getElementById('homeLink').classList.remove('active');
            document.getElementById('aboutToggle').classList.remove('active');
            document.getElementById('contactToggle').classList.remove('active');
        });

        // View toggle functionality
        function toggleView(view) {
            const container = document.getElementById('jobsContainer');
            const gridBtn = document.getElementById('gridBtn');
            const listBtn = document.getElementById('listBtn');

            if (view === 'grid') {
                container.className = 'jobs-container jobs-grid';
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
            } else {
                container.className = 'jobs-container jobs-list';
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
            }
        }

        // Removed About and Contact toggle event listeners as per user request

        // About toggle active state
        document.getElementById('aboutToggle').addEventListener('click', function(e) {
            e.preventDefault();
            const aboutBox = document.getElementById('aboutBox');
            const homeSection = document.getElementById('homeSection');
            const jobsSection = document.getElementById('jobsSection');
            if (homeSection.style.display === 'block') {
                if (aboutBox.style.display === 'none' || aboutBox.style.display === '') {
                    aboutBox.style.display = 'block';
                    aboutBox.scrollIntoView({ behavior: 'smooth' });
                } else {
                    aboutBox.style.display = 'none';
                }
            } else if (jobsSection.style.display === 'block') {
                // Switch to home section and show aboutBox
                homeSection.style.display = 'block';
                jobsSection.style.display = 'none';
                aboutBox.style.display = 'block';
                aboutBox.scrollIntoView({ behavior: 'smooth' });
                // Update active classes on nav links
                document.getElementById('homeLink').classList.add('active');
                document.getElementById('jobsLink').classList.remove('active');
                document.getElementById('aboutToggle').classList.add('active');
                document.getElementById('contactToggle').classList.remove('active');
            }
        });

        // Contact toggle active state
        document.getElementById('contactToggle').addEventListener('click', function(e) {
            e.preventDefault();
            const contactBox = document.getElementById('contactBox');
            const homeSection = document.getElementById('homeSection');
            const jobsSection = document.getElementById('jobsSection');
            if (homeSection.style.display === 'block') {
                if (contactBox.style.display === 'none' || contactBox.style.display === '') {
                    contactBox.style.display = 'block';
                    contactBox.scrollIntoView({ behavior: 'smooth' });
                } else {
                    contactBox.style.display = 'none';
                }
            } else if (jobsSection.style.display === 'block') {
                // Switch to home section and show contactBox
                homeSection.style.display = 'block';
                jobsSection.style.display = 'none';
                contactBox.style.display = 'block';
                contactBox.scrollIntoView({ behavior: 'smooth' });
                // Update active classes on nav links
                document.getElementById('homeLink').classList.add('active');
                document.getElementById('jobsLink').classList.remove('active');
                document.getElementById('contactToggle').classList.add('active');
                document.getElementById('aboutToggle').classList.remove('active');
            }
        });

        // Apply for job functionality
        function applyForJob(jobId) {
            // Open the application modal and set the job ID
            const modal = document.getElementById('applicationModal');
            modal.style.display = 'block';
            document.getElementById('jobIdInput').value = jobId;
        }

        // Toggle more job details
        function toggleMoreDetails(jobId) {
            console.log('toggleMoreDetails called for jobId:', jobId);
            const details = document.getElementById('moreDetails-' + jobId);
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }
    </script>

    <!-- Application Form Modal -->
    <div id="applicationModal" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6);">
        <div style="background-color: #fff; margin: 5% auto; padding: 20px; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <span id="closeModal" style="position: absolute; top: 15px; right: 20px; font-size: 28px; font-weight: bold; color: #333; cursor: pointer;">&times;</span>
            <h2 style="margin-bottom: 1rem; color: var(--dark-color);">Job Application Form</h2>
            <form id="applicationForm" method="POST" action="index2.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" name="job_id" id="jobIdInput" value="">
                <label for="applicantName">Full Name</label>
                <input type="text" id="applicantName" name="applicant_name" required placeholder="Enter your full name" style="padding: 0.75rem; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem;">
                
                <label for="applicantEmail">Email Address</label>
                <input type="email" id="applicantEmail" name="applicant_email" required placeholder="Enter your email address" style="padding: 0.75rem; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem;">
                
                <label for="applicantPhone">Phone Number</label>
                <input type="tel" id="applicantPhone" name="applicant_phone" required placeholder="Enter your phone number" style="padding: 0.75rem; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem;">
                
                <label for="applicantResume">Upload Resume (PDF, DOC, DOCX)</label>
                <input type="file" id="applicantResume" name="applicant_resume" accept=".pdf,.doc,.docx" required style="font-size: 1rem;">
                
                <label for="applicantCoverLetter">Cover Letter</label>
                <textarea id="applicantCoverLetter" name="applicant_cover_letter" rows="4" placeholder="Write your cover letter here..." style="padding: 0.75rem; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem;"></textarea>
                
                <button type="submit" style="background: var(--gradient); color: white; border: none; padding: 0.85rem; border-radius: 12px; font-size: 1.1rem; font-weight: 700; cursor: pointer; letter-spacing: 1px;">Submit Application</button>
            </form>
        </div>
    </div>
    <script>
        // Removed About and Contact toggle active state event listeners as per user request

            // Close modal when clicking the close button
            document.getElementById('closeModal').addEventListener('click', function() {
                document.getElementById('applicationModal').style.display = 'none';
                document.getElementById('applicationForm').reset();
            });

            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('applicationModal');
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.getElementById('applicationForm').reset();
                }
            });
        </script>

        <?php if ($application_success): ?>
            <script>
                alert("<?php echo addslashes($application_success); ?>");
            </script>
        <?php elseif ($application_error): ?>
            <script>
                alert("<?php echo addslashes($application_error); ?>");
            </script>
        <?php endif; ?>
    </body>
    </html>
