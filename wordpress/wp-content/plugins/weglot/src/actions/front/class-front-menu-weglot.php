<?php

namespace WeglotWP\Actions\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Helpers\Helper_Pages_Weglot;

/**
 *
 * @since 2.0
 *
 */
class Front_Menu_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @since 2.4.0
	 */
	public function __construct() {
		$this->option_services            = weglot_get_service( 'Option_Service_Weglot' );
		$this->button_services            = weglot_get_service( 'Button_Service_Weglot' );
		$this->custom_url_services        = weglot_get_service( 'Custom_Url_Service_Weglot' );
		$this->request_url_services       = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->private_language_services  = weglot_get_service( 'Private_Language_Service_Weglot' );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function hooks() {
		if ( is_admin() ) {
			return;
		}

		add_filter( 'wp_get_nav_menu_items', [ $this, 'weglot_wp_get_nav_menu_items' ], 20 );
		add_filter( 'nav_menu_link_attributes', [ $this, 'add_nav_menu_link_attributes' ], 10, 2 );
		add_filter( 'wp_nav_menu_objects', [ $this, 'wp_nav_menu_objects' ] );
	}

	/**
	 * @since 2.4.0
	 * @param string $items
	 * @return string
	 */
	public function weglot_fallback_menu( $items ) {
		$button = $this->button_services->get_html();
		$items .= $button;

		return $items;
	}

	/**
	 * @since 2.4.0
	 * @param array $items
	 * @return array
	 */
	public function weglot_wp_get_nav_menu_items( $items ) {
		if ( ! $this->request_url_services->is_translatable_url() || ! weglot_current_url_is_eligible() || $this->private_language_services->private_mode_for_all_languages() ) {
			foreach ( $items as $key => $item ) {
				if ( 'weglot-switcher' !== $item->post_name ) {
					continue;
				}
				unset( $items[ $key ] );
			}

			return $items;
		}

		// Prevent customizer
		if ( doing_action( 'customize_register' ) ) {
			return $items;
		}

		$new_items = [];
		$offset    = 0;

		foreach ( $items as $key => $item ) {
			if ( strpos( $item->post_name, 'weglot-switcher' ) === false ) {
				$item->menu_order += $offset;
				$new_items[] = $item;
				continue;
			}
			$id = $item->ID;
			$i  = 0;

			$classes          = [ 'weglot-lang', 'menu-item-weglot' , 'weglot-language' ];
			$options          = $this->option_services->get_option( 'menu_switcher' );
			$with_flags       = $this->option_services->get_option_button( 'with_flags' );
			$dropdown         = 0;
			if ( isset( $options[ 'menu-item-' . $id ] ) && isset( $options[ 'menu-item-' . $id ]['dropdown'] ) ) {
				$dropdown = $options[ 'menu-item-' . $id ]['dropdown'];
			}
			$hide_current         = 0;
			if ( isset( $options[ 'menu-item-' . $id ] ) && isset( $options[ 'menu-item-' . $id ]['hide_current'] ) ) {
				$hide_current = $options[ 'menu-item-' . $id ]['hide_current'];
			}

			if ( ! $hide_current && $with_flags ) {
				$classes   = array_merge( $classes, explode( ' ', $this->button_services->get_flag_class() ) );
			}

			$languages        = weglot_get_languages_configured();
			$current_language = $this->request_url_services->get_current_language_entry();

			if ( $dropdown ) {
				$title = __( 'Choose your language', 'weglot' );
				if ( ! $hide_current ) {
					$title      = $this->button_services->get_name_with_language_entry( $current_language );
				}
				$item->title      = apply_filters( 'weglot_menu_parent_menu_item_title', $title );
				$item->attr_title = $current_language->getLocalName();
				$item->classes    = array_merge( [ 'weglot-parent-menu-item' ], $classes, [ $current_language->getIso639() ] );
				$new_items[]      = $item;
				$offset++;
			}

			foreach ( $languages as $language ) {
				if ( $this->private_language_services->is_active_private_mode_for_lang( $language->getIso639() ) ) {
					continue;
				}

				if (
					( $dropdown && $current_language->getIso639() === $language->getIso639() ) ||
					( $hide_current && $current_language->getIso639() === $language->getIso639() ) ) {
					continue;
				}

				$add_classes = [];
				if ( $hide_current && $with_flags ) { // Just for children without flag classes
					$classes   = array_merge( $classes, explode( ' ', $this->button_services->get_flag_class() ) );
				}

				$add_classes[] = 'weglot-' . $language->getIso639();
				if ( $with_flags ) {
					$add_classes[] = $language->getIso639();
				}

				$language_item             = clone $item;
				$language_item->ID         = 'weglot-' . $item->ID . '-' . $language->getIso639();
				$language_item->title      = $this->button_services->get_name_with_language_entry( $language );
				$language_item->attr_title = $language->getLocalName();

				$language_code_rewrited = apply_filters('weglot_language_code_replace' , array());
				$l = isset($language_code_rewrited[$language->getIso639()]) ? $language_code_rewrited[$language->getIso639()]:$language->getIso639();
				$language_item->url        = $this->custom_url_services->get_link_button_with_key_code( $l );

				$language_item->lang       = $language->getIso639();
				$language_item->classes    = array_merge( $classes, $add_classes );
				$language_item->menu_order += $offset + $i++;
				if ( $dropdown ) {
					$language_item->menu_item_parent = $item->db_id;
					$language_item->db_id            = 0;
				}

				$new_items[] = $language_item;
			}
			$offset += $i - 1;
		}

		return $new_items;
	}

	/**
	 * @since 2.7.0
	 * @param object $item
	 * @return array
	 */
	public function get_ancestors( $item ) {
		$ids     = array();
		$_anc_id = (int) $item->db_id;
		while ( ( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) && ! in_array( $_anc_id, $ids ) ) {
			$ids[] = $_anc_id;
		}
		return $ids;
	}

	/**
	 * @since 2.7.0
	 * @param array $items
	 * @return array
	 */
	public function wp_nav_menu_objects( $items ) {
		$r_ids = $k_ids = [];

		foreach ( $items as $item ) {
			if ( ! empty( $item->classes ) && is_array( $item->classes ) ) {
				if ( in_array( 'menu-item-weglot', $item->classes ) ) {
					$item->current = false;
					$item->classes = array_diff( $item->classes, array( 'current-menu-item' ) );
					$r_ids         = array_merge( $r_ids, $this->get_ancestors( $item ) ); // Remove the classes for these ancestors
				} elseif ( in_array( 'current-menu-item', $item->classes ) ) {
					$k_ids = array_merge( $k_ids, $this->get_ancestors( $item ) ); // Keep the classes for these ancestors
				}
			}
		}

		$r_ids = array_diff( $r_ids, $k_ids );

		foreach ( $items as $item ) {
			if ( ! empty( $item->db_id ) && in_array( $item->db_id, $r_ids ) ) {
				$item->classes = array_diff( $item->classes, array( 'current-menu-ancestor', 'current-menu-parent', 'current_page_parent', 'current_page_ancestor' ) );
			}
		}

		if ( apply_filters( 'weglot_active_current_menu_item', false ) ) {
			$current_language = weglot_get_current_language();
			foreach ( $items as $item ) {
				if ( ! empty( $item->classes ) && is_array( $item->classes ) ) {
					if ( in_array( 'menu-item-weglot', $item->classes, true ) && in_array( 'weglot-' . $current_language, $item->classes, true ) ) {
						$item->classes[] = 'current-menu-item';
					}
				}
			}
		}

		return $items;
	}


	/**
	 * @since 2.0
	 * @version 2.4.0
	 * @see nav_menu_link_attributes
	 * @param array $attrs
	 * @param object $item
	 * @return array
	 */
	public function add_nav_menu_link_attributes( $attrs, $item ) {
		$str              = 'weglot-switcher';
		if ( strpos( $item->post_name, $str ) !== false ) {
			$current_language = $this->request_url_services->get_current_language();
			if ( ! $this->request_url_services->is_translatable_url() || ! weglot_current_url_is_eligible() ) {
				$attrs['style'] = 'display:none';
				return $attrs;
			}

			$attrs['data-wg-notranslate'] = 'true';
		}

		return $attrs;
	}
}

