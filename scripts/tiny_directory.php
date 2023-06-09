<?php
/**
 * Gestion de l'annuaire
 */

// -- Tiny Directory --
use MUDF_ISTEP\Entity\Member;
use MUDF_ISTEP\Entity\Team;

add_shortcode('users_directory', 'create_directory_from_DB_users');

/**
 * Récupère les utilisateurs dans la base de donnée et les affect à un tableau HTML
 * @return string Le tableau HTML
 */
function create_directory_from_DB_users($atts): string
{

    if (! function_exists('get_editable_roles')) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
    }
    $roles = get_editable_roles();

    //Gestions des paramètres
    $list_parameters = shortcode_atts(array(
        'role' => '',
        'team' => ''
    ), $atts);

    //Paramètre Role
    $role_parameter = $list_parameters['role'];
    $role_parameter = strtolower(sanitize_text_field($role_parameter));
    //Si le role n'éxiste pas alors on ne trie pas
    if (!isset($role_parameter) ||$role_parameter == "" ||$roles[$role_parameter] == null) {
        $role_parameter = "";
    }

    //Paramètre équipe
    $team = $list_parameters['team'];
    $team = sanitize_text_field($team);
    if (!isset($team) ||$team == "" ||!in_array($team, Team::getAllNames())) {
        $team = "";
    }


    //Ajout de la feuille de style et du javascript
    wp_enqueue_style('tiny-directory-css', plugins_url('../public/styles/tiny-directory.css', __FILE__));
    wp_enqueue_script('tiny-directory-js', plugins_url('../public/scripts/tiny-directory.js', __FILE__), array(), false, true);
    $users = Member::getAll();
    // Vérifier s'il y a des utilisateurs
    if (!empty($users)) {
        //Génère le tableau
        $html =
            <<<HTML
<div class="tiny-directory-div">
    <label for="search-input-members">Rechercher : </label>
    <input type="hidden" value="$role_parameter" id="role-parameter">
    <input type="hidden" value="$team" id="team-parameter">
    <input type="text" id="search-input-members"" placeholder="Robin...">
            <select id="select-role">
HTML;



        foreach ($roles as $key => $value) {
            $html.= "<option value=\"".$key."\">".$value['name']."</option>";
        }
        $html .= <<<HTML
        </select>
    <div class="scrollable-div">
    <table class="tiny-directory-table">
    <thead >
        <tr class="tiny-directory-th">
            <th class="tiny-directory-th" colspan="1">NOM / Prénom</th>
            <th class="tiny-directory-th" colspan="1">Email</th> 
            <th class="tiny-directory-th" colspan="1">Téléphone</th> 
            <th class="tiny-directory-th" colspan="1">Fonction</th> 

    </thead>
    <tbody> 
    HTML;
        foreach ($users as $user) {
            $userID = $user->getWpId();
            $userAvatar = $user->getAvatar();
            $wp_user = get_user_by("id", $userID);
            $linkToProfilePage = home_url()."/membres-istep/$wp_user->user_login";

            //Listes des roles
            $users_roles = $wp_user->roles;
            $users_roles_str = implode("-", $users_roles);

            //Listes des équipes
            $users_teams = $user->getTeamsNames();
            $users_teams_str = implode("-", $users_teams);

            $tower = $user->getReadableOfficeTower();
            $campus = $user->getLocation()->getName();
            $phone = $user->getPhone();
            $function = $user->getFunction();
            $office = $user->getOffice();
            $html.= <<<HTML
            <a class="no-display-fields" href="$linkToProfilePage"></a>

        <tr class="user-$userID tiny-directory-tr" tabindex="0">
            
            <td class="no-display-fields" id="pp-$userID" data-id="$userID">$userAvatar
                <input type="hidden" value="$users_roles_str" id="input-roles"/>
                <input type="hidden" value="$users_teams_str" id="input-teams"/>
            </td>
            <td class="no-display-fields" id="login-$userID">$linkToProfilePage</td>
            <td class="tiny-directory-td name-$userID">$wp_user->display_name</td>
            <td class="tiny-directory-td email-$userID"><a href="mailto:$wp_user->user_email">$wp_user->user_email</td>
            <td class="tiny-directory-td phone-$userID">$phone</td>
            <td class="tiny-directory-td"$userID">
            $function
            </td>
            <td class="no-display-fields campus-$userID">
                $campus
            </td>
            <td class="no-display-fields tower-$userID">
                $tower
            </td>
            <td class="no-display-fields office-$userID">
                $office
            </td>
        </tr>
HTML;
        }
        $html.= <<<HTML
    </tbody>
</table>
</div>
</div>
HTML;
        return $html;
    } else {
        return "Error no users has been found :(";
    }
}
