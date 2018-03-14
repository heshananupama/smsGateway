<?php
/**
 * Created by PhpStorm.
 * User: HeshanAnu
 * Date: 3/13/2018
 * Time: 12:36 PM
 */

require_once("../session.php");

require_once("../class.user.php");
require_once("../dbConnect.php");

$auth_user = new USER();


$user_id = $_SESSION['user_session'];

$stmt = $auth_user->runQuery("SELECT * FROM users WHERE user_id=:user_id");
$stmt->execute(array(":user_id" => $user_id));

$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if($userRow['role']!=1)
{
    // session no set redirects to login page
    $session->redirect('../index.php');
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="../style.css" type="text/css"  />
    <link rel="stylesheet" href="../css/datatables.min.css">

    <script src="../js/jquery.min.js"></script>
    <script src="../js/datatables.min.js"></script>

    <title>SMS Gateway UOK</title>
</head>
<body style="background:#f1f9f9;">
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">SMS Gateway -UOK</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li ><a href="addUser.php">Add an user</a></li>
                <li class="active"><a href="index.php">Summary</a></li>
                <li><a href="../home.php">Send messages</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-user"></span>&nbsp;Hi  <?php echo $userRow['user_name']; ?>&nbsp;<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#"><span class="glyphicon glyphicon-user"></span>&nbsp;View Profile</a></li>
                        <li><a href="../logout.php?logout=true"><span class="glyphicon glyphicon-log-out"></span>&nbsp;Sign Out</a></li>
                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
<br><br>
<h2 style="color: gray;font-family: Calibri;font-size: 40px"  align="center"> Summary of messages sent</h2>
<table id="summaryTable" class="table table-hover" >
    <thead>
    <tr>
        <th >Username</th>
        <th>Message</th>
        <th>Sender</th>
        <th>Date/time</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT * FROM messagelog";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>";
            echo  $row["user_name"] ;
            echo "</td>";
            echo "<td>";
            echo  $row["message"] ;
            echo "</td>";
            echo "<td>";
            echo  $row["sender"] ;
            echo "</td>";
            echo "<td>";
            echo $row["timestamp"];
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>
    </tbody>


</table>
<script>
    $(document).ready( function () {
        $('#summaryTable').DataTable();
    } );
</script>
<script src="../bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
