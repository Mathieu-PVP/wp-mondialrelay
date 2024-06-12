<?php

if (!class_exists('Mondial_Relay_WooCommerce')) {
    class Mondial_Relay_WooCommerce {
        public function __construct() {
            add_action('woocommerce_shipping_methods', array($this, 'add_mondial_relay_method'));
            add_action('woocommerce_checkout_before_order_review', array($this, 'display_mondial_relay_widget'));
            add_action('woocommerce_checkout_update_order_meta', array($this, 'save_mondial_relay_point'));
            add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'display_mondial_relay_point_in_admin'));
            add_action('woocommerce_checkout_process', array($this, 'update_shipping_fields_with_mondial_relay'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        public function add_mondial_relay_method($methods) {
            $methods['mondial_relay'] = 'WC_Shipping_Mondial_Relay';
            return $methods;
        }

        public function display_mondial_relay_widget() {
            ?>
            <p id="Mondial_Relay_Address"></p>
            
            <div id="Mondial_Relay_Window">
                <div id="Mondial_Relay_Window_Content">
                    <div id="Mondial_Relay_Window_Header">
                        <h2>Choisissez votre <span class="Mondial_Relay_Color_Text">Point Relay®</span></h2>
                    </div>
                    <div id="Mondial_Relay_Zone_Widget"></div>
                    <div id="Mondial_Relay_Window_Footer">
                        <a id="Mondial_Relay_Submit_Button" class="Mondial_Relay_Button">Valider</a>
                    </div>
                    <input type="hidden" name="mondial_relay_point" id="Mondial_Relay_Target_Widget" required />
                    <input type="hidden" name="mondial_relay_point_display" id="Mondial_Relay_TargetDisplay_Widget" required />
                    <input type="hidden" name="mondial_relay_point_info" id="Mondial_Relay_TargetDisplayInfoPR_Widget" required />
                </div>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    let isMrEnabled = false;
                    let codeEnseigne = '<?php echo get_option('mrw_enseigne'); ?>' || 'BDTEST';
                    let nbrResultats = '<?php echo get_option('mrw_nbr_resultats'); ?>' || '7';

                    function initMondialRelayWidget() {
                        $('#Mondial_Relay_Zone_Widget').MR_ParcelShopPicker({
                            Target: '#Mondial_Relay_Target_Widget',
                            TargetDisplay: '#Mondial_Relay_TargetDisplay_Widget',
                            TargetDisplayInfoPR: '#Mondial_Relay_TargetDisplayInfoPR_Widget',
                            Brand: codeEnseigne,
                            Country: 'FR',
                            AllowedCountries: 'FR',
                            NbResults: nbrResultats,
                            EnableGmap: true,
                            Responsive: true,
                            DisplayMapInfo: true,
                            ShowResultsOnMap: true,
                            DisplaySideBar: true,
                            OnParcelShopSelected: function(data) {
                                $('#Mondial_Relay_Target_Widget').val(data.ID);
                                $('#Mondial_Relay_TargetDisplay_Widget').val(data.Nom);
                                $('#Mondial_Relay_TargetDisplayInfoPR_Widget').val(data.Adresse1 + (data.Adresse2 ? ' ' + data.Adresse2 : '') + ', ' + data.CP + ' ' + data.Ville + ', ' + data.Pays);
                                $('#Mondial_Relay_Address').html('<strong>Livraison à </strong><br>' + data.Nom + '<br>' + data.Adresse1 + (data.Adresse2 ? ' ' + data.Adresse2 : '') + '<br>' + data.CP + ' ' + data.Ville + ', ' + data.Pays + '<br><a id="Mondial_Relay_Choose_Button" href="#" alt="Choisir Point Relay®">Choisir Point Relay®</a>');
                                $('#Mondial_Relay_Choose_Button').on('click', function() {
                                    $('#Mondial_Relay_Window').show();
                                    $('body').addClass('disable-scroll');
                                });
                            }
                        });
                    }

                    function resetFields() {
                        $('#Mondial_Relay_Target_Widget').val('');
                        $('#Mondial_Relay_TargetDisplay_Widget').val('');
                        $('#Mondial_Relay_TargetDisplayInfoPR_Widget').val('');
                        $('#Mondial_Relay_Address').html('');
                    }

                    $('form.checkout').on('change', 'input[name^="shipping_method"]', toggleWidgetVisibility);

                    $('#Mondial_Relay_Submit_Button').on('click', function() {
                        if (isMrEnabled && (!$('#Mondial_Relay_Target_Widget').val() || !$('#Mondial_Relay_TargetDisplay_Widget').val() || !$('#Mondial_Relay_TargetDisplayInfoPR_Widget').val())) {
                            alert('Veuillez renseigner une adresse Mondial Relay !');
                            return;
                        }
                        $('#Mondial_Relay_Window').hide();
                        $('body').removeClass('disable-scroll');
                    });

                    function toggleWidgetVisibility() {
                        if ($('input[name="shipping_method[0]"]:checked').val() === 'mondial_relay') {
                            isMrEnabled = true;
                            $('#Mondial_Relay_Window').show();
                            $('body').addClass('disable-scroll');
                        } else {
                            isMrEnabled = false;
                            resetFields();
                            $('#Mondial_Relay_Window').hide();
                            $('body').removeClass('disable-scroll');
                        }
                    }

                    toggleWidgetVisibility();
                    initMondialRelayWidget();
                });
            </script>
            <?php
        }

        public function save_mondial_relay_point($order_id) {
            if (!empty($_POST['mondial_relay_point']) && !empty($_POST['mondial_relay_point_display']) && !empty($_POST['mondial_relay_point_info'])) {
                update_post_meta($order_id, '_mondial_relay_point', sanitize_text_field($_POST['mondial_relay_point']));
                update_post_meta($order_id, '_mondial_relay_point_display', sanitize_text_field($_POST['mondial_relay_point_display']));
                update_post_meta($order_id, '_mondial_relay_point_info', sanitize_text_field($_POST['mondial_relay_point_info']));

                list($address, $second_part) = explode(',', sanitize_text_field($_POST['mondial_relay_point_info']));
                list($postcode, $city_country) = explode(' ', trim($second_part), 2);
                list($city, $country) = explode(',', trim($city_country), 2);

                $order = wc_get_order($order_id);
                $order->set_shipping_company(sanitize_text_field($_POST['mondial_relay_point_display']));
                $order->set_shipping_address_1($address);
                $order->set_shipping_city($city);
                $order->set_shipping_postcode($postcode);
                $order->set_shipping_country($country);
                $order->save();
            }
        }

        public function display_mondial_relay_point_in_admin($order) {
            $point_id = get_post_meta($order->get_id(), '_mondial_relay_point', true);
            $point_name = get_post_meta($order->get_id(), '_mondial_relay_point_display', true);
            $point_info = get_post_meta($order->get_id(), '_mondial_relay_point_info', true);

            if ($point_name && $point_info) {
                echo '<p><strong>' . __('Point Relais Mondial Relay', 'woocommerce') . '</strong><br>ID du Point Relay : ' . esc_html($point_id) . '</p>';
            }
        }

        public function update_shipping_fields_with_mondial_relay() {
            if (!empty($_POST['mondial_relay_point']) && !empty($_POST['mondial_relay_point_display']) && !empty($_POST['mondial_relay_point_info'])) {
                list($address, $second_part) = explode(',', sanitize_text_field($_POST['mondial_relay_point_info']));
                list($postcode, $city_country) = explode(' ', trim($second_part), 2);
                list($city, $country) = explode(',', trim($city_country), 2);

                WC()->customer->set_shipping_address_1($address);
                WC()->customer->set_shipping_postcode($postcode);
                WC()->customer->set_shipping_city($city);
                WC()->customer->set_shipping_country($country);
            }
        }

        public function enqueue_scripts() {
            wp_enqueue_style('leaflet-css', '//unpkg.com/leaflet/dist/leaflet.css');
            wp_enqueue_script('leaflet-js', '//unpkg.com/leaflet/dist/leaflet.js', array('jquery'), null, true);
            wp_enqueue_script('mondial-relay-widget', 'https://widget.mondialrelay.com/parcelshop-picker/jquery.plugin.mondialrelay.parcelshoppicker.min.js', array('jquery'), null, true);
        }
    }
}

if (!class_exists('WC_Shipping_Mondial_Relay')) {
    class WC_Shipping_Mondial_Relay extends WC_Shipping_Method {
        public function __construct($instance_id = 0) {
            $this->id = 'mondial_relay';
            $this->instance_id = absint($instance_id);
            $this->method_title = __('Mondial Relay', 'woocommerce');
            $this->method_description = __('Livraison via Mondial Relay', 'woocommerce');
            $this->supports = array('shipping-zones', 'instance-settings');

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();
            $this->title = 'Mondial Relay';
            $this->cost = $this->get_option('cost', '0');

            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        function init_form_fields() {
            $this->instance_form_fields = array(
                'table' => array(
                    'title'       => __('Tarifs par poids', 'woocommerce'),
                    'type'        => 'title',
                    'description' => __('Définissez les tarifs de livraison en fonction du poids.', 'woocommerce'),
                ),
                'weight_0_5' => array(
                    'title'       => __('0 - 0.5 kg', 'woocommerce'),
                    'type'        => 'number',
                    'description' => __('Tarif pour les colis pesant entre 0 et 0.5 kg', 'woocommerce'),
                    'default'     => '3.5',
                ),
                'weight_1' => array(
                    'title'       => __('0.5 - 1 kg', 'woocommerce'),
                    'type'        => 'number',
                    'description' => __('Tarif pour les colis pesant entre 0.5 et 1 kg', 'woocommerce'),
                    'default'     => '4.5',
                ),
                'weight_2' => array(
                    'title'       => __('1 - 2 kg', 'woocommerce'),
                    'type'        => 'number',
                    'description' => __('Tarif pour les colis pesant entre 1 et 2 kg', 'woocommerce'),
                    'default'     => '5.5',
                ),
                'weight_5' => array(
                    'title'       => __('2 - 5 kg', 'woocommerce'),
                    'type'        => 'number',
                    'description' => __('Tarif pour les colis pesant entre 2 et 5 kg', 'woocommerce'),
                    'default'     => '6.5',
                ),
                'weight_10' => array(
                    'title'       => __('5 - 10 kg', 'woocommerce'),
                    'type'        => 'number',
                    'description' => __('Tarif pour les colis pesant entre 5 et 10 kg', 'woocommerce'),
                    'default'     => '7.5',
                ),
            );
        }

        public function calculate_shipping($package = array()) {
            $weight = 0;
            foreach ($package['contents'] as $item_id => $values) {
                $_product = $values['data'];
                $weight += floatval($_product->get_weight()) * intval($values['quantity']);
            }

            $shipping_rates = array(
                '0.5' => floatval($this->get_option('weight_0_5')),
                '1'   => floatval($this->get_option('weight_1')),
                '2'   => floatval($this->get_option('weight_2')),
                '5'   => floatval($this->get_option('weight_5')),
                '10'  => floatval($this->get_option('weight_10')),
            );

            $cost = 0;
            foreach ($shipping_rates as $max_weight => $rate) {
                if ($weight <= floatval($max_weight)) {
                    $cost = $rate;
                    break;
                }
            }

            $this->add_rate(array(
                'id'    => $this->id,
                'label' => $this->title,
                'cost'  => $cost,
            ));
        }
    }
}

function add_mondial_relay_shipping_method($methods) {
    $methods['mondial_relay'] = 'WC_Shipping_Mondial_Relay';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_mondial_relay_shipping_method');