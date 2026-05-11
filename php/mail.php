<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

define('BASE_URL', 'http://51.68.91.213/info9/Ateliers-mercredi-grp9');

function buildMailHtml(string $corps, string $lien, string $lienParent): string {
    return "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;'>
        <div style='background: #fdd835; padding: 20px; text-align: center;'>
            <h1 style='margin: 0; color: #3e2723; font-size: 1.5rem; font-family: Arial, sans-serif;'>Ateliers du Mercredi</h1>
        </div>
        <div style='padding: 30px; background: #ffffff;'>
            <p style='font-family: Arial, sans-serif; color: #333; font-size: 15px;'>Bonjour,</p>
            <p style='font-family: Arial, sans-serif; color: #333; font-size: 15px; line-height: 1.6;'>" . nl2br(htmlspecialchars($corps)) . "</p>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' style='margin: 30px auto;'>
                <tr>
                    <td style='padding-right: 10px;'>
                        <a href='" . $lien . "'
                           style='display: inline-block; background: #ff5e78; color: #ffffff; padding: 12px 24px;
                                  border-radius: 8px; text-decoration: none; font-weight: bold;
                                  font-family: Arial, sans-serif; font-size: 14px; mso-padding-alt: 0;
                                  border: 2px solid #ff5e78;'>
                            Voir les activites
                        </a>
                    </td>
                    <td>
                        <a href='" . $lienParent . "'
                           style='display: inline-block; background: #1a1a2e; color: #ffffff; padding: 12px 24px;
                                  border-radius: 8px; text-decoration: none; font-weight: bold;
                                  font-family: Arial, sans-serif; font-size: 14px; mso-padding-alt: 0;
                                  border: 2px solid #1a1a2e;'>
                            Mon espace parent
                        </a>
                    </td>
                </tr>
            </table>
            <p style='color: #888; font-size: 0.85rem; font-family: Arial, sans-serif;'>Cordialement,<br>Les Ateliers du Mercredi</p>
        </div>
        <div style='background: #f5f5f5; padding: 15px; text-align: center; font-size: 0.8rem; color: #888; font-family: Arial, sans-serif;'>
            Ateliers du Mercredi - 5 avenue Jean-Cocteau, 31400 Toulouse
        </div>
    </div>";
}

function createMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ranjatinasoa@gmail.com';
    $mail->Password = 'timkmklfgvegnwec';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('ranjatinasoa@gmail.com', 'Ateliers du Mercredi');
    return $mail;
}

/**
 * Mail de bienvenue à l'inscription
 */
function envoyerMailBienvenue(string $email, string $nomFamille): void {
    try {
        $mail = createMailer();
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenue aux Ateliers du Mercredi !';

        $corps = "Bienvenue, famille $nomFamille !\n\n"
            . "Votre compte a bien été créé sur le site des Ateliers du Mercredi.\n\n"
            . "Vous pouvez dès maintenant vous connecter et inscrire vos enfants aux activités du mercredi.\n\n"
            . "Rendez-vous sur votre espace parent pour commencer.";

        $lien = BASE_URL . '/activites.php';
        $lienParent = BASE_URL . '/parent-enfants.php';

        $mail->Body = buildMailHtml($corps, $lien, $lienParent);
        $mail->AltBody = "Bonjour,\n\n" . $corps . "\n\nVoir les activites : " . $lien
                       . "\nMon espace : " . $lienParent . "\n\nCordialement,\nLes Ateliers du Mercredi";
        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur envoi mail bienvenue : " . ($mail->ErrorInfo ?? $e->getMessage()));
    }
}

/**
 * Mail de confirmation d'inscription à un créneau
 */
function envoyerMailConfirmationInscription(
    PDO $db,
    int $idFamille,
    int $idEnfant,
    int $idCreneau
): void {
    $stmtF = $db->prepare("SELECT login, nom FROM Famille WHERE id = ?");
    $stmtF->execute([$idFamille]);
    $famille = $stmtF->fetch();
    if (!$famille) return;

    $stmtE = $db->prepare("SELECT prenom, nom FROM Enfant WHERE id = ?");
    $stmtE->execute([$idEnfant]);
    $enfant = $stmtE->fetch();
    if (!$enfant) return;

    $stmtC = $db->prepare("SELECT date, debut, fin, nom_activite, id_salle FROM Creneau WHERE id = ?");
    $stmtC->execute([$idCreneau]);
    $creneau = $stmtC->fetch();
    if (!$creneau) return;

    $dateF = date('d/m/Y', strtotime($creneau['date']));
    $debut = substr($creneau['debut'], 0, 5);
    $fin = substr($creneau['fin'], 0, 5);
    $activite = $creneau['nom_activite'];
    $salle = $creneau['id_salle'] ? " - Salle " . $creneau['id_salle'] : '';

    $prenomNom = $enfant['prenom'] . ' ' . $enfant['nom'];

    $corps = "Inscription confirmée !\n\n"
        . "Bonjour famille " . $famille['nom'] . ",\n\n"
        . "Votre enfant $prenomNom a bien été inscrit à l'activité suivante :\n\n"
        . "• Activité : $activite\n"
        . "• Date : $dateF\n"
        . "• Horaire : $debut - $fin$salle\n\n"
        . "Vous pouvez consulter ou gérer cette inscription depuis votre espace parent.\n\n"
        . "À mercredi !";

    // Insérer notification en base
    $db->prepare("
        INSERT INTO Notification (id_famille, id_enfant, id_creneau, type, message)
        VALUES (?, ?, ?, 'accepte', ?)
    ")->execute([$idFamille, $idEnfant, $idCreneau, $corps]);

    try {
        $mail = createMailer();
        $mail->addAddress($famille['login']);
        $mail->isHTML(true);
        $mail->Subject = "Inscription confirmée - $activite du $dateF";

        $lien = BASE_URL . '/activites.php';
        $lienParent = BASE_URL . '/parent-enfants.php';

        $mail->Body = buildMailHtml($corps, $lien, $lienParent);
        $mail->AltBody = "Bonjour,\n\n" . $corps . "\n\nVoir les activites : " . $lien
                       . "\nMon espace : " . $lienParent . "\n\nCordialement,\nLes Ateliers du Mercredi";
        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur envoi mail confirmation inscription : " . ($mail->ErrorInfo ?? $e->getMessage()));
    }
}

/**
 * Notifie une famille (mise en attente, acceptation, annulation, modification)
 */
function notifierFamille(
    PDO $db,
    int $idFamille,
    int $idEnfant,
    int $idCreneau,
    string $type,
    string $msgTexte
): void {
    $stmtF = $db->prepare("SELECT login FROM Famille WHERE id = ?");
    $stmtF->execute([$idFamille]);
    $famille = $stmtF->fetch();
    if (!$famille) return;
    $emailFamille = $famille['login'];

    $db->prepare("
        INSERT INTO Notification (id_famille, id_enfant, id_creneau, type, message)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$idFamille, $idEnfant, $idCreneau, $type, $msgTexte]);

    try {
        $mail = createMailer();
        $mail->addAddress($emailFamille);
        $mail->isHTML(true);

        $sujets = [
            'accepte' => 'Place confirmée - Ateliers du Mercredi',
            'annulation' => 'Activité annulée - Ateliers du Mercredi',
            'modification' => 'Modification d\'activité - Ateliers du Mercredi',
        ];
        $mail->Subject = $sujets[$type] ?? "Liste d'attente - Ateliers du Mercredi";

        $lien = BASE_URL . '/activites.php';
        $lienParent = BASE_URL . '/parent-enfants.php';

        $mail->Body = buildMailHtml($msgTexte, $lien, $lienParent);
        $mail->AltBody = "Bonjour,\n\n" . $msgTexte . "\n\nVoir les activites : " . $lien
                       . "\nMon espace : " . $lienParent . "\n\nCordialement,\nLes Ateliers du Mercredi";
        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur envoi mail notifierFamille : " . ($mail->ErrorInfo ?? $e->getMessage()));
    }
}

/**
 * Notifie tous les inscrits d'un créneau (modification ou annulation)
 */
function notifierCreneauModifie(
    PDO $db,
    int $idCreneau,
    string $type,
    string $msgTexte
): void {
    $stmt = $db->prepare("
        SELECT DISTINCT e.id_famille, e.id AS id_enfant
        FROM Enfant_Creneau ec
        JOIN Enfant e ON e.id = ec.id_enfant
        WHERE ec.id_creneau = ?
    ");
    $stmt->execute([$idCreneau]);
    $inscrits = $stmt->fetchAll();

    $stmt2 = $db->prepare("
        SELECT DISTINCT e.id_famille, e.id AS id_enfant
        FROM ListeAttente la
        JOIN Enfant e ON e.id = la.id_enfant
        WHERE la.id_creneau = ?
    ");
    $stmt2->execute([$idCreneau]);
    $attente = $stmt2->fetchAll();

    foreach (array_merge($inscrits, $attente) as $p) {
        notifierFamille($db, (int)$p['id_famille'], (int)$p['id_enfant'], $idCreneau, $type, $msgTexte);
    }
}

/**
 * Envoie un mail de modification de créneau (avec option de désinscription)
 */
function envoyerMailModificationCreneau(
    PDO $db,
    int $idCreneau,
    string $ancienneDate,
    string $ancienDebut,
    string $ancienneFin,
    string $nouvelleDate,
    string $nouveauDebut,
    string $nouveauFin,
    string $nomActivite
): void {
    $msgTexte = "Un créneau de l'activité \"$nomActivite\" a été modifié par l'administration.\n\n"
        . "Ancien horaire : " . date('d/m/Y', strtotime($ancienneDate)) . " de " . substr($ancienDebut, 0, 5) . " à " . substr($ancienneFin, 0, 5) . "\n"
        . "Nouvel horaire : " . date('d/m/Y', strtotime($nouvelleDate)) . " de " . substr($nouveauDebut, 0, 5) . " à " . substr($nouveauFin, 0, 5) . "\n\n"
        . "Votre inscription reste valide. Si ce nouvel horaire ne vous convient pas, vous pouvez vous désinscrire depuis votre espace parent.\n\n"
        . "Rendez-vous sur votre espace parent pour gérer vos inscriptions.";

    notifierCreneauModifie($db, $idCreneau, 'modification', $msgTexte);
}

/**
 * Envoie un mail de réinitialisation de mot de passe
 */
function envoyerMailMotDePasseOublie(string $email, string $token): bool {
    try {
        $mail = createMailer();
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de mot de passe - Ateliers du Mercredi';

        $lienReset = BASE_URL . '/reset-password.php?token=' . urlencode($token);

        $corps = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;'>
            <div style='background: #fdd835; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; color: #3e2723; font-size: 1.5rem; font-family: Arial, sans-serif;'>Ateliers du Mercredi</h1>
            </div>
            <div style='padding: 30px; background: #ffffff;'>
                <p style='font-family: Arial, sans-serif; color: #333; font-size: 15px;'>Bonjour,</p>
                <p style='font-family: Arial, sans-serif; color: #333; font-size: 15px; line-height: 1.6;'>
                    Vous avez demandé la réinitialisation de votre mot de passe.<br>
                    Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.<br>
                    Ce lien est valable pendant <strong>1 heure</strong>.
                </p>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' style='margin: 30px auto;'>
                    <tr>
                        <td>
                            <a href='" . $lienReset . "'
                               style='display: inline-block; background: #ff5e78; color: #ffffff;
                                      padding: 14px 32px; border-radius: 8px; text-decoration: none;
                                      font-weight: bold; font-family: Arial, sans-serif; font-size: 15px;
                                      border: 2px solid #ff5e78;'>
                                Réinitialiser mon mot de passe
                            </a>
                        </td>
                    </tr>
                </table>
                <p style='font-family: Arial, sans-serif; color: #888; font-size: 13px;'>
                    Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.
                </p>
                <p style='color: #888; font-size: 0.85rem; font-family: Arial, sans-serif;'>Cordialement,<br>Les Ateliers du Mercredi</p>
            </div>
            <div style='background: #f5f5f5; padding: 15px; text-align: center; font-size: 0.8rem; color: #888; font-family: Arial, sans-serif;'>
                Ateliers du Mercredi - 5 avenue Jean-Cocteau, 31400 Toulouse
            </div>
        </div>";

        $mail->Body = $corps;
        $mail->AltBody = "Bonjour,\n\nPour réinitialiser votre mot de passe, cliquez sur ce lien (valable 1h) :\n"
                       . $lienReset . "\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\nCordialement,\nLes Ateliers du Mercredi";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur envoi mail reset password : " . ($mail->ErrorInfo ?? $e->getMessage()));
        return false;
    }
}