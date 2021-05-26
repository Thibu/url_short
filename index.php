    <?php

        require 'src/includes/debut.php';
        // si le formulaire a été envoyer, je vérifie si j'ai reçu des données
        if(isset($_POST["url"]) && !empty($_POST["url"])):
            //envoyer notre url dans une variable
            //verifier si l'url est valide
            //on va raccourcir l'url
            //verifier si l'url a déjà été proposée
            //stocker l'url est son raccourcie dans la DB
            //afficher l'url raccourcie à notre utilisateur
            //mise en place de la relation URL raccourcie et le lien physique dans la db

            // I :
            $url = $_POST["url"];

            // II :
            //on utilise la fonction native filter_var() prendre en premier parametre la variable à vérifier
            //en second paramètre elle va prendre FILTER_VALIDATE_URL
            if(!filter_var($url,FILTER_VALIDATE_URL)){
                //si l'url n'est pas un lien, on simule un GET qui prend un booléen
                // (true) et un message que j'affiche à notre user
                header("location: ../?error=true&message=url n'est pas valide");
                //quand on demande une redirection, on termine le script grace à exit()
                exit();
            }

            // III : Raccourcir l'URL
            // La fonction crypt() qui prends 2 paramètres : la variable à crypter
            // + rand (équivaux au principe de grain de sel)
            $shortcut = crypt($url, rand());

            // IV : Verifier si l'URL a déjà été proposée
            //Je crée une variable pour me co a la DB
            $bdd = new PDO("mysql:host=localhost;dbname=bitly;charset=utf8","root","");
            //Si le count(* ) > 0 cela veut dire que mon URL est déjà présentedans la DB
            //JE vais donc récuprer le raccourci de l'URL pour l'afficher à notre visiteur
            $request = $bdd->prepare("SELECT COUNT(*)AS x, shortcut
                                        FROM links
                                        WHERE link = ?");

            $request->execute(array($url));
            // Je lance avec un While qui va parcourir chaque entrée de notre requete vers la DB
            while($result = $request->fetch()):
                //Si l'URL envoyée par le User match avec une URL de ma db je vais avoir un count de 1
                if($result["x"] != 0){
                    //Je récupère l'URL raccourcie qui se trouve dans la collone Shortcut de
                    //ma DB et que j'ai capturé dans ma variable $result["shortcut"]
                    $_SESSION["shortcut"] = $result["shortcut"];
                    //je redirige l'utilisateur vers une url personalisée avec un GET
                    header("location: ../?error=true&message=Adresse déjà raccourcie");
                    exit();
                }
            endwhile;
            // V : Stocker l'URL et son raccourci dans ma DB
    
            $request = $bdd->prepare("INSERT INTO links(link, shortcut)
                                            VALUES(?, ?)");
    
            $request->execute(array($url, $shortcut));

            // VI : Afficher l'url raccourcie à l'utilisateur
            //on redirige l'utilisateur vers une URL ou j'aurais encapsulé la
            //valeur raccourcie dans un get

            header('location: ../?short='.$shortcut);
            exit();

            
        endif;


    ?>
    <section class="formulaire">
        <form action="" method="post">
            <input type="url" name="url" id="" placeholder="Shorten your link" required>
            <input type="submit" value="Shorten" >
        </form>
        <p>By clicking SHORTEN, you are agreeing to Bitly’s <a href="">Terms of Service</a> and <a href="">Privacy Policy</a>.</p>
        <?php 
            if(isset($_GET["short"])){
        ?>
        <div class="erreur">
            <h4>URL Raccourcie: </h4>
            <h3>http://localhost/?q=<?= htmlspecialchars($_GET["short"]) ?></h3>
        </div>
        <?php } 
        
        if(isset($_GET["error"]) && $_GET["error"] == true){
            if($_GET["message"] == "url n'est pas valide"){ ?>
                
            <div class="erreur">
                <h4><?= $_GET["message"]?></h4>
            </div>

           <?php }; ?>
           
        <div class="erreur">
            <h4><?= $_GET["message"]?></h4>
        </div>
        <?php }; ?>

    </section>
    <?php require 'src/includes/footer.php'; ?>