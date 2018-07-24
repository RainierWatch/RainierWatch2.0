<?php

/**
 * API class
 */
class Printful_REST_API
{
    /**
     * Register the REST API routes.
     */
    public static function init() {
        register_rest_route('wc/v2', '/printful/access', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( 'Printful_REST_API', 'set_printful_access' ),
                'args' => array(
                    'accessKey' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => __( 'Printful access key', 'printful' ),
                    ),
                    'storeId' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => __( 'Store Identifier', 'printful' ),
                    ),
                ),
            )
        ) );

        register_rest_route('wc/v2', '/printful/products/(?P<product_id>\d+)/size-chart', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( 'Printful_REST_API', 'post_size_guide' ),
                'args' => array(
                    'product_id' => array(
                        'description' => __( 'Unique identifier for the resource.', 'printful' ),
                        'type'        => 'integer',
                    ),
                    'size_chart' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => __( 'Printful size guide', 'printful' ),
                    )
                )
            )
        ) );

        register_rest_route('wc/v2', '/printful/version', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( 'Printful_REST_API', 'get_version' ),
            )
        ) );

        register_rest_route('wc/v2', '/printful/store_data', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( 'Printful_REST_API', 'get_store_data' ),
            )
        ) );
    }

    /**
     * @param WP_REST_Request $request
     * @return array
     */
    public static function set_printful_access( $request )
    {
        $error = false;

        $api_key  = $request->get_param('accessKey');
        $store_id = $request->get_param('storeId');

        $option  = Printful_Integration::instance()->get_option( 'printful_settings', array() );
        $store_id = intval( $store_id );

        if ( ! is_string( $api_key ) || strlen( $api_key ) == 0 || $store_id == 0 ) {
            $error = 'Failed to update access data';
        }

        $option['printful_key']      = $api_key;
        $option['printful_store_id'] = $store_id;

        Printful_Integration::instance()->update_settings( $option );

        return array(
            'error' => $error,
        );
    }

    /**
     * Submit size guide
     * @param array $data
     * @return array|WP_Error
     */
    public static function post_size_guide( $data )
    {
        if ( empty( $data['size_chart'] ) ) {
            return new WP_Error( 'printful_api_size_chart_empty', __( 'No size chart was provided', 'printful' ), array('status' => 400));
        }

        //product id is valid
        $product_id = intval( $data['product_id'] );
        if ( $product_id < 1 ) {
            return new WP_Error( 'printful_api_product_not_found', __( 'The product ID is invalid', 'printful' ), array('status' => 400));
        }

        //product exists
        $product = get_post( $product_id );
        if ( empty( $product ) || $product->post_type != 'product' ) {
            return new WP_Error( 'printful_api_product_not_found', __( 'The product is not found', 'printful' ), array('status' => 400));
        }

        //how about permissions?
        $post_type = get_post_type_object( $product->post_type );
        if ( ! current_user_can( $post_type->cap->edit_post, $product->ID ) ) {
            return new WP_Error( 'printful_api_user_cannot_edit_product_size_chart', __( 'You do not have permission to edit the size chart', 'printful' ), array('status' => 401));
        }

        //lets do this
        update_post_meta( $product_id, 'pf_size_chart', htmlspecialchars( $data['size_chart'] ) );

        return array(
            'product'    => $product,
            'size_chart' => $data['size_chart'],
        );
    }

    /**
     * Allow remotely get plugin version for debug purposes
     */
    public static function get_version() {

        $error = false;

        try {
            $client    = Printful_Integration::instance()->get_client();
            $store_data = $client->get( 'store' );
        } catch ( Exception $exception ) {
            $error = $exception->getMessage();
        }

        $checklist = Printful_Admin_Status::get_checklist();
        $checklist['overall_status'] = ( $checklist['overall_status'] ? 'OK' : 'FAIL' );

        foreach ( $checklist['items'] as $checklist_key => $checklist_item ) {

            if ( $checklist_item['status'] == Printful_Admin_Status::PF_STATUS_OK ) {
                $checklist_item['status'] = 'OK';
            } elseif ( $checklist_item['status'] == Printful_Admin_Status::PF_STATUS_WARNING ) {
                $checklist_item['status'] = 'WARNING';
            } else {
                $checklist_item['status'] = 'FAIL';
            }

            $checklist['items'][ $checklist_key ] = $checklist_item;
        }

        return array(
            'version'          => Printful_Base::VERSION,
            'api_key'          => Printful_Integration::instance()->get_option('printful_key'),
            'store_id'         => ! empty( $store_data['id'] ) ? $store_data['id'] : false,
            'error'            => $error,
            'status_checklist' => $checklist,
        );
    }

    /**
     * Get necessary store data
     * @return array
     */
    public static function get_store_data() {
        return array(
            'website'   => get_site_url(),
            'version'   => WC()->version,
            'name'      => get_bloginfo( 'title', 'display' )
        );
    }
}
