<?php
 header('Content-Type: application/json');
 header('Access-Control-Allow-Origin: *');
 header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
 header('Access-Control-Allow-Headers: Content-Type, Authorization');
 
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
    require_once __DIR__ . '/../../controlleurs/activites.php';

    $controlleurActivites = new ControlleurActivites;

    switch($_SERVER['REQUEST_METHOD']) { 
        case 'GET': // Read 
            if(isset($_GET['id'])) {
                // récupération d'une activite dont l'id est passé en paramètre 
                $controlleurActivites->afficherUneActivite($_GET['id']);
            } 
            elseif(isset($_GET['utilisateur_id'])) {
                // récupération des activites du panier d'un utilisateur dont l'id est passé en paramètre
                $controlleurActivites->afficherActivitesPanierUtilisateur($_GET['utilisateur_id']);
            }
            else {
                // Récupération de l'ensemble des activites
                $controlleurActivites->afficherActivites();
            } 
            break; 

        case 'POST': // Create - Ajout d'une activite
            $corpsJSON = file_get_contents('php://input'); 
            $data = json_decode($corpsJSON, TRUE);
            $controlleurActivites->ajouterActivite($data);
            break; 

        case 'PUT': // Update - Modification d'une activite
            if(isset($_GET['id'])) { 
                // modification des données d'une activite
                $corpsJSON = file_get_contents('php://input'); 
                $data = json_decode($corpsJSON, TRUE);
                $controlleurActivites->modifierActivite($data);
            }
            else {
                $resultat = new stdClass();
                $resultat->erreur = "Erreur: Impossible de modifier une activite, aucun identifiant fourni.";
                echo json_encode($resultat);
            }
            break; 

        case 'DELETE': // Delete - Suppression d'une activite
            if(isset($_GET['id'])) { 
                $controlleurActivites->supprimerActivite($_GET['id']); 
            }
            else {
                $resultat = new stdClass();
                $resultat->erreur = "Erreur: Impossible de supprimer une activite, aucun identifiant fourni.";
                echo json_encode($resultat);
            }
            break; 

        default:
            $controlleurActivites->afficherActivites();
    } 
?>
