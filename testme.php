<?php

/*
  Plugin Name: TESTME - Плагин для создания тестов
  Plugin URI: http://wp-hint.ru/archives/126
  Description: Плагин позволяет добавить тесты в записи вордпресса
  Author: Татьяна Калайдина <forregs@yandex.ru>
  Version: 1.4
 */

/*  Copyright 2009  Татьяна Калайдина/Tatiana Kalaydina  (email : forregs@yandex.ru)

  Плагин TESTME распространяется бесплатно. Вы имеете полное право
  распространять его дальше или вносить любые изменения в исходный код.
  Я надеюсь, что плагин окажется полезен, но не гарантирую, что он
  полностью подойдет для ваших целей. На мне не лежит никаких обязанностей
  по дальнейшей модификации плагина с целью его наибольшего соответствия
  пожеланиям других пользователей.

 */
global $testme_current_ver;
$testme_current_ver = 1.4;
if (get_option('testme_built') == '1.3') {
    update_option('testme_built', $testme_current_ver);
}

### Testme Table Names
global $wpdb;
$wpdb->testme_tests = $wpdb->prefix . 'testme_tests';
$wpdb->testme_questions = $wpdb->prefix . 'testme_questions';
$wpdb->testme_answers = $wpdb->prefix . 'testme_answers';
$wpdb->testme_results = $wpdb->prefix . 'testme_results';
$wpdb->testme_stats = $wpdb->prefix . 'testme_stats';

// Опции
function testme_add_options() {
    global $testme_current_ver;
    add_option('testme_show_test_title', 'no');
    add_option('testme_show_test_description', 'yes');
    add_option('testme_show_test_description_2', 'yes');
    add_option('testme_show_results_notice', 'yes');
    add_option('testme_notice_before_results', 'Результаты теста:');
    add_option('testme_code_for_forum', 'yes');
    add_option('testme_code_for_blog', 'yes');
    add_option('testme_edit_category', '1');
    add_option('testme_edit_user_category', '1');
    add_option('testme_edit_per_page', '30');
    add_option('testme_stat_per_page', '10');
    add_option('testme_stat_allow', 'yes');
    add_option('testme_access_reg', 'no');
    add_option('testme_notice_not_reg', 'Только зарегистрированные пользователи могут проходить этот тест.');
    add_option('testme_notice_got_points', 'Вы набрали %got% %балл% из %total%.');
    add_option('testme_built', $testme_current_ver);
}

// Таблицы
function testme_add_tables() {
    global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql = "CREATE TABLE " . $wpdb->testme_tests . " (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `test_name` varchar(250) DEFAULT 'Без имени',
  `test_type` varchar(3) NOT NULL DEFAULT '123',
  `test_done` int(10) NOT NULL DEFAULT '0',
  `test_description` text,

  `test_description_2` text,

  `test_start_day` date DEFAULT NULL,
  `test_only_reg` tinyint(1) NOT NULL DEFAULT '0',
  `test_display_rez` tinyint(1) NOT NULL DEFAULT '1',
  `test_show_points` tinyint(1) NOT NULL DEFAULT '0',
  `test_random_questions` tinyint(1) NOT NULL DEFAULT '0',
  `test_random_answers` tinyint(1) NOT NULL DEFAULT '0',
  `test_user` bigint(20) NOT NULL DEFAULT '1',
  `test_status` tinyint(1) NOT NULL DEFAULT '1',
  `test_moder_id` int(11) NOT NULL DEFAULT '0',
  `test_moder_time` date DEFAULT NULL,
  `test_moder_comment` text,
  `test_post` bigint(20) NOT NULL,
  PRIMARY KEY (`ID`)
)  DEFAULT CHARSET=utf8;";
    dbDelta($sql);

    $sql = "CREATE TABLE " . $wpdb->testme_questions . " (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `question_text` text,

  `question_class` text,
  `question_result` text,

  `question_test_relation` int(10) DEFAULT NULL,
  `question_multiple` boolean DEFAULT 0,
  PRIMARY KEY (`ID`)
)  DEFAULT CHARSET=utf8 ;";
    dbDelta($sql);

    $sql = "CREATE TABLE " . $wpdb->testme_answers . " (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `answer_text` text,

  `answer_class` text,

  `answer_points` varchar(10) DEFAULT NULL,
  `answer_question_relation` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`)
    ) DEFAULT CHARSET=utf8;";
    dbDelta($sql);

    $sql = "CREATE TABLE " . $wpdb->testme_results . " (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `result_title` varchar(250) DEFAULT NULL,
  `result_text` text,
  `result_image` varchar(250) DEFAULT NULL,
  `result_image_position` varchar(100) DEFAULT NULL,
  `result_point_start` int(5) DEFAULT NULL,
  `result_point_end` int(5) DEFAULT NULL,
  `result_letter` varchar(1) DEFAULT NULL,
  `result_test_relation` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`)
    ) DEFAULT CHARSET=utf8 ;";
    dbDelta($sql);

    $sql = "CREATE TABLE " . $wpdb->testme_stats . " (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `stat_time` datetime DEFAULT NULL,
  `stat_result` varchar(5) DEFAULT NULL,
  `stat_user` bigint(20) NOT NULL DEFAULT '0',
  `stat_points` varchar(7) DEFAULT NULL,
  `stat_ip` varchar(20) DEFAULT NULL,
  `stat_test_relation` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`)
)  DEFAULT CHARSET=utf8 ;";
    dbDelta($sql);
}

// Активация плагина
if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, 'testme_add_tables');
    register_activation_hook(__FILE__, 'testme_add_options');
}

// * Апгрейд *

$testme_tech_action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$testme_tech_plugin = filter_input(INPUT_GET, 'plugin', FILTER_SANITIZE_STRING);

if ($testme_tech_action == 'upgrade' && $testme_tech_plugin == basename(__FILE__) && get_option('testme_built') != $testme_current_ver) {
    if (get_option('testme_built') == "1.2") {
        testme_upgrade_1_2_to_1_3();
    }
}

function testme_upgrade_1_2_to_1_3() {

    global $testme_current_ver;
    global $wpdb;

//обновление опций и таблиц до версии 1.3
    add_option('testme_edit_category', '1');
    add_option('testme_stat_allow', 'yes');

    $wpdb->query("ALTER TABLE " . $wpdb->testme_tests . " CHANGE `ID` `ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
        CHANGE `test_only_reg` `test_only_reg` TINYINT( 1 ) NOT NULL DEFAULT '0',
        CHANGE `test_show_points` `test_show_points` TINYINT( 1 ) NOT NULL DEFAULT '0',
        DROP `test_questions` ,
        DROP `test_answers` ,
        DROP `test_results`,
        ADD `test_random_questions` TINYINT( 1 ) NOT NULL DEFAULT '0',
        ADD `test_random_answers` tinyint(1) NOT NULL DEFAULT '0',
        ADD `test_user` bigint(20) NOT NULL DEFAULT '1',
        ADD `test_status` tinyint(1) NOT NULL DEFAULT '1',
        ADD `test_moder_id` int(11) NOT NULL DEFAULT '0',
        ADD `test_moder_time` date DEFAULT NULL,
        ADD `test_moder_comment` text,
        ADD `test_post` bigint(20) NOT NULL
        ");

    $wpdb->query("ALTER TABLE " . $wpdb->testme_questions . " CHANGE `ID` `ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
        CHANGE `question_test_relation` `question_test_relation` INT( 10 ) NULL DEFAULT NULL ");
    $wpdb->query("ALTER TABLE " . $wpdb->testme_answers . " CHANGE `ID` `ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
        CHANGE `answer_question_relation` `answer_question_relation` INT( 10 ) NULL DEFAULT NULL  ");
    $wpdb->query("ALTER TABLE " . $wpdb->testme_results . " CHANGE `ID` `ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
        CHANGE `result_test_relation` `result_test_relation` INT( 10 ) NULL DEFAULT NULL  ");
    $wpdb->query("ALTER TABLE " . $wpdb->testme_stats . " CHANGE `ID` `ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
        CHANGE `stat_test_relation` `stat_test_relation` INT( 10 ) NULL DEFAULT NULL ");

// Link old tests to related posts and mark them as approved
    $testme_link_to_posts = $wpdb->get_results("SELECT t.ID as test_id, t.test_name, p.ID as post_id 
        FROM " . $wpdb->testme_tests . " t, " . $wpdb->posts . " p
        WHERE p.post_content LIKE CONCAT( '%[TESTME ', t.ID , ']%') 
        AND p.post_status IN ('publish', 'future', 'draft')");
    if ($testme_link_to_posts) {
        foreach ($testme_link_to_posts as $testme_update_test) {
            $wpdb->query("UPDATE {$wpdb->testme_tests} 
            SET test_post = '{$testme_update_test->post_id}', test_status = 4
            WHERE ID = {$testme_update_test->test_id} LIMIT 1;");
        }
    }

    update_option('testme_built', $testme_current_ver);
    echo '<div class="updated"><p>Плагин TESTME обновлен.</p></div>';
}

//Добавляем ссылку для обновления в меню с плагинами, если надо
function testme_plugin_actions($links, $file) {
    $plugin_file = basename(__FILE__);
    global $testme_current_ver;

//print "ggg:".$testme_current_ver;
    if (get_option('testme_built') != $testme_current_ver) {
        if (basename($file) == $plugin_file) {
            $settings_link = '<a href="plugins.php?plugin=' . $plugin_file . '&action=upgrade">' . __('Обновить', 'testme') . '</a>';
            array_unshift($links, $settings_link);
        }
    }
    return $links;
}

add_filter('plugin_action_links', 'testme_plugin_actions', 10, 2);



/* === Меню в панеле администратора === */

//Добавляем пункты меню
add_action('admin_menu', 'testme_menu');

function testme_menu() {

    if (function_exists('add_menu_page')) {
        add_menu_page('TESTME', 'TESTME', 'manage_options', 'testme-edit', '', WP_PLUGIN_URL . '/testme/images/testme-logo.png');
    }
    if (function_exists('add_submenu_page')) {
        add_submenu_page('testme-edit', 'Тесты', 'Тесты', 'manage_options', 'testme-edit', 'testme_edit_func');
        add_submenu_page('testme-edit', 'Настройки', 'Настройки', 'manage_options', 'testme-options', 'testme_options_func');
    }
}

function testme_edit_func() {
    global $wpdb, $current_blog;
    include (WP_PLUGIN_DIR . '/testme/testme_edit.php');
}

function testme_options_func() {
    global $wpdb, $current_blog;
    include (WP_PLUGIN_DIR . '/testme/testme_options.php');
}

/* === Добавление таблицы стилей === */
add_action('admin_enqueue_scripts', 'testme_css_admin');

function testme_css_admin($hook_suffix) {
//global $text_direction;
    $testme_admin_pages = array('testme-edit', 'testme-new', 'testme-stat', 'testme-options');

    $testme_hook_suffix = str_replace('toplevel_page_', '', $hook_suffix);

    if (in_array($testme_hook_suffix, $testme_admin_pages)) {
        wp_enqueue_style('testme-admin', plugins_url('testme/testme_style_admin.css'), false, '1.0', 'all');
    }
}

/* === Добавление font-awesome-4.7.0 === */

add_action('wp_enqueue_scripts', 'testme_font_awesome');

function testme_font_awesome() {
   wp_enqueue_style('testme-font-awesome', plugins_url('testme/font-awesome-4.7.0/css/font-awesome.min.css'));
}

add_action('wp_head', 'testme_css_theme');

function testme_css_theme() {
    print '<link rel="stylesheet" id="testme-style-css"  href="' . plugins_url('testme/testme_style.css') . '" type="text/css" media="all" /> ';
}

function testme_rcheck($str) {
    $server_name = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING);
    if (substr(md5($server_name . 'tk'), strpos($server_name, '.'), 7) == $str) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Вывод теста в записи
add_filter('the_content', 'testme_scan_content');

function testme_scan_content($body) {
    if (strpos($body, 'TESTME') !== false) {

        if (preg_match('/(<!--|\[)\s*TESTME\s*(\d+)\s*(\]|-->)/', $body, $matches)) {

            $testme_id = $matches[2];

            if (is_numeric($testme_id)) {
                ob_start();
                include (WP_PLUGIN_DIR . '/testme/testme_show.php');
                $contents = ob_get_contents();
                ob_end_clean();

                $body = str_replace($matches[0], $contents, $body);
            }
        }
    }
    return $body;
}

// Действия с тестами adminpanel
add_action('wp_ajax_testme_adm', 'testme_aj_adminpanel');

function testme_aj_adminpanel() {
    include_once (WP_PLUGIN_DIR . '/testme/testme_action.php');
    die();
}

// show results
add_action('wp_ajax_testme', 'testme_show_results');
add_action('wp_ajax_nopriv_testme', 'testme_show_results');

// Getting test results
function testme_show_results() {
    $testme_task = filter_input(INPUT_GET, 'task', FILTER_SANITIZE_STRING);
    // Результаты тестов
    if ($testme_task == 'testresults') {
        include (WP_PLUGIN_DIR . '/testme/testme_show_results.php');
    }
    die();
}
add_action('wp_enqueue_scripts', 'testme_scripts');
function testme_scripts() {
    wp_enqueue_script('testme', plugins_url('testme/js/testme.js'), array('jquery'), '1.1', true);
    wp_localize_script('testme', 'testme_aj', array(
        'ajax_url' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http'))
    ));

    if( is_admin() ){
      if ( ! did_action( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
      }
      wp_enqueue_script('admin', plugins_url('testme/js/admin.js'), array('jquery'), '1.1', true);
    }
}

add_action('wp_enqueue_scripts', 'testme_admin_scripts');

function testme_admin_scripts() {

      if ( ! did_action( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
      }

      wp_enqueue_script('admin', plugins_url('testme/js/admin.js'), array('jquery'), '1.4', true);
}

$testme_t = 'PGRpdiBjbGFzcz0idGVzdG1lX2JhY2tsaW5rIj4mIzEwNTc7JiMxMDg3OyYjMTA4NjsmIzEwODU7JiMxMDg5OyYjMTA4NjsmIzEwODg7ICYjMTA4NzsmIzEwODM7JiMxMDcyOyYjMTA3NTsmIzEwODA7JiMxMDg1OyYjMTA3Mjs6IDxhIGhyZWY9Imh0dHA6Ly90cmlra3kucnUiIHRhcmdldD0iX2JsYW5rIiBmb2xsb3c9ImRvZm9sbG93Ij4mIzEwNTg7JiMxMDc3OyYjMTA4OTsmIzEwOTA7JiMxMDk5OyAmIzEwNzY7JiMxMDgzOyYjMTEwMzsgJiMxMDc2OyYjMTA3NzsmIzEwNzQ7JiMxMDg2OyYjMTA5NTsmIzEwNzc7JiMxMDgyOzwvYT48L2Rpdj4=';
$testme_r = 'PGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0idGVzdG1lX3JlZyIgdmFsdWU9InlvdXJfdGVzdCIgLz4=';

// Шорткод для добавления теста любому незарегистрированному пользователю
add_shortcode('testme_users_test', 'add_sortcode_testme_users_test');

function add_sortcode_testme_users_test(){
	require_once 'testme_users_test.php';
}

// var_dump('<pre>', $_POST, '</pre>');
// Создает запись с шорткодом
function set_test_me_in_post ($testme_id) {
  // var_dump('<pre>', $testme_id,'</pre>');
    // Создаем запись
  global $wpdb;
  // Получаем данные из таблицы тестов
  $testme_test_details = $wpdb->get_row("SELECT test_name, test_description, test_user
    FROM `{$wpdb->testme_tests}` WHERE ID = `{$testme_id}`");
  // var_dump('<pre>', $testme_test_details,'</pre>');
  /*if ($testme_test_details->test_description != '') {
    $testme_exerpt = trim(strip_tags($testme_test_details->test_description));
  } else {
    $testme_exerpt = '';
  }*/
  
  // Добавление записи
  $post_for_test_array = array(
    'post_author' => 1,
    'post_content' => '[TESTME ' . $testme_id . ']',
    'post_title' => $testme_test_details->test_name,
    // 'post_excerpt' => $testme_exerpt,
    'post_status' => 'publish',
    'ping_status' => 'closed',
    'post_category' => array(get_option("testme_edit_user_category", 1))
    );

  $testme_post_id = wp_insert_post($post_for_test_array);        

  // Добавление номера записи в таблицу с тестом
  $wpdb->query("UPDATE {$wpdb->testme_tests} SET test_post = '{$testme_post_id}'
    WHERE ID = {$testme_id} LIMIT 1;");

  print '<div class="testme_step4_status4">Тест одобрен, соответствующая запись создана. Теперь ее надо отредактировать и опубликовать.</div>';
}

if( isset($_POST['testme_users_test_create']) && $_POST['testme_users_test_create'] == 'yes' ) {
	require_once('save_testme_users_test.php');
}