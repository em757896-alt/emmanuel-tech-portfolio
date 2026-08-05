<?php
// search.php
require 'db.php';

$query = "";
$result = null;

if (isset($_GET['q']) && $_GET['q'] !== "") {
    $query = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT * FROM students
            WHERE first_name LIKE '%$query%'
               OR last_name LIKE '%$query%'
               OR email LIKE '%$query%'
               OR course LIKE '%$query%'
               OR department LIKE '%$query%'";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Student</title>
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
        form{
            margin-bottom:20px;
        }
        input[type="text"]{
            width:70%;
            padding:8px;
        }
        button{
            padding:8px 15px;
            background:#003366;
            color:white;
            border:none;
            cursor:pointer;
        }
        button:hover{
            background:#0059b3;
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
    <h2>Search Students</h2>

    <form method="get" action="search.php">
        <input type="text" name="q" placeholder="Search by name, email, course, department"
               value="<?php echo htmlspecialchars($query); ?>">
        <button type="submit">Search</button>
    </form>

    <?php if ($result !== null): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Department</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No results found.</td></tr>
            <?php endif; ?>
        </table>
    <?php endif; ?>
</div>

<div class="footer">
    &copy; 2026 Student Management System | Created by Elevate Media Productions<br>
    WhatsApp: +254 775 333 673 | Call: +254 111 275 630 | Email: em757896@gmail.com
</div>

</body>
</html>