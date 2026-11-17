<?php
/**
 * SNIPPET « Catégories d'établissement (badges) » — à coller dans Code Snippets.
 *
 * LE PROBLÈME
 * -----------
 * Le dropdown « Catégorie (badge) » de Injah Studio → Annuaire ne lit PAS la
 * taxonomie. Il boucle sur injah_etab_category_defs(), un tableau figé :
 *
 *     Admin Annuaire, l.1115  -> $defs     = injah_etab_category_defs()
 *     Admin Annuaire, l.1230  -> $cat_defs = injah_etab_category_defs()
 *     Grille publique         -> $cat_defs = injah_etab_category_defs()
 *
 * C'est pourquoi « Soutien scolaire », bien présent dans la taxonomie
 * (slug soutien-scolaire, 1 fiche), n'apparaît pas dans le dropdown :
 * le formulaire n'interroge jamais la base pour construire ses options.
 *
 * Le même tableau alimente les chips de filtre de [etablissements_grid] :
 * corriger ici corrige les deux endroits à la fois.
 *
 * SLUGS RÉELS relevés dans la taxonomie — NE PAS LES INVENTER
 * ----------------------------------------------------------
 *     ecoles                   54
 *     facultes                  0
 *     organismes-de-formation  13
 *     soutien-scolaire          1
 *     universites              35
 *
 * ⚠️ « organismes-formation » n'existe pas : le slug exact est
 *    « organismes-de-formation ». Un slug erroné produit une option fantôme
 *    qui ne filtre rien et n'enregistre rien.
 *
 * COMMENT L'UTILISER
 * ------------------
 * 1. Retrouver le snippet qui définit DÉJÀ injah_etab_category_defs() :
 *      /injah-dump-snippets.php?key=<CLE>&search=injah_etab_category_defs
 * 2. SUPPRIMER (ou commenter) cette ancienne définition.
 * 3. Coller ce fichier à la place.
 *
 * ⚠️ POURQUOI L'ÉTAPE 2 EST OBLIGATOIRE
 * -------------------------------------
 * Si l'ancienne définition n'a pas de garde function_exists(), PHP lèvera
 * « Cannot redeclare injah_etab_category_defs() » et le site tombera en
 * erreur fatale. Le garde ci-dessous protège le cas inverse (mon snippet
 * chargé en second), mais il ne peut rien si l'ancienne définition arrive
 * après la mienne : dans ce cas mon fichier est simplement ignoré.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'injah_etab_category_defs' ) ) {

	/**
	 * Liste slug => libellé des badges, dans l'ordre d'affichage.
	 *
	 * @return array<string,string>
	 */
	function injah_etab_category_defs() {

		/*
		 * Ordre et libellés maîtrisés : c'est ce que voit l'opérateur.
		 * Ajoutez une ligne ici pour un nouveau badge.
		 *
		 * « facultes » (0 fiche) existe dans la taxonomie mais n'était pas
		 * affiché ; décommentez la ligne pour le faire apparaître.
		 */
		$base = array(
			'universites'             => 'Universités',
			'ecoles'                  => 'Ecoles et Instituts',
			'organismes-de-formation' => 'Organismes de formation',
			'soutien-scolaire'        => 'Soutien scolaire',
			// 'facultes'             => 'Facultés',
		);

		/*
		 * Filet de sécurité : toute catégorie de la taxonomie qui contient au
		 * moins une fiche, mais qui manque à $base, est ajoutée à la fin.
		 * Un futur badge créé dans wp-admin apparaîtra donc sans retoucher ce
		 * code. hide_empty => true évite de ressusciter les catégories vides
		 * comme « Facultés », volontairement masquée ci-dessus.
		 */
		if ( taxonomy_exists( 'categorie_etablissement' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'categorie_etablissement',
					'hide_empty' => true,
				)
			);
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$slug = isset( $term->slug ) ? (string) $term->slug : '';
					if ( '' === $slug || isset( $base[ $slug ] ) ) {
						continue;
					}
					$base[ $slug ] = isset( $term->name ) ? (string) $term->name : $slug;
				}
			}
		}

		return $base;
	}
}
