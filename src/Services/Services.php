<?php

namespace MacoBackend\Services;
use PHPMailer\PHPMailer\PHPMailer;
use Exception;

class Services
{    
    /**
     * Função que envia e-mail
     * 
     * @param $title
     * @param $html
     * @param $address
     * @param $name
     */
    public static function sendMail(string $title, string $html, string $address, string $name)
    {
        try
        {
            $mail = new PHPMailer;
            
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'email-ssl.com.br'; // Locaweb
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->Username = getenv('EMAIL_USER');
            $mail->Password = getenv('EMAIL_PASSWORD');

            $mail->setFrom('maco@maco.com.br', 'Maco - UNIFAE');
            // $mail->addReplyTo('', ''); -- Copia oculta

            $mail->addAddress($address, $name);
            $mail->AddBCC("notificacao@gn1.com.br", "Copia Oculta");    
            
            $mail->Subject = utf8_decode($title);            
            $mail->msgHTML(utf8_decode($html), __DIR__);            
            
            $mail->send();  

            return [
                'status' => 'success',
                'message' => 'Email successfully sent',
            ];
        }
        catch(Exception $e)                     
        {
            return [
                'status' => 'Error',
                'message' => $e->getMessage(),
            ];
        }
    }    
}
