<?php
/**
 * Include and setup custom metaboxes and fields. (make sure you copy this file to outside the CMB2 directory)
 *
 * Be sure to replace all instances of 'bridge_child_' with your project's prefix.
 * http://nacin.com/2010/05/11/in-wordpress-prefix-everything/
 *
 * @category YourThemeOrPlugin
 * @package  Demo_CMB2
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/CMB2/CMB2
 */

/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */

if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

/**
 * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
 */
function bridge_child_register_demo_metabox() {

	$document_prefix = 'bridge_document_';
	$frontend_document_prefix = 'frontend_document_';

	// Register metabox for CPT (documents)

	$cmb_demo = new_cmb2_box( array(
		'id'            => $document_prefix . 'metabox',
		'title'         => esc_html__( 'Document', 'bridge-child' ),
		'object_types'  => array( 'documents' ), // post type
	) );

	$cmb_demo->add_field(
		array(
		'name' => esc_html__( 'Upload Pdf Document', 'bridge-child' ),
		'desc' => esc_html__( 'Upload a Pdf or enter a URL for Document viewer.', 'bridge-child' ),
		'id'   => $document_prefix . 'document_file',
		'type' => 'file',
		'text'    => array(
			'add_upload_file_text' => 'Add Pdf File' // Change upload button text. Default: "Add or Upload File"
		),
		'query_args' => array(
			'type' => 'application/pdf', // Make library only display PDFs.
		),
	) );

	$cmb_demo->add_field( array(
		'name' => esc_html__( 'Upload Document', 'bridge-child' ),
		'desc' => esc_html__( 'Upload a doc,docx,xlxs or pptx file or enter a URL for Document download.', 'bridge-child' ),
		'id'   => $document_prefix . 'document_download',
		'type' => 'file',
		'text'    => array(
			'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
		),
		'query_args' => array(
			'type' => 'application/pdf', // Make library only display PDFs.
			'type' => array(
				'application/msword',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/pdf',
				'application/vnd.ms-powerpoint',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'application/vnd.ms-excel',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			),
		),
	) );

	$cmb_demo->add_field( array(
		'name' => esc_html__( 'YouTube Video Link', 'cmb2' ),
		'id'   => $document_prefix . 'document_youtube',
		'desc' => esc_html__( 'Make sure not to use shortlinks for youtube videos. Copy link from the url address and not from share.', 'bridge-child' ),
		'type' => 'oembed',
	) );

	$cmb_demo->add_field( array(
		'name' => esc_html__( 'Google Drive file Link', 'cmb2' ),
		'id'   => $document_prefix . 'document_gdrive',
		'desc' => esc_html__( 'To generate Google File link Click on File=>Publish to the web, copy link and add only link here.', 'bridge-child' ),
		'type' => 'oembed',
	) );
	$cmb_demo->add_field( array(
		'name' => esc_html__( 'Effective Begin Date', 'bridge-child' ),
		'id'   => '_' . $document_prefix . 'effective_begin_date',
		'type' => 'text_date',
		'date_format' => 'Y-m-d',
		'after_field' => esc_html__( 'Select the effective begin date for this document.', 'bridge-child' ),
	) );

	$cmb_demo->add_field( array(
		'name' => esc_html__( 'Effective End Date', 'bridge-child' ),
		'id'   => '_' . $document_prefix . 'effective_end_date',
		'type' => 'text_date',
		'date_format' => 'Y-m-d',
		'after_field' => esc_html__( 'Select the effective end date for this document.', 'bridge-child' ),
	) );

	$cmb_demo->add_field( array(
		'name' => esc_html__( 'Pending Flag', 'bridge-child' ),
		'id'   => '_' . $document_prefix . 'pending_flag',
		'type' => 'select',
		'desc' => esc_html__( 'Select whether this document is pending.', 'bridge-child' ),
		'options' => array(
			'no' => __( 'No', 'bridge-child' ),
			'yes' => __( 'Yes', 'bridge-child' ),
		),
		'default' => 'no',
	) );


	// Register metabox for CPT (frontend_documents)

	$fd_metabox = new_cmb2_box( array(
		'id'            => $frontend_document_prefix . 'metabox',
		'title'         => esc_html__( 'Frontend Document', 'bridge-child' ),
		'object_types'  => array( 'frontend_documents' ), // post type
	) );

	$fd_metabox->add_field( array(
		'name' => esc_html__( 'Upload Document', 'bridge-child' ),
		'id'   => $frontend_document_prefix . 'file',
		'type' => 'file',
		'text'    => array(
			'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
		),
		'query_args' => array(
			'type' => 'application/pdf', // Make library only display PDFs.
			'type' => array(
				'application/msword',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/pdf',
				'application/vnd.ms-powerpoint',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'application/vnd.ms-excel',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			),
		),
	) );

}

add_action( 'cmb2_admin_init', 'bridge_child_register_demo_metabox' );

/**
 * Hook in and add a metabox to add fields to taxonomy terms
 */
function bridge_child_register_taxonomy_metabox() {

	$document_prefix = 'documents_category_';
	$frontend_document_prefix = 'frontend_documents_category_';

	/**
	 * Metabox to add fields to categories and tags
	 */
	$cmb_demo = new_cmb2_box( array(
		'id'               => $document_prefix . 'edit',
		'title'            => esc_html__( 'Order Documents', 'cmb2' ), // Doesn't output for term boxes
		'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
		'taxonomies'       => array( 'documents_category' ), // Tells CMB2 which taxonomies should have these fields
		// 'new_term_section' => true, // Will display in the "Add New Category" section
	) );

	$cmb_demo->add_field( array(
		'name'             => 'Users Tools and Templates Access',
		'id'               => $document_prefix . 'users_tools_templates_access',
		'type'             => 'radio',
		'desc' => esc_html__( 'Disable category or document for users from having Tools and Templates access.', 'cmb2' ),
		'options'          => array(
			'disable' => __( 'Disable Tools & Templates Access', 'cmb2' ),
			'none' => __( 'Enable Tools & Templates Access (default)', 'cmb2' ),
		),
		'default' => 'none',
	) );
        
	$cmb_demo->add_field( array(
		'name'             => 'Users Policies and Procedures Access',
		'id'               => $document_prefix . 'users_policies_procedures_access',
		'type'             => 'radio',
		'desc' => esc_html__( 'Disable category or document for users from having Policies and Procedures access.', 'cmb2' ),
		'options'          => array(
			'disable' => __( 'Disable Policies & Procedures Access', 'cmb2' ),
			'none' => __( 'Enable Policies & Procedures Access (default)', 'cmb2' ),
		),
		'default' => 'none',
	) );

	$cmb_demo->add_field( array(
		'name'             => 'Order Documents',
		'id'               => $document_prefix . 'order_documents',
		'type'             => 'radio',
		'show_option_none' => true,
		'options'          => array(
			'alphabatically' => __( 'Alphabatically', 'cmb2' ),
			'date'   => __( 'Published Date', 'cmb2' ),
			'menu_order'     => __( 'Document Order', 'cmb2' ),
		),
	) );

	// frontend documents meta fields

	$cmb_demo = new_cmb2_box( array(
		'id'               => $frontend_document_prefix . 'edit',
		'title'            => esc_html__( 'Order Documents', 'cmb2' ), // Doesn't output for term boxes
		'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
		'taxonomies'       => array( 'frontend_documents_category' ), // Tells CMB2 which taxonomies should have these fields
		// 'new_term_section' => true, // Will display in the "Add New Category" section
	) );

	$cmb_demo->add_field( array(
		'name'             => 'Order Documents',
		'id'               => $frontend_document_prefix . 'order_documents',
		'type'             => 'radio',
		'show_option_none' => true,
		'options'          => array(
			'alphabatically' => __( 'Alphabatically', 'cmb2' ),
			'date'   => __( 'Published Date', 'cmb2' ),
			'menu_order'     => __( 'Document Order', 'cmb2' ),
		),
	) );

}

add_action( 'cmb2_admin_init', 'bridge_child_register_taxonomy_metabox' );


/**
 * Hook in and add a metabox to add fields to taxonomy terms
 */
function bridge_child_register_company_metabox() {
	$prefix = 'users_comapny_';

	/**
	 * Metabox to add fields to categories and tags
	 */
	$cmb_demo = new_cmb2_box( array(
		'id'               => $prefix . 'edit',
		'title'            => esc_html__( 'Order Documents', 'cmb2' ), // Doesn't output for term boxes
		'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
		'taxonomies'       => array( 'company' ), // Tells CMB2 which taxonomies should have these fields
		// 'new_term_section' => true, // Will display in the "Add New Category" section
	) );

	$cmb_demo->add_field( array(
		'name'             => 'State',
		'id'               => $prefix . 'state',
		'type'             => 'select',
		'show_option_none' => true,
		'default'          => 'custom',
		'attributes' => array(
			'required' => 'required',
		),
		'options'          => array(
			'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		),
	) );
	
	$cmb_demo->add_field( array(
		'name'             => 'Company Status',
		'id'               => $prefix . 'company_status',
		'type'             => 'radio',
		'desc' => esc_html__( 'Disabling company access will disable all users under this company.', 'cmb2' ),
		'options'          => array(
			'none' => __( 'None', 'cmb2' ),
			'disable' => __( 'Disable', 'cmb2' ),
		),
	) );

	$cmb_demo->add_field( array(
		'name'             => 'Access Document Library ',
		'id'               => $prefix . 'company_frontend_documents_access',
		'type'             => 'radio',
		'desc' => esc_html__( 'Disabling will disable access to the documents under this company.', 'cmb2' ),
		'options'          => array(
			'enable' => __( 'Enable', 'cmb2' ),
			'disable' => __( 'Disable', 'cmb2' ),
		)
	) );

}

add_action( 'cmb2_admin_init', 'bridge_child_register_company_metabox' );