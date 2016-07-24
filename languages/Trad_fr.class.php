<?php

class Trad {

		# Mots

	const W_ISSUE = 'Demande';
	const W_OPEN = 'Ouvert';
	const W_OPENED = 'Ouvert';
	const W_CLOSED = 'Fermé';
	const W_REOPENED = 'Réouvert';
	const W_COMMENTED = 'Commenté';
	const W_NOBODY = 'personne';
	const W_SOMEONE = 'quelqu\'un';
	const W_ENABLED = 'Activé';
	const W_DISABLED = 'Désactivé';
	const W_NOTFOUND = 'Page non trouvée';
	const W_FORBIDDEN = 'Accès refusé';
	const W_MENU = 'Menu';

	const W_EXAMPLE = 'Exemple';
	const W_HEX = 'Hex';
	const W_RENDERING = 'Rendu';
	const W_ID = 'ID';
	const W_DISPLAY_NAME = 'Nom affiché';
	
	const W_SECONDE = 'seconde';
	const W_MINUTE = 'minute';
	const W_HOUR = 'heure';
	const W_DAY = 'jour';
	const W_WEEK = 'semaine';
	const W_MONTH = 'mois';
	const W_YEAR = 'année';
	const W_DECADE = 'décennie';
	const W_SECONDE_P = 'secondes';
	const W_MINUTE_P = 'minutes';
	const W_HOUR_P = 'heures';
	const W_DAY_P = 'jours';
	const W_WEEK_P = 'semaines';
	const W_MONTH_P = 'mois';
	const W_YEAR_P = 'années';
	const W_DECADE_P = 'décennies';

	const W_PREVIOUS = 'Précédente';
	const W_NEXT = 'Suivante';
	const W_CURRENT = 'Page %nb1% sur %nb2%';

	const W_NOT_LOGGED = 'Non connecté';

	const W_SUSPENSION = '…';
	const W_EXTRACT = '« %text% »';

	const W_USER = 'Utilisateur';
	const W_DEVELOPPER = 'Développeur';
	const W_SUPERUSER = 'Superutilisateur';
	const W_S_NEW = 'Nouveau';
	const W_S_CONFIRMED = 'Confirmé';
	const W_S_ASSIGNED = 'Assigné à %user%';
	const W_S_RESOLVED = 'Résolu';
	const W_S_REJECTED = 'Rejeté';
	const W_L_URGENT = 'Urgent';
	const W_L_IMPROVEMENT = 'Amélioration';
	const W_L_PRIVATE = 'Privé';

	const W_PROFILEPIC = 'avatar';

	const W_RSS = 'Flux RSS';

		# Verbes

	const V_UPDATE = 'Mettre à jour';
	const V_UPDATE_DETAILS = 'Mettre à jour la demande';
	const V_UPDATE_CONTENT = 'Mettre à jour le contenu';
	const V_UPDATE_AND = 'Mettre à jour & %adjective%';
	const V_CANCEL = 'Annuler';
	const V_PREVIEW = 'Aperçu';
	const V_COMMENT = 'Commenter';
	const V_SUBMIT = 'Envoyer';
	const V_SELECT_FILE = 'Choisir un fichier…';
	const V_UPLOADING = 'Transfert en cours…';
	const V_SAVE_CONFIG = 'Enregistrer les réglages';
	const V_APPLY = 'Appliquer';
	const V_EDIT = 'Modifier';
	const V_SIGNUP = 'S\'inscrire';
	const V_CONTINUE = 'Continuer';
	const V_REMOVE_ISSUE = 'Supprimer la demande';
	const V_CLOSE = 'fermer';
	const V_REOPEN = 'réouvrir';
	const V_EXPORT = 'Exporter les données brutes';

		# Phrases

	const S_NOLABEL = '–';
	const S_NODEPENDENCY = '–';
	const S_COMMENT_LEAVE = 'Poster un commentaire :';
	const S_AGO = 'il y a %duration% %pediod%';
	const S_ISSUE_ABOUT = 'À propos :';
	const S_UPLOAD_ADD = 'Joindre un fichier :';
	const S_WELCOME = 'Bienvenue, %user%';
	const S_NEVER = 'Jamais';
	const S_ME = 'À chaque mise à jour de demandes auxquelles j\'ai participé';
	const S_ALWAYS = 'Tout le temps';
	const S_START_NOTIF = 'Être averti des mises à jour';
	const S_STOP_NOTIF = 'Ne plus être averti lors des mises à jour';
	const S_NOTFOUND = 'La page que vous recherchez n\'existe pas...';
	const S_FORBIDDEN = 'Vous n\'êtes pas autorisé à accèder à cette page. Merci de vous connecter pour continuer.';

	const S_VIEW_PARTICIPATION = 'Voir ses participations';
	const S_VIEW_STATUS = 'Voir les demandes « %status% ».';

	const S_ISSUE_CREATED = 'par %user% %time%';
	const S_ISSUE_UPDATED = '%adj% par %user% %time%.';
	const S_ISSUE_STATUS_UPDATED = 'Statut changé à %status% par %user% %time%.';

	const S_RSS_ISSUE_UPDATED = '%adj% par %user%.';
	const S_RSS_ISSUE_STATUS_UPDATED = 'Statut changé à « %status% » par %user%.';

	const S_INTRO_INSTALL = 'On dirait que c\'est la première fois que vous utilisez Bumpy Booby ! Merci de le configurer :';
	const S_FIRST_ISSUE_TITLE = 'Ça roule ma poule ?';
	const S_FIRST_ISSUE = 'Je suis votre toute première demande ! Après vous être connecté, vous pourrez me supprimer.';

	const S_NO_USER = 'Aucun utilisateur ne correspond à votre requête.';
	const S_NO_ISSUE = 'Aucune demande ne correspond à votre requête.';
	const S_MATCHING_ISSUES = '%nb% demandes correspondantes';
	const S_NO_ACTIVITY = 'Aucune activité récente.';
	const S_NO_UPLOAD = 'Aucun fichier.';
	const S_SIZE_REMAINING = '%remain% restant sur les %total% autorisés.';
	const S_NO_PROJECT = 'Aucun projet.';

	const S_SEARCH = '#12, @qqn, mots…';
	const S_COPYRIGHT = 'Propulsé par %name%.';

	const S_LAST_UPDATES = 'Dernières mises à jour…';

		# Alerts

	const A_ERROR_DIRECTORY = '<strong>Erreur :</strong> impossible de créer le dossier « %name% ».';
	const A_ERROR_FILE = '<strong>Erreur :</strong> impossible de lire le fichier « %name% ».';
	const A_ERROR_FILE_WRITE = '<strong>Erreur :</strong> impossible de mettre à jour le fichier « %name% ».';
	const A_ERROR_FATAL = 'Désolé, une erreur s\'est produite. Merci de contacter un administrateur si le problème persiste.';
	const A_ERROR = '<strong>%title%:</strong><br /><br />%message%<br /><br />Fichier « <strong>%file%</strong> » à la ligne <strong>%line%</strong>.';

	const A_SUCCESS_INSTALL = '<strong>Terminé :</strong> Bumpy Booby est maintenant configuré.';
	const A_ERROR_INSTALL = '<strong>Erreur :</strong> Bumpy Booby est déjà configuré. Si vous souhaitez revenir à la configuration par défaut, supprimez le fichier de configuration.';
	const A_MODIF_SAVED = 'Les modifications ont été sauvegardées.';

	const A_ERROR_FORM = 'Une erreur s\'est produite. Merci de soumettre à nouveau le formulaire.';
	const A_ERROR_TOKEN = 'Mauvais jeton de sécurité. Merci de soumettre à nouveau le formulaire.';
	const A_ERROR_EMPTY = 'Vous devez fournir un nom d\'utilisateur et un mot de passe.';
	const A_ERROR_SAME_USERNAME = 'Ce nom d\'utilisateur n\'est pas disponible.';
	const A_SUCCESS_SIGNUP = '<strong>Inscription terminée :</strong> vous pouvez maintenant vous connecter.';

	const A_CONFIRM_DELETE_COMMENT = 'Voulez-vous vraiment supprimer ce commentaire ?';
	const A_SUCCESS_DELETE_COMMENT = 'Le commentaire a été supprimé.';
	const A_CONFIRM_DELETE_ISSUE = 'Voulez-vous vraiment supprimer cette demande ?';
	const A_SUCCESS_DELETE_ISSUE = 'La demande a été supprimée.';
	const A_CONFIRM_DELETE_UPLOAD = 'Voulez-vous vraiment supprimer ce fichier ?';
	const A_CONFIRM_DELETE_PROJECT = 'Voulez-vous vraiment supprimer ce projet ? Toutes les demandes correspondantes seront perdues.';

	const A_LOGGED = 'Vous êtes maintenant connecté.';
	const A_LOGGED_OUT = 'Vous êtes maintenant déconnecté.';
	const A_ERROR_CONNEXION = '<strong>Erreur:</strong> mauvais nom d\'utilisateur ou mot de passe.';
	const A_ERROR_CONNEXION_WAIT = '<strong>Erreur :</strong> mauvais nom d\'utilisateur ou mot de passe. Merci de patienter %duration% %period% avant de réessayer.';
	const A_ERROR_LOGIN_WAIT = 'Merci de patienter %duration% %period% avant de réessayer. Ceci est une protection contre les attaques malveillantes.';

	const A_ERROR_UPLOAD = 'Une erreur s\'est produite. Merci de réessayer';
	const A_ERROR_UPLOAD_SIZE = 'La taille du fichier dépasse la limite autorisée (%nb% maximum).';
	const A_ERROR_UPLOAD_FULL = 'Vous ne disposez pas de suffisament d\'espace pour envoyer ce fichier : %nb% restant.';

	const A_PLEASE_LOGIN_ISSUES = 'Connectez-vous pour accèder aux demandes.';
	const A_PLEASE_LOGIN_COMMENT = 'Connectez-vous pour poster un commentaire. Pas encore inscrit ? Créez un compte : c\'est gratuit et ultra rapide !';
	const A_PLEASE_LOGIN_ISSUE = 'Connectez-vous pour soumettre une demande. Pas encore inscrit ? Créez un compte : c\'est gratuit et ultra rapide !';
	const A_SHOULD_LOGIN = 'Si vous possèdez déjà un compte, connectez-vous. Sinon, pensez à vous inscrire : c\'est gratuit et ultra rapide !';

	const A_IE = 'Votre navigateur est obsolète : <a href="http://www.browserchoice.eu">effectuez une mise à niveau ou changez de navigateur</a>.';

		# Mails

	const M_NEW_COMMENT_O = '[%title% — %project% — Demande #%id%] Nouveau commentaire';
	const M_NEW_COMMENT = 'Salut, %username% !

La demande #%id% — « %summary% » vient juste d\'être commentée par %by%. Vous pouvez lire ce nouveau commentaire ici:
	%url%.

Si vous ne souhaitez plus recevoir de notifications à propos de cette demande, une option est disponible (après connexion) sur la page ci-dessus.

-----
Ceci est un message automatisé. Merci de ne pas y répondre.
	';

	const M_NEW_ISSUE_O = '[%title% — %project%] Nouvelle demande';
	const M_NEW_ISSUE = 'Salut, %username% !

La demande #%id% — « %summary% » vient tout juste d\'être postée par %by%. Vous pouvez la consulter ici :
	%url%.

Si vous ne souhaitez plus recevoir de notifications à propos de cette demande, une option est disponible (après connexion) sur la page ci-dessus.

-----
Ceci est un message automatisé. Merci de ne pas y répondre.
	';

		# Titre

	const T_INSTALLATION = 'Installation';
	const T_SETTINGS = 'Réglages';
	const T_GLOBAL_SETTINGS = 'Réglages généraux';
	const T_APPEARANCE = 'Apparence';
	const T_ISSUES = 'Demandes';
	const T_GROUPS = 'Groupes';
	const T_USERS = 'Utilisateurs';
	const T_BROWSE_ISSUES = 'Parcourir les demandes';
	const T_NEW_ISSUE = 'Nouvelle demande';
	const T_PROJECTS = 'Projets';
	const T_DASHBOARD = 'Tableau de bord';
	const T_LAST_UPDATES = 'Dernières mises à jour';
	const T_LAST_ACTIVITY = 'Activité récente';
	const T_UPLOADS = 'Fichiers';
	const T_SEARCH = 'Recherche';


		# FORMS

	const F_USERNAME = 'Nom d\'utilisateur :';
	const F_PASSWORD = 'Mot de passe :';
	const F_USERNAME2 = 'Nom d\'utilisateur';
	const F_PASSWORD2 = 'Mot de passe';
	const F_NAME = 'Nom :';
	const F_URL = 'Url :';
	const F_URL_REWRITING = 'Url rewriting :';
	const F_INTRO = 'Introduction :';
	const F_DESCRIPTION = 'Description :';
	const F_EMAIL = 'Email :';
	const F_MAX_UPLOAD = 'Taille maximum par envoi de fichier :';
	const F_ALLOCATED_SPACE = 'Espace alloué à chaque utilisateur :';
	const F_GROUP = 'Groupe :';
	const F_NOTIFICATIONS = 'Être averti :';
	const F_PROJECT_X = 'Projet « %name% » :';
	const F_LANGUAGE = 'Langue :';
	const F_LOGS = 'Logs :';

	const F_ISSUES_PAGE = 'Demandes par page :';
	const F_ISSUES_PAGE_SEARCH = 'Demandes par page (recherche) :';
	const F_PREVIEW_ISSUE = 'Longueur des extraits (demandes) :';
	const F_PREVIEW_SEARCH = 'Longueur des extraits (recherche) :';
	const F_PREVIEW_PROJECT = 'Longueur des extraits (projets) :';
	const F_LAST_EDITS = 'Nombre de demandes affichées sur les tableaux de bord:';
	const F_LAST_ACTIVITY = 'Nombre de demandes affichées sur la page des utilisateurs :';

	const F_ADD_PROJECT = 'Nouveau projet';
	const F_ADD_COLOR = 'Nouvelle couleur';
	const F_ADD_STATUS = 'Nouveau statut';
	const F_ADD_LABEL = 'Nouvelle étiquette';
	const F_ADD_GROUP = 'Nouveau groupe';
	const F_ADD_USER = 'Nouveau utilisateur';

	const F_SORT_BY = 'Trier par :';
	const F_SORT_ID = 'ID';
	const F_SORT_MOD = 'mise à jour';
	const F_SORT_DESC = 'décroissant';
	const F_SORT_ASC = 'croissant';
	const F_FILTER_STATUSES = 'Filtrer les statuts :';
	const F_FILTER_STATES = 'Filtrer les états :';
	const F_FILTER_LABELS = 'Filtrer les labels :';
	const F_FILTER_USERS = 'Filtrer les utilisateurs :';

	const F_WRITE = 'Écrire :';
	const F_SUMMARY = 'Résumé';
	const F_CONTENT = 'Description complète';

	const F_STATUS = 'Statut :';
	const F_RELATED = 'Liée à :';
	const F_LABELS2 = 'Étiquettes :';

	const F_GENERAL_SETTINGS = 'Réglages généraux :';
	const F_PROJECTS = 'Projets :';
	const F_DATABASE = 'Base de données :';
	const F_UPLOADS = 'Fichiers téléchargés :';
	const F_COLORS = 'Gérer les couleurs :';
	const F_DISPLAY = 'Gérer l\'affichage :';
	const F_STATUSES = 'Gérer les statuts :';
	const F_LABELS = 'Gérer les étiquettes :';
	const F_GROUPS = 'Gérer les groupes :';
	const F_PERMISSIONS = 'Gérer les permissions :';
	const F_USERS = 'Gérer les utilisateurs :';

	const F_TIP_NAME = 'Ce nom sera affiché en haut de chaque page.';
	const F_TIP_URL_REWRITING = 'Laissez ce champ vide pour désactiver l\'url rewriting. Sinon, il doit contenir le chemin du dossier de Bumpy Booby (en commençant et terminant par un "/") par rapport au nom de domaine.';
	const F_TIP_INTRO = 'Cette introduction sera affichée sur la page d\'accueil et mise en forme avec la syntaxe Markdown. Remarque : s\'il n\'y a qu\'un seul projet nommé « %name% », la page d\'accueil est automatiquement redirigée vers le tableau de bord de ce projet, et ce texte ne sera donc jamais affiché.';
	const F_TIP_EMAIL = 'Laissez ce champ vide si vous ne souhaitez pas activer les notifications par mail. Sinon, cette adresse sera utilisée comme expéditrice des mails envoyés.';
	const F_TIP_PASSWORD = 'Laissez ce champ vide si vous ne souhaitez pas changer le mot de passe.';
	const F_TIP_USER_EMAIL = 'Non obligatoire : seulement nécessaire si vous souhaitez recevoir des notifications.';
	const F_TIP_NOTIFICATIONS = 'Ceci est un réglage général : vous pouvez également le modifier pour chaque demande.';
	const F_TIP_NOTIFICATIONS_DISABLED = 'Remarque : les notifications sont actuellement désactivées par l\'administrateur.';
	const F_TIP_DESCRIPTION = 'Ce texte sera affiché sur le tableau de bord du projet, et mis en forme avec la syntaxe Markdown.';

	const F_TIP_MAX_UPLOAD = 'Chaque fichier envoyé ne peut pas dépasser cette taille.';
	const F_TIP_ALLOCATED_SPACE = 'Un utilisateur ne pourra plus envoyer de fichier une fois cette limite atteinte. <em>Attention :</em> cette limite ne s\'applique pas aux utilisateurs non connectés (avec les réglages par défaut, ils ne peuvent pas envoyer de fichier du tout ).';

	const F_TIP_ID_STATUS = '<b>Attention :</b> le statut de chaque demande ne sera pas mis à jour, il pointera donc toujours vers l\'ancien ID. Dans le cas où ce statut ne correspondrait plus à aucun des nouveaux ID, la demande retrouverait l\'état par défaut.';
	const F_TIP_ID_LABEL = '<b>Attention :</b> les étiquettes des demandes ne seront pas mis à jour, elles pointeront donc toujours vers les anciens ID. Dans le cas où une étiquette d\'une demande ne correspondrait plus à aucun des nouveaux ID, la demande perdrait cette étiquette.';
	const F_TIP_ID_GROUP = '<b>Attention :</b> le groupe de chaque utilisateur ne sera pas mis à jour, il pointera donc toujours vers l\'ancien ID. Dans le cas où ce groupe ne correspondrait plus à aucun des nouveaux ID, l\'utilisateur rejoindrait le groupe par défaut.';

	const HELP_MARKDOWN = '
		<h2>Syntaxe Markdown :</h2>

		<p>Paragraphes :</p>
<pre><code class="blank no-highlight">Sautez au moins une ligne pour créer un nouveau paragraphe.
Ce texte sera donc affiché à la suite de la phrase précédente : le retour à la ligne n\'est pas suffisant.

Pour revenir à la ligne sans créer de nouveau paragraphe :  
insérez deux espaces juste avant de revenir à la ligne (comme à la ligne ci-dessus).</code></pre>
		<p>Mise en valeur :</p>
<pre><code class="blank no-highlight">*Je suis du texte en italique...*  
_...et moi aussi !_  

**Je suis du texte en gras...**  
__...et moi aussi !__</code></pre>

		<p>Liens :</p>
<pre><code class="blank no-highlight">Ceci est [un exemple](http://example.com) de lien au milieu d\'une phrase.  
Et ceci en est un autre : &lt;http://example.com&gt;.</code></pre>

		<p>Images :</p>
<pre><code class="blank no-highlight">![Je suis une image](http://example.com/image.png)</code></pre>

		<p>Titres :</p>
<pre><code class="blank no-highlight"># Titre principal
## Titre secondaire
### Titre de troisième niveau
#### Titre de quatrième niveau</code></pre>

		<p>Liste :</p>
<pre><code class="blank no-highlight">- une chose
* et une seconde !

1. ce qui constitue la première étape
2. et ce qui constitue la deuxième</code></pre>

		<p>Citations :</p>
<pre><code class="blank no-highlight">> Je suis une citation constituée de deux paragraphes.
>
> Je suis le second paragraphe.</code></pre>

		<p>Zones de code :</p>
<pre><code class="blank no-highlight">Ceci est `un bout de code` inséré dans une phrase.</code></pre>
<pre><code class="blank no-highlight">    &lt;?php echo "Je suis une zone de code, car je suis indenté
    avec 4 espaces"; ?&gt;</code></pre>
<pre><code class="blank no-highlight">```
&lt;?php echo "Je suis une zone de code."; ?&gt;
```

```php
&lt;?php echo "Les langages reconnus sont : bash, cs, ruby, diff, javascript, css, xml, http, java, php, python, sql, ini, perl, json, cpp, markdown, no-highlight"; ?&gt;
```</code></pre>
	';


	private static $permissions = array(
		'home' => array(
			'title' => 'Page d\'accueil :',
			'description' => 'Peut accèder à la page d\'accueil.'
		),
		'dashboard' => array(
			'title' => 'Tableaux de bord :',
			'description' => 'Peut accèder aux tableaux de bord des différents projets.'
		),
		'issues' =>  array(
			'title' => 'Demandes publiques :',
			'description' => 'Peut consulter les demandes publiques.'
		),
		'private_issues' => array(
			'title' => 'Demandes privées :',
			'description' => 'Peut consulter les demandes étiquettées comme privées.'
		),
		'search' => array(
			'title' => 'Recherche :',
			'description' => 'Peut rechercher une demande ou un utilisateur.'
		),
		'new_issue' => array(
			'title' => 'Nouvelle demande :',
			'description' => 'Peut soumettre une nouvelle demande.'
		),
		'edit_issue' => array(
			'title' => 'Modifier les demandes :',
			'description' => 'Peut modifier le texte de toutes les demandes, et les supprimer.'
		),
		'update_issue' => array(
			'title' => 'Mettre à jour les demandes :',
			'description' => 'Peut mettre à jour les demandes : modifier les statuts, ajouter une étiquette, ouvrir ou fermer une demande, ...'
		),
		'post_comment' => array(
			'title' => 'Poster un commentaire :',
			'description' => 'Peut poster un commentaire.'
		),
		'edit_comment' => array(
			'title' => 'Modifier les commentaires :',
			'description' => 'Peut modifier tous les commentaires, et les supprimer (dans tous les cas, un utilisateur peut modifier ses propres commentaires).'
		),
		'view_user' => array(
			'title' => 'Profiles des utilisateurs:',
			'description' => 'Peut consulter le profile de tous les utilisateurs.'
		),
		'upload' => array(
			'title' => 'Envoyer un fichier :',
			'description' => 'Peut joindre un fichier à une demande ou un commentaire.'
		),
		'view_upload' => array(
			'title' => 'Consulter les fichiers envoyers :',
			'description' => 'Peut consulter les fichiers envoyés.'
		),
		'settings' => array(
			'title' => 'Changer les réglages:',
			'description' => 'Peut accèder à cette page et modifier tous les réglages.'
		),
		'signup' => array(
			'title' => 'Inscription :',
			'description' => 'Peut s\'inscrire.'
		),
		'view_errors' => array(
			'title' => 'Erreurs fatales',
			'description' => 'Peut voir la description des erreurs fatales.'
		)
	);

	public static function permissions($id, $type = 'description') {
		return self::$permissions[$id][$type];
	}

	private static $settings = array(
		'validate_url' => 'L\'url n\'est pas valide.',
		'validate_email' => 'L\'adresse email n\'est pas valide.',
		'private_label_removed' => 'Vous ne pouvez pas supprimer l\'étiquette privée ni changer son ID, mais vous pouvez la renommer.',
		'default_status_removed' => 'Vous ne pouvez pas supprimer le statut par défaut ni changer son ID, mais vous pouvez le renommer.',
		'default_group_removed' => 'Vous ne pouvez pas supprimer le groupe par défaut ni changer son ID, mais vous pouvez le renommer.',
		'default_group_superuser_removed' => 'Vous ne pouvez pas supprimer le groupe des superutilisateurs ni changer son ID, mais vous pouvez le renommer.',
		'validate_same_username' => 'Attention : deux utilisateurs ont le même nom d\'utilisateur.',
		'validate_same_project_name' => 'Deux projets ne peuvent avoir le même nom. L\'un des deux a été automatiquement renommé.',
		'language_modified' => 'Actualisez cette page pour la voir dans la nouvelle langue.'
	);

	public static function settings($id) {
		return self::$settings[$id];
	}

	private static $errors = array(
		E_ERROR => 'Fatale erreur',
		E_WARNING => 'Attention',
		E_PARSE => 'Erreur d\'analyse',
		E_NOTICE => 'Remarque',
		E_STRICT => 'Conseil',
		E_DEPRECATED => 'Obsolète',
		'default' => 'Erreur'
	);
	public static function errors($no) {
		return (isset(self::$errors[$no])) ? self::$errors[$no] : self::$errors['default'];
	}
}

?>