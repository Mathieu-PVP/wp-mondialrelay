<?php
/**
 * Plugin Name: Mondial Relay pour WooCommerce
 * Description: Ajoutez Mondial Relay dans vos modes d'expéditions WooCommerce
 * Version: 1.0.0
 * Author: Alibee by PVP
 */

if (!defined('ABSPATH')) {
    exit;
}

include_once dirname(__FILE__) . '/includes/class-mondial-relay.php';

function mrw_init() {
    new Mondial_Relay_WooCommerce();
}

add_action('plugins_loaded', 'mrw_init');

function mrw_add_admin_menu() {
    $icon_url = plugin_dir_url(__FILE__) . 'assets/medias/icons/mondial_relay_icon.svg';

    add_menu_page(
        'Mondial Relay',
        'Mondial Relay',
        'manage_options',
        'mondial_relay',
        'mrw_render_admin_page',
        $icon_url
    );

    add_submenu_page(
        'mondial_relay',
        'Configuration',
        'Configuration',
        'manage_options',
        'mondial_relay_config',
        'mrw_render_config_page'
    );

    add_action('admin_enqueue_scripts', 'mrw_admin_menu_styles');
}

add_action('admin_menu', 'mrw_add_admin_menu');

function mrw_render_admin_page() {
    ?>
    <div class="mr_container">
        <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/medias/icons/mondial_relay_logo.svg'; ?>" width="400px">
        <h2 class="mr_title">Le plugin (non-officiel) pour WordPress</h2>
        <p>Ce plugin a été développé par Alibee by PVP.<br><i>N'oubliez pas de configurer votre code enseigne dans l'onglet -></i> <a href="/wp-admin/admin.php?page=mondial_relay_config">configuration</a>
        <br>
        <strong>Attention ! Ce plugin ne génère pas les étiquettes et ne communique pas avec Mondial Relay, vous devez gérer l'expédition et la génération d'étiquettes directement sur le site de Mondial Relay avec les informations fournies sur la commande.</strong>
        </p>
        <a class="btn btn-primary" href="https://www.mondialrelay.fr/" target="_blank">Aller sur mondialrelay.fr</a>
    </div>
    <script>
        const $ = jQuery;

        $('#wpbody-content div').each(function() {
            if ($(this).attr('class') && Array.from($(this).attr('class').split(' ')).some(className => className.includes('notice'))) {
                $(this).css('display', 'none');
            }
        });
    </script>
    <?php
}

function mrw_render_config_page() {
    $new_parameters = false;

    if (isset($_POST['mrw_code_enseigne'])) {
        update_option('mrw_code_enseigne', sanitize_text_field($_POST['mrw_code_enseigne']));
        $new_parameters = true;
    }

    if (isset($_POST['mrw_nbr_resultats'])) {
        update_option('mrw_nbr_resultats', sanitize_text_field($_POST['mrw_nbr_resultats']));
        $new_parameters = true;
    }

    if ($new_parameters) {
        echo '<div class="mr_banner success"><p>Vos paramètres ont bien été sauvegardés.</p></div>';
    }

    $code_enseigne = get_option('mrw_code_enseigne');
    $nbr_resultats = get_option('mrw_nbr_resultats');

    ?>
    <div class="mr_container">
        <h2 class="mr_title">Configuration de votre profil</h2>
        <form class="mr_form" method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="mrw_code_enseigne">Code enseigne <span class="mr_color_red">(*)</span></label></th>
                    <td>
                        <input type="text" name="mrw_code_enseigne" id="mrw_code_enseigne" value="<?php echo esc_attr($code_enseigne); ?>" class="regular-text" required>
                        <p class="description">Renseigner votre code enseigne Mondial Relay. ("<strong>BDTEST </strong>" pour la version démo)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mrw_code_enseigne">Nombre de résultats</label></th>
                    <td>
                        <input type="number" min="0" name="mrw_nbr_resultats" id="mrw_nbr_resultats" value="<?php echo esc_attr($nbr_resultats); ?>" class="regular-text">
                        <p class="description">Renseigner le nombre de résultats par recherche. ("<strong>7</strong>" par défaut)</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer'); ?>
        </form>
    </div>
    <script>
        const $ = jQuery;

        $('#wpbody-content div').each(function() {
            if ($(this).attr('class') && Array.from($(this).attr('class').split(' ')).some(className => className.includes('notice'))) {
                $(this).css('display', 'none');
            }
        });
    </script>
    <?php
}

function mrw_admin_menu_styles() {
    wp_enqueue_style('mrw-admin-menu-style', plugin_dir_url(__FILE__) . 'assets/css/mondial-relay-admin-style.css');
}

function mrw_styles() {
    wp_enqueue_style('mrw-style', plugin_dir_url(__FILE__) . 'assets/css/mondial-relay-style.css');
}

add_action('wp_enqueue_scripts', 'mrw_styles');
?>