<?php
namespace App\Service;

use App\Entity\Users;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendLoginNotification(Users $user): void
    {
        $email = (new Email())
            ->from('abderrahmen.deakayr@gmail.com')
            ->to($user->getEmail())
            ->subject('Login Notification')
            ->text('Dear ' . $user->getPrenom() . ' ' . $user->getNom() . ' (' . $user->getUsername() . '), you have successfully logged in to our website.')
            ->html('
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Login Notification</title>
                </head>
                <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 20px auto;">
                        <tr>
                            <td style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <!-- Header -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 20px; text-align: center; background-color: #2c3e50; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Login Notification</h1>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Content -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 30px; color: #333333; font-size: 16px; line-height: 1.5;">
                                            <p style="margin: 0 0 20px;">Dear ' . htmlspecialchars($user->getPrenom()) . ' ' . htmlspecialchars($user->getNom()) . ' (' . htmlspecialchars($user->getUsername()) . '),</p>
                                            <p style="margin: 0 0 20px;">You have successfully logged in to our website. If this wasn\'t you, please contact our support team immediately.</p>
                                            <p style="margin: 0 0 20px;">For your security, we recommend:</p>
                                            <ul style="margin: 0 0 20px; padding-left: 20px;">
                                                <li>Using a strong, unique password</li>
                                                <li>Enabling two-factor authentication</li>
                                                <li>Regularly reviewing your account activity</li>
                                            </ul>
                                            <p style="margin: 0;">Thank you for choosing our service!</p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- CTA Button -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 0 30px 30px; text-align: center;">
                                            <a href="" style="background-color: #3498db; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 4px; display: inline-block; font-size: 16px;">Secure Your Account</a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Footer -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 20px; text-align: center; background-color: #f8f8f8; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                                            <p style="color: #666666; font-size: 14px; margin: 0 0 10px;">© ' . date('Y') . ' Local Lans. All rights reserved.</p>
                                            <p style="color: #666666; font-size: 14px; margin: 0;">
                                                <a href="" style="color: #3498db; text-decoration: none;">Contact Support</a> | 
                                                <a href="" style="color: #3498db; text-decoration: none;">Privacy Policy</a>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
            ');

        $this->mailer->send($email);
    }

    public function sendPasswordResetEmail(Users $user, $resetToken): void
    {
        $resetUrl = 'http://127.0.0.1:8000/reset-password/reset/' . $resetToken->getToken();
        
        $email = (new Email())
            ->from('abderrahmen.deakayr@gmail.com')
            ->to($user->getEmail())
            ->subject('Reset Your Password')
            ->html('
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Reset Your Password</title>
                </head>
                <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 20px auto;">
                        <tr>
                            <td style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <!-- Header -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 20px; text-align: center; background-color: #2c3e50; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Reset Your Password</h1>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Content -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 30px; color: #333333; font-size: 16px; line-height: 1.5;">
                                            <p style="margin: 0 0 20px;">Hello ' . htmlspecialchars($user->getPrenom()) . ' ' . htmlspecialchars($user->getNom()) . ',</p>
                                            <p style="margin: 0 0 20px;">We received a request to reset your password. If you didn\'t make this request, you can safely ignore this email.</p>
                                            <p style="margin: 0 0 20px;">To reset your password, click the button below:</p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- CTA Button -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 0 30px 30px; text-align: center;">
                                            <a href="' . $resetUrl . '" style="background-color: #3498db; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 4px; display: inline-block; font-size: 16px;">Reset Password</a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- URL Copy -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 0 30px 30px; text-align: center;">
                                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">Or copy and paste this URL into your browser:</p>
                                            <p style="margin: 0; color: #3498db; word-break: break-all;">' . $resetUrl . '</p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Expiration Notice -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 0 30px 30px; text-align: center;">
                                            <p style="margin: 0; color: #e74c3c; font-size: 14px;">This password reset link will expire in 1 hour.</p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Footer -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 20px; text-align: center; background-color: #f8f8f8; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                                            <p style="color: #666666; font-size: 14px; margin: 0 0 10px;">© ' . date('Y') . ' Local Lans. All rights reserved.</p>
                                            <p style="color: #666666; font-size: 14px; margin: 0;">
                                                If you did not request this password reset, please contact support.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
            ');

        $this->mailer->send($email);
    }



    public function sendEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('your_email@example.com') // 🛑 Important: Set a real FROM email address!
            ->to($to)
            ->subject($subject)
            ->html($content);

        $this->mailer->send($email);
    }
}
