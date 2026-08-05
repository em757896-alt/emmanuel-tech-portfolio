<?php
// home.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Management System</title>
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

        .welcome{
            background:white;
            padding:20px;
            border-radius:5px;
            margin-bottom:20px;
        }

        .card{
            width:22%;
            background:white;
            float:left;
            margin:1%;
            padding:20px;
            text-align:center;
            border-radius:5px;
            box-shadow:0px 0px 5px gray;
        }

        .card h2{
            color:#003366;
        }

        .footer{
            clear:both;
            background:#003366;
            color:white;
            text-align:center;
            padding:15px;
            margin-top:30px;
        }

        .marquee-box{
            background:linear-gradient(90deg,#003366,#0059b3,#003366);
            color:white;
            padding:12px;
            border-top:4px solid gold;
            border-bottom:4px solid gold;
            font-size:22px;
            font-weight:bold;
            text-shadow:2px 2px 4px black;
            box-shadow:0px 3px 8px gray;
        }

        .marquee-box marquee{
            letter-spacing:2px;
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

<div class="marquee-box">
    <marquee scrollamount="8">
        🌟 WELCOME TO THE STUDENT MANAGEMENT SYSTEM 🌟
        ★ REGISTER STUDENTS ★
        ★ SEARCH RECORDS ★
        ★ UPDATE INFORMATION ★
        ★ MANAGE ACADEMIC DATA EASILY ★
        🌟 YOUR FUTURE STARTS HERE 🌟
    </marquee>
</div>

<div class="container">
    <div class="welcome">
        <h2>Welcome</h2>
        <p>
            This Student Management System allows users to
            register students, manage records, search student
            information and maintain academic data efficiently.
        </p>
    </div>

    <div class="card">
        <h2>100</h2>
        <p>Total Students</p>
    </div>

    <div class="card">
        <h2>10</h2>
        <p>Courses</p>
    </div>

    <div class="card">
        <h2>5</h2>
        <p>Departments</p>
    </div>

    <div class="card">
        <h2>15</h2>
        <p>Lecturers</p>
    </div>
</div>

<div class="footer">
    &copy; 2026 Student Management System | Created by Elevate Media Productions<br>
    WhatsApp: +254 775 333 673 | Call: +254 111 275 630 | Email: em757896@gmail.com
</div>

</body>
</html>