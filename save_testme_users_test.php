<?php 
/*$_POST = array(
	"test_name_new" => trim(filter_input(INPUT_POST, 'test_name_new', FILTER_SANITIZE_MAGIC_QUOTES)),
	"test_description" => trim(filter_input(INPUT_POST, 'test_description', FILTER_SANITIZE_MAGIC_QUOTES)),
	"test_random_questions" => trim(filter_input(INPUT_POST, 'test_random_questions', FILTER_SANITIZE_MAGIC_QUOTES)),
	"test_random_answers" => filter_input(INPUT_POST, 'test_random_answers', FILTER_VALIDATE_INT),
	"question_text_new" => filter_input(INPUT_POST, 'question_text_new', FILTER_VALIDATE_INT),
	"question_text_style_new" => filter_input(INPUT_POST, 'question_text_style_new', FILTER_VALIDATE_INT),
	"answer_points_new" => filter_input(INPUT_POST, 'answer_points_new', FILTER_VALIDATE_INT),
	"answer_text_new" => filter_input(INPUT_POST, 'answer_text_new', FILTER_VALIDATE_INT),
	"answer_text_style_new" => filter_input(INPUT_POST, 'answer_text_style_new', FILTER_SANITIZE_STRING),
	"result_point_start_new" => filter_input(INPUT_POST, 'result_point_start_new', FILTER_VALIDATE_INT),
	"result_point_end_new" => filter_input(INPUT_POST, 'result_point_end_new', FILTER_VALIDATE_INT),
	"result_title_new" => filter_input(INPUT_POST, 'result_title_new', FILTER_VALIDATE_INT),
	);
*/

	$test_random_questions = ( isset($_POST['test_random_questions']) ) ? true : false;
	$test_random_answers = ( isset($_POST['test_random_answers']) ) ? true : false;

	$wpdb->insert($wpdb->testme_tests, array("test_name" => $_POST['test_name_new'], "test_random_questions" => $test_random_questions, "test_random_answers" => $test_random_answers ), array('%s', '%s', '%s'));


	$testme_id = $wpdb->insert_id;
// echo $testme_id;
// var_dump('<pre>', $_POST['question_text_new'], '</pre>');

	if (isset($_POST['question_text_new'])) {

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

	if ($_POST['result_title_new']) {
		foreach ($_POST['result_title_new'] as $num => $new_title) {

			$num = intval($num);

			if (!(trim($new_title) == '' && $_POST['result_image_new'][$num] == '' && $_POST['result_text_new'][$num] == '') && !($_POST['result_point_start_new'][$num] == 0 && $_POST['result_point_end_new'][$num] == 0)) {

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