<?php
/*
 * Settings Page for "Ank Prism For WP" Plugin
 * Needs main class object also
 */

/* no direct access*/
if (!defined('ABSPATH')) exit;

if (!class_exists('Ank_Prism_For_WP')) {
    wp_die(__('This file can not be run alone. This file is the part of <b>Ank Prism For WP</b> plugin.'));
}


class Ank_Prism_For_WP_Admin
{


    function __construct()
    {
        /*save setting upon plugin activation*/
        register_activation_hook(plugin_basename(APFW_BASE_FILE), array($this, 'apfw_init_settings'));

        /* Add settings link under admin settings */
        add_action('admin_menu', array($this, 'apfw_settings_menu'));
        /* Add settings link to plugin list page */
        add_filter('plugin_action_links_' . plugin_basename(APFW_BASE_FILE), array($this, 'apfw_plugin_actions_links'), 10, 2);

        //add a button to mce editor
        //source: https://www.gavick.com/blog/wordpress-tinymce-custom-buttons/
        add_action('admin_head', array($this, 'apfw_add_editor_button'));
        add_action('admin_print_scripts', array($this, 'apfw_admin_inline_script'), 10);
        add_action('admin_print_styles', array($this, 'apfw_admin_inline_style'), 99);

    }   //end constructor


    function apfw_init_settings()
    {
        /* if settings exists then return early */
        if (get_option(APFW_OPTION_NAME)) {
            return;
        }

        //default settings
        $new_options = array(
            'plugin_ver' => APFW_PLUGIN_VERSION,
            'theme' => 1,
            'lang' => array(1, 2, 3),
            'plugin' => array(4),
            'onlyOnPost' => 0,
            'noAssistant' => 0,
        );
        add_option(APFW_OPTION_NAME, $new_options);

    }

    function apfw_settings_menu()
    {
        $page_hook_suffix = add_submenu_page('options-general.php', '&#9650; Ank Prism For WP', 'Ank Prism For WP', 'manage_options', APFW_PLUGIN_SLUG, array($this, 'APFW_Option_Page'));

        /* add help drop down menu on option page  wp v3.3+ */
        add_action("load-$page_hook_suffix", array($this, 'apfw_help_menu_tab'));

    }

    private function apfw_settings_page_url()
    {
        return add_query_arg('page', APFW_PLUGIN_SLUG, 'options-general.php');
    }


    function apfw_plugin_actions_links($links)
    {

        if (current_user_can('manage_options')) {
            array_unshift(
                $links,
                sprintf('<a href="%s">%s</a>', esc_attr($this->apfw_settings_page_url()), __('Settings'))
            );
        }

        return $links;
    }

    function  APFW_Option_Page()
    {

        $options = get_option('ank_prism_for_wp');
        global $Ank_Prism_For_WP_Obj;

        if (isset($_POST['save_apfw_form'])) {
            /* WP inbuilt form security check */
            check_admin_referer('apfw_form', '_wpnonce-apfw_form');

            $options['plugin_ver'] = APFW_PLUGIN_VERSION;

            if (isset($_POST['ptheme'])) {
                $options['theme'] = intval($_POST['ptheme']);
            } else {
                $options['theme'] = 1;
            }
            if (isset($_POST['plang'])) {
                $options['lang'] = $_POST['plang'];
            } else {
                $options['lang'] = array();
            }
            if (isset($_POST['pplugin'])) {
                $options['plugin'] = $_POST['pplugin'];
            } else {
                $options['plugin'] = array();
            }

            $options['onlyOnPost'] = (isset($_POST['onlyOnPost'])) ? 1 : 0;
            $options['noAssistant'] = (isset($_POST['noAssistant'])) ? 1 : 0;
            //save back to database
            update_option('ank_prism_for_wp', $options);

            echo "<div class='updated notice is-dismissible'><p>Your settings has been <b>saved</b>.</p></div>";
            //delete js and css files
            $this->delete_file('prism-css.css');
            $this->delete_file('prism-js.js');

            /* Detect if cache is enabled and warn user to flush cache */
            if (WP_CACHE) {
                echo "<div class='notice-warning notice'>It seems that a caching/performance plugin is active on this site. Please manually <b>invalidate/flush</b> that plugin <b>cache</b> to reflect the settings you saved here.</div>";
            }
        }//end if isset post

        ?>
            <!--option page start-->
            <style type="text/css">
                div.meta-col { width: 32.5%; vertical-align: top; display: inline-block }
                @media screen and (max-width: 782px) { div.meta-col { width: 99%; display: block } }
                .hndle { text-align: center; cursor: default !important; background: #fdfdfd; border-bottom-color: #DFDFDF !important; }
            </style>
            <div class="wrap">
                <h2>Ank Prism For WP <small>(v<?php echo APFW_PLUGIN_VERSION ?>)</small></h2>
                <form action="" method="post">
                    <?php wp_nonce_field('apfw_form', '_wpnonce-apfw_form'); ?>
                    <p style="text-align: center">
                        <button class="button button-primary" type="submit" name="save_apfw_form" value="Save »"><i class="dashicons-before dashicons-upload"> </i>Save Settings </button>
                    </p>
                    <div id="poststuff">
                        <div class="postbox meta-col">
                            <h3 class="hndle"><i class="dashicons-before dashicons-admin-appearance" style="color: #02af00"> </i><span>Select a Theme</span></h3>
                            <div class="inside">
                                <?php
                                $theme_list = $Ank_Prism_For_WP_Obj->apfw_theme_list();
                                for ($i = 1; $i <= count($theme_list); $i++) {
                                    echo '<input ';
                                    echo ($options['theme'] == $i) ? ' checked ' : '';
                                    echo 'name="ptheme" value="' . $i . '" id="ptheme-' . $i . '" type="radio">';
                                    echo '<label for="ptheme-' . $i . '">' . $theme_list[$i]['name'] . "</label>";
                                    echo '&emsp;<a target="_blank" href="' . $theme_list[$i]['url'] . '">Preview</a><br>';
                                }
                                ?>
                            </div>
                        </div>
                        <!--end post box-->
                        <div class="postbox meta-col">
                            <h3 class="hndle"><i class="dashicons-before dashicons-format-aside" style="color: #5b27af"> </i><span>Select Languages</span></h3>

                            <div class="inside" id="plang-list">
                                <?php
                                $lang_list = $Ank_Prism_For_WP_Obj->apfw_lang_list();
                                for ($i = 1; $i <= count($lang_list); $i++) {
                                    echo '<input ';
                                    echo (in_array($i, $options['lang'])) ? ' checked ' : '';
                                    echo ($lang_list[$i]['require'] !== '') ? ' data-require="' . $lang_list[$i]['require'] . '" ' : '';
                                    echo ' name="plang[]" value="' . $i . '" id="plang-' . $lang_list[$i]['id'] . '" type="checkbox">';
                                    echo '<label for="plang-' . $lang_list[$i]['id'] . '">' . $lang_list[$i]['name'] . "</label>";
                                    echo ($lang_list[$i]['require'] !== '') ? '&emsp;<i>(Requires: ' . $lang_list[$i]['require'] . ')</i>' : '';
                                    echo '<br>';
                                }
                                ?>
                            </div>
                        </div>
                        <!--end post box-->
                        <div class="postbox meta-col">
                            <h3 class="hndle"><i class="dashicons-before dashicons-admin-plugins" style="color: #af0013"> </i><span>Select Plugins</span></h3>

                            <div class="inside">
                                <?php
                                $plugin_list = $Ank_Prism_For_WP_Obj->apfw_plugin_list();
                                for ($i = 1; $i <= count($plugin_list); $i++) {
                                    echo '<input ';
                                    echo (in_array($i, $options['plugin'])) ? ' checked ' : '';
                                    echo ' name="pplugin[]" value="' . $i . '" id="pplugin-' . $i . '" type="checkbox">';
                                    echo '<label for="pplugin-' . $i . '">' . $plugin_list[$i]['name'] . "</label>";
                                    echo '&emsp;<a target="_blank" href="' . $plugin_list[$i]['url'] . '">View Demo</a><br>';
                                }
                                ?>
                            </div>
                        </div>
                        <!--end post box-->
                    </div>
                    <!--end post stuff-->
                    <hr>
                    <p>
                        <input name="onlyOnPost" id="p_onlyOnPost" type="checkbox" <?php echo @($options['onlyOnPost'] === 1) ? ' checked ' : '' ?>>
                        <label for="p_onlyOnPost">Enqueue Prism files (CSS+JS) only to post/single pages</label>&ensp;
                        <input name="noAssistant" id="p_noAssistant" type="checkbox" <?php echo @($options['noAssistant'] === 1) ? ' checked ' : '' ?>>
                        <label for="p_noAssistant">Don't show Assistant Button in editor</label>
                    </p>
                    <hr>
                </form>
                <!--end form-->
                Created with &hearts; by <a target="_blank" href="http://ank91.github.io/"> Ankur Kumar</a> |
                View <a target="_blank" href="http://www.prismjs.com">Original Site </a>for Demos and Instructions |
                Fork on <a target="_blank" href="https://github.com/ank91/ank-prism-for-wp">GitHub</a>
                <!--end dev info-->
                 <script type="text/javascript">
                    jQuery(function ($) {
                        $.fn.hasAttr = function (name) {
                            return this.attr(name) !== undefined;
                        };
                        var plang = $("#plang-list");
                        var plist = plang.find('input:checkbox');
                        plist.change(function () {
                            if (!$(this).is(":checked")) {
                                var tid = $(this).attr('id');
                                $(plist).each(function () {
                                    if ($(this).hasAttr('data-require')) {
                                        if ('plang-' + $(this).attr('data-require') == tid) {
                                            $(this).prop('checked', false).trigger('change');
                                        }
                                    }
                                });
                            }
                            if ($(this).hasAttr('data-require') && $(this).is(":checked")) {
                                $("#plang-list").find("#plang-" + $(this).attr('data-require')).prop('checked', true).trigger('change');
                            }
                        });
                    });
                </script>
                <?php if (isset($_GET['debug']) || WP_DEBUG == true) {
                    echo '<hr><p><h5>Showing Debugging Info:</h5>';
                    var_dump($options);
                    echo '</p><hr>';
                }?>
            </div> <!--end wrap-->
            <!--options page ends here -->
        <?php
        }//end function apfw_option_page

    private function delete_file($file)
    {
        $file = __DIR__ . '/' . $file;
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    function apfw_help_menu_tab()
    {
            /*get current screen obj*/
            $curr_screen = get_current_screen();

            $curr_screen->add_help_tab(
                array(
                    'id' => 'apfw-overview',
                    'title' => 'Overview',
                    'content' => '<p><strong>Thanks for using "Ank Prism For WP"</strong><br>' .
                        'This plugin allows you to control and use <i>Prism Syntax Highlighter</i> on your website. Just configure options below and ' .
                        'save your settings.Then use something like this in your posts.' .
                        '<code>&lt;pre&gt;&lt;code class="language-css"&gt;p { color: red }&lt;/code&gt;&lt;/pre&gt;</code>' .
                        '<br>You can also use in editor <i>Prism Assistant Button</i>.</p>'

                )
            );

            $curr_screen->add_help_tab(
                array(
                    'id' => 'apfw-troubleshoot',
                    'title' => 'Troubleshoot',
                    'content' => '<p><strong>Things to remember</strong>' .
                        '<ul>' .
                        '<li>If you are using a cache/performance plugin, you need to flush/delete your site cache after  saving settings here.</li>' .
                        '<li>Only selected languages are available at this time. Stay tuned for more.</li>' .
                        '<li>Please make sure that plugin\'s folder is writable, because we create new files each time you save settings here.</li>' .
                        '</ul></p>'

                )
            );
            $curr_screen->add_help_tab(
                array(
                    'id' => 'apfw-more-info',
                    'title' => 'More',
                    'content' => '<p><strong>Need more information ?</strong><br>' .
                        'A brief FAQ is available <a href="https://wordpress.org/ank-prism-for-wp/faq/" target="_blank">here</a><br>' .
                        'You can also check out instructions from original developer <a href="http://www.prismjs.com" target="_blank">here</a> .<br>' .
                        'Support is only available on WordPress Forums, click <a href="https://wordpress.org/support/plugin/ank-prism-for-wp" target="_blank">here</a> to ask anything about this plugin.<br>' .
                        'You can also report a bug at plugin&apos;s GitHub <a href="https://github.com/ank91/ank-prism-for-wp" target="_blank">page</a>.' .
                        ' I will try to reply as soon as possible. </p>'

                )
            );

            /*help sidebar links */
            $curr_screen->set_help_sidebar(
                '<p><strong>Quick Links</strong></p>' .
                '<p><a href="https://wordpress.org/ank-prism-for-wp/faq/" target="_blank">Plugin FAQ</a></p>' .
                '<p><a href="https://github.com/ank91/ank-prism-for-wp" target="_blank">Plugin Home</a></p>'
            );
        }

    public function apfw_add_editor_button()
    {
        if ($this->apfw_check_if_btn_can_be() == true) {
            add_filter("mce_external_plugins", array($this, "afpw_add_tinymce_plugin"));
            add_filter('mce_buttons', array($this, 'afpw_register_tinymce_button'));
        }

    }

    function afpw_register_tinymce_button($buttons)
    {
        array_push($buttons, "afpw_assist_button");
        return $buttons;
    }

    function afpw_add_tinymce_plugin($plugin_array)
    {
        $plugin_array['afpw_assist_button'] = plugins_url('/apfw-editor-plugin.min.js', APFW_BASE_FILE);
        return $plugin_array;
    }

    function apfw_admin_inline_script($hook)
    {
        if ($this->apfw_check_if_btn_can_be() == true) {
            global $Ank_Prism_For_WP_Obj;
            $lang_list = $Ank_Prism_For_WP_Obj->apfw_lang_list();
            echo "<script type='text/javascript'> /* <![CDATA[ */";
            echo 'var apfw_lang=[';
            for ($i = 1; $i <= count($lang_list); $i++) {
                if ($lang_list[$i]['in_popup'] == 1)
                    echo '{text:"' . esc_attr(ucwords($lang_list[$i]['id'])) . '", value:"' . esc_attr($lang_list[$i]['id']) . '"},';
            }
            echo "]; /* ]]> */</script>";

        }
    }

    function apfw_admin_inline_style($hook)
    {
        if ($this->apfw_check_if_btn_can_be() == true) {
            ?>
            <style type="text/css"> .mce-i-apfw-icon:before {
                    content: '\f499';
                    font: 400 20px/1 dashicons;
                    padding: 0;
                    vertical-align: top;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                } </style>
        <?php
        }
    }

    private function apfw_check_if_btn_can_be()
    {
        // check user permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return false;
        }

        //check if user don't want the
        $options = get_option('ank_prism_for_wp');
        if ($options['noAssistant'] == 1)
            return false;
        // check if WYSIWYG is enabled
        if (get_user_option('rich_editing') == 'true') {
            return true;
        }
        return false;
    }



}  //end class
