<?php

<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );


// Google Tag Manager Script im <head>
function childtheme_add_gtm_head() {
    ?>
    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-PGGXHFNG');
    </script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action('wp_head', 'childtheme_add_gtm_head');


// Google Tag Manager noscript direkt nach <body>
function childtheme_add_gtm_body() {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PGGXHFNG"
                      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action('wp_body_open', 'childtheme_add_gtm_body');


// Webhook direkt ausführen
/*add_action('fluentform_submission_inserted', function ($entryId, $formData, $form) {

    if (!class_exists('ActionScheduler')) {
        return;
    }

    $store = ActionScheduler::store();

    // Hole alle geplanten Tasks für fluentform_do_scheduled_tasks
    $scheduledActions = $store->query_actions([
        'hook' => 'fluentform_do_scheduled_tasks',
        'status' => ActionScheduler_Store::STATUS_PENDING,
        'per_page' => 5,
    ]);

    foreach ($scheduledActions as $action_id) {
        // Direkt ausführen
        ActionScheduler::execute_action($action_id);
    }

	error_log('🟣 Webhook aus Formular wird sofort ausgeführt');
}, 10, 3);*/


add_action('fluentform_submission_inserted', function ($entryId, $formData, $form) {
    $webhookUrl = 'https://my.offertenschweiz.ch/form/webhook';

    if (empty($_SESSION['uuid'])) {
        $uuid = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $_SESSION['uuid'] = $uuid;
    }

    $formName = $form->title;
    $formData['form_name'] = $formName;
    $formData['verified_method'] = $formData['verified_method'] ?? null;
    $formData['uuid'] = $formData['uuid'] ?? $_SESSION['uuid'] ?? bin2hex(random_bytes(8));

    // WPML Sprache hinzufügen
    if (function_exists('icl_get_current_language')) {
        $formData['lang'] = icl_get_current_language();
    } else {
        $formData['lang'] = get_locale();
    }

    $response = wp_remote_post($webhookUrl, [
        'method'    => 'POST',
        'headers'   => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'body'      => $formData,
        'timeout'   => 15,
    ]);

    if (is_wp_error($response)) {
        error_log('FluentForm Webhook Fehler: ' . $response->get_error_message());
    } else {
        error_log('FluentForm Webhook OK: ' . wp_remote_retrieve_body($response));
    }
}, 10, 3);






/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        [
            'hello-elementor-theme-style',
        ],
        HELLO_ELEMENTOR_CHILD_VERSION
    );

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );


add_action('wa_ff_uuid_injection', function($form) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['uuid'])) {
        $uuid = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $_SESSION['uuid'] = $uuid;
    }

    echo '<input type="hidden" name="uuid_value" id="uuid_value" data-name="uuid_value" value="' . esc_attr($_SESSION['uuid']) . '">';
}, 10, 1);

add_action('wp_footer', function () {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Warte bis alles von Elementor geladen ist
            const popupCheck = setInterval(function () {
                if (typeof elementorProFrontend !== 'undefined' &&
                    elementorProFrontend.modules &&
                    elementorProFrontend.modules.popup) {

                    clearInterval(popupCheck);

                    const hash = window.location.hash;
                    const match = hash.match(/popup=(\d+)/);
                    if (match) {
                        const popupId = parseInt(match[1]);
                        elementorProFrontend.modules.popup.showPopup({ id: popupId });
                    }
                }
            }, 200); // prüfe alle 200ms
        });
    </script>
    <?php
});


// === Automatische UTM & Referrer Übergabe an Fluent Forms ===
add_action('wp_footer', function () {
    ?>
    <script>
        (function() {
            const utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
            const urlParams = new URLSearchParams(window.location.search);

            // 1. UTM-Parameter aus URL in localStorage speichern
            utmParams.forEach(param => {
                if (urlParams.has(param)) {
                    localStorage.setItem(param, urlParams.get(param));
                }
            });

            // 2. Referer speichern, falls es keiner der eigenen Seiten ist
            if (document.referrer && !document.referrer.includes(location.hostname)) {
                localStorage.setItem('referrer', document.referrer);
            }

            // 3. Beim Laden der Seite in vorhandene Hidden Fields eintragen
            document.addEventListener("DOMContentLoaded", function () {
                utmParams.concat(['referrer']).forEach(param => {
                    const field = document.querySelector(`[name="${param}"]`);
                    if (field && !field.value) {
                        field.value = localStorage.getItem(param) || '';
                    }
                });
            });
        })();
    </script>
    <?php
});



add_action('init', function () {
    if (!current_user_can('manage_options')) return;

    // Nur einmal ausführen
    if (get_option('fluentform_utm_fields_migrated_v2')) return;

    global $wpdb;
    $table = "{$wpdb->prefix}fluentform_forms";
    $forms = $wpdb->get_results("SELECT id, form_fields FROM $table");

    $utm_fields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer'];
    $updated_count = 0;

    foreach ($forms as $form) {
        $form_id = $form->id;
        $fields_data = json_decode($form->form_fields, true);

        if (!isset($fields_data['fields']) || !is_array($fields_data['fields'])) {
            continue;
        }

        // Namen aller vorhandenen Felder sammeln
        $existing_names = array_column(array_column($fields_data['fields'], 'attributes'), 'name');
        $added_fields = [];

        foreach ($utm_fields as $utm) {
            if (!in_array($utm, $existing_names)) {
                $added_fields[] = [
                    "element" => "input_hidden",
                    "attributes" => [
                        "type" => "hidden",
                        "name" => $utm,
                        "value" => "",
                        "class" => "",
                        "placeholder" => ""
                    ],
                    "settings" => [
                        "label" => $utm,
                        "name" => $utm,
                        "container_class" => "",
                        "conditional_logics_status" => "no",
                        "visibility" => "everyone"
                    ],
                    "editor_options" => [
                        "id" => "",
                        "field_container_class" => "",
                        "label_placement" => "",
                        "help_message" => "",
                        "admin_field_label" => $utm
                    ]
                ];
            }
        }

        // Nur aktualisieren, wenn neue Felder ergänzt werden
        if (!empty($added_fields)) {
            $form_fields = $fields_data['fields'];
            $form_fields = array_merge($form_fields, $added_fields);
            $fields_data['fields'] = $form_fields;

            $wpdb->update(
                $table,
                ['form_fields' => wp_json_encode($fields_data)],
                ['id' => $form_id]
            );
            $updated_count++;
        }
    }

    update_option('fluentform_utm_fields_migrated_v2', 1); // Option setzen, damit es nicht nochmal läuft

    // Hinweis im Admin-Bereich
    add_action('admin_notices', function () use ($updated_count) {
        echo '<div class="notice notice-success is-dismissible">';
        echo "<p><strong>FluentForms aktualisiert:</strong> {$updated_count} Formulare wurden mit UTM-Feldern ergänzt.</p>";
        echo '</div>';
    });
});

/* Formular abbrüche speichern */
add_action('wp_footer', function () {
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll("form[id^='fluentform_']").forEach(function (form) {
                let formChanged = false;
                const formId = form.id.replace('fluentform_', '');

                form.addEventListener("input", () => {
                    formChanged = true;
                });

                form.addEventListener("submit", () => {
                    formChanged = false;
                });

                window.addEventListener("beforeunload", function () {
                    if (formChanged) {
                        const payload = {
                            form_id: formId,
                            url: window.location.href,
                            timestamp: Date.now()
                        };

                        // REST API Call (abgebrochene Formulare)
                        navigator.sendBeacon("<?php echo esc_url(rest_url('waoffertenschweizapi/v1/track-exit')); ?>", JSON.stringify(payload));

                        // Google Tag Manager
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            event: 'form_abandon',
                            form_id: formId
                        });
                    }
                });
            });
        });
    </script>
    <?php
});

add_action('rest_api_init', function () {
    register_rest_route('waoffertenschweizapi/v1', '/track-exit', [
        'methods' => 'POST',
        'callback' => 'waoffertenschweizapi_track_exit',
        'permission_callback' => '__return_true', // Kein Auth nötig
    ]);
});

function waoffertenschweizapi_track_exit($request) {
    $params = $request->get_json_params();

    // Validierung
    if (!isset($params['form_id'])) {
        return new WP_REST_Response(['error' => 'Missing form_id'], 400);
    }

    $form_id = sanitize_text_field($params['form_id']);
    $url = esc_url_raw($params['url'] ?? '');
    $timestamp = intval($params['timestamp'] ?? time());

    // === Variante 1: E-Mail senden ===
    $message = "Formular #$form_id wurde abgebrochen.\nURL: $url\nZeit: " . date('Y-m-d H:i:s', $timestamp);
    wp_mail('anfrage@offertenschweiz.ch', "Formular-Abbruch erkannt", $message);
    wp_mail('info@webaufbau.ch', "Formular-Abbruch erkannt", $message);

    // === Variante 2: In DB schreiben (optionale Tabelle nötig) ===
    global $wpdb;
    $wpdb->insert("{$wpdb->prefix}form_abandons", [
        'form_id' => $form_id,
        'url' => $url,
        'timestamp' => current_time('mysql')
    ]);

    return new WP_REST_Response(['success' => true]);
}


function offerten_language_switcher_dropdown() {
    $languages = apply_filters('wpml_active_languages', null);
    if (empty($languages)) return '';

    $short_labels = [
        'de' => 'DE',
        'fr' => 'FR',
        'it' => 'IT',
        'en' => 'EN',
    ];

    $output = '<select onchange="location.href=this.value;" id="language-switcher-dropdown" style="padding: 4px 6px; font-size: 14px; height: 30px; line-height: 1; border-radius: 4px; min-width: 60px;">';

    foreach ($languages as $lang) {
        $code = $lang['language_code'];
        $label = $short_labels[$code] ?? strtoupper($code);
        $selected = ($lang['active'] == '1') ? ' selected' : '';

        $output .= '<option value="' . esc_url($lang['url']) . '"' . $selected . '>' . esc_html($label) . '</option>';
    }

    $output .= '</select>';

    return $output;
}
add_shortcode('lang_dropdown', 'offerten_language_switcher_dropdown');


