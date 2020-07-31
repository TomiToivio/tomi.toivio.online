<?php

/**
 * Modifies Polylang filters
 *
 * @since 0.1
 */
class Lingotek_Filters_Columns extends PLL_Admin_Filters_Columns {
	/**
	 * Lingotek model.
	 *
	 * @var object
	 */
	public $lgtm;
	/**
	 * Constructor
	 *
	 * @since 0.1
	 *
	 * @param object $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		parent::__construct( $polylang );

		$this->lgtm = &$GLOBALS['wp_lingotek']->model;
		// FIXME remove quick edit and bulk edit for now waiting for a solution to remove it only for uploaded documents.
		remove_filter( 'quick_edit_custom_box', array( &$this, 'quick_edit_custom_box' ), 10, 2 );
		remove_filter( 'bulk_edit_custom_box', array( &$this, 'quick_edit_custom_box' ), 10, 2 );
	}

	/**
	 * Adds languages and translations columns in posts, pages, media, categories and tags tables
	 * overrides Polylang method to display all languages including the filtered one
	 * as well as displaying a tooltip with the language name and locale when there is no flag
	 *
	 * @since 0.2
	 *
	 * @param array  $columns list of table columns.
	 * @param string $before the column before which we want to add our languages.
	 * @return array modified list of columns
	 */
	protected function add_column( $columns, $before ) {
		$n = array_search( $before, array_keys( $columns ), true );
		if ( $n ) {
			$end = array_slice( $columns, $n );
			$columns = array_slice( $columns, 0, $n );
		}
		$this->print_patch_error();
		$this->print_cancel_error();
		foreach ( $this->model->get_languages_list() as $language ) {
			$columns[ 'language_' . $language->locale ] = $language->flag ? $language->flag :
				sprintf('<a href="" title="%s">%s</a>',
					esc_html( "$language->name ($language->locale)" ),
					esc_html( $language->slug )
				);
		}

		return isset( $end ) ? array_merge( $columns, $end ) : $columns;
	}

	private function print_cancel_error() {
		$lingotek_log_errors = get_option('lingotek_log_errors');
		$message = isset($lingotek_log_errors['disassociate_document_error']) ? $lingotek_log_errors['disassociate_document_error'] : '';
		if (get_option('disassociate_source_failed', FALSE)) {
			$message = str_replace('cancel', 'delete', $message);
			printf('<div class="notice notice-warning is-dismissible">
			<p>%s</p>
			</div>', $message);
			update_option('disassociate_source_failed', FALSE);
		} elseif (get_option('disassociate_target_failed', FALSE)) {
			$message = str_replace('cancel', 'delete', $message);
			printf('<div class="notice notice-warning is-dismissible">
			<p>%s</p>
			</div>', $message);
			update_option('disassociate_target_failed', FALSE);
		} elseif (get_option('cancel_source_failed', FALSE)) {
			printf('<div class="notice notice-warning is-dismissible">
			<p>%s</p>
			</div>', $message);
			update_option('cancel_source_failed', FALSE);
		} elseif (get_option('cancel_target_failed', FALSE)) {
			printf('<div class="notice notice-warning is-dismissible">
			<p>%s</p>
			</div>', $message);
			update_option('cancel_target_failed', FALSE);
		}
	}

	private function print_patch_error() {
		$lingotek_log_errors = get_option('lingotek_log_errors');
		if (empty($lingotek_log_errors['patch_document_error'])) {
			return;
		}
		printf('<div class="notice notice-error is-dismissible"><p>%s</p></div>', $lingotek_log_errors['patch_document_error']);
		unset($lingotek_log_errors['patch_document_error']);
		update_option('lingotek_log_errors', $lingotek_log_errors);
	}

	/**
	 * Fills the language and translations columns in the posts and taxonomies lists tables
	 * take care that when doing ajax inline edit, the post or term may not be updated in database yet
	 *
	 * @since 0.2
	 *
	 * @param string $type 'post' or 'term'.
	 * @param string $column column name.
	 * @param int    $object_id id of the current object in row.
	 * @param item   $custom_data data.
	 */
	protected function _column( $type, $column, $object_id, $custom_data = null ) {
		$action = 'post' === $type ? 'inline-save' : 'inline-save-tax';
		$get_action = filter_input( INPUT_GET, 'action' );
		$inline_lang_choice = filter_input( INPUT_POST, 'inline_lang_choice' );
		$inline = defined( 'DOING_AJAX' ) && $get_action === $action && ! empty( $inline_lang_choice );
		$lang = $inline ?
			$this->model->get_language( $inline_lang_choice ) :
			'post' === $type ? PLL()->model->post->get_language( $object_id ) : PLL()->model->term->get_language( $object_id );

		if ( false === strpos( $column, 'language_' ) || ! $lang ) {
			if ( $custom_data ) {
				return $custom_data;
			} else {
				return '';
			}
		}

		$language = $this->model->get_language( substr( $column, 9 ) );

		// FIXME should I suppress quick edit?.
		// yes for uploaded posts, but I will need js as the field is built for all posts.
		// /!\ also take care not add this field two times when translations are managed by Polylang.
		// hidden field containing the post language for quick edit (used to filter categories since Polylang 1.7).
		if ( $column === $this->get_first_language_column() ) {
			printf( '<div class="hidden" id="lang_%d">%s</div>', esc_attr( $object_id ), esc_html( $lang->slug ) );
		}

		$id = ($inline && $lang->slug !== $this->model->get_language( filter_input( INPUT_POST, 'old_lang' ) )->slug) ?
			($language->slug === $lang->slug ? $object_id : 0) :
			'post' === $type ? PLL()->model->post->get( $object_id, $language ) : PLL()->model->term->get( $object_id, $language );

		$document = $this->lgtm->get_group( $type, $object_id );
		if ( isset( $document->source ) ) {
			$source_language = 'post' === $type ? PLL()->model->post->get_language( $document->source ) : PLL()->model->term->get_language( $document->source );
			$source_profile = Lingotek_Model::get_profile( $this->content_type, $source_language, $document->source );
		} else {
			$source_language = $lang;
		}

		// FIXME not very clean.
		$actions = 'post' === $type ? $GLOBALS['wp_lingotek']->post_actions : $GLOBALS['wp_lingotek']->term_actions;

		$profile = Lingotek_Model::get_profile( $this->content_type, $language, $object_id );
		
		$disabled = 'disabled' === $profile['profile'] || !Lingotek::is_allowed_tms_locale($language->lingotek_locale);
		// post ready for upload.
		if ( $this->lgtm->can_upload( $type, $object_id ) && $object_id === $id ) {
			return $disabled ? ('post' === $type ? parent::post_column( $column, $object_id ) : parent::term_column( '', $column, $object_id ))
			: ($document && (count( $document->desc_array ) >= 3) ? $actions->upload_icon( $object_id, true ) : $actions->upload_icon( $object_id ));
		} // if language is set to copy and profile is manual.
		elseif (('post' === $type || 'term' === $type) && ((isset( $source_profile['targets'][ $language->slug ] ) &&
				'copy' === $source_profile['targets'][ $language->slug ]) || (isset( $profile['targets'][ $language->slug ] ) &&
				'copy' === $profile['targets'][ $language->slug ] ) && isset( $document->source ))) {
		 	if (isset( $document->desc_array[ $language->slug ])) {
		 		return ('post' === $type) ? parent::post_column( $column, $object_id ) : parent::term_column( '', $column, $object_id );
			 }
			 else {
		 		if ( $document ) {
		 			return $actions->copy_icon( $document->source, $language->slug );				
				}
				else {
		 			return $actions->copy_icon( $object_id, $language->slug );
		 		}
		 	}
		} // translation disabled.
		elseif (( isset( $document->source ) && $document->is_disabled_target( $source_language, $language ) &&
				! isset( $document->translations[ $language->locale ] ) ) || !Lingotek::is_allowed_tms_locale($language->lingotek_locale) ) {
			return 'post' === $type ? parent::post_column( $column, $object_id ) : parent::term_column( '', $column, $object_id );
		} // source post is uploaded.
		elseif ( isset( $document->source ) && $document->source === $id ) {
			// source ready for upload.
			delete_option( $document->source );
		 	if ( $this->lgtm->can_upload( $type, $id ) ) {
		 		return $actions->upload_icon( $id );
			}
		 	// importing source.
		 	if ( $id === $object_id && 'importing' === $document->status ) {
				return Lingotek_Actions::importing_icon( $document );
			}
			// source failed
			if ($id === $object_id && 'failed' === $document->status) {
				return $actions->failed_import_icon($document->status, $object_id);
			}
		 	return 'post' === $type ? Lingotek_Post_actions::uploaded_icon( $id ) : Lingotek_Term_actions::uploaded_icon( $id );
		} // translations.
		elseif ( isset( $document->translations[ $language->locale ] ) || (isset( $document->source ) && 'current' === $document->status) ) {
			return Lingotek_Actions::translation_icon( $document, $language );
		}
		elseif ( ( 'term' === $type && ! isset( $document->translations[ $language->locale ] ) && $document->source !== $object_id ) || !Lingotek::is_allowed_tms_locale($language->lingotek_locale) ) { 
			return parent::term_column( '', $column, $object_id );
		} // translations exist but are not managed by Lingotek TMS.
		elseif ( empty( $document->source ) ) {
			return $object_id === $id && ! $disabled ? $actions->upload_icon( $object_id, true ) : ('post' === $type ? parent::post_column( $column, $object_id ) : parent::term_column( '', $column, $object_id ));
		}
		elseif ($document->status == 'failed') {
			return $actions->failed_import_icon($document->status, $object_id);
		}
		elseif ($document->status === 'cancelled') {
			return parent::post_column( $column, $object_id );
		}
		// no translation.
		else {
			return '<div class="lingotek-color dashicons dashicons-no"></div>';
		}
	}

	/**
	 * Fills the language and translations columns in the posts, pages and media library tables
	 * take care that when doing ajax inline edit, the post may not be updated in database yet
	 *
	 * @since 0.1
	 *
	 * @param string $column column name.
	 * @param int    $post_id post id.
	 */
	public function post_column( $column, $post_id ) {
		$this->content_type = get_post_type( $post_id );

		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'class' => array(),
				'title' => array(),
				'target' => array()
			),
			'div' => array(
				'title' => array(),
				'class' => array(),
			),
			'img' => array(
				'src' => array()
			)
		);
		echo wp_kses( $this->_column( 'post', $column, $post_id ), $allowed_html );

		/**
		*	Setup workflow specific logic for posts.
		*/
		$post = get_post( $post_id );
		$source_language = PLL()->model->post->get_language( $post_id );
		$target_language = $this->model->get_language( substr( $column, 9 ) );
		if (is_object($source_language) && is_object($target_language)) {
			$workflow_id = Lingotek_Model::get_profile_option( 'workflow_id', $post->post_type, $source_language, $target_language, $post_id );
			$workflow = Lingotek_Workflow_Factory::get_workflow_instance( $workflow_id ); 
			// $workflow->override_events( $workflow_id ); // Loads appropriate .js file.
			$workflow->echo_posts_modal( $post_id, $target_language->locale ); // adds modal html to page.
		}

		// checking for api errors.
		$document = $this->lgtm->get_group( 'post', $post_id );
		if ( isset( $document->source ) ) {
			$source_language = PLL()->model->post->get_language( $document->source );
			$this->error_icon_html( $column, $post_id, $source_language->locale );
		} else {
			$this->error_icon_html( $column, $post_id );
		}
	}

	/**
	 * Fills the language and translations columns in the categories and post tags tables
	 * take care that when doing ajax inline edit, the term may not be updated in database yet
	 *
	 * @since 0.2
	 *
	 * @param string $custom_data data.
	 * @param string $column column name.
	 * @param int    $term_id term id.
	 */
	public function term_column( $custom_data, $column, $term_id ) {
		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'class' => array(),
				'title' => array(),
				'target' => array()
			),
			'div' => array(
				'title' => array(),
				'class' => array(),
			),
			'span' => array(
				'class' => array(),
			),
			'img' => array(
				'src' => array()
			)
		);
		$this->content_type = $GLOBALS['taxonomy'];

		/**
		*	Setup workflow specific logic for terms.
		*/
		$source_language = PLL()->model->term->get_language( $term_id );
		$target_language = $this->model->get_language( substr( $column, 9 ) );
		if (is_object($source_language) && is_object($target_language)) {
			$workflow_id = Lingotek_Model::get_profile_option( 'workflow_id', $this->content_type, $source_language, $target_language );
			$workflow = Lingotek_Workflow_Factory::get_workflow_instance( $workflow_id ); 
			// $workflow->override_events( $workflow_id ); // Loads appropriate .js file.
			$workflow->echo_terms_modal( $term_id,  $target_language->locale); // adds modal html to page.
		}

		if ( ! $custom_data ) {
			echo wp_kses( $this->_column( 'term', $column, $term_id ), $allowed_html );
		} else {
			echo wp_kses( $this->_column( 'term', $column, $term_id, $custom_data ), $allowed_html );
		}
		// checking for api errors.
		$this->error_icon_html( $column, $term_id );
	}

	/**
	 * Checks for errors in the lingotek_log_errors option and displays an icon
	 *
	 * @since 1.2
	 *
	 * @param string $column column.
	 * @param string $object_id id.
	 * @param item   $source_locale locale.
	 */
	protected function error_icon_html( $column, $object_id, $source_locale = null ) {
		// checking for api errors.
		$source_column = substr( $column, 9 );
		$column_language_only = substr( $column, 0, 11 );
		$errors = get_option( 'lingotek_log_errors' );

		if ( $source_column === $source_locale ) {
			if ( isset( $errors[ $object_id ] ) ) {
				$api_error = Lingotek_Actions::retrieve_api_error( $errors[ $object_id ] );
				$allowed_html = array(
					'a' => array(
						'href' => array(),
						'class' => array(),
						'title' => array(),
						'target' => array()
					),
				);
				echo wp_kses( Lingotek_Actions::display_error_icon( 'error', $api_error ), $allowed_html );
			}
		}
	}
}
