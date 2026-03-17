<?php
if (!defined('ABSPATH')) {
    exit;
}

$selected_products = get_option('ams_membership_products', array());
?>

<div class="wrap">
    <h1><?php _e('Membership Products', 'ams'); ?></h1>
    
    <p><?php _e('Select which WooCommerce products should trigger membership ID generation when purchased.', 'ams'); ?></p>
    
    <form method="post" action="options.php">
        <?php settings_fields('ams_settings'); ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="50"><?php _e('Select', 'ams'); ?></th>
                    <th><?php _e('Product Name', 'ams'); ?></th>
                    <th><?php _e('ID', 'ams'); ?></th>
                    <th><?php _e('Type', 'ams'); ?></th>
                    <th><?php _e('Price', 'ams'); ?></th>
                    <th><?php _e('Status', 'ams'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                );
                $products = get_posts($args);
                
                if (empty($products)) {
                    echo '<tr><td colspan="6">' . __('No products found. Please create some products first.', 'ams') . '</td></tr>';
                } else {
                    foreach ($products as $product_post) {
                        $product = wc_get_product($product_post->ID);
                        $is_selected = in_array($product_post->ID, (array)$selected_products);
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       name="ams_membership_products[]" 
                                       value="<?php echo $product_post->ID; ?>" 
                                       <?php checked($is_selected); ?>>
                            </td>
                            <td><strong><?php echo esc_html($product_post->post_title); ?></strong></td>
                            <td><?php echo $product_post->ID; ?></td>
                            <td><?php echo esc_html($product->get_type()); ?></td>
                            <td><?php echo $product->get_price_html(); ?></td>
                            <td>
                                <?php
                                $status = get_post_status($product_post->ID);
                                $status_obj = get_post_status_object($status);
                                echo '<span class="status-' . esc_attr($status) . '">' . esc_html($status_obj->label) . '</span>';
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
        
        <?php submit_button(__('Save Product Selection', 'ams')); ?>
    </form>
    
    <div class="ams-product-tips" style="margin-top: 30px; background: #fff; padding: 20px; border-left: 4px solid #0073aa;">
        <h3><?php _e('Tips', 'ams'); ?></h3>
        <ul>
            <li><?php _e('Select all products that represent membership purchases.', 'ams'); ?></li>
            <li><?php _e('When a customer completes an order with any of these products, a membership ID will be automatically generated.', 'ams'); ?></li>
            <li><?php _e('If you\'re using WooCommerce Memberships, the membership ID will be linked to the membership plan.', 'ams'); ?></li>
            <li><?php _e('For digital memberships, consider setting products as "Virtual" to enable automatic order completion.', 'ams'); ?></li>
        </ul>
    </div>
</div>

<style>
.status-publish { color: green; }
.status-draft { color: orange; }
.status-pending { color: blue; }
</style>
