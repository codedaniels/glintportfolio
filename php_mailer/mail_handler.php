<?php
// print_r($_POST);  // do this first to test
// exit();          // do this first to test
require_once('email_config.php');
require('phpmailer/PHPMailer/PHPMailerAutoload.php');

// validate POST inputs
$message = [];
$output = [
    'success' => null,
    'messages' => []
];

// sanitize name field
$message['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
if(empty($message['name'])) {
    $output['success'] = false;
    $output['messages'][] = 'missing name key';
}

// validate email field
$message['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if(empty($message['email'])){
    $output['success'] = false;
    $output['messages'][] = 'invalid email key';
}

// sanitize body message field
$message['message'] = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
if(empty($message['message'])) {
    $output['success'] = false;
    $output['messages'][] = 'missing message';
}

// sanitize subject field
$message['subject'] = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
// if(empty($message['subject'])) {
//     $output['success'] = false;
//     $output['messages'][] = 'missing subject line';
// } // only if needed/required put this code

// sanitize phone number field
$message['phone'] = preg_replace('/[^0-9]/','',$_POST['phone number']);
// if(empty($message['phone']) && count($message['phone']) >= 10 && count($message['phone']) <= 11) {
//     $output['success'] = false;
//     $output['messages'][] = 'missing phone number';
// }  // only if needed/required put this code

if ($output['success'] !== null) {
    http_response_code(400); 
    echo json_encode($output); 
    exit(); // exits entire script (file)
}


foreach($_POST as $key=>$value){
    $_POST[$key] = htmlentities( addslashes( $value ));
}
$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$body = $_POST['body'];

$mail = new PHPMailer;          // New instantiation of the class PHPMailer
$mail->SMTPDebug = 3;           // Enable verbose debug output. Change to 0 to disable debugging output.

$mail->isSMTP();                // Set mailer to use SMTP.
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers.
$mail->SMTPAuth = true;         // Enable SMTP authentication


$mail->Username = EMAIL_USER;   // SMTP username
$mail->Password = EMAIL_PASS;   // SMTP password
$mail->SMTPSecure = 'tls';      // Enable TLS encryption, `ssl` also accepted, but TLS is a newer more-secure encryption
$mail->Port = 587;              // TCP port to connect to
$options = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->smtpConnect($options);
// $mail->From = $message['email'];  // sender's email address (shows in "From" field)
// $mail->FromName = $message['name'];   // sender's name (shows in "From" field)
// $mail->addAddress(EMAIL_TO_ADDRESS, EMAIL_USERNAME);  // Add a recipient
    // OR
$mail->From = EMAIL_USER;
$mail->FromName = EMAIL_USERNAME;
$mail->addAddress(EMAIL_TO_ADDRESS, EMAIL_USERNAME);

//$mail->addAddress('ellen@example.com');                        // Name is optional
$mail->addReplyTo($message['email'], $message['name']);                          // Add a reply-to address
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

// only neccessary if no subject provided
// $message['subject'] = $message['name']." has sent you a message on your portfolio";
// $message['subject'] = substr($message['message'], 0,78);

$mail->Subject = $message['subject'];

// $message['message'] = nl2br($message['message']); // converts newline characters to line break html tags
$mail->Body    = $message['message'];
$mail->AltBody = htmlentities($message['message']);


// Attempt email send, output result to client
if(!$mail->send()) {
    $output['success'] = false;
    $output['messages'][] = $mail->ErrorInfo;
} else {
    $output['success'] = true;
}
echo json_encode($output);
?>
