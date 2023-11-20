<?php
require_once 'email.php';

$res = getMessages();
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <title>Document</title>
</head>

<body>
    <div class="container mt-3">
        <div class="row mt-1 align-right">
            <button class="button" onclick="saveData()">Send to DB</button>
            <button class="button" onclick="sendEmails()">Forward Emails</button>
        </div>
        <div class="row mt-1">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < count($res); $i++) { ?>
                        <tr>
                            <td>
                                <?php echo ($i + 1); ?>
                            </td>
                            <td>
                                <?php echo $res[$i]['sender']; ?>
                            </td>
                            <td>
                                <?php echo $res[$i]['subject']; ?>
                            </td>
                            <td>
                                <?php echo $res[$i]['date']; ?>
                            </td>
                            <td>
                                <?php echo $res[$i]['time'] ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>


</body>

<script>
    var res = <?php echo json_encode($res); ?>;

    function saveData() {
        $.ajax({
            type: 'POST',
            url: "/controller.php",
            data: {
                data: res,
                method: 'saveEmails'
            },
            success: (data) => {
                if (data) {
                    alert('Successfully Saved!');
                }
            }
        })
    }

    function sendEmails() {
        $.ajax({
            type: "POST",
            url: "/controller.php",
            data: {
                data: res,
                method: "formwardEmaiils"
            },
            success: (data) => {
                if (data) {
                    alert('Successfully Forwarded!')
                }
            }
        })
    }
</script>

</html>