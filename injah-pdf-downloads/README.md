# Injah – Page « دليل الوثائق » (téléchargement PDF) — sans Elementor

Un seul fichier : `injah-pdf-downloads.php`, à coller dans l'extension **Code Snippets**.
Il ajoute le shortcode **`[injah_pdf_downloads]`**.

## Installation (5 min)

1. **Extensions > Code Snippets > Ajouter**.
   - Titre : `Injah PDF downloads`
   - Collez tout le contenu de `injah-pdf-downloads.php`. La première ligne `<?php` peut être supprimée ou laissée : Code Snippets la gère.
   - Portée : **« Exécuter le snippet partout »**, puis cliquez **Enregistrer et activer**.
2. Un nouveau menu **Injah PDF** apparaît dans l'admin.
   - Pour chaque document : renseignez le titre et la description en arabe, puis cliquez **Choisir PDF**. La taille se remplit automatiquement.
   - Vous pouvez ajouter une miniature, c'est-à-dire une image de la 1re page. Sans miniature, une couverture est dessinée automatiquement (jaune, noir ou violet).
   - Cochez « جديد » pour afficher le badge.
   - Vous pouvez réordonner (▲▼), supprimer ou ajouter des documents, puis **Enregistrer**.
3. **Pages > Ajouter** :
   - Titre : par exemple `دليل الوثائق`.
   - Modèle de page (panneau de droite) : **Full Width Content Layout**.
   - Contenu : ajoutez un bloc **Code court / Shortcode** contenant `[injah_pdf_downloads]`, puis publiez.

## Fonctionnement
- Le visiteur clique sur **تحميل**. Un popup s'ouvre (en *bottom-sheet* sur mobile) et lui demande son nom et son email.
- Après l'envoi :
  - le lead est enregistré (**Injah PDF > Leads**, avec export CSV compatible Excel/arabe) ;
  - un email contenant le lien du PDF est envoyé au visiteur ;
  - le téléchargement démarre automatiquement.
- L'URL du PDF n'est jamais présente dans la page. Elle n'est renvoyée qu'après validation du formulaire.
- Anti-spam : un champ piège (honeypot) et une limite de 15 demandes par tranche de 10 minutes et par IP.
- Le visiteur est mémorisé (localStorage) : ses champs sont pré-remplis la fois suivante, et le bouton affiche « تحميل مجددًا ».
- Tout le CSS est préfixé `.ijdl` pour ne pas entrer en conflit avec Monstroid2.

## Emails
WordPress envoie les emails avec `wp_mail()`. Si les emails n'arrivent pas, installez **WP Mail SMTP** (ou une extension équivalente) et configurez-la avec votre boîte mail.
Dans **Injah PDF > Réglages**, vous pouvez aussi saisir une adresse pour être notifié à chaque nouveau lead.

## Remarques
- Le header et le footer de la maquette (logo + sélecteur عربي | FR) ne sont pas repris : la page utilise ceux de votre thème.
- Le bouton « اختر دليلك » en bas de l'écran n'apparaît que sur mobile, comme dans la maquette.
