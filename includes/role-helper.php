<?php

/**
 * helper functions for roles and capabilities
 */
class Ekc_Role_Helper {

	const CUSTOM_FIELD_EKC_PUBLIC_TEMPLATE = 'ekc-public-template';

	const ROLE_ADMINISTRATOR = 	'administrator'; // standard WP role 
	const ROLE_EKC_TOURNAMENT_ADMINISTRATOR = 'ekc_tournament_administrator';
	const ROLE_EKC_TOURNAMENT_DIRECTOR = 'ekc_tournament_director';

	// EKC Tournament Manager custom capabilities
	const CAPABILITY_EKC_READ_TOURNAMENTS = 'ekc_read_tournaments';
	const CAPABILITY_EKC_EDIT_TOURNAMENTS = 'ekc_edit_tournaments';
	const CAPABILITY_EKC_EDIT_OTHERS_TOURNAMENTS = 'ekc_edit_others_tournaments';
	const CAPABILITY_EKC_MANAGE_TOURNAMENTS = 'ekc_manage_tournaments';
	const CAPABILITY_EKC_MANAGE_OTHERS_TOURNAMENTS = 'ekc_manage_others_tournaments';
	const CAPABILITY_EKC_DELETE_TOURNAMENTS = 'ekc_delete_tournaments';
	const CAPABILITY_EKC_DELETE_OTHERS_TOURNAMENTS = 'ekc_delete_others_tournaments';
	const CAPABILITY_EKC_MANAGE_BACKUPS = 'ekc_manage_backups';

	const EKC_TOURNAMENT_META_CAPS = array(
		self::CAPABILITY_EKC_EDIT_TOURNAMENTS => self::CAPABILITY_EKC_EDIT_OTHERS_TOURNAMENTS,
		self::CAPABILITY_EKC_MANAGE_TOURNAMENTS => self::CAPABILITY_EKC_MANAGE_OTHERS_TOURNAMENTS,
		self::CAPABILITY_EKC_DELETE_TOURNAMENTS => self::CAPABILITY_EKC_DELETE_OTHERS_TOURNAMENTS
	);

	// WP standard capabilities
	const CAPABILITY_READ = 'read';
	const CAPABILITY_READ_PRIVATE_PAGES = 'read_private_pages';
	const CAPABILITY_PUBLISH_PAGES = 'publish_pages';
	const CAPABILITY_EDIT_PAGES = 'edit_pages';
	const CAPABILITY_EDIT_PRIVATE_PAGES = 'edit_private_pages';
	const CAPABILITY_EDIT_PUBLISHED_PAGES = 'edit_published_pages';
	const CAPABILITY_EDIT_OTHERS_PAGES = 'edit_others_pages';
	const CAPABILITY_DELETE_PAGES = 'delete_pages';
	const CAPABILITY_DELETE_PRIVATE_PAGES = 'delete_private_pages';
	const CAPABILITY_DELETE_PUBLISHED_PAGES = 'delete_published_pages';
	const CAPABILITY_DELETE_OTHERS_PAGES = 'delete_others_pages';
	const CAPABILITY_UPLOAD_FILES = 'upload_files';
	const CAPABILITY_EDIT_THEME_OPTIONS = 'edit_theme_options'; // Appearance -> Menus


	public function init_roles_and_capabilities() {
		$roles = wp_roles();
		$administrator = $roles->get_role(self::ROLE_ADMINISTRATOR);

		// Administrator (WP standard role) gets all capabilities
		$administrator->add_cap(self::CAPABILITY_EKC_READ_TOURNAMENTS);
		$administrator->add_cap(self::CAPABILITY_EKC_EDIT_TOURNAMENTS);
		$administrator->add_cap(self::CAPABILITY_EKC_EDIT_OTHERS_TOURNAMENTS);
		$administrator->add_cap(self::CAPABILITY_EKC_MANAGE_TOURNAMENTS);
		$administrator->add_cap(self::CAPABILITY_EKC_MANAGE_OTHERS_TOURNAMENTS);
		$administrator->add_cap(self::CAPABILITY_EKC_DELETE_TOURNAMENTS);
		$administrator->add_cap(self::CAPABILITY_EKC_DELETE_OTHERS_TOURNAMENTS);
		$administrator->add_cap(self::CAPABILITY_EKC_MANAGE_BACKUPS);
		
		// EKC Tournament Administrator gets all capabilities (without WP admin capabilities)
		$roles->add_role(
			self::ROLE_EKC_TOURNAMENT_ADMINISTRATOR,
			'EKC Tournament Administrator',
			array(
				 self::CAPABILITY_READ => true,
				 self::CAPABILITY_READ_PRIVATE_PAGES => true,
				 self::CAPABILITY_PUBLISH_PAGES => true,
				 self::CAPABILITY_EDIT_PAGES => true,
				 self::CAPABILITY_EDIT_PRIVATE_PAGES => true,
				 self::CAPABILITY_EDIT_PUBLISHED_PAGES => true,
				 self::CAPABILITY_EDIT_OTHERS_PAGES => true,
				 self::CAPABILITY_DELETE_PAGES => true,
				 self::CAPABILITY_DELETE_PRIVATE_PAGES => true,
				 self::CAPABILITY_DELETE_PUBLISHED_PAGES => true,
				 self::CAPABILITY_DELETE_OTHERS_PAGES => true,
				 self::CAPABILITY_UPLOAD_FILES => true,
				 self::CAPABILITY_EDIT_THEME_OPTIONS => true,
				 self::CAPABILITY_EKC_READ_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_EDIT_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_EDIT_OTHERS_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_MANAGE_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_MANAGE_OTHERS_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_DELETE_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_DELETE_OTHERS_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_MANAGE_BACKUPS => true
			)
	   );

	   	// EKC Tournament Director gets capabilities required for own tournaments
		$roles->add_role(
			self::ROLE_EKC_TOURNAMENT_DIRECTOR,
			'EKC Tournament Director',
			array(
				 self::CAPABILITY_READ => true,
				 self::CAPABILITY_READ_PRIVATE_PAGES => true,
				 self::CAPABILITY_PUBLISH_PAGES => true,
				 self::CAPABILITY_EDIT_PAGES => true,
				 self::CAPABILITY_EDIT_PRIVATE_PAGES => true,
				 self::CAPABILITY_EDIT_PUBLISHED_PAGES => true,
				 self::CAPABILITY_DELETE_PAGES => true,
				 self::CAPABILITY_DELETE_PRIVATE_PAGES => true,
				 self::CAPABILITY_DELETE_PUBLISHED_PAGES => true,
				 self::CAPABILITY_UPLOAD_FILES => true,
				 self::CAPABILITY_EDIT_THEME_OPTIONS => true,
				 self::CAPABILITY_EKC_READ_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_EDIT_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_MANAGE_TOURNAMENTS => true,
				 self::CAPABILITY_EKC_DELETE_TOURNAMENTS => true
			)
	   );
	}

	public function delete_roles_and_capabilities() {
		$roles = wp_roles();
		$administrator = $roles->get_role(self::ROLE_ADMINISTRATOR);

		// remove custom capabilities from Administrator role (WP standard role)
		$administrator->remove_cap(self::CAPABILITY_EKC_READ_TOURNAMENTS);
		$administrator->remove_cap(self::CAPABILITY_EKC_EDIT_TOURNAMENTS);
		$administrator->remove_cap(self::CAPABILITY_EKC_EDIT_OTHERS_TOURNAMENTS);
		$administrator->remove_cap(self::CAPABILITY_EKC_MANAGE_TOURNAMENTS);
		$administrator->remove_cap(self::CAPABILITY_EKC_MANAGE_OTHERS_TOURNAMENTS);
		$administrator->remove_cap(self::CAPABILITY_EKC_DELETE_TOURNAMENTS);
		$administrator->remove_cap(self::CAPABILITY_EKC_DELETE_OTHERS_TOURNAMENTS);
		$administrator->remove_cap(self::CAPABILITY_EKC_MANAGE_BACKUPS);
		
		// remove custom roles
		$roles->remove_role(self::ROLE_EKC_TOURNAMENT_ADMINISTRATOR);
		$roles->remove_role(self::ROLE_EKC_TOURNAMENT_DIRECTOR);
	}

	public function filter_map_meta_cap( $caps, $cap, $user_id, $args ) {
		$is_meta_cap = array_key_exists( $cap, self::EKC_TOURNAMENT_META_CAPS );		
		if ( $is_meta_cap && isset( $args[0] ) ) {
			// passed in argument is always a tournament ID
			$tournament_id = $args[0];
			$db = new Ekc_Database_Access();
			$tournament = $db->get_tournament_by_id( $tournament_id );
			if ( $tournament && $tournament->get_owner_user_id() && $tournament->get_owner_user_id() !== $user_id ) {
				// if tournament has an owner user which is not the current user, the meta capability is required (e.g. ekc_edit_others_tournaments)
				$caps[] = self::EKC_TOURNAMENT_META_CAPS[$cap];
			}
		}
		if ( in_array( self::CAPABILITY_EDIT_OTHERS_PAGES, $caps ) && isset( $args[0] ) ) {
			$post_id = $args[0];
			$custom_values = get_post_custom_values( self::CUSTOM_FIELD_EKC_PUBLIC_TEMPLATE, $post_id );
			if ( isset( $custom_values[0] ) && $custom_values[0] ) {
				$key = array_search( self::CAPABILITY_EDIT_OTHERS_PAGES, $caps );
				unset($caps[$key]);
			}
		}
		return $caps;
	}
}
