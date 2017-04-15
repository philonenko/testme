<?php
global $wpdb, $current_user;

$testme_task = filter_input(INPUT_GET, 'task', FILTER_SANITIZE_STRING);

$testme_user_level = 'manage_options';

// Нажатие кнопки "Готово"
if ($testme_task == 'testready' && current_user_can($testme_user_level)) {
    $test_start_day = date("Y-m-d");
    $testme_id = filter_input(INPUT_GET, 'testme_id', FILTER_VALIDATE_INT);

    if ($wpdb->query("UPDATE {$wpdb->testme_tests} SET test_start_day = '{$test_start_day}', test_status = 4,
	test_moder_id = '{$current_user->ID}', test_moder_time = '{$test_start_day}', test_moder_comment = ''
	WHERE ID = '" . $testme_id . "' LIMIT 1;")) {

        // Создаем запись
        // Получаем данные из таблицы тестов
        $testme_test_details = $wpdb->get_row("SELECT test_name, test_description, test_user
			FROM {$wpdb->testme_tests} WHERE ID = {$testme_id}");
        if ($testme_test_details->test_description != '') {
            $testme_exerpt = trim(strip_tags($testme_test_details->test_description));
        } else {
            $testme_exerpt = '';
        }

        // Добавление записи
        
        // Добавление записи
        $post_for_test_array = array(
            'post_author' => $testme_test_details->test_user,
            'post_content' => '[TESTME ' . $testme_id . ']',
            'post_title' => $testme_test_details->test_name,
            'post_excerpt' => $testme_exerpt,
            'post_status' => 'draft',
            'ping_status' => 'closed',
            'post_category' => array(get_option("testme_edit_category", 1))
        );

        $testme_post_id = wp_insert_post($post_for_test_array);        
        
        // Добавление номера записи в таблицу с тестом
        $wpdb->query("UPDATE {$wpdb->testme_tests} SET test_post = '{$testme_post_id}'
			WHERE ID = {$testme_id} LIMIT 1;");

        print '<div class="testme_step4_status4">Тест одобрен, соответствующая запись создана. Теперь ее надо отредактировать и опубликовать.</div>';
    } else {
        print '<span class="testme_error">Не удалось выполнить операцию. Обновите страницу и попробуйте еще раз.</span>';
    }
}

// Добавление нового теста
if ($testme_task == 'newtest' && current_user_can($testme_user_level)) {

    $testme_title = filter_input(INPUT_GET, 'testme_title', FILTER_SANITIZE_STRING);
    $test_start_day = date("Y-m-d");
    $test_start_day_eur = date("d.m.Y");

    $testme_newtest_array = array(
        "test_name" => $testme_title,
        "test_start_day" => $test_start_day,
        "test_user" => $current_user->ID
    );

    $wpdb->insert($wpdb->testme_tests, $testme_newtest_array, array('%s', '%s', '%d'));
    $testme_id = $wpdb->insert_id;

    print "<tr>\n";
    print "<td>{$testme_id}</td>";
    print "<td>" . $testme_title . "</td>";
    print "<td>" . $test_start_day_eur . "</td>";
    print "<td class=\"testme_admin_img_col\">";
    print "<a href=\"?page=testme-edit&amp;task=edit&amp;testme_id={$testme_id}\" title=\"Изменить\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/edit_16.gif\" alt=\"Изменить\" title=\"Редактировать\" /></a>";
    print "<a href=\"?page=testme-edit&amp;task=delete&amp;testme_id={$testme_id}\" title=\"Удалить\" class=\"testme_delete\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/del_16.gif\" alt=\"Удалить\" title=\"Удалить\" /></a><input name=\"testme_del_id\" type=\"hidden\" value=\"" . $testme_id . "\" />";
    print "<a href=\"?page=testme-edit&amp;task=edit&amp;testme_id={$testme_id}&amp;step=4\" title=\"Просмотр теста\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/srch_16.gif\" alt=\"Просмотр теста\" title=\"Просмотр теста\" /></a>";
    print "<img src=\"" . WP_PLUGIN_URL . "/testme/images/chart_16_grey.gif\" alt=\"Статистика пока недоступна\" title=\"Статистика пока недоступна\" /></a>";
    print "</td>";
    print "<td><a href=\"profile.php\">" . $current_user->display_name . "</a></td>";
    print "<td>Черновик</td>";
    print "</tr>";
}

// Удаление теста
if ($testme_task == 'deltest' && current_user_can($testme_user_level)) {

    $testme_id = filter_input(INPUT_GET, 'testme_id', FILTER_VALIDATE_INT);

    // Проверяем, можно ли удалить этот тест
    $testme_test_details = $wpdb->get_row("SELECT test_user, test_status, p.ID AS p_id FROM " . $wpdb->testme_tests . " AS t
	LEFT JOIN " . $wpdb->posts . " AS p ON p.ID = t.test_post 
	WHERE t.ID = '" . $testme_id . "'");
    if ($testme_test_details) {
        if (($testme_test_details->test_user == $current_user->ID && $testme_test_details->test_status == 1 ) || (current_user_can($testme_user_level) && $testme_test_details->p_id == '')) {
            print "ok";
            // *** Само удаление ***
            // ответы
            $wpdb->query("DELETE FROM {$wpdb->testme_answers} WHERE answer_question_relation IN (SELECT q.ID from {$wpdb->testme_questions} q "
                    . "WHERE question_test_relation = '" . $testme_id . "')");
            // вопросы
            $wpdb->delete($wpdb->testme_questions, array('question_test_relation' => $testme_id));
            // результаты 
            $wpdb->delete($wpdb->testme_results, array('result_test_relation' => $testme_id));
            // статистику
            $wpdb->delete($wpdb->testme_stats, array('stat_test_relation' => $testme_id));
            // сам тест
            $wpdb->delete($wpdb->testme_tests, array('ID' => $testme_id));
        } else {
            print "У вас нет прав для удаления этого теста.";
        }
    } else {
        print "Такого теста не существует.";
    }
}


// Результат теста
//if (isset($_GET['task']) && $_GET['task'] == 'testresults') {
//    include (WP_PLUGIN_DIR . '/testme/testme_show_results.php');
//}
