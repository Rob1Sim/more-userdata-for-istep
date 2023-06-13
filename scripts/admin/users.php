<?php
/**
 * Gestion de la listes des utilisateur
 */

use MUDF_ISTEP\Entity\Member;
use MUDF_ISTEP\Entity\Location;
use MUDF_ISTEP\Entity\PersonalPage;
use MUDF_ISTEP\Entity\Team;
use MUDF_ISTEP\Exception\EntityNotFound;
use MUDF_ISTEP\Exception\InsertError;
use MUDF_ISTEP\Exception\InvalidParameter;
use MUDF_ISTEP\Exception\MemberNotFound;
use MUDF_ISTEP\Exception\UpdateError;

wp_enqueue_script('more-userdata-for-istep-admin-js', plugins_url('../../public/scripts/more-userdata-for-istep-admin.js', __FILE__), array(), false, true);


/**
 * Affiche toutes les informations des utilisateurs de l'ISTeP
 * @return void
 */
function more_userdata_istep_users_list():void
{

    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?><div class="wrap">
        <h1>Liste des membres de l'ISTeP</h1>
        <label for="dropdown-colonne">Trier par :</label>
        <select id="dropdown-colonne">
            <option value="0">ID</option>
            <option value="1">Nom de l'utilisateur</option>
            <option value="2">Login</option>
            <option value="3">Equipe</option>
            <option value="4">Fonction</option>
            <option value="5">Email</option>
            <option value="6">Numéro de téléphone</option>
            <option value="7">Rang dans l'équipe</option>
            <option value="8">Tour du bureau</option>
            <option value="9">Bureau</option>
            <option value="10">Campus</option>
            <option value="11">Employeur</option>
            <option value="12">Case courrier</option>
        </select>

        <label for="search">Rechercher :</label>
        <input type="text" id="search">
        <table class="wp-list-table widefat fixed striped " id="istep-users-list">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom de l'utilisateur</th>
                <th>Login</th>
                <th>Equipes</th>
                <th>Fonction</th>
                <th>Email</th>
                <th>Numéro de téléphone</th>
                <th>Rang dans l'équipe</th>
                <th>Tour du bureau</th>
                <th>Bureau</th>
                <th>Campus</th>
                <th>Employeur</th>
                <th>Case courrier</th>
                <th>Modifier</th>
                <th>Supprimer</th>
            </tr>
            </thead>
            <tbody>
<?php
    $users = Member::getAll();
    foreach ($users as $user) {
        try {
            $member = Member::findById($user->getId());
            $teams = $member->getTeamsNames();
            $wp_user = $member->getWPUser();
            echo '<tr>';
            echo '<td>' . $member->getId() . '</td>';
            echo '<td>' . $wp_user->display_name . '</td>';
            echo '<td>' . $wp_user->user_login . '</td>';
            echo '<td>';
            foreach ($teams as $team) {
                echo $team.', ';
            }
            echo '</td>';
            echo '<td>' . $member->getFunction() . '</td>';
            echo '<td>' . $wp_user->user_email . '</td>';
            echo '<td>' . $member->getPhone() . '</td>';
            echo '<td>' . $member->getTeamRank() . '</td>';
            echo '<td>' . $member->getReadableOfficeTower() . '</td>';
            echo '<td>' . $member->getOffice() . '</td>';
            try {
                echo '<td>' . $member->getLocation()->getName() . '</td>';
            } catch (EntityNotFound $e) {
                echo '<td>Pas de campus </td>';
            }
            echo '<td>' . $member->getEmployer() . '</td>';
            echo '<td>' . $member->getMailCase() . '</td>';
            echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=modify_users_data&id=' . $member->getId()) . '">
                            <input type="hidden" name="id" value="' . $member->getId() . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
            echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=erase_user&id=' . $member->getId()) . '">
                            <input type="hidden" name="user_delete_id" value="' . $member->getId() . '">
                            <button type="submit" class="button button-primary" style="background: #d0021b; border-color: #d0021b">Supprimer</button>
                        </form>
                      </td>';
            echo '</tr>';
        } catch (InvalidParameter|MemberNotFound $e) {
            echo '<div class="notice notice-error"><p>Une erreur est survenue</p></div>';
        }

    }
    ?></tbody>
        </table>
    </div><?php
}

/**
 * Formulaire de modification de l'équipe d'un utilisateur
 * @return void
 */
function more_userdata_istep_users_edit_data():void
{
    if (! current_user_can(ADMIN_CAPACITY)) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (isset($_POST['changeTeams']) &&(isset($_POST['idUser']))) {
        $id_user = sanitize_text_field($_POST['idUser']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $function = sanitize_text_field($_POST['function']);
        $rank = sanitize_text_field($_POST['rank']);
        $employer = sanitize_text_field($_POST['employer']);
        $mailCase = sanitize_text_field($_POST['mailCase']);
        $campus = sanitize_text_field($_POST['campus']);
        $office_tower = sanitize_text_field($_POST['tourBureau']);
        $office = sanitize_text_field($_POST['office']);
        $phone = sanitize_text_field($_POST['phone']);

        if(isset($last_name) && isset($first_name) && isset($function) && isset($rank)
            && isset($employer) && isset($mailCase) && isset($campus) && isset($office_tower) && isset($office) && isset($phone)) {

            //Vérification du campus
            if(!Location::is_location($campus)) {
                echo '<div id="message" class="notice notice-error">Le campus n\'existe pas !</div>';
                edit_user_form();
                exit();
            }

            //Vérification du téléphone
            if (strlen($phone)!=10) {
                echo '<div id="message" class="notice notice-error">Le numéro de téléphone est incorrecte !</div>';
                edit_user_form();
                exit();
            }
            //Vérification de l'employeur
            if ($employer !== "sorbonne-universite" && $employer != "cnrs" && $employer != "aucun") {

                echo '<div id="message" class="notice notice-error">L\'employeur n\'existe pas !</div>';
                edit_user_form();
                exit();
            }
            //Mis à jour de l'utilisateur

            try {
                $new_member = Member::findById($id_user);
                $new_member->setFunction($function);
                $new_member->setEmployer($employer);
                $new_member->setLocation($campus);
                $new_member->setOffice($office);
                $new_member->setOfficeTower($office_tower);
                $new_member->setPhone($phone);
                $new_member->setMailCase($mailCase);
                $new_member->setTeamRank($rank);
                $new_member->save();

                $display_name = $last_name ." ".$first_name;
                $user_data = $new_member->getWPUser();
                $user_data->display_name = $display_name;

                $result = wp_update_user($user_data);

                if (is_wp_error($result)) {
                    $error_message = $result->get_error_message();
                    echo '<div id="message" class="notice notice-error">Erreur lors de la modifcation du nom : '.$error_message.'</div>';
                    edit_user_form();
                    exit();
                }
                //Supprime la page personel pour la recreer
                $new_member->deletePersonalPage();
                PersonalPage::create_personal_page($display_name, $user_data->user_login, $new_member->getWpId());
            } catch (InsertError|UpdateError |InvalidParameter|MemberNotFound $e) {
                echo '<div id="message" class="notice notice-error">'.$e->getMessage().'</div>';
            }
        }
        //Vérification des équipes
        $verified_teams = [];
        try {
            $member = Member::findById($id_user);
            $already_exist_data = $member->getTeamsId();
            $teams_already_in = [];
            if (!isset($_POST['teams'])) {
                $verified_teams[] = get_option("default_team");
            } else {
                $teams = $_POST['teams'];

                foreach ($teams as $team) {
                    $team_object = Team::findById(sanitize_text_field($team))->getId();
                    //Si l'équipe n'éxiste pas on l'ajoute
                    if (!in_array($team, $already_exist_data)) {
                        $verified_teams[] = $team_object;
                    } else {
                        $teams_already_in[] = $team_object;
                    }
                }
            }

            //On récupère les tables qui n'était pas coché mais présent dans les tables de l'utilisateur
            $teams_to_delete = array_diff($already_exist_data, $teams_already_in);
            foreach ($teams_to_delete as $team) {
                //on les supprime
                $member->deleteTeam($team);
            }
            //on ajoute les nouvelle
            $member->addTeam($verified_teams);


            echo '<div id="message" class="updated notice"><p>Mis à jour réalisé avec succès.</p></div>';
            echo '<a href="'.admin_url("admin.php?page=istep_users_list").'">Retour à la liste</a>';
        } catch (InvalidParameter|MemberNotFound $e) {
            echo '<div class="notice notice-error"><p>Une erreur est survenue</p></div>';
            echo '<a href="'.admin_url("admin.php?page=istep_users_list").'">Retour à la liste</a>';
        }
    } else {
        edit_user_form();
    }


}

/**
 * Supprime l'utilisateur passé dans la requête
 * @return void
 */
function more_userdata_istep_users_delete_user():void
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (current_user_can(ADMIN_CAPACITY) && isset($_POST['user_delete_id'])) {
        $id = sanitize_text_field($_POST['user_delete_id']);
        try {
            $member = Member::findById($id);
            $wp_user = $member->getWPUser();
            if($wp_user !== false) {
                wp_delete_user($wp_user->ID);
                echo '<div id="message" class="updated notice"><p>L\'utilisateur à été supprimé avec succès.</p></div>';
                echo '<a href="'.admin_url("admin.php?page=istep_users_list").'">Retour à la liste</a>';
            } else {
                echo '<div class="notice notice-error"><p>Une erreur est survenue lors de la suppression</p></div>';
                echo '<a href="'.admin_url("admin.php?page=istep_users_list").'">Retour à la liste</a>';
            }
            $member->deletePersonalPage();
        } catch (InvalidParameter|MemberNotFound $e) {
            echo '<div class="notice notice-error"><p>Une erreur est survenue</p></div>';
            echo '<a href="'.admin_url("admin.php?page=istep_users_list").'">Retour à la liste</a>';
        }
    }
}

/**
 * Formulaire de modification d'un utilisateur
 * @return void
 */
function edit_user_form():void
{
    if (isset($_POST['id']) || isset($_GET["id"])) {
        $id_user = sanitize_text_field($_POST['id'] ?? $_GET["id"]);

        $teams = Team::getAll();
        try {
            $user_data = Member::findById($id_user);
            $users_teams = $user_data->getTeams();
            $user_data_wp = get_user_by('id', $user_data->getWpId());

            $display_name = explode(" ", $user_data_wp->display_name);
            $last_name = $display_name[0] ?? "";
            $first_name = $display_name[1] ?? "";

            echo "<div class='update-user'>";
            echo "<h1>Modifier les informations d'un utilisateur</h1>";
            echo "<form method='POST' action=''>";
            echo '<div><label for="last_name" >Nom de famille : </label><input type="text" name="last_name" id="last_name" value="'.$last_name.'" required></div>';
            echo '<div><label for="first_name" >Prénom : </label><input type="text" name="first_name"  id="first_name" value="'.$first_name.'" required></div>';

            echo '<div><label for="phone" >Numéro de téléphone : </label><input type="tel" name="phone" id="phone" value="'.($user_data->getPhone() ?? "").'" required></div>';

            echo '<div><label for="office" >Bureau : </label><input type="text" name="office" id="office" value="'.($user_data->getOffice() ?? "").'" required></div>';
            echo '<div><label for="function" >Fonction : </label><input type="text" name="function" id="function" value="'.($user_data->getFunction() ?? "").'" required></div>';

            echo '<div><label for="tower" id="tower">Tour du bureau : <ul>';

            //Liste des tours
            $towerList = ["tour-46-00-2e","tour-46-00-3e","tour-46-00-4e","tour-46-45-2e","tour-56-66-5e","tour-56-55-5e"];
            foreach($towerList as $tower) {
                echo '<li><label></label><input type="radio" name="tourBureau" value="'.$tower.'"'.($tower == $user_data->getOfficeTower() ? "checked":""). ' />' .$user_data->getReadableOfficeTower().'</label> </li>';
            }
            echo ' </ul></label></div>';


            //Listes des équipes
            echo "<h2>Listes des équipes</h2>";
            echo "<table class=\"form-table\">";
            echo '<tr><th>Nom de l\'équipe</th><th>Fait partie de</th></tr>';
            foreach ($teams as $team) {

                $team_name = $team->getName();
                $team_id = $team->getId();

                //Coche toute les équipes dont l'utilisateur fait déjà partie
                $checked = (in_array($team_id, $users_teams))? "checked":"";

                echo '<tr><td>'.$team_name.'</td><td><input type="checkbox" name="teams[]" value="'.$team_id.'"'.$checked.' id="team-'.$team_id.'"></td></tr>';
            }
            echo "</table>";

            echo '<div><label for="rank" >Rang dans l\'équipe : </label><input type="text" id="rank" name="rank" value="'.($user_data->getTeamRank() ?? "").'" required></div>';

            //Listes de campus
            echo '<div><label for="campus" >Campus : <select name="campus" id="campus">';
            $campus = Location::getAll();
            foreach ($campus as $one_campus) {
                echo "<option value=\"".$one_campus->getId()."\">".$one_campus->getName()."</option>";
            }
            echo '</select></label></div>';

            //Listes des employeur
            echo '<div><label for="employer" >Employeur : </label>
                    <select name="employer" id="employer">
                        <option value="sorbonne-universite">Sorbonne université</option>
                        <option value="cnrs">CNRS</option>
                        <option value="autre">Autre</option>
                    </select></div>';

            echo '<div><label for="mailCase" >Case de courrier : </label><input type="text" name="mailCase" id="mailCase" value="'.($user_data->getMailCase() ?? "").'" required></div>';


            echo '<input type="hidden" name="idUser" value="' . $id_user . '">';
            echo '<input type="hidden" name="idUserWp" value="' . $user_data->getWpId() . '">';
            submit_button('Modifier', 'primary', 'changeTeams');
            echo "</form>";
            echo "</div>";
        } catch (InvalidParameter|MemberNotFound $e) {
            echo '<div class="notice notice-error"><p>Une erreur est survenue</p></div>';
        }


    } else {
        wp_redirect(admin_url("admin.php?page=istep_users_list"));

    }
}
