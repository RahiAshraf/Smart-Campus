    <?php

    include('../config/auth.php');
    include('../config/db.php');

    $total_sessions = mysqli_fetch_assoc(

    mysqli_query(

    $conn,

    "SELECT COUNT(*) total
    FROM attendance"

    )

    )['total'];

    $today_sessions = mysqli_fetch_assoc(

    mysqli_query(

    $conn,

    "SELECT COUNT(*) total
    FROM attendance
    WHERE attendance_date = CURDATE()"

    )

    )['total'];

    $this_month_sessions = mysqli_fetch_assoc(

    mysqli_query(

    $conn,

    "SELECT COUNT(*) total

    FROM attendance

    WHERE

    MONTH(attendance_date)=MONTH(CURDATE())

    AND

    YEAR(attendance_date)=YEAR(CURDATE())"

    )

    )['total'];

    $total_students = mysqli_fetch_assoc(

    mysqli_query(

    $conn,

    "SELECT COUNT(*) total
    FROM students"

    )

    )['total'];

    $students_covered = mysqli_fetch_assoc(

    mysqli_query(

    $conn,

    "SELECT COUNT(DISTINCT student_id) total
    FROM attendance_records"

    )

    )['total'];

    $recent_attendance = mysqli_query(

    $conn,

    "SELECT

    a.*,

    c.class_name,

    s.section_name,

    p.period_name,

    sub.subject_name

    FROM attendance a

    LEFT JOIN classes c
    ON a.class_id = c.class_id

    LEFT JOIN sections s
    ON a.section_id = s.section_id

    LEFT JOIN periods p
    ON a.period_id = p.period_id

    LEFT JOIN subjects sub
    ON a.subject_id = sub.subject_id

    ORDER BY

    a.attendance_date DESC,
    a.attendance_id DESC

    LIMIT 20"

    );
    $attendance_counts = [];

    $count_query = mysqli_query(

    $conn,

    "SELECT

    attendance_id,

    COUNT(*) total

    FROM attendance_records

    GROUP BY attendance_id"

    );

    while(

    $count_row =
    mysqli_fetch_assoc(
    $count_query
    )

    ){

    $attendance_counts[
    $count_row['attendance_id']
    ] = $count_row['total'];

    }

 $overall_present = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM attendance_records

WHERE status != 'absent'"

)

)['total'];

$overall_total = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM attendance_records"

)

)['total'];

$overall_attendance_rate = 0;

if($overall_total > 0){

$overall_attendance_rate = round(

($overall_present / $overall_total) * 100,

2

);

}

    $today_present = mysqli_fetch_assoc(

        mysqli_query(

            $conn,

            "SELECT COUNT(*) total

            FROM attendance_records ar

            JOIN attendance a
            ON ar.attendance_id=a.attendance_id

            WHERE

            a.attendance_date=CURDATE()

            AND ar.status != 'absent'"

        )

    )['total'];

    $today_total = mysqli_fetch_assoc(

        mysqli_query(

            $conn,

            "SELECT COUNT(*) total

            FROM attendance_records ar

            JOIN attendance a
            ON ar.attendance_id=a.attendance_id

            WHERE

            a.attendance_date=CURDATE()"

        )

    )['total'];

    $today_attendance_rate = 0;

    if($today_total > 0){

        $today_attendance_rate = round(

            ($today_present / $today_total) * 100,

            2

        );

    }
    $classes_covered = mysqli_fetch_assoc(

        mysqli_query(

            $conn,

            "SELECT

            COUNT(DISTINCT class_id) total

            FROM attendance"

        )

    )['total'];

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

    <meta charset="UTF-8">

<script>
(() => {
    if (localStorage.getItem("mindmerge-theme") === "dark") {
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>

    <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>
    Attendance Management | MindMerge SmartCampus
    </title>

    

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/portals.css">

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .attendance-stats-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:16px;
    }
    .attendance-table-wrapper{
    width:100%;
    overflow-x:auto;
    }

    .attendance-table-wrapper .data-table{
    width:100%;
    min-width:1100px;
    table-layout:auto;
    }

    @media(max-width:992px){
    .attendance-stats-grid{
    grid-template-columns:repeat(2,1fr);
    }
    }

    @media(max-width:576px){
    .attendance-stats-grid{
    grid-template-columns:1fr;
    }
    }
    </style>
    </head>

    <body>

    <div class="app-layout">

    <?php include('../partials/sidebar.php'); ?>

    <div class="main-content">

    <?php include('../partials/topbar.php'); ?>

    <div class="page-content">

    <div class="page-header">

    <div>

    <h1>
    Attendance Management
    </h1>

    <p>
    Manage student attendance, teacher attendance, reports and analytics.
    </p>
    </div>

    <?php if (canCreate('attendance')) { ?>
    <a
    href="mark.php"
    class="btn btn-primary">

    <i class="fa-solid fa-clipboard-check"></i>

    Mark Attendance

    </a>
    <?php } ?>

    </div>

    <div class="attendance-hub-grid">

    <a href="mark.php" class="hub-card">
    <i class="fa-solid fa-user-graduate"></i>
    <h3>Student Attendance</h3>
    <p>Mark and manage period-wise student attendance sessions.</p>
    </a>

    <?php if (canView('teacher_attendance')) { ?>
    <a href="teacher/index.php" class="hub-card">
    <i class="fa-solid fa-chalkboard-user"></i>
    <h3>Teacher Attendance</h3>
    <p>Track and mark staff attendance records.</p>
    </a>
    <?php } ?>

    <a href="report.php" class="hub-card">
    <i class="fa-solid fa-chart-column"></i>
    <h3>Reports</h3>
    <p>Generate attendance reports and summaries.</p>
    </a>

    <a href="index.php#analytics" class="hub-card">
    <i class="fa-solid fa-chart-line"></i>
    <h3>Analytics</h3>
    <p>View institution-wide attendance analytics.</p>
    </a>

    </div>

    <?php if(isset($_GET['success'])){ ?>

    <div
    style="
    background:#dcfce7;
    color:#166534;
    padding:14px 18px;
    border-radius:14px;
    margin-bottom:20px;
    font-weight:500;
    ">

    <?php

    if($_GET['success']=='marked'){

    echo "Attendance marked successfully.";

    }
    elseif($_GET['success']=='updated'){

    echo "Attendance updated successfully.";

    }
    elseif($_GET['success']=='deleted'){

    echo "Attendance deleted successfully.";

    }

    ?>

    </div>

    <?php } ?>
    <?php if(isset($_GET['error'])){ ?>

    <div
    style="
    background:#fee2e2;
    color:#991b1b;
    padding:14px 18px;
    border-radius:14px;
    margin-bottom:20px;
    font-weight:500;
    ">

    <?php

    if($_GET['error']=='delete_failed'){

        echo "Unable to delete attendance session.";

    }
    elseif($_GET['error']=='invalid_attendance'){

        echo "Invalid attendance session selected.";

    }

    ?>

    </div>

    <?php } ?>

    <div class="dashboard-grid attendance-stats-grid">

    <div class="dashboard-card stat-card">

    <div class="stat-top">

    <div class="card-icon">

    <i class="fa-solid fa-calendar-check"></i>

    </div>

    <h3>

    <?php echo $total_sessions; ?>

    </h3>

    </div>

    <p>
    Student Sessions
    </p>

    </div>

    <div class="dashboard-card stat-card">

    <div class="stat-top">

    <div class="card-icon">

    <i class="fa-solid fa-calendar-day"></i>

    </div>

    <h3>

    <?php echo $today_sessions; ?>

    </h3>

    </div>
    <p>
    Today's Sessions
    </p>

    </div>

    <div class="dashboard-card stat-card">

    <div class="stat-top">

    <div class="card-icon">

    <i class="fa-solid fa-calendar-week"></i>

    </div>

    <h3>

    <?php echo $this_month_sessions; ?>

    </h3>

    </div>

    <p>
    Monthly Sessions
    </p>

    </div>

    <div class="dashboard-card stat-card">

    <div class="stat-top">

    <div class="card-icon">

    <i class="fa-solid fa-user-graduate"></i>

    </div>

    <h3>

    <?php echo $students_covered; ?>

    </h3>

    </div>

    <p>
    Students Marked
    </p>

    </div>

    <div class="dashboard-card stat-card">

    <div class="stat-top">

    <div class="card-icon">

    <i class="fa-solid fa-percent"></i>

    </div>

    <h3>

    <?php echo $overall_attendance_rate; ?>%

    </h3>

    </div>

    <p>
    Overall Attendance
    </p>

    </div>
    <div class="dashboard-card stat-card">

    <div class="stat-top">

    <div class="card-icon">

    <i class="fa-solid fa-school"></i>

    </div>

    <h3>

    <?php echo $classes_covered; ?>

    </h3>

    </div>

    <p>
    Classes Covered
    </p>

    </div>

    </div>

    <?php if($total_students == 0){ ?>

    <div
    style="
    background:#fee2e2;
    color:#991b1b;
    padding:14px 18px;
    border-radius:14px;
    margin-top:20px;
    margin-bottom:20px;
    font-weight:500;
    ">

    No students found. Add students before marking attendance.

    </div>

    <?php } ?>

    <div
    class="dashboard-section"
    id="recentAttendance"
    >

    <div class="section-header">

    <h2>
    Attendance Activity Log
    </h2>

    </div>

    <div
    class="table-responsive attendance-table-wrapper"
    >

    <table class="data-table sortable-table custom-table">

    <thead>

    <tr>

    <th data-sort="true">
    Date
    <i class="fa-solid fa-sort"></i>
    </th>

    <th data-sort="true">
    Class
    <i class="fa-solid fa-sort"></i>
    </th>

    <th data-sort="true">
    Section
    <i class="fa-solid fa-sort"></i>
    </th>

    <th data-sort="true">
    Period
    <i class="fa-solid fa-sort"></i>
    </th>

    <th data-sort="true">
    Subject
    <i class="fa-solid fa-sort"></i>
    </th>

    <th data-sort="true">
    Day
    <i class="fa-solid fa-sort"></i>
    </th>

    <th data-sort="true">
    Students
    <i class="fa-solid fa-sort"></i>
    </th>

    <th>
    Actions
    </th>

    </tr>

    </thead>

    <tbody>

    <?php

    if(mysqli_num_rows($recent_attendance) > 0){

    while($row = mysqli_fetch_assoc($recent_attendance)){
    $student_count =

    $attendance_counts[
    $row['attendance_id']
    ]

    ??

    0;

    ?>

    <tr>
    <td
    data-value="<?php echo strtotime($row['attendance_date']); ?>"
    >

    <?php
    echo date(
    'd M Y',
    strtotime(
    $row['attendance_date']
    )
    );
    ?>

    </td>
    <td>

    <?php

    echo htmlspecialchars(
    $row['class_name']
    );

    ?>

    </td>

    <td>

    <?php

    echo htmlspecialchars(
    $row['section_name']
    );

    ?>

    </td>

    <td>

    <?php

    echo htmlspecialchars(
    $row['period_name']
    );

    ?>

    </td>

    <td>

    <?php

    echo htmlspecialchars(
    $row['subject_name']
    );

    ?>

    </td>

    <td>

    <?php

    echo htmlspecialchars(
    ucfirst($row['attendance_day'])
    );

    ?>

    </td>

    <td
    data-value="<?php echo $student_count; ?>"
    >

    <?php echo $student_count; ?>

    </td>

    <td>

    <div
    style="
    display:flex;
    gap:6px;
    flex-wrap:nowrap;
    white-space:nowrap;
    ">

    <a
    href="view.php?attendance_id=<?php echo $row['attendance_id']; ?>"
    class="btn btn-primary">

    <i class="fa-solid fa-eye"></i>

    View

    </a>

    <a
    href="edit.php?attendance_id=<?php echo $row['attendance_id']; ?>"
    class="btn">

    <i class="fa-solid fa-pen"></i>

    Edit

    </a>

    <a
    href="delete.php?attendance_id=<?php echo $row['attendance_id']; ?>"
    class="btn"
    style="
    background:#ef4444;
    color:white;
    "
    onclick="return confirm('Delete this attendance session?');">

    <i class="fa-solid fa-trash"></i>

    Delete

    </a>

    </div>

    </td>

    </tr>

    <?php

    }

    }
    else{

    ?>
    <tr>

    <td
    colspan="8"
    style="
    text-align:center;
    padding:60px 20px;
    ">

    <div>

    <i
    class="fa-solid fa-clipboard-check"
    style="
    font-size:48px;
    color:var(--muted);
    margin-bottom:16px;
    "></i>

    <h3>
    No Attendance Sessions Found
    </h3>

    <p
    style="
    margin-top:10px;
    margin-bottom:20px;
    color:var(--muted);
    ">

    No attendance sessions have been created yet.

    </p>

    <a
    href="mark.php"
    class="btn btn-primary">

    <i class="fa-solid fa-plus"></i>

    Mark First Attendance

    </a>

    </div>

    </td>

    </tr>

    <?php

    }

    ?>

    </tbody>

    </table>

    </div>

    </div>
    </div>

    </div>

    </div>

    <script src="../assets/js/common.js"></script>
    <script>

    document.querySelectorAll(
    'a[href^="#"]'
    ).forEach(anchor=>{

    anchor.addEventListener(
    'click',
    function(e){

    e.preventDefault();

    const target=document.querySelector(
    this.getAttribute('href')
    );

    if(target){

    target.scrollIntoView({
    behavior:'smooth',
    block:'start'
    });

    }

    });

    });

    </script>
    </body>

    </html>