<?php

class ConnexionController extends BaseController
{


    private $valid;
    private $err_nom;
    private $err_prenom;
    private $err_mail;
    private $err_pwd;


    public function connexion()
    {
        $errors = '';

        if (isset($_POST['mail'])   && isset($_POST['pwd'])) {
            $mail = $_POST['mail'];
            $pwd = $_POST['pwd'];
            if ($this->verif_connexion($mail, $pwd)) {
                header('Location: /');
                exit;
            } else {
                $errors = 'Identifiants incorrects';
            }
        }
        // on choisi la template à appeler
        $template = $this->twig->load('connexion/connexion.html');

        // $post = new Post();
        // $listPost = $post->getPosts();


        // Puis on affiche la page avec la méthode render
        echo $template->render([
            'title' => 'Connexion',
            'errors' => $errors,
        ]);
    }

    private function verif_connexion($mail, $pwd)
    {

        $connexion = new Connexion();
        $resultConnexion = $connexion->checkConnexion($mail, $pwd);
        var_dump($resultConnexion);
        return true;



        //Variables d'entrées

        //Variables déclarées
        $this->valid = (bool) true;




        global $DB;

        $mail = trim($mail);
        $pwd = trim($pwd);

        if (empty($mail)) {
            $this->valid  = false;
            $this->err_mail = "Ce champ ne peut pas être vide";
        }

        if (empty($pwd)) {
            $this->valid  = false;
            $this->err_pwd = "Ce champ ne peut pas être vide";
        }



        if ($this->valid) {
            $req = $DB->prepare("SELECT pwd FROM utilisateur WHERE mail =?");
            $req->execute(array($mail));

            $req = $req->fetch();


            if (isset($req['pwd'])) {
                if (!password_verify($pwd, $req['pwd'])) {
                    $this->valid  = false;
                    $this->err_pwd = "Les informations rentrées sont incorrectes.";
                }
            } else {
                $this->valid  = false;
                $this->err_pwd = "Les informations rentrées sont incorrectes.";
            }
        }


        if ($this->valid) {
            $req = $DB->prepare("SELECT * FROM utilisateur WHERE mail =?");
            $req->execute(array($mail));

            $req_user = $req->fetch();

            if (isset($req_user['id'])) {
                $date_connexion = date('Y-m-d H:i:s');


                $req = $DB->prepare("UPDATE utilisateur SET date_connexion = ? WHERE id = ?");
                $req->execute(array($date_connexion, $req_user['id']));

                $_SESSION['id'] = $req_user['id'];
                $_SESSION['prenom'] = $req_user['prenom'];
                $_SESSION['mail'] = $req_user['mail'];
                $_SESSION['role'] = $req_user['role'];

                header('Location: index.php');
                exit;
            } else {
                $this->valid  = false;
                $this->err_pwd = "Les informations rentrées sont incorrectes.";
            }
        }

        return [$this->err_mail, $this->err_pwd];
    }
}