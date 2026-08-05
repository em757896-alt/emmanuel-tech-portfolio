<?php
// register.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Student</title>
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
            width:60%;
            margin:auto;
            padding:20px;
        }
        form{
            background:white;
            padding:20px;
            border-radius:5px;
            box-shadow:0px 0px 5px gray;
        }
        label{
            display:block;
            margin-top:10px;
        }
        input, select{
            width:100%;
            padding:8px;
            margin-top:5px;
            box-sizing:border-box;
        }
        button{
            margin-top:15px;
            padding:10px 15px;
            background:#003366;
            border:none;
            color:white;
            cursor:pointer;
        }
        button:hover{
            background:#0059b3;
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
    <h2>Register New Student</h2>
    <form action="process_register.php" method="post">
        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Phone</label>
        <input type="text" name="phone" required>

        <label>Course</label>
        <input type="text" name="course" required>

        <label>Department</label>
        <input type="text" name="department" required>

        <button type="submit">Register Student</button>
    </form>
</div>

<div class="footer">
    &copy; 2026 Student Management System | Created by Elevate Media Productions<br>
    WhatsApp: +254 775 333 673 | Call: +254 111 275 630 | Email: em757896@gmail.com
</div>

</body>
</html>