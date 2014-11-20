<?php
/*
Plugin Name: Ank Prism For WP
Plugin URI: http://ank91.github.io/ank-prism-for-wp/
Description: Control Prism syntax highlighter in WordPress.
Version: 1.4
Author: Ankur Kumar
Author URI: http://ank91.github.io/
License: GPL2
*/
/*
    Copyright 2014  Ankur Kumar  (http://ank91.github.io/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
/* no direct access*/
if (!defined('ABSPATH')) exit;

/*check for duplicate class*/
if (!class_exists( 'Ank_Prism_For_WP' ) ) {

    if (!defined('APFW_PLUGIN_VERSION')) {
        define('APFW_PLUGIN_VERSION', '1.4');
    }
    if (!defined('APFW_PLUGIN_SLUG')) {
        define('APFW_PLUGIN_SLUG', 'apfw_plugin_settings');
    }
    if (!defined('APFW_MINIFY_CSS')) {
        define('APFW_MINIFY_CSS', 1);
    }


    class Ank_Prism_For_WP
    {

        function __construct()
        {
            /*
            * Add settings link to plugin list page
            */
            add_filter('plugin_action_links', array($this, 'apfw_plugin_actions_links'), 10, 2);
            /*
             * Additional links
             */
            add_filter('plugin_row_meta', array($this, 'apfw_plugin_meta_links'), 10, 2);
            /* Save settings if first time */
            if (false == get_option('ank_prism_for_wp')) {
                $this->apfw_init_settings();
            }
            //late init
            add_action('wp_enqueue_scripts',array($this,'apfw_user_style'),99);
            add_action('wp_enqueue_scripts',array($this,'apfw_user_script'),99);
            //add a button to mce editor
            //source: https://www.gavick.com/blog/wordpress-tinymce-custom-buttons/
            add_action('admin_head', array($this,'apfw_add_editor_button'));
            add_action( 'admin_print_scripts', array($this,'apfw_admin_inline_script'),10 );
            add_action( 'admin_print_styles', array($this,'apfw_admin_inline_style'),99 );

        }



        private function apfw_settings_page_url()
        {
            return add_query_arg('page', APFW_PLUGIN_SLUG, 'options-general.php');
        }


        function apfw_plugin_actions_links($links, $file)
        {
            static $plugin;
            $plugin = plugin_basename(__FILE__);
            if ($file == $plugin && current_user_can('manage_options')) {
                array_unshift(
                    $links,
                    sprintf('<a href="%s">%s</a>', esc_attr($this->apfw_settings_page_url()), __('Settings'))
                );
            }

            return $links;
        }

        function apfw_plugin_meta_links($links,$file)
        {
            /*
            * additional link on plugin list page
            */
            static $plugin;
            $plugin = plugin_basename( __FILE__ );
            if ( $file == $plugin ) {
                $links[] = '<a target="_blank" href="' . plugins_url('readme.txt',__FILE__) . '">Read Me</a>';
                $links[] = '<a target="_blank" href="http://www.prismjs.com/">Original Site</a>';
            }
            return $links;
        }

        function apfw_init_settings(){
            $new_options=array(
                'plugin_ver'=>APFW_PLUGIN_VERSION,
                'theme'=>1,
                'lang'=>array(1,2,3),
                'plugin'=>array(4),
                'onlyOnPost'=>0,
                'noAssistant'=>0,
            );
            add_option('ank_prism_for_wp', $new_options);

        }

        function apfw_theme_list()
        {    //base url for demos
            $base_url='http://prismjs.com/index.html?theme=';
            $list=array(
                1=>array('name'=>'Default','url'=>$base_url.'prism','file'=>'prism'),
                2=>array('name'=>'Coy','url'=>$base_url.'prism-coy','file'=>'prism-coy'),
                3=>array('name'=>'Dark','url'=>$base_url.'prism-dark','file'=>'prism-dark'),
                4=>array('name'=>'Okaidia','url'=>$base_url.'prism-okaidia','file'=>'prism-okaidia'),
                5=>array('name'=>'Tomorrow','url'=>$base_url.'prism-tomorrow','file'=>'prism-tomorrow'),
                6=>array('name'=>'Twilight','url'=>$base_url.'prism-twilight','file'=>'prism-twilight'),

            );
            return $list;
        }
        function apfw_plugin_list()
        {   //$base_url, lets not repeat code ,since domains are subject to change
            $base_url='http://prismjs.com/plugins/';
            //JS and related CSS file name must be same, except extension
            $list=array(
                1=>array('name'=>'Autolinker ','url'=>$base_url.'autolinker/','file'=>'prism-autolinker','need_css'=>1),
                2=>array('name'=>'File Highlight ','url'=>$base_url.'file-highlight/','file'=>'prism-file-highlight','need_css'=>0),
                3=>array('name'=>'Line Highlight','url'=>$base_url.'line-highlight/','file'=>'prism-line-highlight','need_css'=>1),
                4=>array('name'=>'Line Numbers','url'=>$base_url.'line-numbers/','file'=>'prism-line-numbers','need_css'=>1),
                5=>array('name'=>'Show Invisibles','url'=>$base_url.'show-invisibles/','file'=>'prism-show-invisibles','need_css'=>1),
                6=>array('name'=>'Show Language','url'=>$base_url.'show-language/','file'=>'prism-show-language','need_css'=>1),
                7=>array('name'=>'WebPlatform Docs','url'=>$base_url.'wpd/','file'=>'prism-wpd','need_css'=>1),
            );
            return $list;
        }
        function apfw_lang_list()
        {
           //lets keep order and requirement
            //require is the id  of some other lang
            //id will be used in tiny mce popup too
            $list=array(
                1=>array('id'=>'markup','name'=>'Markup','file'=>'prism-markup','require'=>'','in_popup'=>1),
                2=>array('id'=>'css','name'=>'CSS','file'=>'prism-css','require'=>'','in_popup'=>1),
                3=>array('id'=>'css-extras','name'=>'CSS Extras','file'=>'prism-css-extras','require'=>'css','in_popup'=>0),
                4=>array('id'=>'clike','name'=>'C-Like','file'=>'prism-clike','require'=>'','in_popup'=>1),
                5=>array('id'=>'javascript','name'=>'Java-Script','file'=>'prism-javascript','require'=>'clike','in_popup'=>1),
                6=>array('id'=>'php','name'=>'PHP','file'=>'prism-php','require'=>'clike','in_popup'=>1),
                7=>array('id'=>'php-extras','name'=>'PHP Extras','file'=>'prism-php-extras','require'=>'php','in_popup'=>0),
                8=>array('id'=>'sql','name'=>'SQL','file'=>'prism-sql','require'=>'','in_popup'=>1),
            );
            return $list;
        }

        function apfw_user_style()
        {
            if($this->apfw_check_if_enqueue()==false)
                return;
            //enqueue front end css
            if(!file_exists(__DIR__.'/prism-css.css')){
                //try to create file
                $this->apfw_write_a_file($this->apfw_decide_css(),'prism-css.css');
            }

            /* unique file version, every time the file get modified */
            $file_ver=esc_attr(filemtime(__DIR__.'/prism-css.css'));

            wp_enqueue_style('prism-theme',plugins_url('prism-css.css',__FILE__),array(),$file_ver);
        }

        function apfw_user_script(){
            if($this->apfw_check_if_enqueue()==false)
                return;
            //enqueue front end js
            if(!file_exists(__DIR__.'/prism-js.js')){
                //try to create file
                $this->apfw_write_a_file($this->apfw_decide_js(),'prism-js.js');
            }

            /* unique file version, every time the file get modified */
            $file_ver=esc_attr(filemtime(__DIR__.'/prism-js.js'));
            //no dependency + enqueue to footer
            wp_enqueue_script('prism-script',plugins_url('prism-js.js',__FILE__),array(),$file_ver,true);

        }
        function apfw_check_if_enqueue(){
           $options=get_option('ank_prism_for_wp');
           if(@$options['onlyOnPost']==1){
                if(is_single()) {return true; }else return false;
           }
            return true;
        }
        function apfw_decide_css(){
            $options=get_option('ank_prism_for_wp');
            $theme_list=$this->apfw_theme_list();
            $plugin_list=$this->apfw_plugin_list();

            $style=file_get_contents(__DIR__.'/themes/'.$theme_list[intval($options['theme'])]['file'].'.css') ;
            //check if selected plugins require css
            foreach($options['plugin'] as $plugin){
                if($plugin_list[$plugin]['need_css']==1) {
                $style.= file_get_contents(__DIR__.'/plugins/'.$plugin_list[$plugin]['file'].'.css');
                }
            }
            //minify css before saving to file
            if(APFW_MINIFY_CSS==true){
                return $this->apfw_minify_css($style);
            } else{
                return $style;
            }


        }


        function apfw_decide_js(){
            $options=get_option('ank_prism_for_wp');
             $lang_list=$this->apfw_lang_list();
             $plugin_list=$this->apfw_plugin_list();
             //always include core js file
            $script=file_get_contents(__DIR__.'/prism-core.js');
             //include selected langs  js
            foreach($options['lang'] as $lang){
               $script.= file_get_contents(__DIR__.'/languages/'.$lang_list[$lang]['file'].'.js');
            }
             //include selected plugin js
            foreach($options['plugin'] as $plugin){
                $script.= file_get_contents(__DIR__.'/plugins/'.$plugin_list[$plugin]['file'].'.js');
            }
            //all js file are already minified
            return $script;

        }

        function apfw_write_a_file($data,$file_name){
            $file_name=__DIR__.'/'.$file_name;
            $handle = fopen($file_name, 'w');
            if($handle){
                if(!fwrite($handle, $data)){
                    //could not write file
                    @fclose($handle);
                    return false;
                }else{
                    //success
                    @fclose($handle);
                    return true;
                }
            }else{
                //could not open handle
                return false;
            }
        }

        function apfw_minify_css($buffer) {
            /* remove comments */
            $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
            /* remove tabs, spaces, newlines, etc. */
            $buffer = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $buffer);
            /* remove other spaces before/after ; */
            $buffer = preg_replace(array('(( )+{)','({( )+)'), '{', $buffer);
            $buffer = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $buffer);
            $buffer = preg_replace(array('(;( )+)','(( )+;)'), ';', $buffer);
            return $buffer;
        }

        public function apfw_add_editor_button() {
            if ( $this->apfw_check_if_btn_can_be()==true) {
                add_filter("mce_external_plugins", array($this,"afpw_add_tinymce_plugin"));
                add_filter('mce_buttons', array($this,'afpw_register_tinymce_button'));
             }

        }
        function afpw_register_tinymce_button($buttons) {
            array_push($buttons, "afpw_assist_button");
            return $buttons;
        }
        function afpw_add_tinymce_plugin($plugin_array) {
            $plugin_array['afpw_assist_button'] = plugins_url( '/apfw-editor-plugin.js', __FILE__ );
            return $plugin_array;
        }

        function apfw_admin_inline_script($hook){
            if ( $this->apfw_check_if_btn_can_be()==true) {
                $lang_list=$this->apfw_lang_list();
                echo "<script type='text/javascript'> /* <![CDATA[ */";
                echo 'var apfw_lang=[';
                for($i=1;$i<=count($lang_list);$i++){
                if($lang_list[$i]['in_popup']==1)
                echo '{text:"'.esc_attr(ucwords($lang_list[$i]['id'])).'", value:"'.esc_attr($lang_list[$i]['id']).'"},';
                }
                echo "]; /* ]]> */</script>";

        }
        }
        function apfw_admin_inline_style($hook){
            if ( $this->apfw_check_if_btn_can_be()==true)
            {
            ?><style type="text/css"> .mce-i-apfw-icon:before {content: '\f499';font: 400 20px/1 dashicons; padding: 0; vertical-align: top; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;} </style>
            <?php
        }
        }
        function  apfw_check_if_btn_can_be(){
            global $typenow;
            // check user permissions
            if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
                return false;
            }
            // verify the post type
            if( ! in_array( $typenow, array( 'post', 'page' ) ) )
                return false;
            //check if user don't want the
            $options=get_option('ank_prism_for_wp');
            if(@$options['noAssistant']==1)
                return false;
            // check if WYSIWYG is enabled
            if ( get_user_option('rich_editing') == 'true') {
                 return true;
            }
             return false;
        }

    }//end class
}//end if class exists


/* Options Page */
require(trailingslashit(dirname(__FILE__)) . "apfw_options_page.php");

if ( class_exists( 'Ank_Prism_For_WP' ) ) {
    /*Init main class */
    if(!isset($Ank_Prism_For_WP_Obj)){
        $Ank_Prism_For_WP_Obj=new Ank_Prism_For_WP();
    }
}

if ( class_exists( 'Ank_Prism_For_WP_Option_Page' )&&isset($Ank_Prism_For_WP_Obj) ) {
    /*Init option page class class */
    if(!isset($Ank_Prism_For_WP_Option_Page_Obj)){
        $Ank_Prism_For_WP_Page_Obj=new Ank_Prism_For_WP_Option_Page();
    }
}