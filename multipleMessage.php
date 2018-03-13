<?php
/**
 * Created by PhpStorm.
 * User: HeshanAnu
 * Date: 3/9/2018
 * Time: 12:56 PM
 */

require_once("session.php");
require_once("class.user.php");
include("dbConnect.php");
$auth_user = new USER();


$user_id = $_SESSION['user_session'];

$stmt = $auth_user->runQuery("SELECT * FROM users WHERE user_id=:user_id");
$stmt->execute(array(":user_id" => $user_id));

$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
$uname = $userRow['user_name'];

$totalNumOfSentMessages = 0;
$successfullPhoneNumbers = [];
$successfullRecievers = [];

echo "<h2 align='center'>Following Messages were sent </h2>\n";
echo "<body bgcolor='lightgreen'>";

foreach ($_POST['numbers'] as $fieldIndex => $fieldValue) {

    $data = explode("-", $fieldValue);
    $mobile_number = $data[0];
    $messages = $_POST['messages'];

    $gsm_send_sms = new gsm_send_sms();
    $gsm_send_sms->debug = false;
    $gsm_send_sms->port = 'COM6';
    $gsm_send_sms->baud = 115200;
    $gsm_send_sms->init();

    $status = $gsm_send_sms->send($mobile_number, $messages);
    if ($status) {
        $totalNumOfSentMessages++;
        $successfullPhoneNumbers . array_push($mobile_number);
        $successfullRecievers . array_push($data[1]);


    } else {
        echo "<body bgcolor='lightgreen'>";
        echo "<br>";
        echo "<h3 align='center'>Message not sent to $mobile_number </h3>\n";
        echo "</body>";
    }


    $gsm_send_sms->close();

    try {

        $stmt = $conn->prepare("INSERT INTO messagelog (user_name, message, sender) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $messsge, $mobile);


        $username = $uname;
        $messsge = $messages;
        $mobile = $mobile_number;


        $stmt->execute();

    } catch (PDOException $e) {
        echo $e->getMessage();
    }


}
echo "<br>";
echo "<h3 align='center'>Message successfully sent to $mobile_number </h3>\n";
echo "</body>";


//Send SMS via serial SMS modem
class gsm_send_sms
{

    public $port = 'COM6';
    public $baud = 115200;

    public $debug = false;

    private $fp;
    private $buffer;

    //Setup COM port
    public function init()
    {

        $this->debugmsg("Setting up port: \"{$this->port} @ \"{$this->baud}\" baud");

        exec("MODE {$this->port}: BAUD={$this->baud} PARITY=N DATA=8 STOP=1", $output, $retval);
        if ($retval != 0) {
            throw new Exception('Unable to setup COM port, check it is correct');
        }

        $this->debugmsg(implode("\n", $output));

        $this->debugmsg("Opening port");

        //Open COM port
        $this->fp = fopen($this->port . ':', 'r+');

        //Check port opened
        if (!$this->fp) {
            throw new Exception("Unable to open port \"{$this->port}\"");
        }

        $this->debugmsg("Port opened");
        $this->debugmsg("Checking for response from modem");

        //Check modem connected
        fputs($this->fp, "AT\r");

        //Wait for ok
        $status = $this->wait_reply("OK\r\n", 30);

        if (!$status) {
            throw new Exception('Did not receive response from modem');
        }

        $this->debugmsg('Modem connected');

        //Set modem to SMS text mode
        $this->debugmsg('Setting text mode');
        fputs($this->fp, "AT+CMGF=1\r");

        $status = $this->wait_reply("OK\r\n", 30);

        if (!$status) {
            throw new Exception('Unable to set text mode');
        }

        $this->debugmsg('Text mode set');

    }

    //Wait for reply from modem
    private function wait_reply($expected_result, $timeout)
    {

        $this->debugmsg("Waiting {$timeout} seconds for expected result");

        //Clear buffer
        $this->buffer = '';

        //Set timeout
        $timeoutat = time() + $timeout;

        //Loop until timeout reached (or expected result found)
        do {

            $this->debugmsg('Now: ' . time() . ", Timeout at: {$timeoutat}");

            $buffer = fread($this->fp, 1024);
            $this->buffer .= $buffer;

            usleep(200000);//0.2 sec

            $this->debugmsg("Received: {$buffer}");

            //Check if received expected responce
            if (preg_match('/' . preg_quote($expected_result, '/') . '$/', $this->buffer)) {
                $this->debugmsg('Found match');
                return true;
                //break;
            } else if (preg_match('/\+CMS ERROR\:\ \d{1,3}\r\n$/', $this->buffer)) {
                return false;
            }

        } while ($timeoutat > time());

        $this->debugmsg('Timed out');

        return false;

    }

    //Print debug messages
    private function debugmsg($message)
    {

        if ($this->debug == true) {
            $message = preg_replace("%[^\040-\176\n\t]%", '', $message);
            echo $message . "\n";
        }

    }

    //Close port
    public function close()
    {

        $this->debugmsg('Closing port');

        fclose($this->fp);

    }

    //Send message
    public function send($tel, $message)
    {

        //Filter tel
        $tel = preg_replace("%[^0-9\+]%", '', $tel);

        //Filter message text
        $message = preg_replace("%[^\040-\176\r\n\t]%", '', $message);

        $this->debugmsg("Sending message \"{$message}\" to \"{$tel}\"");

        //Start sending of message
        fputs($this->fp, "AT+CMGS=\"{$tel}\"\r");

        //Wait for confirmation
        $status = $this->wait_reply("\r\n> ", 5);

        if (!$status) {
            //throw new Exception('Did not receive confirmation from modem');
            $this->debugmsg('Did not receive confirmation from modem');
            return false;
        }

        //Send message text
        fputs($this->fp, $message);

        //Send message finished indicator
        fputs($this->fp, chr(26));

        //Wait for confirmation
        $status = $this->wait_reply("OK\r\n", 180);

        if (!$status) {
            //throw new Exception('Did not receive confirmation of message sent');
            $this->debugmsg('Did not receive confirmation of message sent');
            return false;
        }

        $this->debugmsg("Message Send Successfully...");

        return true;

    }
}

?>
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
<h2> Total of <?php echo $totalNumOfSentMessages ?>were sent</h2>

</body>
</html>