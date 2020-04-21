<?php
$aco_options = get_option( 'aco_options' );
$aco_objects = isset( $aco_options['objects'] ) ? $aco_options['objects'] : array();
$aco_tags = isset( $aco_options['tags'] ) ? $aco_options['tags'] : array();

$advanced_view = isset( $aco_options['show_advanced_view']) ? $aco_options['show_advanced_view'] : '';
?>
<style>
.aco-toggle {
    position: relative;
    display:inline-block;
    user-select: none;
}

.aco-toggle__items {
    box-sizing: border-box;
}

.aco-toggle__items > * {
    box-sizing: inherit;
}

.aco-toggle__input[type=checkbox] {
    border-radius: 2px;
    border: 2px solid #6c7781;
    margin-right: 12px;
    transition: none;
    height: 100%;
    left: 0;
    top: 0;
    margin: 0;
    padding: 0;
    opacity: 0;
    position: absolute;
    width: 100%;
    z-index: 1;
}

.aco-toggle__track {
    background-color: #fff;
    border: 2px solid #6c7781;
    border-radius: 9px;
    display: inline-block;
    height: 18px;
    width: 36px;
    vertical-align: top;
    transition: background .2s ease;
}

.aco-toggle__thumb {
    background-color: #6c7781;
    border: 5px solid #6c7781;
    border-radius: 50%;
    display: block;
    height: 10px;
    width: 10px;
    position: absolute;
    left: 4px;
    top: 4px;
    transition: transform .2s ease;
}

.aco-toggle__off {
    position: absolute;
    right: 6px;
    top: 6px;
    color: #6c7781;
    fill: currentColor;
}

.aco-toggle__on {
    position: absolute;
    top: 6px;
    left: 8px;
    border: 1px solid #fff;
    outline: 1px solid transparent;
    outline-offset: -1px;
    display: none;
}

.aco-toggle__input[type=checkbox]:checked + .aco-toggle__items .aco-toggle__track {
    background-color: #11a0d2;
    border: 9px solid transparent;
}

.aco-toggle__input[type=checkbox]:checked + .aco-toggle__items .aco-toggle__thumb {
    background-color: #fff;
    border-width: 0;
    transform: translateX(18px);
}

.aco-toggle__input[type=checkbox]:checked + .aco-toggle__items .aco-toggle__off {
    display: none;
}

.aco-toggle__input[type=checkbox]:checked + .aco-toggle__items .aco-toggle__on {
    display: inline-block;
}

.aco-reset-response {
    margin-left:15px;
    color:#0085ba;
}
</style>

<!-- WRAP BEGINS -->
<div class="wrap">

    <div id="icon-tools" class="icon32"><br/></div>
    <h1><?php esc_html_e( get_admin_page_title(), ACO_TEXTDOMAIN ); ?></h1>
    <?php if ( isset( $_GET['msg'] ) ) : ?>
        <div id="message" class="updated below-h2">
            <?php if ( $_GET['msg'] == 'update' ) : ?>
                <p><?php _e( 'Settings Updated.', ACO_TEXTDOMAIN ); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'aco_nonce' ); ?>
        <div id="aco_select_objects">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Check to Sort Post Types', ACO_TEXTDOMAIN ) ?></th>
                        <td>
                            <label>
                                <div class="aco-toggle">
                                    <input id="aco_selectall_objects" class="aco-toggle__input" type="checkbox">
                                    <div class="aco-toggle__items">
                                        <span class="aco-toggle__track"></span>
                                        <span class="aco-toggle__thumb"></span>
                                        <svg class="aco-toggle__off" width="6" height="6" aria-hidden="true"
                                             role="img" focusable="false" viewBox="0 0 6 6">
                                            <path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path>
                                        </svg>
                                        <svg class="aco-toggle__on" width="2" height="6" aria-hidden="true"
                                             role="img" focusable="false" viewBox="0 0 2 6">
                                            <path d="M0 0h2v6H0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                &nbsp;<?php _e( 'Select All', ACO_TEXTDOMAIN ) ?></label><br>
                            <?php
                            $post_types_args = apply_filters( 'aco_post_types_args', array(
                                'show_ui'      => true,
                                'show_in_menu' => true,
                            ), $aco_options );

                            $post_types = get_post_types( $post_types_args, 'objects' );

                            foreach ( $post_types as $post_type ) {
                                if ( $post_type->name == 'attachment' )
                                    continue;
                                ?>
                                <label>
                                    <div class="aco-toggle">
                                        <input class="aco-toggle__input" type="checkbox"
                                               name="objects[]" value="<?php echo $post_type->name; ?>" <?php
                                        if ( isset( $aco_objects ) && is_array( $aco_objects ) ) {
	                                        if ( in_array( $post_type->name, $aco_objects ) ) {
		                                        echo 'checked="checked"';
	                                        }
                                        }
		                                ?>>
                                        <div class="aco-toggle__items">
                                            <span class="aco-toggle__track"></span>
                                            <span class="aco-toggle__thumb"></span>
                                            <svg class="aco-toggle__off" width="6" height="6" aria-hidden="true"
                                                 role="img" focusable="false" viewBox="0 0 6 6">
                                                <path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path>
                                            </svg>
                                            <svg class="aco-toggle__on" width="2" height="6" aria-hidden="true"
                                                 role="img" focusable="false" viewBox="0 0 2 6">
                                                <path d="M0 0h2v6H0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    &nbsp;<?php echo $post_type->label; ?></label><br>
                                    <?php
                                }
                                ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="aco_select_tags">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Check to Sort Taxonomies', ACO_TEXTDOMAIN ) ?></th>
                        <td>
                            <label>
                                <div class="aco-toggle">
                                    <input id="aco_selectall_tags" class="aco-toggle__input" type="checkbox">
                                    <div class="aco-toggle__items">
                                        <span class="aco-toggle__track"></span>
                                        <span class="aco-toggle__thumb"></span>
                                        <svg class="aco-toggle__off" width="6" height="6" aria-hidden="true"
                                             role="img" focusable="false" viewBox="0 0 6 6">
                                            <path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path>
                                        </svg>
                                        <svg class="aco-toggle__on" width="2" height="6" aria-hidden="true"
                                             role="img" focusable="false" viewBox="0 0 2 6">
                                            <path d="M0 0h2v6H0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                &nbsp;<?php _e( 'Select All', ACO_TEXTDOMAIN ) ?></label><br>
                            <?php
                            $taxonomies = get_taxonomies( array( 'show_ui' => true ), 'objects' );

                            foreach ( $taxonomies as $taxonomy ) {
                                if ( $taxonomy->name == 'post_format' )
                                    continue;
                                ?>
                                <label>
                                    <div class="aco-toggle">
                                        <input class="aco-toggle__input" type="checkbox"
                                               name="tags[]" value="<?php echo $taxonomy->name; ?>" <?php
                                        if ( isset( $aco_tags ) && is_array( $aco_tags ) ) {
	                                        if ( in_array( $taxonomy->name, $aco_tags ) ) {
		                                        echo 'checked="checked"';
	                                        }
                                        }
                                        ?>>
                                        <div class="aco-toggle__items">
                                            <span class="aco-toggle__track"></span>
                                            <span class="aco-toggle__thumb"></span>
                                            <svg class="aco-toggle__off" width="6" height="6" aria-hidden="true"
                                                 role="img" focusable="false" viewBox="0 0 6 6">
                                                <path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path>
                                            </svg>
                                            <svg class="aco-toggle__on" width="2" height="6" aria-hidden="true"
                                                 role="img" focusable="false" viewBox="0 0 2 6">
                                                <path d="M0 0h2v6H0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                   &nbsp;<?php echo $taxonomy->label ?></label><br>
                                    <?php
                                }
                                ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="scporder_advanved_view">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Check to see advanced view of Post Types ', ACO_TEXTDOMAIN ) ?></th>
                        <td>
                            <label>
                                <div class="aco-toggle">
                                    <input class="aco-toggle__input" type="checkbox"
                                        name="show_advanced_view" value="1" <?php checked( '1', $advanced_view, 'checked="checked"' );  ?>>
                                    <div class="aco-toggle__items">
                                        <span class="aco-toggle__track"></span>
                                        <span class="aco-toggle__thumb"></span>
                                        <svg class="aco-toggle__off" width="6" height="6" aria-hidden="true"
                                            role="img" focusable="false" viewBox="0 0 6 6">
                                            <path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path>
                                        </svg>
                                        <svg class="aco-toggle__on" width="2" height="6" aria-hidden="true"
                                            role="img" focusable="false" viewBox="0 0 2 6">
                                            <path d="M0 0h2v6H0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <?php echo __( 'Show advanced view of Post Types', ACO_TEXTDOMAIN ); ?>
                            </label><br>
                            <p class="description"><?php _e( 'Notice: This is for advanced users only.', ACO_TEXTDOMAIN ); ?></p>
                            <!--@todo : please look into below description. -->
                            <p class="description"><?php _e( 'Check to include other custom post types. You will need to update your settings after enabling this option.', ACO_TEXTDOMAIN ); ?></p>

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="submit">
            <input type="submit" class="button-primary" name="aco_submit" value="<?php _e( 'Update',  ACO_TEXTDOMAIN ); ?>">
        </p>
    </form>

    <div class="aco-reset-order">
        <h1>Want to reset the order of the posts?</h1>
        <div id="scpo_reset_select_objects">
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Check to reset order of Post Types',  ACO_TEXTDOMAIN ) ?></th>
                    <td>
                        <?php
                        foreach ( $post_types as $post_type ) {
                            if ( $post_type->name == 'attachment' )
                                continue;
                            ?>
                            <label>
                                <div class="aco-toggle">
                                    <input class="aco-toggle__input" type="checkbox"
                                           name="<?php echo $post_type->name; ?>" value="">
                                    <div class="aco-toggle__items">
                                        <span class="aco-toggle__track"></span>
                                        <span class="aco-toggle__thumb"></span>
                                        <svg class="aco-toggle__off" width="6" height="6" aria-hidden="true"
                                             role="img" focusable="false" viewBox="0 0 6 6">
                                            <path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path>
                                        </svg>
                                        <svg class="aco-toggle__on" width="2" height="6" aria-hidden="true"
                                             role="img" focusable="false" viewBox="0 0 2 6">
                                            <path d="M0 0h2v6H0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                &nbsp;<?php echo $post_type->label; ?></label><br>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
        <div>
            <a id="reset-aco-order" class="button button-primary" href="#">Reset order</a>
            <span class="aco-reset-response"></span>
        </div>
    </div>

</div><!-- ends wrap -->

<script>
    (function ($) {

        $( "#aco_selectall_objects" ).on( 'click', function () {
            var items = $( "#aco_select_objects input");
            if ( $(this).is( ':checked' ) )
                $( items ).prop( 'checked', true );
            else
                $( items ).prop( 'checked', false );
        });

        $( "#aco_selectall_tags" ).on( 'click', function () {
            var items = $( "#aco_select_tags input" );
            if ( $( this ).is( ':checked' ) )
                $( items ).prop( 'checked', true );
            else
                $(items).prop('checked', false);
        });

        // Reset order function
        $( '#reset-aco-order' ).click(function ( e ) {

            e.preventDefault();
            var btn = $(this),
                item_input = $(this).parents( '.aco-reset-order' ).find( 'input:checked' ),
                items = [],
                data = {
                    action: 'aco_reset_order',
                    aco_security: '<?php echo wp_create_nonce( "aco-reset-order" ); ?>'
                };

            if ( item_input.length > 0 ) {
                item_input.each(function ( i, item ) {
                    items.push( item.name );
                });

                data['items'] = items;

                $.post("<?php echo admin_url( 'admin-ajax.php' );  ?>", data, function ( response ) {
                    if (response) {
                        btn.next( '.aco-reset-response' ).text( response );
                    }
                });
            }
        });

    })(jQuery)
</script>