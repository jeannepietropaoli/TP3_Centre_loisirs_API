<?php

require_once __DIR__ . '/../modeles/activites.php';

class ControlleurActivites {
    function afficherActivites() {
        $res = modele_activite::ObtenirTous();
        echo json_encode($res);
    }

    // récupère une liste à partir de l'identifiant (id) passé en paramètre dans l'URL
    function afficherUneActivite() {
        $res = new stdClass();
        
        if(isset($_GET["id"])) {
            $res = modele_activite::ObtenirUne($_GET["id"]);
            if(!$res->activite) {
                $res->erreur = "Aucune activite correspondant à l'id fourni";
            } 
        } else {
            $res->erreur = "L'identifiant (id) de la liste est manquant dans l'url";
        }
        echo json_encode($res);
    }

    function afficherActivitesPanierUtilisateur() {
        $res = new stdClass();

        if(isset($_GET["utilisateur_id"])) {
            $res = modele_utilisateur::ObtenirUn($_GET["utilisateur_id"]);
            if($res->utilisateur) {
                // si l'utilisateur existe
                $res = modele_activite::ObtenirActivitesPanierUtilisateur($_GET["utilisateur_id"]);
            }
        } else {
            $res->erreur = "L'identifiant (id) de l'utilisateur est manquant dans l'url";
        }
        echo json_encode($res);
    }

    function ajouterActivite($data) {
        $res = new stdClass();
        if(isset($data['nom']) && isset($data['type']) && isset($data['prix']) && isset($data['date']) && isset($data['heure']) && isset($data['description']) && isset($data['imgUrl'])) {    
            $res->message = modele_activite::ajouter($data['nom'], $data['type'], $data['prix'], $data['date'], $data['heure'], $data['description'], $data['imgUrl']);
        } else {
            $res->message = "Impossible d'ajouter une activite. Des informations sont manquantes ou erronées.";
        }
        echo json_encode($res);

    }

    function modifierActivite($data) {
        $res = new stdClass();

        if(isset($_GET["id"]) && isset($data['nom']) && isset($data['type']) && isset($data['prix']) && isset($data['date']) && isset($data['heure']) && isset($data['description']) && isset($data['imgUrl'])) {   
            $res->message = modele_activite::editer($_GET["id"], $data['nom'], $data['type'], $data['prix'], $data['date'], $data['heure'], $data['description'], $data['imgUrl']);
        } else {
            $res->message = "Impossible de modifier l'activite. Des informations sont manquantes ou erronées.";
        }

        echo json_encode($res);

    }

    function supprimerActivite() {
        $res = new stdClass();
        $res->message = modele_activite::supprimer($_GET['id']);
        echo json_encode($res);
    }

    // devrait etre dans modele panier ?
    function ajouterActiviteAuPanierDUnUtilisateur($liste_id, $chanson_id) {
        $res = new stdClass();
        $res->message = modele_activite::ajouterActiviteAuPanierDUnUtilisateur($liste_id, $chanson_id);
        echo json_encode($res);
    }
}

?>