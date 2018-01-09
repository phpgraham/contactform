<?php
/**
* Simple contact form demo, w3c compliant, html and php validation, error notifications, smtp email, bot secure
* using php7, html5, jquery
* uses multiple spam protection techniques
* another using bootstrap 4 - http://#
* another using bootstrap 4 and vue - http://#
* another using laravel and angularjs - http://#
* another using nodejs - http://#
**/

//Load composer's autoloader
//used to load PHPMailer and vlucas Dotenv
require '../vendor/autoload.php';

//load environment and email variables
$dotenv = new Dotenv\Dotenv('../');
$dotenv->load();

require('Classes/mail.php');

// define variables and set to empty values
$name = $email = $message = $loadtime = $human = $errorMsg = "";
$errMess = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // avoid polluting the global variable space, call form handler then extract variables
  $response = validateContactForm($_POST);
  if($response=='success'){
    $success = 'Your message has been successfully sent. We will contact you soon.';
  } else {
    $errorMsg = $response;
  }
}

function validateContactForm($arr) {
  extract($arr);
  //check for spam bots
  //two hidden elements, time trap, email in name field - multiple methods to really annoy the bots
  //clean inputs, check for empty required fields
  //use html and php validation with just simple error notification array
  if (empty($name)) {
    $errMess[] = "Name is required";
  } else {
    $name = clean_input($name);
    //just simple for now, could add special chars ('-,^) etc
    if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
      $errMess[] = "Only letters and white space allowed for name";
    }
  }

  if (empty($email)) {
    $errMess[] = "Email is required";
  } else {
    //just simple email check for now, could add checkdnsrr and FILTER_SANITIZE_EMAIL checks
    $email = clean_input($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errMess[] = "Invalid email format";
    }
  }

  //simple check for now, could add min char check
  if (empty($message)) {
    $message = "";
  } else {
    $message = clean_input($message);
  }

  //bot check - time trap
  $totaltime = time() - $loadtime;
  if($totaltime < 5) {
      $errMess[] = "That was quick, are you human. Please take time to fill in the form before submitting.";
  }

  //bot check hidden field
  if (!empty($human) && !($human == "10" || strtolower($human) == "ten")) {
      $errMess[] = "Are you human, leave field blank or complete sum.";
  }

  //bot check hidden field
  if (!empty($url)) {
      $errMess[] = "Are you human, leave field blank.";
  }

  if(isset($errMess) && $errMess){
    //validation errors
    return $errMess;
  } else {
    // process response, send email, return success message
    // PHPMailer returns success or error messages
    $mail = new Mail();

    $sendemail = $mail->sendMail(
        $email,
        $name,
        'Message received from Contact Form',
        '<h1>This is the HTML message</h1><p>'.$message.'</p>',
        $message
    );

    if($sendemail=='success') {
      // email sent successfully
      return 'success';
    } else {
      // mailing error
       return $sendemail;
    }
  }
}

function clean_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

?>
<!DOCTYPE HTML>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contact Form</title>

<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>

    <header class="body">
      <h1>Contact Form</h1>
    </header>

    <section class="body">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

        <?php if(isset($errorMsg) && $errorMsg) { ?>
          <ul class="errorMessages">
            <?php foreach($errorMsg as $error) { ?>
              <li><span class="fa fa-times-circle">&nbsp;</span><?=htmlspecialchars($error)?></li>
            <?php } ?>
          </ul>
        <?php } ?>
        <?php if(isset($success) && $success) { ?>
          <ul class="successMessages">
              <li><span class="fa fa-check-circle">&nbsp;</span><?=htmlspecialchars($success)?></li>
          </ul>
        <?php } ?>

        <label for="form_email">Name</label>
        <input name="name" placeholder="Enter your name here" id="form_email" value="<?php if(isset($_POST['name']) && !isset($success)) echo htmlspecialchars($_POST['name']); ?>" required>

        <label for="form_name">Email</label>
        <input name="email" type="email" placeholder="Enter your email here" id="form_name" value="<?php if(isset($_POST['email']) && !isset($success)) echo htmlspecialchars($_POST['email']); ?>" required>

        <label for="message">Message</label>
        <textarea name="message" placeholder="Enter your message here" id="message"><?php if(isset($_POST['message']) && !isset($success)) echo htmlspecialchars($_POST['message']); ?></textarea>

        <p class="inputhere" id="inputhere">
          <label for="form_message">What is five plus five ? (Anti-spam)</label>
          <input name="human" placeholder="Type Here" id="form_message">

          <label for="form_url">Url: </label>
          <input type="text" name="url" id="form_url" value="<?php if(isset($_POST['url'])) echo htmlspecialchars($_POST['url']); ?>">
        </p>

        <input type="hidden" name="loadtime" value="<?php echo time(); ?>">
        <input id="submit" name="submit" type="submit" value="Submit">

      </form>
    </section>

    <footer class="body">
    </footer>

</body>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g="
        crossorigin="anonymous"></script>
<script>
$(document).ready(
  function() {
    $('#inputhere').hide();
  }
);
</script>
</html>
