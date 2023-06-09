
# More User data for ISTeP

Plugin wordpress conçue pour le laboratoire ISTeP, il offre la possibilité de stocker diverses informations sur les membre via un formulaire de création d'utilisateur personnalisé, ainsi que de gérer les différentes équipes, de plus il créer à chaque utilisateur une page personnelle affichant ses informations, il lui donne aussi accès pour la modifier, enfin il fournit un annuaire pour avoir des informations sur ses membres.

## Auteurs

- [@Rob1Sim](https://github.com/Rob1Sim)

## Configuration  
la configuration et création des équipes et des lieu est trouvable dans le menu administrateur, pour les administrateurs: Pour changer les droits de vision de cette page il faut modifier la configuration dans la page __Gérer les permissions__, toutes les personnes cochés auront le droit de gérer les équipes, les utilisateurs , les campus, ansi que donné la permissions au différents roles pour la création d'un utilisateur.

## Installation

Cloner ce repo dans le dossier des plugins de wordpress

```bash
cd wp-content/plugins/
git clone https://github.com/Rob1Sim/more-userdata-for-istep.git
```
Pour utiliser le formualaire sur une page il suffit d'ajouter le shortcode (code court) suivant : 
```bash
[add_istep_user_form]
 ```  
Pour utiliser l'annuaire sur une page il suffit d'ajouter le shortcode (code court) suivant : 
```bash
[users_directory]
 ```  
Pour afficher les information de l'utilisateur sur une page il suffit d'ajouter le shortcode (code court) suivant : 
```bash
[istep_user_data]
 ```  
 **Attention ce shortcode récupère les informations de l'auteur de la page, l'utilisateur doit donc être l'auteur de ça page pour que ses informations soit afficher**
## Modification du code
Le projet utilise composer : 
```bash
composer install
 ```
Si les class ne sont pas trouvé : 
```bash
 composer dumpautoload -o
 ```
## Architecture
Le projets est divisé en plusieurs fichier : 
Tous d'abord le fichier __index.php__ est le fichier principal du projet tous le code s'éxécute dessus.
Il contient les scripts éxécuté à la création et à la suppression du plugin (création de tables, ...)  
Les classes se situe dans le dossier __src/__ .  
Les fichiers contenant les shortcodes et la gestion des formulaires se trouve dans __script/__ :  
- Le fichier __add_user_form.php__ contients les shortcode et l'éxécution du formulaire de création d'utilisateur.
- Le fichier __admin-functions.php__ contients toute les menu de configuration disponible dans le menu d'administration
    par ailleurs tous les fichier relatif à ces menu se situe eux même dans le dossier __admin/__.
- Le fichier __personal_pages.php__ contients les shortcodes ainsi que les fonction de création/suppression d'une page personnalisé.
- Le fichier __tiny_directories.php__ contients le shortcode relatif à l'annuaire.
- Le fichier __utilities.php__ contients des fonctions réccurente utilisé dans l'ensemble du projet.

Le dossier __admin/__ contient lui 3 fichier :
- __location.php__ qui s'occupe de la gestions des différents campus. 
- __teams.php__ qui s'occupe de la gestions des différents équipes .
- __users.php__ qui s'occupe de la gestions des utilisateur (modification/suppression) .


Les fichier qui s'éxécute sur le naviagteur se trouve dans le dossier __public/__ :  
Le dossier __scripts__ contients les scripts utilisé par les différents shortcode listés au dessus, il contient notament des vérifications de champs pour les formulaires.

Le dossier __styles__ contients les fichier de style utilisé par les différents shortcode listés au dessus. 

## Liens utiles 
Le projets utiliser simplement [l'API de wordpress]("https://developer.wordpress.org/") 

