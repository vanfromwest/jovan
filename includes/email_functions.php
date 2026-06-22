<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getMailConfig() {
    global $conn;

    $config = [
        'host'       => MAIL_HOST,
        'port'       => MAIL_PORT,
        'username'   => MAIL_USERNAME,
        'password'   => MAIL_PASSWORD,
        'encryption' => MAIL_ENCRYPTION,
        'from_email' => MAIL_FROM_ADDRESS,
        'from_name'  => MAIL_FROM_NAME,
    ];

    try {
        $stmt = $conn->prepare("SELECT `key`, `value` FROM email_config WHERE `key` IN ('mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'from_email', 'from_name')");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $map = [
                'mail_host'       => 'host',
                'mail_port'       => 'port',
                'mail_username'   => 'username',
                'mail_password'   => 'password',
                'mail_encryption' => 'encryption',
                'from_email'      => 'from_email',
                'from_name'       => 'from_name',
            ];
            $key = $map[$row['key']] ?? null;
            if ($key) {
                $config[$key] = $row['value'];
            }
        }
    } catch (Exception $e) {
    }

    return $config;
}

function buildEmailHtml($title, $contentHtml) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $title . '</title>
    </head>
    <body style="margin:0;padding:0;background-color:#f8f9fa;font-family:\'Segoe UI\',Tahoma,Geneva,Verdana,sans-serif;">
        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;background-color:#f8f9fa;padding:20px 0;">
            <tr>
                <td align="center">
                    <table role="presentation" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                        <tr>
                            <td style="background:linear-gradient(135deg,#2d5016,#1a3a0a);color:#ffffff;padding:25px;text-align:center;border-radius:10px 10px 0 0;border-bottom:3px solid #ffd700;">
                                <h1 style="margin:0;font-size:22px;font-weight:700;">' . SITE_NAME . '</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="background:#ffffff;padding:30px;border-radius:0 0 10px 10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                                ' . $contentHtml . '
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center;padding:15px;color:#999999;font-size:12px;">
                                &copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

function setupMailer($mailConfig) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = $mailConfig['encryption'];
    $mail->Port       = (int)$mailConfig['port'];
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    return $mail;
}

function sendApprovalEmail($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT fullname, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || empty($user['email'])) {
        return false;
    }

    $mailConfig = getMailConfig();

    if (empty($mailConfig['username']) || empty($mailConfig['password'])) {
        return false;
    }

    try {
        $mail = setupMailer($mailConfig);
        $mail->addAddress($user['email'], $user['fullname']);
        $mail->Subject = 'Account Approved - ' . SITE_NAME;

        $content = '
        <p style="font-size:15px;color:#333333;margin:0 0 15px 0;">Dear <strong style="color:#2d5016;">' . htmlspecialchars($user['fullname']) . '</strong>,</p>
        <p style="font-size:15px;color:#333333;margin:0 0 20px 0;">We are pleased to inform you that your account has been <strong style="color:#28a745;">approved</strong>.</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="background:rgba(45,80,22,0.08);border-left:4px solid #2d5016;padding:15px 20px;margin:0 0 20px 0;border-radius:5px;width:100%;">
            <tr>
                <td style="padding:0 0 10px 0;font-weight:700;color:#2d5016;font-size:14px;">ACCOUNT DETAILS</td>
            </tr>
            <tr>
                <td style="padding:4px 0;font-size:14px;color:#555555;"><strong style="width:80px;display:inline-block;">Email:</strong> ' . htmlspecialchars($user['email']) . '</td>
            </tr>
            <tr>
                <td style="padding:4px 0;font-size:14px;color:#555555;"><strong style="width:80px;display:inline-block;">Role:</strong> <span style="background:#2d5016;color:#ffffff;padding:2px 10px;border-radius:3px;font-size:12px;">' . htmlspecialchars($user['role']) . '</span></td>
            </tr>
        </table>
        <p style="font-size:15px;color:#333333;margin:0 0 25px 0;">You can now log in to the system using your credentials.</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 25px 0;width:100%;">
            <tr>
                <td align="center">
                    <a href="' . SITE_URL . '/login.php" style="display:inline-block;background:linear-gradient(135deg,#2d5016,#1a3a0a);color:#ffffff;padding:14px 40px;text-decoration:none;border-radius:5px;font-size:16px;font-weight:600;font-family:\'Segoe UI\',Tahoma,Geneva,Verdana,sans-serif;">Login Now</a>
                </td>
            </tr>
        </table>
        <p style="font-size:14px;color:#666666;margin:0 0 20px 0;">If you have any questions, please contact the administrator.</p>
        <hr style="border:none;border-top:1px solid #eeeeee;margin:20px 0;">
        <p style="font-size:14px;color:#333333;margin:0;">Best regards,<br><strong style="color:#2d5016;font-size:15px;">' . SITE_NAME . '</strong></p>';

        $mail->isHTML(true);
        $mail->Body = buildEmailHtml('Account Approved', $content);
        $mail->AltBody = "Dear {$user['fullname']},\n\nYour account has been approved.\n\nEmail: {$user['email']}\nRole: {$user['role']}\n\nYou can now log in at: " . SITE_URL . "/login.php\n\nBest regards,\n" . SITE_NAME;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function sendRejectionEmail($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT fullname, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || empty($user['email'])) {
        return false;
    }

    $mailConfig = getMailConfig();

    if (empty($mailConfig['username']) || empty($mailConfig['password'])) {
        return false;
    }

    try {
        $mail = setupMailer($mailConfig);
        $mail->addAddress($user['email'], $user['fullname']);
        $mail->Subject = 'Account Update - ' . SITE_NAME;

        $content = '
        <p style="font-size:15px;color:#333333;margin:0 0 15px 0;">Dear <strong style="color:#2d5016;">' . htmlspecialchars($user['fullname']) . '</strong>,</p>
        <p style="font-size:15px;color:#333333;margin:0 0 20px 0;">We regret to inform you that your account registration has been <strong style="color:#dc3545;">declined</strong>.</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="background:rgba(220,53,69,0.08);border-left:4px solid #dc3545;padding:15px 20px;margin:0 0 20px 0;border-radius:5px;width:100%;">
            <tr>
                <td style="padding:0 0 10px 0;font-weight:700;color:#dc3545;font-size:14px;">ACCOUNT DETAILS</td>
            </tr>
            <tr>
                <td style="padding:4px 0;font-size:14px;color:#555555;"><strong style="width:80px;display:inline-block;">Email:</strong> ' . htmlspecialchars($user['email']) . '</td>
            </tr>
            <tr>
                <td style="padding:4px 0;font-size:14px;color:#555555;"><strong style="width:80px;display:inline-block;">Role:</strong> <span style="background:#2d5016;color:#ffffff;padding:2px 10px;border-radius:3px;font-size:12px;">' . htmlspecialchars($user['role']) . '</span></td>
            </tr>
        </table>
        <p style="font-size:14px;color:#666666;margin:0 0 20px 0;">If you believe this is a mistake or would like more information, please contact the administrator.</p>
        <hr style="border:none;border-top:1px solid #eeeeee;margin:20px 0;">
        <p style="font-size:14px;color:#333333;margin:0;">Best regards,<br><strong style="color:#2d5016;font-size:15px;">' . SITE_NAME . '</strong></p>';

        $mail->isHTML(true);
        $mail->Body = buildEmailHtml('Account Update', $content);
        $mail->AltBody = "Dear {$user['fullname']},\n\nYour account registration has been declined.\n\nIf you have questions, please contact the administrator.\n\nBest regards,\n" . SITE_NAME;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function testMailConnection() {
    $mailConfig = getMailConfig();

    if (empty($mailConfig['username']) || empty($mailConfig['password'])) {
        return ['success' => false, 'message' => 'SMTP credentials are not configured.'];
    }

    try {
        $mail = setupMailer($mailConfig);
        $mail->Timeout = 10;
        $mail->addAddress($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->Subject = 'Test Email - ' . SITE_NAME;

        $content = '
        <p style="font-size:15px;color:#333333;margin:0 0 15px 0;">Hello!</p>
        <p style="font-size:15px;color:#333333;margin:0 0 20px 0;">This is a test email from <strong style="color:#2d5016;">' . SITE_NAME . '</strong>.</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="background:#d4edda;border-left:4px solid #28a745;padding:15px 20px;margin:0 0 20px 0;border-radius:5px;width:100%;">
            <tr>
                <td style="font-size:15px;color:#155724;">Your SMTP configuration is working correctly!</td>
            </tr>
        </table>
        <hr style="border:none;border-top:1px solid #eeeeee;margin:20px 0;">
        <p style="font-size:14px;color:#666666;margin:0;">Best regards,<br><strong style="color:#2d5016;font-size:15px;">' . SITE_NAME . '</strong></p>';

        $mail->isHTML(true);
        $mail->Body = buildEmailHtml('Test Email', $content);

        $mail->send();
        return ['success' => true, 'message' => 'Test email sent successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Mail error: ' . $mail->ErrorInfo];
    }
}

?>
