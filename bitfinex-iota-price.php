<?php
/*
Plugin Name: Bitfinex Realtime IOTA Price
Version: 1.0
Plugin URI: https://wordpress.org/plugins/realtime-iota-price/
Author: SERGIO CASIZZONE
Author URI: https://www.sergiocasizzone.it/
Description: Adds the current Bitfinex IOTA Price to your WordPress Website. The Price updates automatically. It's not required to reload the whole site. Shortcode: <code>[iota_price]</code>
Text Domain: realtime-iota-price
Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('IOTA_PRICE_TICKER')) {

    class IOTA_PRICE_TICKER {

        function __construct() {
            $this->iot_plugin_includes();
        }

        function iot_plugin_includes() {
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_action('wp_enqueue_scripts', 'iot_header_script');
            add_shortcode('iota_price', 'iot_init');
            //allows shortcode execution in the widget, excerpt and content
            add_filter('widget_text', 'do_shortcode');
            add_filter('the_excerpt', 'do_shortcode', 11);
            add_filter('the_content', 'do_shortcode', 11);
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugins_loaded_handler()
        {
            load_plugin_textdomain('clappr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
        }
    }
    $GLOBALS['bitfinex_iota_price'] = new IOTA_PRICE_TICKER();
}

function iot_header_script() {
    if (!is_admin()) {
        $plugin_url = plugins_url('', __FILE__);
        #wp_register_script('pusher-js', $plugin_url . '/js/pusher.min.js', array(), '4.2.1', false);
        #wp_enqueue_script('pusher-js');
    }
}

function iot_footer_script()
{
    echo "<!-- Bitfinex Live Ticker by SERGIO CASIZZONE -->\n";
    echo "<script type=\"text/javascript\">\n";
    echo "document.getElementById('iota_price').innerHTML = 'loading...';\n";
   	echo "var ws = new WebSocket('wss://api.bitfinex.com/ws/');\n";
	echo "ws.onopen = function() {\n";
	echo "	ws.send(JSON.stringify({\n";
	echo " 		'event': 'subscribe', 'channel': 'trades', 'pair': 'IOTUSD'	}));\n";
	echo "  };";

	echo "ws.onmessage = function(msg) {\n";
	echo "    	var response = JSON.parse(msg.data);\n";
	echo "		if (response[1] === 'te') {\n ";
	echo "			document.getElementById('iota_price').innerHTML = '$ '+response[4];\n";
	echo "		}\n";
	echo "}\n";
    echo "</script>\n";
    echo "<!-- Bitfinex Live Ticker by SERGIO CASIZZONE -->\n";
}

function iot_init($atts) {
	add_action('wp_footer', 'iot_footer_script');
    extract(shortcode_atts(array(
        'size' => '',
        'color' => ''
    ), $atts));
    $styles = '';
    if ($size || $color) {
        $styles = "style=\"font-size: $size; color: $color;\"";
    }
    $output = "<span id=\"iota_price\" $styles></span>";

    return $output;
}
?>
