<?php
//SMTP mail class
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Mail extends PHPMailer{

  protected $mail;

  public function mailer($instance = null) {
    if ($instance !== null) {
      $this->mail = $instance;
    }

    if (!$this->mail) {
      $this->mail = new PHPMailer(true);
      $this->mail->isHTML(true);
      $this->mail->SMTPDebug = 0;
      $this->mail->isSMTP();
      $this->mail->Host = getenv('MAIL_HOST');
      $this->mail->SMTPAuth = true;
      $this->mail->Username = getenv('MAIL_USERNAME');
      $this->mail->Password = getenv('MAIL_PASSWORD');
      $this->mail->SMTPSecure = 'tls';
      $this->mail->Port = getenv('MAIL_PORT');
      $this->mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
    }

    return $this->mail;
  }

  public function sendMail($to, $to_name, $subject, $body, $plainText) {
    $mailer = $this->mailer();

    $mailer->addAddress($to, $to_name);
    $mailer->Subject = $subject;
    $mailer->Body = $body;
    $mailer->AltBody = $plainText;

    try{
      $mailer->send();
      return 'success';
    } catch (phpmailerException $e) {
      return 'Message could not be sent, Mailer Error: ' . $mailer->ErrorInfo;
    }
  }

}
