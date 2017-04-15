<?php
//phpinfo();

global $current_user;

$testme_id = filter_input(INPUT_POST, 'testme_id', FILTER_VALIDATE_INT);
$testme_form_step = filter_input(INPUT_POST, 'testme_form_step', FILTER_VALIDATE_INT);
$testme_step = filter_input(INPUT_GET, 'step', FILTER_VALIDATE_INT);
if ($testme_step < 1 || $testme_step > 4) {
    $testme_step = 1;
}

$testme_user_level = 'manage_options';

//Изменение в уже существующем тесте
if ($testme_id > 0) {

// Проверяем, имеет ли пользователь право изменять этот тест
// Т.е. имеет право редактировать ИЛИ тест пользователя и еще не опубликован
    $testme_test_access_data = $wpdb->get_row("SELECT test_user, test_status  FROM {$wpdb->testme_tests} 
WHERE ID = " . $testme_id);


    if (($testme_test_access_data->test_user == $current_user->ID && $testme_test_access_data->test_status == 1 ) || current_user_can($testme_user_level)) {

// 1. Изменяем шапку  - название теста, описание, дату, доступ и баллы
        if ($testme_form_step == 1) {

            $test_name = trim(filter_input(INPUT_POST, 'test_name', FILTER_SANITIZE_STRING));
            if ($test_name == "") {
                $test_name = "Без имени";
            }

            // *************Обновление ШАГ-1
            $test_update_array = array(
                "test_name" => $test_name,
                "test_description" => trim(filter_input(INPUT_POST, 'test_description', FILTER_SANITIZE_MAGIC_QUOTES)),
                "test_description_2" => trim(filter_input(INPUT_POST, 'test_description_2', FILTER_SANITIZE_MAGIC_QUOTES)),
                "test_only_reg" => filter_input(INPUT_POST, 'test_only_reg', FILTER_VALIDATE_INT),
                "test_show_points" => filter_input(INPUT_POST, 'test_show_points', FILTER_VALIDATE_INT),
                "test_display_rez" => filter_input(INPUT_POST, 'test_display_rez', FILTER_VALIDATE_INT),
                "test_random_questions" => filter_input(INPUT_POST, 'test_random_questions', FILTER_VALIDATE_INT),
                "test_random_answers" => filter_input(INPUT_POST, 'test_random_answers', FILTER_VALIDATE_INT),
                "test_type" => filter_input(INPUT_POST, 'test_type', FILTER_SANITIZE_STRING),
                "test_post" => filter_input(INPUT_POST, 'test_post', FILTER_VALIDATE_INT)
            );

            $wpdb->update($wpdb->testme_tests, $test_update_array, array("ID" => $testme_id));

            print "<div class=\"updated\"><p>Изменения внесены. Вы можете продолжить редактирование параметров или перейти к шагу 2.</p></div>";
        } // -- 1.
// 2. Изменение вопросов и ответов
        elseif ($testme_form_step == 2) {

            function testme_step2_get_clear_points($points) {
                if (!is_int($points) and function_exists('mb_strtolower')) {
                    $points = mb_strtolower($points);
                }
                $unnecessary_chars = array(')', ' ', '.', '!', '%');
                $points = str_replace($unnecessary_chars, '', $points);
                return $points;
            }

            $test_data = filter_input_array(INPUT_POST, array(
                'answer_text_old' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'multiple_old' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'question_text_old' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'answer_points_old' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'answer_text_for_old' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'answer_points_for_old' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'question_text_new' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'answer_text_new' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'multiple_new' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'answer_points_new' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY)
                    )
            );

            // Старые ответы
            if (isset($test_data['answer_text_old'])) {
                foreach ($test_data['answer_text_old'] as $num => $answer) {
                    $num = intval($num);

                    if ($answer == "") {
                        $wpdb->delete($wpdb->testme_answers, array('ID' => $num));
                    } else {
                        // Получаем баллы
                        $points = testme_step2_get_clear_points($test_data['answer_points_old'][$num]);

                        // Записываем новые данные
                        $wpdb->update($wpdb->testme_answers, array("answer_text" => $answer, "answer_points" => $points), array("ID" => $num));
                    }
                }
            }
            // Старые вопросы
            if (isset($test_data['question_text_old'])) {
                foreach ($test_data['question_text_old'] as $num => $question) {
                    $num = intval($num);
					

                    if ($question == "") {
                        $wpdb->delete($wpdb->testme_answers, array('answer_question_relation' => $num));
                        $wpdb->delete($wpdb->testme_questions, array('ID' => $num));
                    } else {
                        //-- новые ответы к старым вопросам
                        if (isset($test_data['answer_text_for_old'][$num])) {
                            foreach ($test_data['answer_text_for_old'][$num] as $a_num => $answer) {
                                $num = intval($num);

                                if ($answer == "") {
                                    
                                } else {
                                    // Получаем баллы
                                    $points = testme_step2_get_clear_points($test_data['answer_points_for_old'][$num][$a_num]);

                                    // Записываем новые данные
                                    $wpdb->insert($wpdb->testme_answers, array(
                                        "answer_text" => $answer, "answer_points" => $points, "answer_question_relation" => $num), array('%s', '%s', '%d')
                                    );
                                }
                            }
                        }
                        // сам вопрос
						if(isset($test_data['multiple_old'][$num]))$multiple=true; else $multiple=false;
					
                        $wpdb->update($wpdb->testme_questions, array("question_text" => $question, "question_multiple" => $multiple), array("ID" => $num));
                        
                    }
                }
            }
            // Новые вопросы
            if (isset($test_data['question_text_new'])) {

                foreach ($test_data['question_text_new'] as $num => $value) {
                    $num = intval($num);

                    //вопрос
                    if ($value != '') {
						if(isset($test_data['multiple_new'][$num]))$multiple=true; else $multiple=false;
                        $wpdb->insert($wpdb->testme_questions, array("question_text" => $value, "question_test_relation" => $testme_id, "question_multiple" => $multiple), array('%s', '%d'));

                        $testme_question_id = $wpdb->insert_id;

                        // ответы
                        $testme_answer_code = $test_data['answer_text_new'][$num];

                        $testme_points_code = $test_data['answer_points_new'][$num];
                        foreach ($testme_answer_code as $num_ans => $answer_value) {
                            $num_ans = intval($num_ans);

                            if ($answer_value != '') {
                                //обработка пойнтов
                                $points = testme_step2_get_clear_points($testme_points_code[$num_ans]);

                                $wpdb->insert($wpdb->testme_answers, array(
                                    "answer_text" => $answer_value, "answer_points" => $points, "answer_question_relation" => $testme_question_id), array('%s', '%s', '%d')
                                );
                            }
                        }
                    }
                }
            }
            print "<div class=\"updated\"><p>Изменения внесены. Вы можете продолжить редактирование вопросов или перейти к шагу 3.</p></div>";
        } // -- 2.
// 3. Изменение результатов
        elseif ($testme_form_step == 3) {

            $test_data = filter_input_array(INPUT_POST, array(
                'result_letter' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_point_start' => array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_point_end' => array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_image' => array('filter' => FILTER_VALIDATE_URL, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_image_position' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_title' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_text' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_letter_new' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_point_start_new' => array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_point_end_new' => array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_image_new' => array('filter' => FILTER_VALIDATE_URL, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_image_position_new' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_title_new' => array('filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_REQUIRE_ARRAY),
                'result_text_new' => array('filter' => FILTER_SANITIZE_MAGIC_QUOTES, 'flags' => FILTER_REQUIRE_ARRAY)
                    )
            );

            //-- уже бывших результатов
            if ($test_data['result_title']) {
                foreach ($test_data['result_title'] as $num => $title) {
                    $num = intval($num);

                    if (!(trim($title) == '' && $test_data['result_image'][$num] == '' && $test_data['result_text'][$num] == '') &&
                            !($test_data['result_letter'][$num] == '' && $test_data['result_point_start'][$num] == 0 && $test_data['result_point_end'][$num] == 0)) {

                        $testme_result_update_array = array(
                            "result_title" => trim($title),
                            "result_text" => $test_data['result_text'][$num],
                            "result_image" => $test_data['result_image'][$num],
                            "result_image_position" => $test_data['result_image_position'][$num],
                            "result_letter" => $test_data['result_letter'][$num],
                            "result_point_start" => $test_data['result_point_start'][$num],
                            "result_point_end" => $test_data['result_point_end'][$num]
                        );

                        $wpdb->update($wpdb->testme_results, $testme_result_update_array, array("ID" => $num));
                    } else {
                        $wpdb->delete($wpdb->testme_results, array("ID" => $num));
                    }
                }
            }

            // }
            //-- новых результатов
            if ($test_data['result_title_new']) {
                foreach ($test_data['result_title_new'] as $num => $new_title) {
                    $num = intval($num);
                    if (!(trim($new_title) == '' && $test_data['result_image_new'][$num] == '' && $test_data['result_text_new'][$num] == '') &&
                            !($test_data['result_letter_new'][$num] == '' && $test_data['result_point_start_new'][$num] == 0 && $test_data['result_point_end_new'][$num] == 0)) {

                        $testme_result_insert_array = array(
                            "result_title" => trim($new_title),
                            "result_text" => $test_data['result_text_new'][$num],
                            "result_image" => $test_data['result_image_new'][$num],
                            "result_image_position" => $test_data['result_image_position_new'][$num],
                            "result_test_relation" => $testme_id,
                            "result_letter" => $test_data['result_letter_new'][$num],
                            "result_point_start" => $test_data['result_point_start_new'][$num],
                            "result_point_end" => $test_data['result_point_end_new'][$num]
                        );

                        $wpdb->insert($wpdb->testme_results, $testme_result_insert_array, array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d'));
                    }
                }
            }


            print "<div class=\"updated\"><p>Изменения внесены. Вы можете продолжить редактирование результатов или перейти к шагу 4.</p></div>";
        } // -- 3.
    } // -- конец редактирования
// Если нельзя редактировать - все остальные случаи
    else {
        print '<p class="testme_error">У вас нет права редактировать этот тест.</p>';
    }
}

// Если нет Id через пост, получаем через гет
else {
    $testme_id = filter_input(INPUT_GET, 'testme_id', FILTER_VALIDATE_INT);
}
?>

<h3>Редактирование теста</h3>

<?php
//Проверяем, есть ли строка с таким ИД

// *********************Выводит данные в админпанель настроек теста(ШАГ 1)

$testme_test_details = $wpdb->get_row("SELECT ID, test_name, test_type, test_description, test_description_2, test_start_day,
test_only_reg,  test_show_points, test_user, test_random_questions, test_display_rez, test_random_answers, test_status,
test_moder_comment, DATE_FORMAT(test_moder_time,GET_FORMAT(DATE,'EUR')) AS test_moder_time, test_post
FROM {$wpdb->testme_tests} WHERE ID = {$testme_id}");

//Если нет такого теста
if (!$testme_test_details->ID || ($testme_test_details->test_user != $current_user->ID && !current_user_can($testme_user_level) )) {
    print "Теста с таким ID не найдено.";
}
// А если есть, то редактируем его
else {

    // Общая часть
    ?>

    <div class="testme_edit_menu <?php
    if ($testme_step == 1) {
        print "testme_edit_menu_active";
    }
    ?>"><a href="?page=testme-edit&amp;task=edit&amp;testme_id=<?php print $testme_id; ?>">Шаг 1 - Описание</a></div>
    <div class="testme_edit_menu <?php
    if ($testme_step == 2) {
        print "testme_edit_menu_active";
    }
    ?>"><a href="?page=testme-edit&amp;task=edit&amp;testme_id=<?php print $testme_id; ?>&amp;step=2">Шаг 2 - Вопросы</a></div>
    <div class="testme_edit_menu <?php
    if ($testme_step == 3) {
        print "testme_edit_menu_active";
    }
    ?>"><a href="?page=testme-edit&amp;task=edit&amp;testme_id=<?php print $testme_id; ?>&amp;step=3">Шаг 3 - Результаты</a></div>
    <div class="testme_edit_menu <?php
    if ($testme_step == 4) {
        print "testme_edit_menu_active";
    }
    ?>"><a href="?page=testme-edit&amp;task=edit&amp;testme_id=<?php print $testme_id; ?>&amp;step=4">Шаг 4 - Публикация</a></div>

    <div class="testme_edit_one">

        <?php
        if ($testme_step == 1) {
            include ('testme_edit_one_step1.php');
        } elseif ($testme_step == 2) {
            include ('testme_edit_one_step2.php');
        } elseif ($testme_step == 3) {
            include ('testme_edit_one_step3.php');
        } elseif ($testme_step == 4) {
            include ('testme_edit_one_step4.php');
        }
        ?>

    </div>



    <?php
}