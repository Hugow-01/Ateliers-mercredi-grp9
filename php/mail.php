<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

function notifierFamille(
    PDO    $db,
    string $loginFamille,
    int    $idEnfant,
    int    $idCreneau,
    string $type,
    string $msgTexte
): void {

    // Notification en base
    $db->prepare("
        INSERT INTO Notification (login_famille, id_enfant, id_creneau, type, message)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$loginFamille, $idEnfant, $idCreneau, $type, $msgTexte]);

    try {

        $mail = new PHPMailer(true);

        // SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ranjatinasoa@gmail.com';
        $mail->Password   = 'timkmklfgvegnwec';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Encodage UTF-8
        $mail->CharSet = 'UTF-8';

        // Expéditeur
        $mail->setFrom('ranjatinasoa@gmail.com', 'Ateliers du Mercredi');

        // Destinataire
        $mail->addAddress($loginFamille);

        // Contenu
        $mail->isHTML(false);

        $mail->Subject = ($type === 'accepte')
            ? 'Inscription confirmée – Ateliers du Mercredi'
            : 'Mise en liste d\'attente – Ateliers du Mercredi';

        $mail->Body =
            "Bonjour,\n\n"
            . $msgTexte . "\n\n"
            . "Connectez-vous à votre espace parent pour plus de détails :\n"
            . "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/parent-enfants.php\n\n"
            . "Cordialement,\n"
            . "Les Ateliers du Mercredi";

        $mail->send();

    } catch (Exception $e) {

        error_log("Erreur email : " . $mail->ErrorInfo);

    }
}