<?php
session_start();

// Check if user is logged in, else redirect to login page
if (!isset($_SESSION['recruiter_loggedin']) || $_SESSION['recruiter_loggedin'] !== true) {
    header("Location: index1.php");
    exit();
}

// Database connection parameters (reuse from index2.php)
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

// Fetch all job applications
$sql = "SELECT * FROM job_applications ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Submitted Job Applications - Backend</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #4a90e2;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #4a90e2;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        a.resume-link {
            color: #357ABD;
            text-decoration: none;
        }
        a.resume-link:hover {
            text-decoration: underline;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .logout-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #dc3545;
            font-weight: 600;
            text-decoration: none;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="index1.php?action=logout" class="logout-link">Logout</a>
    <h1>Submitted Job Applications</h1>
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Job ID</th>
                    <th>Applicant Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Resume</th>
                    <th>Cover Letter</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['job_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['applicant_email']); ?></td>
                    <td><?php echo htmlspecialchars($row['applicant_phone']); ?></td>
                    <td>
                        <?php if (!empty($row['resume_path'])): ?>
                            <a href="<?php echo htmlspecialchars($row['resume_path']); ?>" target="_blank" class="resume-link">View Resume</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?php echo nl2br(htmlspecialchars($row['cover_letter'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">No job applications found.</div>
    <?php endif; ?>
</body>
</html>
<?php
$conn->close();
?>
