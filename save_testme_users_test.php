<?php 

$test_random_questions = ( isset($_POST['test_random_questions']) ) ? true : false;
$test_random_answers = ( isset($_POST['test_random_answers']) ) ? true : false;

$wpdb->insert($wpdb->testme_tests, array("test_name" => $_POST['test_name_new'], "test_random_questions" => $test_random_questions, "test_random_answers" => $test_random_answers ), array('%s', '%s', '%s'));

// Вопросы и ответы
$testme_id = $wpdb->insert_id * 1;

if ( isset($_POST['question_text_new']) ) {

	foreach ($_POST['question_text_new'] as $num => $value) {
		$num = intval($num);
		$question_class = $_POST['question_text_style_new'][$num];

		if ($value != '') {

			$multiple = ( isset($_POST['multiple_new'][$num]) ) ? true : false;

			$wpdb->insert($wpdb->testme_questions, array("question_text" => $value, "question_class" => $question_class, "question_test_relation" => $testme_id, "question_multiple" => $multiple), array('%s', '%s', '%s', '%d'));

			$testme_question_id = $wpdb->insert_id;

						// ответы
			$testme_answer_code = $_POST['answer_text_new'][$num];
			$answer_classes = $_POST['answer_text_style_new'][$num];

			$testme_points_code = $_POST['answer_points_new'][$num];
			foreach ($testme_answer_code as $num_ans => $answer_value) {
				$num_ans = intval($num_ans);

				if ($answer_value != '') {
		  //обработка пойнтов
					$points = $testme_points_code[$num_ans];
					$answer_class = $answer_classes[$num_ans];

					$wpdb->insert($wpdb->testme_answers, array(
						"answer_text" => $answer_value, "answer_class" => $answer_class,  "answer_points" => $points, "answer_question_relation" => $testme_question_id), array('%s', '%s', '%d', '%d')
					);
				}
			}
		}
	}
}
// Результаты
if ($_POST['result_title_new']) {
	foreach ($_POST['result_title_new'] as $num => $new_title) {

		$num = intval($num);

		if (!($_POST['result_point_start_new'][$num] == 0 && $_POST['result_point_end_new'][$num] == 0)) {

			$testme_result_insert_array = array(
				"result_title" => trim($new_title),
				"result_test_relation" => $testme_id,
				"result_point_start" => $_POST['result_point_start_new'][$num],
				"result_point_end" => $_POST['result_point_end_new'][$num]
				);

			$wpdb->insert($wpdb->testme_results, $testme_result_insert_array, array('%s', '%d', '%d', '%d'));
		}
	}
}

// Нажатие кнопки "Сохранить"
$test_start_day = date("Y-m-d");

if ($wpdb->query("UPDATE {$wpdb->testme_tests} SET test_start_day = '{$test_start_day}', test_status = 4, test_moder_time = '{$test_start_day}', test_moder_comment = '' WHERE ID = " . $testme_id . " LIMIT 1;")) {

	do_action('set_test_me_in_post', $testme_id);
	add_action('init', 'set_test_me_in_post', 10, 1);

} else {
	print '<span class="testme_error">Не удалось выполнить операцию. Обновите страницу и попробуйте еще раз.</span>';
}