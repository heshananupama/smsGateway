<?php
/**
 * Created by PhpStorm.
 * User: HeshanAnu
 * Date: 3/12/2018
 * Time: 1:46 PM
 */
require_once("session.php");

require_once("class.user.php");
$auth_user = new USER();


$user_id = $_SESSION['user_session'];

$stmt = $auth_user->runQuery("SELECT * FROM users WHERE user_id=:user_id");
$stmt->execute(array(":user_id" => $user_id));

$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/jquery.min.js"></script>

    <title>SMS Gateway UOK</title>
</head>
<body style="background:#f1f9f9;">
<!--<div style=" padding: 10px;" class="jumbotron">
    <img src="images/Kelaniya.png" height="100px" width="100px;" alt="">
    <h1 style="text-align: center;" class="display-3">UOK SMS Gateway</h1>

</div>-->
<ul class="nav justify-content-end">

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
           aria-expanded="false">Hello <?php echo $userRow['user_name']; ?> </a>

        <div class="dropdown-menu">
            <a class="dropdown-item" href="profile.php"><span class="glyphicon glyphicon-user"></span>&nbsp;View Profile</a>
     <a class="dropdown-item" href="logout.php?logout=true"><span class="glyphicon glyphicon-log-out"></span>&nbsp;Sign Out</a>

    </div>

    </li>

</ul>
<div class="header" style="margin-left: 150px">
    <img src="images/Kelaniya.png" alt="logo"/>
    <h1 style="font-size: 60px;">UOK SMS Gateway</h1>
</div>
<br>
<hr>
 <div class="row">
    <div class="col-sm-3">
        <div class="card" style="margin-left: 5px;">
            <div class="card-header">Single SMS</div>
            <br>
            <form style="margin-left: 20px;width: 80%" action="singleMessage.php" method="post">
                <div class="form-group">
                    <label for="mobileNum">Enter Mobile Number</label>
                    <div id="mobileNum">
                        <input class="form-control" type="text" name="mobileNum[]"/>
                    </div>
                </div>
                <input class="btn btn-info" type="button" id="addNumber" value="Add another number"/>
                <br>
                <br>
                <div class="form-group">
                    <label for="message">Enter message</label>
                    <div id="message">
                        <textarea class="form-control" cols=25 rows=5 name="messages"></textarea>
                    </div>
                </div>


                <div style="text-align: center">
                    <input id="send" style="text-align: center" class="btn btn-success" type="submit" name="action"
                           value="Send SMS"
                           title="Click here to send SMS.">
                </div>

            </form>

        </div>
    </div>
    <div class="col-sm-9">
        <div class="card">
            <div class="card-header">Bulk SMS</div>
            <div class="row">
                <div class="col-sm-4"><br><br><br>
                    <div class="card" style="margin: 5px;">
                        <div class="card-body">
                            <div class="form-group">
                                <input class="form-control" type="file" id="fileUploadCSV"/><br>
                                <input class="btn btn-outline-info  btn-block" type="button" value="Load CSV"
                                       id="btnUpload"/>
                            </div>
                        </div>

                    </div>


                    <!--<input type="button" value="Upload csv" id="btnUpload"/>-->
                </div>
                <div class="col-sm-8">
                    <form method="post" action="multipleMessage.php">
                        <table class="table" id="tblMultileads">
                            <tr>
                                <th> <input type="checkbox" onClick="toggle(this)" /> Select All </th>
                                <th>Name</th>

                                <th>Phone Number</th>

                            </tr>
                            <tbody id="tbodyLeads">
                            </tbody>

                        </table>
                        <div id="messageToAll" class="form-group">
                            <label for="messageToAll">Enter message</label>
                            <div>
                                <textarea class="form-control" cols=25 rows=3 name="messages"></textarea>
                            </div>
                        </div>
                        <input id="sendAllButton" class="btn btn-success" type="submit" value="Send SMS to Selected">
                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
</body>
<script src="js/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#addNumber').click(function () {
            $('#mobileNum').append(
                $('<br> <input class="form-control" type="text" name="mobileNum[]"/>')
            );
        })
    });

    $(document).on('click', '.browse', function () {
        var file = $(this).parent().parent().parent().find('.file');
        file.trigger('click');
    });
    $(document).on('change', '.file', function () {
        $(this).parent().find('.form-control').val($(this).val().replace(/C:\\fakepath\\/i, ''));
    });
</script>
<script>
    $(function () {
        var csv = $("#fileUploadCSV").val();
        $("#sendAllButton").hide();
        $("#send").show();

        $("#messageToAll").hide();

        $('#chk').click(function () {
            $('#fields').append(
                $('<input type="text" name="number[]"/>')
            );
        })
        $("#btnUpload").bind("click", function () {
            debugger;
            var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/;//regex for Checking valid files csv of txt
            if (regex.test($("#fileUploadCSV").val().toLowerCase())) {
                if (typeof (FileReader) != "undefined") {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var rows = e.target.result.split("\r\n");

                        if (rows.length > 0) {
                            var first_Row_Cells = splitCSVtoCells(rows[0], ","); //Taking Headings

                            if (first_Row_Cells.length != 2) {
                                alert('Please upload a valid csv ,Colums count not matching ');
                                return;
                            }
                            if (first_Row_Cells[0] != "Name") {
                                alert('Please upload a valid csv, Check Heading Name');
                                return;
                            }

                            if (first_Row_Cells[1] != "Phone_Number") {

                                alert('Please upload a valid csv, Check heading Phone Number');
                                return;
                            }

                            var jsonArray = new Array();
                            for (var i = 1; i < rows.length; i++) {
                                var cells = splitCSVtoCells(rows[i], ",");


                                var obj = {};
                                for (var j = 0; j < cells.length; j++) {
                                    obj[first_Row_Cells[j]] = cells[j];
                                }
                                jsonArray.push(obj);
                            }

                            console.log(jsonArray);
                            var html = "";
                            for (i = 0; i < jsonArray.length; i++) {
                                debugger;
                                if (jsonArray[i].Name != "") {
                                    html += "<tr id=\"rowitem\"" + i + "><td style=\"display:none;\">" + i + "</td> <td align=\"center\"><input type=\"checkbox\"   name=\"numbers[]\" value=\"" + jsonArray[i].Phone_Number + "-"+jsonArray[i].Name+"\" /></td><td>" + jsonArray[i].Name + "</td>";
                                    html += "<td>" + jsonArray[i].Phone_Number + "</td>";


                                }
                            }
                            document.getElementById('tbodyLeads').innerHTML = html;
                            //$("#item").hide();
                            $("#sendAllButton").show();
                            $("#send").hide();
                            $("#messageToAll").show();


                        }
                    };
                    reader.readAsText($("#fileUploadCSV")[0].files[0]);
                }
                else {
                    alert("This browser does not support HTML5.");
                }
            } else {
                alert("Select a valid CSV File.");
            }
        });
    });

    function splitCSVtoCells(row, separator) {
        return row.split(separator);
    }

    function toggle(source) {
        checkboxes = document.getElementsByName('numbers[]');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }

</script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</html>
