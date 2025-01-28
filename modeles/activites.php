<?php

require_once  __DIR__ . "/../include/config.php";
require_once __DIR__ . '/../modeles/utilisateurs.php';

class modele_activite {
    public $id; 
    public $nom; 
    public $type;
    public $prix;
    public $date;
    public $heure;

    public function __construct($id, $nom, $type, $prix, $date, $heure) {
        $this->id = $id;
        $this->nom = $nom;
        $this->type = $type;
        $this->prix = $prix;
        $this->date = $date;
        $this->heure = $heure;
    }

    static function connecter() {
        $mysqli = new mysqli(Db::$host, Db::$username, Db::$password, Db::$database);

        if ($mysqli -> connect_errno) {
            echo "Échec de connexion à la base de données MySQL: " . $mysqli -> connect_error;
            exit();
        }

        return $mysqli;
    }

    public static function ObtenirTous() {
        $res = new stdClass();
        $res->activites = [];
        $mysqli = self::connecter();

        $resultatRequete = $mysqli->query("SELECT activites.*, types_activites.type FROM activites INNER JOIN types_activites ON activites.type_activite_id = types_activites.id ORDER BY activites.date");
        if($resultatRequete) {
            foreach ($resultatRequete as $enregistrement) {
                $res->activites[] = new modele_activite($enregistrement['id'], $enregistrement['nom'], $enregistrement['types_activites.type'], $enregistrement['prix'], $enregistrement['date'], $enregistrement['heure']);
            }
        } else {
            $res->erreur = "Erreur: Aucune activite trouvée.";
        }
        $requete->close();
        return $res;
    }

    public static function ObtenirUne($id) {
        $res = new stdClass();
        $mysqli = self::connecter();

        if ($requete = $mysqli->prepare("SELECT activites.*, types_activites.type FROM activites INNER JOIN types_activites ON activites.type_activite_id = types_activites.id ORDER BY activites.date")) {
            $requete->bind_param("i", $id); 
            $requete->execute();
            $resultatRequete = $requete->get_result();
            
            if($enregistrement = $resultatRequete->fetch_assoc()) {
                $res->activite = new modele_activite($enregistrement['id'], $enregistrement['nom'], $enregistrement['types_activites.type'], $enregistrement['prix'], $enregistrement['date'], $enregistrement['heure']);
            } else {
                $res->erreur = "Aucune activite trouvée.";
                $res->activite = null;
            }   
            $requete->close();
        } else {
            $res->erreur = "Une erreur a été détectée dans la requête utilisée.";
        }

        return $res;
    }

    public static function ObtenirActivitesPanierUtilisateur($utilisateur_id) {
        $res = new stdClass();
        $res->activites = [];
        $mysqli = self::connecter();

        // verifie si l'utilisateur existe
        $utilisateurRes = modele_utilisateur::ObtenirUn($utilisateur_id);
        if(property_exists($utilisateurRes, 'errreur')) {
            $res = $utilisateurRes;
        } else {
            $requete = $mysqli->prepare("SELECT activites.*, types_activites.type FROM activites INNER JOIN types_activites ON activites.type_activite_id = types_activites.id INNER JOIN paniers ON activites.id = paniers.utilisateur_id WHERE utilisateur_id = ? ORDER BY activites.date");
            $requete->bind_param("i", $utilisateur_id);
    
            if($requete->execute()) {
                $resultatRequete = $requete->get_result();
                if($resultatRequete) {
                    foreach ($resultatRequete as $enregistrement) {
                        $res->activites[] = new modele_activite($enregistrement['id'], $enregistrement['nom'], $enregistrement['types_activites.type'], $enregistrement['prix'], $enregistrement['date'], $enregistrement['heure']);
                    }
                } else {
                    $res->erreur = "Aucune activite trouvée.";
                }
            } else {
                $res->erreur = "Erreur: Échec de l'exécution de la requête.";
            }
            $requete->close();
        }
        return $res;
    }

    public static function ajouter($nom, $type, $prix, $date, $heure) {
        $message = '';
        $mysqli = self::connecter();
  
        if ($requete = $mysqli->prepare("INSERT INTO activites(nom, type_activite_id, prix, date, heure) VALUES(?,?,?,?,?)")) {      
            $requete->bind_param("sidss", $nom, $type, $prix, $date, $heure);

            if($requete->execute()) {
                $message = "Activite ajoutée";
            } else {
                $message =  "Une erreur est survenue lors de l'ajout.";
            }
            $requete->close();
        } else  {
            $message = "Une erreur a été détectée dans la requête utilisée.";
        }

        return $message;
    }

    public static function editer($nom, $type, $prix, $date, $heure) {
        $message = '';
        $mysqli = self::connecter();
        
        if ($requete = $mysqli->prepare("UPDATE activites SET nom=?, type_activite_id=?, prix=?, date=?, heure=? WHERE id=?")) {      
            $requete->bind_param("sidss", $nom, $type, $prix, $date, $heure);

            if($requete->execute()) {
                $requete->affected_rows === 0 ? $message = "Aucune activite correspondant à cet id" : $message = "Activite modifiée";
            } else {
                $message =  "Une erreur est survenue lors de l'édition.";
            }
            $requete->close();
        } else  {
            $message = "Une erreur a été détectée dans la requête utilisée.";
        }
        return $message;
    }

    public static function supprimer($id) {
        $message = '';
        $mysqli = self::connecter();
        
        if ($requete = $mysqli->prepare("DELETE FROM activites WHERE id=?")) {      
            $requete->bind_param("i", $id);

            if($requete->execute()) { 
                $message = $requete->affected_rows === 0 ? "Aucune activite correspondant à cet id" : "Activite supprimée";
            } else {
                $message =  "Une erreur est survenue lors de la suppression.";
            }
            $requete->close();
        } else  {
            echo "Une erreur a été détectée dans la requête utilisée.";
        }

        return $message;
    }

    public static function activiteExistante($liste_id) {
        $mysqli = self::connecter();
        $requete = $mysqli->prepare("SELECT * FROM activites WHERE id = ?");
        $requete->bind_param("i", $liste_id);
        $requete->execute();
        $resultatRequete = $requete->get_result();
        $requete->close();
    
        return $resultatRequete->num_rows > 0;
    }

    public static function activiteDejaDansLePanier($utilisateur_id, $activite_id) {
        $mysqli = self::connecter();
        $requete = $mysqli->prepare("SELECT * FROM paniers WHERE utilisateur_id = ? AND activite_id = ?");
        $requete->bind_param("ii", $utilisateur_id, $activite_id);
        $requete->execute();
        $resultatRequete = $requete->get_result();
        $requete->close();
    
        return $resultatRequete->num_rows > 0; // true si l'activite est deja dans le panier (un enregistrement existe)
    }

    public static function ajouterActiviteAuPanierDUnUtilisateur($utilisateur_id, $activite_id) {
        $message = '';
        $mysqli = self::connecter();

        if(!self::activiteExistante($activite_id)) {
            $message = "L'activite n'existe pas";
        } elseif (self::activiteDejaDansLePanier($utilisateur_id, $activite_id)) {
            $message = "L'activite est deja dans le panier. Les activites sont nominatives et ne peuvent etre achetees plusieurs fois.";
        } else {
            if ($requete = $mysqli->prepare("INSERT INTO paniers(liste_id, chanson_id) VALUES(?, ?)")) {      
                $requete->bind_param("ii", $utilisateur_id, $activite_id);
                if($requete->execute()) {
                    $message = "Activite ajoutee au panier.";
                } else {
                    $message =  "Une erreur est survenue lors de l'ajout.";
                }
                $requete->close();
            } else  {
                $message = "Une erreur a été détectée dans la requête utilisée.";
            }
        }
        return $message;
    }
}

?>