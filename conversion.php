<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function conversionMonnaie($montant, $taux_conversion){
    $montant_euro = $montant / $taux_conversion;
    return $montant_euro;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["montant_francs"])){
    $montant_francs = $_POST["montant_francs"];
    
    if (!is_numeric($montant_francs) || $montant_francs < 0 || is_float($montant_francs)) {
        echo "<p style='color: red;'>Veuillez saisir un montant positif en chiffres.</p>";
    } else {
        $taux_conversion = 655.71;
        $montant_euros = conversionMonnaie($montant_francs, $taux_conversion);

        setcookie("montant_euros", $montant_euros, time() + 3600); 
        
        $date_conversion = date("Y-m-d H:i:s");

        $date_jour = date("Y-m-d");
        
        if (!isset($_SESSION["historique_par_jour"][$date_jour])) {
            $_SESSION["historique_par_jour"][$date_jour] = array();
        }
        
        $conversion_existe = false;
        foreach ($_SESSION["historique_par_jour"][$date_jour] as $conversion) {
            if ($conversion["montant_francs"] == $montant_francs && $conversion["montant_euros"] == $montant_euros) {
                $conversion_existe = true;
                break;
            }
        }

        if (!$conversion_existe) {
            $_SESSION["historique_par_jour"][$date_jour][] = array(
                "montant_francs" => $montant_francs,
                "montant_euros" => $montant_euros,
                "date_conversion" => $date_conversion
            );
        }
    }
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Conversion Franc - Euro</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
</head>
<body>
    <h1>Conversion Franc - Euro</h1>
    
    <form method="post">
        <label for="montant_francs">Montant en francs :</label>
        <input type="number" name="montant_francs" step="1" required><br>
        <input type="submit" value="Convertir">
    </form>
        <style>
            
        </style>
    <?php
    if (isset($_COOKIE["montant_euros"])) {
        $montant_euros = $_COOKIE["montant_euros"];
        echo "<label for='montant_euros'>Montant en euros :</label>";
        echo "<input type='number' name='montant_euros' value='$montant_euros' readonly>";
        
    }
    ?>
    <br>
    <form method="post" action="" style="margin-top: 10px;">
    <label for="date_filter">Filtrer par date :</label>
    <input type="date" name="date_filter">
    <input type="submit" name="filter" value="Filtrer">
    </form>

    <?php
     if (isset($_POST["delete_date"])) {

        $date_to_delete = $_POST["date_to_delete"];
        if (isset($_SESSION["historique_par_jour"][$date_to_delete])) {
            unset($_SESSION["historique_par_jour"][$date_to_delete]);

            if (isset($_COOKIE["montant_euros"])) {
                setcookie("montant_euros", "", time() - 3600); 
            }
            echo "<p style='color: green;'>Les conversions pour la date $date_to_delete ont été supprimées avec succès.</p>";
        } else {
            echo "<p style='color: red;'>Aucune conversion trouvée pour la date $date_to_delete.</p>";
        }
    }
    if (isset($_SESSION["historique_par_jour"]) && count($_SESSION["historique_par_jour"]) > 0) {
    echo "<h2>Historique des conversions par jour :</h2>";
    echo "<ul>";
    foreach ($_SESSION["historique_par_jour"] as $date => $conversions_jour) {
        echo "<li>Date : $date</li>";
        echo "<form method='post' action=''>";
        echo "<input type='hidden' name='date_to_delete' value='$date'>";
        echo "<input type='submit' name='delete_date' value='Supprimer'>";
        echo "</form></li>";
    
        echo "<ul>";
        foreach ($conversions_jour as $conversion) {
            $montant_francs = $conversion["montant_francs"];
            $montant_euros = $conversion["montant_euros"];
            $date_conversion = $conversion["date_conversion"];
            echo "<li>$montant_francs Francs = $montant_euros Euros (Date: $date_conversion)</li>";
        }
        echo "</ul>";
    }   echo "</ul>";
}



    session_write_close();    
    ?>
</body>
</html>
