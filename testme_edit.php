<div class="wrap">
    <h2>Управление тестами</h2>

    <?php
    // Выбираем, что показывать
    $testme_task = filter_input(INPUT_GET, 'task', FILTER_SANITIZE_STRING);

// Добавление нового теста
    if ($testme_task == 'edit') {
        include (WP_PLUGIN_DIR . '/testme/testme_edit_one.php');
    }
// Статистика теста
    elseif ($testme_task == 'stat') {
        include (WP_PLUGIN_DIR . '/testme/testme_stat.php');
    } else {
        ?>

        <h3>Список тестов</h3>

        <?php
//Страницы

        $testme_limit = get_option("testme_edit_per_page");
        if (!$testme_limit || $testme_limit < 1) {
            $testme_limit = 30;
        }

        $testme_page = filter_input(INPUT_GET, 'showpage', FILTER_VALIDATE_INT);

        if ($testme_page < 1) {
            $testme_page = 1;
        }
        $testme_start = ($testme_page - 1) * $testme_limit;

        $testme_countorder = $wpdb->get_var("
			SELECT COUNT(ID)
			FROM " . $wpdb->testme_tests . "
		");

        $testme_countpage = ceil($testme_countorder / $testme_limit);

// Вывод страниц
        if ($testme_countpage > 1) {
            //Текущий адрес без страницы
            //$testme_current_url = ereg_replace("[\&]showpage\=[0-9]+", "", filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING));

            $testme_page_line = "<p>Страницы:";
            for ($i = 1; $i <= $testme_countpage; $i++) {
                if ($testme_page == $i) {
                    $testme_page_line .= ' <strong>'.$i.'</strong>';
                } elseif ($i == 1) {
                    $testme_page_line .= ' <a href="?page=testme-edit">' . $i . '</a>';
                } else {
                    $testme_page_line .= ' <a href="?page=testme-edit&amp;showpage=' . $i . '">' . $i . '</a>';
                }
            }
            unset($i);
            $testme_page_line .= "</p>";
        } else {
            $testme_page_line = "";
        }

        print $testme_page_line;
        ?>

        <p><a href="" id="testme_show_add_test">Добавить тест</a></p>

        <div id="testme_new_test">Название теста: <input type="text" name="test_name_new" id="test_name_new" value="" style="width:300px;" /> <input type="button"  name="new_test" value="Добавить тест" tabindex="4" id="testme_add_button" /></div>

        <table class="widefat" id="testme_admin_list">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Название теста</th>
                    <th scope="col">Дата</th>
                    <th scope="col">Действия</th>
                    <th scope="col">Автор</th>
                    <th scope="col">Статус</th>
                </tr>
            </thead>
            <tbody id="the-list">

                <?php

                function testme_list_author_url($display_name, $user_id) {
                    global $current_user;
                    if ($user_id == $current_user->ID) {
                        return '<a href="profile.php">' . $display_name . '</a>';
                    } elseif (current_user_can('edit_users')) {
                        return '<a href="user-edit.php?user_id=' . $user_id . '&amp;wp_http_referer=%2Fwp-admin%2Fadmin.php">' . $display_name . '</a>';
                    }
                    return $display_name;
                }

                function testme_list_link_post($post_id, $test_status = 1, $post_status = '') {
                    if ($test_status == 1) {
                        return '<span class="status1">Черновик</span>';
                    } elseif ($test_status == 2) {
                        return '<span class="status2">На модерации</span>';
                    } elseif ($test_status == 3) {
                        return '<span class="status3">На доработке</span>';
                    } elseif ($test_status == 4 && $post_status == 'publish') {
                        return '<a href="' . get_permalink($post_id) . '" target="_blank" class="status4">Опубликовано</a>';
                    } elseif ($test_status == 4 && $post_status == 'future' && current_user_can('edit_others_posts')) {
                        return '<a href="' . get_permalink($post_id) . '" target="_blank"  class="status4">Запланировано</a>';
                    } else {
                        return '<span class="status4">Одобрено</span>';
                    }
                }

                global $current_user;
                if (!current_user_can('edit_others_posts')) {
                    $testme_where = " AND  " . $wpdb->testme_tests . ".test_user = " . $current_user->ID;
                } else {
                    $testme_where = "";
                }

                $testme_posts = $wpdb->get_results(" SELECT t.ID, test_name, DATE_FORMAT(test_start_day,GET_FORMAT(DATE,'EUR')) AS test_start_day_eur, test_user, test_status, test_post, display_name, guid, post_status
    FROM   " . $wpdb->users . " AS u, " . $wpdb->testme_tests . " AS t
	LEFT JOIN " . $wpdb->posts . " AS p ON p.ID = t.test_post
	WHERE u.ID = t.test_user " . $testme_where . "
	ORDER BY test_start_day DESC, ID DESC LIMIT $testme_start, $testme_limit");

                if ($testme_posts) {
                    $class = '';
                    foreach ($testme_posts as $post) {
                        $class = ('alternate' == $class) ? '' : 'alternate';
                        print "<tr class='$class'>\n";
                        print "<td>{$post->ID}</td>";
                        print "<td>{$post->test_name}</td>";
                        print "<td>" . $post->test_start_day_eur . "</td>";
                        print "<td class=\"testme_admin_img_col\">";
                        // Редактирование
                        if (($post->test_status == 2 || $post->test_status == 4) && !current_user_can('edit_others_posts')) {
                            print "<img src=\"" . WP_PLUGIN_URL . "/testme/images/edit_16_grey.gif\" alt=\"Изменить\" title=\"Тест уже опубликован, редактирование запрещено\" />";
                        } else {
                            print "<a href=\"?page=testme-edit&amp;task=edit&amp;testme_id={$post->ID}\" title=\"Изменить\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/edit_16.gif\" alt=\"Изменить\" title=\"Редактировать\" /></a>";
                        }
                        // Удаление
                        if (($post->test_status == 2 || $post->test_status == 4) && !current_user_can('edit_others_posts')) {
                            print "<img src=\"" . WP_PLUGIN_URL . "/testme/images/del_16_grey.gif\" alt=\"Удалить\"  title=\"Тест уже опубликован, удаление запрещено\" />";
                        } elseif ($post->test_status == 4 && current_user_can('edit_others_posts') && $post->guid != '') {
                            print "<img src=\"" . WP_PLUGIN_URL . "/testme/images/del_16_grey.gif\" alt=\"Удалить\"  title=\"Удалить тест можно только после того, как будет удалена соответствующая запись.\" />";
                        } else {
                            print "<a href=\"\" title=\"Удалить\" class=\"testme_delete\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/del_16.gif\" alt=\"Удалить\" title=\"Удалить\" /></a><input name=\"testme_del_id\" type=\"hidden\" value=\"{$post->ID}\" />";
                        }
                        // Просмотр
                        print "<a href=\"?page=testme-edit&amp;task=edit&amp;testme_id={$post->ID}&amp;step=4\" title=\"Просмотр теста\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/srch_16.gif\" alt=\"Просмотр теста\" title=\"Просмотр теста\" /></a>";
                        // Статистика
                        if ($post->test_status == 4) {
                            print "<a href=\"?page=testme-edit&amp;task=stat&amp;testme_id={$post->ID}\" title=\"Статистика\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/chart_16.gif\" alt=\"Статистика\" title=\"Статистика\" /></a>";
                        } else {
                            print "<a href=\"?page=testme-edit&amp;task=stat&amp;testme_id={$post->ID}\" title=\"Статистика\"><img src=\"" . WP_PLUGIN_URL . "/testme/images/chart_16_grey.gif\" alt=\"Статистика\"  /></a>";
                        }
                        print "</td>";
                        print "<td>" . testme_list_author_url($post->display_name, $post->test_user) . "</td>";
                        print "<td>" . testme_list_link_post($post->test_post, $post->test_status, $post->post_status) . "</td>";
                        print "</tr>";
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6">Тестов пока еще нет.</td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>
        </table>

        <script type="text/javascript">

            jQuery(document).ready(function($) {

                $("#testme_show_add_test").click(function() {
                    $("#testme_new_test").slideToggle("slow");
                    return false;
                });
                
                var testme_ajaxurl = '<?php echo admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')); ?>';

                /*  Добавление  */
                $("#testme_add_button").live('click', function() {
                    var testme_title = $('#test_name_new').val();

                    if (!testme_title) {
                        alert('Не введено название теста');
                    }
                    else {
                        $("#testme_new_test").html('<img src="<?php echo plugins_url('testme/images/loading4.gif') ?>" alt="" />');
                        jQuery.ajax({
                            url: testme_ajaxurl,
                            method: 'GET',
                            data: 'action=testme_adm&task=newtest&testme_title=' + encodeURIComponent(testme_title),
                            success: function(html) {
                                $("#the-list").prepend(html);
                                $("#testme_new_test").html('Название теста: <input type="text" name="test_name_new" id="test_name_new" value="" style="width:300px;" /> <input type="button"  name="new_test" value="Добавить тест" tabindex="4" id="testme_add_button" />');
                                $("#testme_new_test").hide();
                            },
                            error: function() {
                                alert('Не удалось выполнить операцию');
                                $("#testme_new_test").html('Название теста: <input type="text" name="test_name_new" id="test_name_new" value="" style="width:300px;" /> <input type="button"  name="new_test" value="Добавить тест" tabindex="4" id="testme_add_button" />');
                                $("#testme_new_test").hide();
                            }
                        });
                    }

                    return false;
                });

                /*  Удаление  */
                $('.testme_delete').live('click', function() {

                    var current_test_id = $(this).parents('td.testme_admin_img_col').find(':hidden').val();
                    var current_tr = $(this).parents('tr');
                    $(current_tr).addClass('row_delete');

                    if (confirm('Вы уверены, что хотите навсегда удалить тест ' + current_test_id + '? \nПосле удаления его уже нельзя будет восстановить.')) {

                        jQuery.ajax({
                            url: testme_ajaxurl,
                            method: 'GET',
                            data: 'action=testme_adm&task=deltest&testme_id=' + current_test_id,
                            success: function(html) {
                                if (html === 'ok') {
                                    $(current_tr).remove();
                                }
                                else {
                                    alert(html);
                                    $(current_tr).removeClass('row_delete');
                                }
                            },
                            error: function() {
                                alert('Не удалось выполнить операцию');
                                $(current_tr).removeClass('row_delete');
                            }
                        });
                    }
                    else {
                        $(current_tr).removeClass('row_delete');
                    }


                    return false;
                });

            });

        </script>	

        <?php
    } // конец таблицы со списком тестов
    ?>

</div>