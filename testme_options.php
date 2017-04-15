<div class="wrap">
    <h2>Настройки для тестов</h2>

    <?php
    $testme_modify_options = filter_input(INPUT_POST, 'testme_modify_options', FILTER_VALIDATE_INT);

//Обработка настроек
    if ($testme_modify_options == 1) {

        $testme_options_array = array(
            'testme_show_test_title' => FILTER_SANITIZE_STRING,
            'testme_show_test_description' => FILTER_SANITIZE_STRING,
            'testme_show_results_notice' => FILTER_SANITIZE_STRING,
            'testme_notice_before_results' => FILTER_SANITIZE_STRING,
            'testme_code_for_forum' => FILTER_SANITIZE_STRING,
            'testme_code_for_blog' => FILTER_SANITIZE_STRING,
            'testme_edit_category' => FILTER_VALIDATE_INT,
            'testme_edit_per_page' => FILTER_VALIDATE_INT,
            'testme_stat_per_page' => FILTER_VALIDATE_INT,
            'testme_stat_allow' => FILTER_SANITIZE_STRING,
            'testme_access_reg' => FILTER_SANITIZE_STRING,
            'testme_notice_not_reg' => FILTER_SANITIZE_STRING,
            'testme_notice_got_points' => FILTER_SANITIZE_STRING,
            'testme_rcode' => FILTER_SANITIZE_STRING
        );

        $testme_options = filter_input_array(INPUT_POST, $testme_options_array);

        // yes and no options. crazy old appendix of the first version...
        $testme_no_array = array('testme_show_test_title', 'testme_show_test_description', 'testme_show_results_notice',
            'testme_code_for_forum', 'testme_code_for_blog', 'testme_access_reg', 'testme_stat_allow');
        foreach ($testme_no_array as $opt) {
            if ($testme_options[$opt] != "yes") {
                $testme_options[$opt] = "no";
            }
        }

        //set the options
        foreach ($testme_options as $key => $option) {
            update_option($key, $option);
            //print $key.": ".$option."<br />";
        }
        echo '<div class="updated"><p>Настройки обновлены.</p></div>';
    }
    ?>


    <form action="" method="post">
        <table class="widefat" style="width:600px;">
            <thead>
                <tr>
                    <th scope="col">Параметры тестов</th>
                </tr>
            </thead>
            <tbody id="the-list">
                <tr>
                    <td>
                        <p><input name="testme_show_test_title" type="checkbox"  id="testme_show_test_title" value="yes"
                            <?php
                            if (get_option("testme_show_test_title") == 'yes') {
                                print "checked";
                            }
                            ?> />
                            <label for="testme_show_test_title">Показывать заголовок теста перед списком вопросов</label></p>
                        <p><input name="testme_show_test_description" type="checkbox"  id="testme_show_test_description" value="yes"
                            <?php
                            if (get_option("testme_show_test_description") == 'yes') {
                                print
                                        "checked";
                            }
                            ?> />
                            <label for="testme_show_test_description">Показывать описание теста перед списком вопросов</label></p>
                    </td>
                </tr>

                <tr class="alternate">
                    <td>
                        <p><input name="testme_show_results_notice" type="checkbox"  id="testme_show_results_notice" value="yes"
                            <?php
                            if (get_option("testme_show_results_notice") == 'yes') {
                                print "checked";
                            }
                            ?> />
                            <label for="testme_show_results_notice">Показывать надпись "Результаты теста:" (или иную) перед результатами теста</label></p>
                        <p><input name="testme_notice_before_results" type="text"  id="testme_notice_before_results"
                                  value="<?php echo get_option("testme_notice_before_results") ?>" />
                            <label for="testme_notice_before_results">Надпись, которая выводится перед результатами теста</label></p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p><input name="testme_code_for_forum" type="checkbox"  id="testme_code_for_forum" value="yes"
                            <?php
                            if (get_option("testme_code_for_forum") == 'yes') {
                                print "checked";
                            }
                            ?> />
                            <label for="testme_code_for_forum">Показывать код для форума после результатов теста.</label></p>
                        <p><input name="testme_code_for_blog" type="checkbox"  id="testme_code_for_blog" value="yes"
                            <?php
                            if (get_option("testme_code_for_blog") == 'yes') {
                                print "checked";
                            }
                            ?> />
                            <label for="testme_code_for_blog">Показывать HTML-код для блогов после результатов теста.</label></p>
                    </td>
                </tr>

                <tr class="alternate">
                    <td>
                        <p><input name="testme_edit_category" type="text"  id="testme_edit_category"
                                  value="<?php echo get_option("testme_edit_category") ?>" size="3" />
                            <label for="testme_edit_category">Номер рубрики по умолчанию.</label></p>
                        <p><input name="testme_edit_per_page" type="text"  id="testme_edit_per_page"
                                  value="<?php echo get_option("testme_edit_per_page") ?>" size="3" />
                            <label for="testme_edit_per_page">Количество тестов на одной странице в панеле управления тестами.</label></p>
                        <p><input name="testme_stat_allow" type="checkbox"  id="testme_stat_allow" value="yes"
                            <?php
                            if (get_option("testme_stat_allow") == 'yes') {
                                print "checked";
                            }
                            ?> />
                            <label for="testme_stat_allow">Включить подсчет поименной статистики прохождений.</label></p>		
                        <p><input name="testme_stat_per_page" type="text"  id="testme_stat_per_page"
                                  value="<?php echo get_option("testme_stat_per_page") ?>" size="3" />
                            <label for="testme_stat_per_page">Количество последних прохождений в статистике тестов.</label></p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p><input name="testme_access_reg" type="checkbox"  id="testme_access_reg" value="yes"
                            <?php
                            if (get_option("testme_access_reg") == 'yes') {
                                print "checked";
                            }
                            ?> />
                            <label for="testme_access_reg">Разрешить прохождение теста только зарегистрированным пользователям
                                (можно изменить в параметрах каждого отдельного теста).</label>
                        </p>
                        <p><input name="testme_notice_not_reg" type="text"  id="testme_notice_not_reg"
                                  value="<?php echo get_option("testme_notice_not_reg") ?>" size="80" /><br />
                            <label for="testme_notice_not_reg">Надпись вместо кнопки "Отправить" для незарегистрированных пользователей,
                                если им нет доступа к тесту.</label></p>
                    </td>
                </tr>

                <tr class="alternate">
                    <td>
                        <p><input name="testme_notice_got_points" type="text"  id="testme_notice_got_points"
                                  value="<?php echo get_option("testme_notice_got_points") ?>" size="80" /><br />
                            <label for="testme_notice_got_points">Надпись, которая говорит пользователю, сколько баллов он набрал в тесте.</label><br />
                            Можно использовать следующие сокращения:<br />
                            %got% - количество баллов за тест у пользователя,<br />
                            %total% - максимальное количество баллов за тест,<br />
                            %балл%, %ответ%, %вопрос% - данные слова в нужном падеже в зависимости от количества баллов, набранных пользователем.<br />
                            (Например, <em>Вы ответили правильно на %got% %вопрос%</em> = <em>Вы ответили правильно на 4 вопроса</em>.)</p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <p><input <?php
                            if (testme_rcheck(get_option("testme_rcode"))) {
                                echo
                                'style="background:#e7f8cc"';
                            } else {
                                echo 'style="background:#fee8ef"';
                            }
                            ?> name="testme_rcode" type="text"  id="testme_rcode"
                                value="<?php echo get_option("testme_rcode") ?>" />
                            <label for="testme_rcode">Код для отключения ссылки. (Получить код можно по адресу forregs@yandex.ru)</label></p>
                    </td>
                </tr>                   

                <tr>
                    <td>
                        <input type="hidden" name="testme_modify_options" value="1" />
                        <input type="submit" name="testme_modify_options_button" value="Внести изменения" class="button" /></td>
                </tr>
            </tbody>
        </table>


    </form>


</div>