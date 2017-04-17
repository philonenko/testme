<div class="testme_area">
    <?php
    global $testme_t, $user_ID, $wpdb, $testme_r;

    //Получаем данные теста
    $testme_test_details = $wpdb->get_row("SELECT test_name, test_description, test_description_2, test_display_rez, test_only_reg, test_random_questions, test_random_answers
FROM {$wpdb->testme_tests} WHERE ID = {$testme_id}");

// -------------------
// Показываем сам тест
// -------------------
    //Список вопросов
    if ($testme_test_details->test_random_questions == 1) {
        $testme_order_by = 'RAND()';
    } else {
        $testme_order_by = 'ID';
    }

    if (testme_rcheck(get_option("testme_rcode"))) {
        echo '<input type="hidden" name="testme_reg" value="' . get_option("testme_rcode") . '" />';
    }

    $testme_all_question = $wpdb->get_results("SELECT ID, question_text, question_class, question_multiple
FROM {$wpdb->testme_questions} WHERE question_test_relation={$testme_id} ORDER BY {$testme_order_by}");

    if ($testme_all_question) {

        // Получаем список ответов для теста
        $testme_all_answers = $wpdb->get_results("SELECT a.ID AS a_id, answer_text, answer_class, q.ID AS q_id
        FROM {$wpdb->testme_answers} AS a, {$wpdb->testme_questions} AS q  
        WHERE q.ID = a.answer_question_relation
        AND question_test_relation={$testme_id} ORDER BY a.ID");

        $answers_list = array();

        if ($testme_all_answers) {
            foreach ($testme_all_answers as $ans) {
                $answers_list[$ans->q_id][$ans->a_id] = $ans->answer_text;
                $class_list[$ans->q_id][$ans->a_id] = $ans->answer_class;
            }
        }

        var_dump('<pre>', $class_list, '</pre>');
        // var_dump('<pre>', $testme_all_answers, '</pre>');
        ?>


        <?php
        // Залоголок и описание 
        if (get_option("testme_show_test_title") == 'yes') {
            print '<div class="testme_title"><h3>' . $testme_test_details->test_name . '</h3></div>';
        }

        if (get_option("testme_show_test_description") == 'yes' && $testme_test_details->test_description != '') {
            print '<div class="testme_show_test_description"><p>' . stripslashes($testme_test_details->test_description) . '</p></div>';
        }

        // *************************Вывод нового поля в фронтенд
        if (get_option("testme_show_test_description_2") == 'yes' && $testme_test_details->test_description_2 != '') {
            print '<div class="testme_show_test_description_2"><p>' . stripslashes($testme_test_details->test_description_2) . '</p></div>';
        }

        $i = 0;
        foreach ($testme_all_question as $ques) {
            $i++;
			$question_class = ( $ques->question_class ) ? $ques->question_class : '';
			if ($ques->question_multiple){ $inputType="checkbox"; $d_multi='data-multiple="1"';  } 
			else {$inputType="radio"; $d_multi="";}
			
            echo '<div class="testme_question" id="testme_question_id_'.$ques->ID.'" '.$d_multi.' >';
            echo '<div class="testme_question_text '. $question_class .'">' . $i . '. ' . stripslashes($ques->question_text) . '</div>';

            if ($testme_test_details->test_only_reg == 1 && !is_user_logged_in()) {
                echo '<ul class="testme_asnwer_list">';
            }

            // Список ответов
            $ans_for_this_q = array_keys($answers_list[$ques->ID]);
            // var_dump('<pre>', $ans_for_this_q, '</pre>');
            if ($testme_test_details->test_random_answers == 1) {
                shuffle($ans_for_this_q);
            }
			
            foreach ($ans_for_this_q as $ans_id) {

                if ($testme_test_details->test_only_reg == 1 && !is_user_logged_in()) {
                    echo '<li>' . stripslashes($answers_list[$ques->ID][$ans_id]) . '</li>';
                } else {
                    echo '<div class="testme_answer_block">
                <input type="'.$inputType.'" name="answer_' . $i . '" id="answer_id_' . $ans_id . '" class="testme_answer" value="' . $ans_id . '" />';
                    echo '<label for="answer_id_' . $ans_id . '"><span></span><p class="'. $class_list[$ques->ID][$ans_id] .'"">' . stripslashes($answers_list[$ques->ID][$ans_id]) . '</p></label></div>';
                }
            }
            if ($testme_test_details->test_only_reg == 1 && !is_user_logged_in()) {
                echo '</ul>';
            }
            echo "</div>";
        }
        ?>

        <?php
        // Проверяем, кто может проходить тест
        if ($testme_test_details->test_only_reg == 1 && !is_user_logged_in()) {
            ?>
            <div class="testme_not_logged"><?php echo get_option("testme_notice_not_reg", "Только зарегистрированные пользователи могут проходить этот тест.") ?></div>

        <?php } else { ?>
            <input type="hidden" name="testme_id" value="<?php echo $testme_id ?>" />
            <input type="button" name="action" class="testme_button" value="Показать результат"  />

        <?php } ?>

        <?php //if (!testme_rcheck(get_option("testme_rcode"))) { echo base64_decode($testme_t); } ?>

        <?php
        unset($i);
    }
    ?>
</div>  