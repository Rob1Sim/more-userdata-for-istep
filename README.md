
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
## Architecture
Le projets est constituer de 3 fichier php : __index.php__ contient  :
- La création des tables lors de l'activation du plugin
- Le shortcode qui gère la création du formulaire de l'utilisateur
- La création de l'utilisateur dans la base de donnée
- Le shortcode qui s'occupe d'afficher l'annuaire
- Le shortcode qui s'occupe d'afficher les informations de l'utilisateur

__admin-functions.php__ contient :
- Le formulaire de gestion de qui à accès a l'ajout d'utilisateur
- Le formulaire de création/Modification/Suppression d'équipe
- Le formulaire de création/Modification/Suppression de campus
- La liste des membres de l'ISTeP et la possibilité de modifier leur équipe ou de les supprimés
- Le formulaire de gestion de qui à l'accès de voir le menu administrateur

__utilities.php__ contients des fonctions utilitaire qui sont utilisé dans les deux fichier au dessus.

Le dossier __scripts__ contients les scripts utilisé par les différents shortcode listés au dessus, il contient notament des vérifications de champs pour les formulaires.

Le dossier __styles__ contients les fichier de style utilisé par les différents shortcode listés au dessus. 

Le fichier __doc__ contient la documentation du projet, elle utilise l'outils [phpDocumentor]("https://www.phpdoc.org/")

## Liens utiles 
Le projets utiliser simplement [l'API de wordpress]("https://developer.wordpress.org/") 

