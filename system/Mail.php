<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{

    public static function send(array $files , array $mails){
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.yandex.ru';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'boot@azebot.ga';                     // SMTP username
            $mail->Password   = 'promo1234';                               // SMTP password
            $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 465;                                    // TCP port to connect to
            $mail->setFrom('boot@azebot.ga', 'LOWES-BOOT');

            foreach ($mails as $mailname) {
                echo $mailname ."\n";
                $mail->addAddress($mailname, 'Test name ');
            }
            foreach ($files as $file) {
                $mail->addAttachment($file);
            }
            $mail->Subject = 'AzeBot Smtp mailer Service';
            $mail->Body = "mailer-service-locator:#CM" . time();
            $mail->send();
            return true;
        } catch (Exception $e){
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return $e;
        }
    }

}