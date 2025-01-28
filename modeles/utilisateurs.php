<?php

require_once  __DIR__ . "/../include/config.php";

class modele_utilisateur {
    public $id; 
    public $nom; 
    public $email;
    public $profileImg;

    public function __construct($id, $nom, $email, $profileImg) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->profileImg = $profileImg;
    }

    static function connecter() {
        $mysqli = new mysqli(Db::$host, Db::$username, Db::$password, Db::$database);

        if ($mysqli -> connect_errno) {
            echo "Échec de connexion à la base de données MySQL: " . $mysqli -> connect_error;
            exit();
        }

        return $mysqli;
    }

    public static function ObtenirUn($id) {
        $res = new stdClass();
        $mysqli = self::connecter();

        if ($requete = $mysqli->prepare("SELECT * FROM utilisateurs WHERE id=?")) {
            $requete->bind_param("i", $id);
            $requete->execute();
            $resultatRequete = $requete->get_result(); 
            
            if($enregistrement = $resultatRequete->fetch_assoc()) {
                $res->utilisateur = new modele_utilisateur($enregistrement['id'], $enregistrement['nom'], $enregistrement['email'], $enregistrement['profile_img']);
            } else {
                $res->erreur = "Aucun utilisateur trouvé.";
                $res->utilisateur = null;
            }   
            $requete->close();
        } else {
            $res->erreur = "Une erreur a été détectée dans la requête utilisée.";
        }
        return $res;
    }

}

?>