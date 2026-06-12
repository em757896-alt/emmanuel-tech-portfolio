<?php
// view.php
require 'db.php';

$result = $conn->query("SELECT * FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Students</title>
    <style>
        body{
            margin:0;
            font-family:Arial, sans-serif;
            background:#f4f4f4;
        }
        .header{
            background:#003366;
            color:white;
            padding:20px;
            text-align:center;
        }
        .menu{
            background:#0059b3;
            overflow:hidden;
        }
        .menu a{
            float:left;
            color:white;
            text-decoration:none;
            padding:14px 20px;
        }
        .menu a:hover{
            background:#003366;
        }
        .container{
            width:90%;
            margin:auto;
            padding:20px;
        }
        table{
            width:100%;
            border-collapse:collapse;
            background:white;
            box-shadow:0px 0px 5px gray;
        }
        th, td{
            padding:10px;
            border:1px solid #ddd;
            text-align:left;
        }
        th{
            background:#003366;
            color:white;
        }
        .msg{
            margin-bottom:10px;
            color:green;
            font-weight:bold;
        }
        .delete-link{
            color:red;
            text-decoration:none;
        }
        .footer{
            clear:both;
            background:#003366;
            color:white;
            text-align:center;
            padding:15px;
            margin-top:30px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>STUDENT MANAGEMENT SYSTEM</h1>
</div>

<div class="menu">
    <a href="home.php">Home</a>
    <a href="register.php">Register Student</a>
    <a href="view.php">View Students</a>
    <a href="search.php">Search Student</a>
    <a href="login.php">Login</a>
</div>

<div class="container">
    <h2>All Registered Students</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="msg"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Course</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                    <td>
                        <a class="delete-link"
                           href="delete_student.php?id=<?php echo $row['id']; ?>"
                           onclick="return confirm('Delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No students found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div class="footer">
    &copy; 2026 Student Management System
</div>

</body>
</html>