<?php
global $wpdb, $current_user;
$testme_id = filter_input(INPUT_GET, 'testme_id', FILTER_VALIDATE_INT, array("default" => 0));

$testme_test_details = $wpdb->get_row("
            SELECT p.ID, test_type, test_only_reg, test_display_rez, test_show_points, guid, test_name, test_done, test_user
            FROM {$wpdb->testme_tests} AS t, {$wpdb->posts} AS p
            WHERE p.ID = t.test_post AND t.ID = '" . $testme_id . "'
        ");


// Check if we got something or not
if ($testme_test_details) {

    if ($testme_test_details->test_only_reg == 1 && !is_user_logged_in()) {
        print '<div class="testme_not_logged">' . get_option("testme_notice_not_reg", "Только зарегистрированные пользователи могут проходить этот тест.") . '</div>';
    } else {

        $testme_ans_list = filter_input(INPUT_POST, 'testme_answers', FILTER_SANITIZE_STRING);
        $all_answers = filter_input(INPUT_POST, 'all_answers', FILTER_SANITIZE_STRING);
        $testme_questions_multi =$_POST['testme_question_m'];
		
		
        // Список результатов
        $testme_where_line = substr($testme_ans_list, 0, -1);

        // Для тестов Абв
        if ($testme_test_details->test_type == 'abc') {

            $testme_score_letters = $wpdb->get_results("SELECT answer_points FROM " . $wpdb->testme_answers . "
			WHERE ID IN (" . $testme_where_line . ")
			");

            $testme_score_array = array();

            //Получаем строку
            foreach ($testme_score_letters as $letters) {
                $testme_letters = $letters->answer_points;
                $testme_one_answer = explode(",", $testme_letters);
                $testme_score_array = array_merge($testme_score_array, $testme_one_answer);
            }

            $testme_score_array_count = array_count_values($testme_score_array);
            arsort($testme_score_array_count);
            $testme_score = key($testme_score_array_count);


            //Получение результата теста
            $testme_result = $wpdb->get_row("SELECT ID, result_title, result_text, result_image, result_image_position
			FROM {$wpdb->testme_results} WHERE result_test_relation = " . $testme_id . "
			AND result_letter = '{$testme_score}' ");

            if (!$testme_result) {
                $testme_result_error = "Ошибка в тесте, результата не найдено.";
            }
        }

        // Для тестов 123
        else {
			if($testme_questions_multi){
				// echo '<pre class="print_r">';
				$question_false_list='';
				foreach($testme_questions_multi as $question_query){
					if(is_numeric($question_query['id'])){
					$question_answers_true = $wpdb->get_results("SELECT ID FROM " . $wpdb->testme_answers . "
							WHERE answer_points > 0
							AND answer_question_relation = ".$question_query['id']."
							ORDER BY ID
							"); 

						$q_answer_list_true=array();
						foreach($question_answers_true as $val){
							$q_answer_list_true[]=$val->ID;
						}						 
						$q_answer_list_query=$question_query['testme_answers'];
						
						sort($q_answer_list_true);
						sort($q_answer_list_query);
						
						if($q_answer_list_query==$q_answer_list_true){
							$testme_where_line.=','.implode(',',$q_answer_list_query);
							
						}else $question_false_list.=$question_query['id'];						
						// echo "\n";
						// print_r($q_answer_list_query);
						// if($q_answer_list_query==$q_answer_list_true)echo "\n == \n"; else echo "\n !== \n"; 
						// print_r($q_answer_list_true);
						// echo "\n _________________________";
						
					}
				}
				// echo '</pre>';
			}
            // Набрано баллов
            $testme_score = $wpdb->get_var("
            SELECT SUM(answer_points)
            FROM " . $wpdb->testme_answers . "
            WHERE ID IN (" . $testme_where_line . ")
			");
			
            // Результат теста
            $testme_result = $wpdb->get_row("SELECT ID, result_title, result_text, result_image, result_image_position
			FROM {$wpdb->testme_results} WHERE result_test_relation = {$testme_id}
			AND {$testme_score} >= result_point_start AND {$testme_score} <= result_point_end LIMIT 1");
			// var_dump('<pre>', $testme_score, '</pre>');
            if (!$testme_result) {
                $testme_result_error = "Ошибка в тесте, результата не найдено.";
            } else {
			
				
				
				if($testme_test_details->test_display_rez==1){
					$testme_right_list=$wpdb->get_results("SELECT a.ID as answer_id
						FROM {$wpdb->testme_questions} AS q, {$wpdb->testme_answers} AS a
						WHERE q.question_test_relation = '".$testme_id."' AND a.answer_question_relation = q.ID AND a.answer_points > 0
					");
					$testme_false_list=$wpdb->get_results("SELECT ID as answer_id
						FROM {$wpdb->testme_answers} as a
						WHERE a.ID IN (" . $all_answers . ") AND a.answer_points <= 0
					");
					$script_str="<input type='hidden' id='question_false_list' value='".$question_false_list."' />";
					$script_str.="<input type='hidden' id='display_rez_right' value='";
					foreach($testme_right_list as $a_row){
						$script_str.=$a_row->answer_id;
						if(!($a_row == end($testme_right_list))) {
							$script_str.=",";
						}	
					}
					$script_str.="' /><input type='hidden' id='display_rez_false' value='";
					foreach($testme_false_list as $a_row){
						$script_str.=$a_row->answer_id;
						if(!($a_row == end($testme_false_list))) {
							$script_str.=",";
						}	
					}
					$script_str.="' />";
					echo $script_str;
					
				}
                //$testme_your_score_notice = $testme_score;
                //Получение максимального результата, если надо
                if ($testme_test_details->test_show_points == 1) {
					
                    // Неизвестный комментарий, список id вопросов, наверное.
                    $testme_questions_array = $wpdb->get_col("SELECT answer_question_relation FROM {$wpdb->testme_answers}
                WHERE ID IN (" . $testme_where_line . ")");
                    $testme_questions_line = implode(",", $testme_questions_array);

                   
					$testme_123_points_max_multi=$wpdb->get_row("SELECT SUM(a.answer_points) AS points 
						FROM {$wpdb->testme_answers} a, {$wpdb->testme_questions} q 
						WHERE a.answer_question_relation = q.ID
						AND q.question_test_relation = {$testme_id}
						AND q.question_multiple = '1'
						");
	
					$testme_123_points_max_notMulti=$wpdb->get_row("SELECT SUM(max_points) AS points FROM 
						(SELECT max(a.answer_points) AS max_points 
							FROM {$wpdb->testme_answers} a, {$wpdb->testme_questions} q 
						WHERE a.answer_question_relation = q.ID
						AND q.question_test_relation = {$testme_id}
						AND q.question_multiple = '0'
						GROUP BY a.answer_question_relation) AS s");
						
					$testme_max_score=$testme_123_points_max_multi->points + $testme_123_points_max_notMulti->points;
                    // Создаем надпись о количестве баллов
                    // Функция родительного падежа и числительного
                    function testme_Num_and_Padezh($number, $array) {

                        $last1 = substr($number, -1, 1);
                        if (strlen($number) > 1) {
                            $last2 = substr($number, -2, 1);
                        } else {
                            $last2 = 0;
                        }

                        $let = array(5, 6, 7, 8, 9, 0);

                        if (in_array($last1, $let)) {
                            $line = $array[0];
                        } elseif ($last2 > 0 AND $last2 == 1) {
                            $line = $array[0];
                        } elseif ($last1 == 1) {
                            $line = $array[1];
                        } else {
                            $line = $array[2];
                        }
                        return $line;
                    }

                    //Собственно, надпись
                    $testme_array_ball = array('баллов', 'балл', 'балла');
                    $testme_array_otvet = array('ответов', 'ответ', 'ответа');
                    $testme_array_vopros = array('вопросов', 'вопрос', 'вопроса');

                    $testme_your_score_notice = '';
                    $testme_your_score_notice = get_option("testme_notice_got_points");
                    $testme_your_score_notice = str_replace("%got%", $testme_score, $testme_your_score_notice);
                    $testme_your_score_notice = str_replace("%total%", $testme_max_score, $testme_your_score_notice);
                    $testme_your_score_notice = str_replace("%балл%", testme_Num_and_Padezh($testme_score, $testme_array_ball), $testme_your_score_notice);
                    $testme_your_score_notice = str_replace("%ответ%", testme_Num_and_Padezh($testme_score, $testme_array_otvet), $testme_your_score_notice);
                    $testme_your_score_notice = str_replace("%вопрос%", testme_Num_and_Padezh($testme_score, $testme_array_vopros), $testme_your_score_notice);
                    $testme_your_score_notice = '<div class="testme_your_score">' . $testme_your_score_notice . '</div>';
                }
            }
        }



// Вывод результатов
        if (isset($testme_result_error)) {
            print '<div class="testme_error">' . $testme_result_error . $testme_score . '</div>';
        } else {
            ?>
            <div class="testme_result_block">
                <?php
                if (get_option("testme_show_results_notice") == 'yes') {
                    print '<div class="testme_before_results">' . get_option("testme_notice_before_results") . '</div>';
                }
                if (isset($testme_your_score_notice)) {
                    print $testme_your_score_notice;
                }
                if ($testme_result->result_title != '') {
                    print '<h3 class="testme_result_title">' . $testme_result->result_title . '</h3>';
                }
                if ($testme_result->result_image != '') {
                    print '<img src="' . $testme_result->result_image . '" class="testme_result_image ' . $testme_result->result_image_position . '" alt="' . $testme_result->result_title . '" />';
                }
                if ($testme_result->result_text != '') {
                    print '<div class="testme_result_text">' . nl2br(stripslashes($testme_result->result_text)) . '</div>';
                }
                ?>
            </div>
            <div style="clear:both;"></div>

            <?php
            // Добавляем одно прохождение
            $testme_done = $testme_test_details->test_done + 1;
            $wpdb->query("UPDATE {$wpdb->testme_tests} SET test_done = '" . $testme_done . "' WHERE ID = {$testme_id} ");

            //Вносим в статистику, если надо
            $ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);

            if (get_option('testme_stat_allow') == 'yes') {
                $testme_stat_array = array(
                    "stat_time" => current_time('mysql'),
                    "stat_result" => $testme_result->ID,
                    "stat_ip" => $ip,
                    "stat_test_relation" => $testme_id,
                    "stat_user" => $current_user->ID,
                    "stat_points" => $testme_score
                );
                $wpdb->insert($wpdb->testme_stats, $testme_stat_array, array('%s', '%d', '%s', '%d', '%d', '%s'));
             }

            //Коды для форумов и блогов
            // -- для форумов
            if (get_option("testme_code_for_forum") == 'yes') {
                ?>
            <div class='testme_code'><p><span>Код для форумов:</span>
                    <textarea>Результаты теста [URL=<?php print get_permalink($testme_test_details->ID); ?>]<?php print $testme_test_details->test_name; ?>[/URL]<?php
                        if ($testme_result->result_title != '') {
                            print "[B]{$testme_result->result_title}[/B]";
                        }
                        if ($testme_result->result_text != '') {
                            print strip_tags(stripslashes($testme_result->result_text));
                        }
                        if ($testme_result->result_image != '') {
                            print " [IMG]{$testme_result->result_image}[/IMG]";
                        }
                        ?>[URL=<?php print get_permalink($testme_test_details->ID); ?>]Пройти этот тест[/URL]</textarea>
                </p></div>
                <?php
            }
            // -- для блогов
            if (get_option("testme_code_for_blog") == 'yes') {
                ?>
            <div class='testme_code'>
<div class="tcode">
         <label for="spoiler">
<span class="btn">HTML-код для блогов и страниц</span>
</label>
<input id="spoiler" type="checkbox"/>
                    <div class="text1">
                    <textarea><p>Результаты теста <a href="<?php print get_permalink($testme_test_details->ID); ?>"><?php print $testme_test_details->test_name; ?></a></p><?php
                        if ($testme_result->result_title != '') {
                            print "<p><strong>{$testme_result->result_title}</strong></p>";
                        }
                        if ($testme_result->result_text != '') {
                            print "<p>" . strip_tags(stripslashes($testme_result->result_text)) . "</p>";
                        }
                        if ($testme_result->result_image != '') {
                            print "<p><img src=\"" . $testme_result->result_image . "\" /></p>";
                        }
                        ?><p><a href="<?php print get_permalink($testme_test_details->ID); ?>">Пройти этот тест</a></p></textarea>
                </p></div>
 </div>
</div>
                <?php
            }
        } // -- конец вывода существующих результатов 
    }
}
// If no test
else {
    print "Тест не найден.";
}