jQuery(document).ready(function ($) {

	// Удаление пустых параграфов
	var paragraph = $(".testme_area").find('p');

	for (var i = 0; i < paragraph.length; i++) {
		var text = paragraph[i];
		if( $(text).text() == '' ){
			$(text).remove();
		}
	}

    $(".testme_button").click(function () {
        testme_result_back();
        
            var testme_id = $(this).parents(".testme_area").find("[name=testme_id]").val(),
				answered_arr = $(this).parents(".testme_area").find(":radio:checked"),
				questions_multi=$(this).parents(".testme_area").find("[data-multiple=1]"),
				answered = answered_arr.length,
				all_checked_arr=$(this).parents(".testme_area").find(":radio:checked, :checkbox:checked"),				
				questions = $(this).parents(".testme_area").find(".testme_question").length;		
				
            if (false) {
                alert('Вы ответили только на ' + answered + ' вопросов из ' + questions + '.\n\nОтветьте на все вопросы.')
            } else {
                var answers_line = '';
                answered_arr.each(function (index, element) {
                    var pr = element.value;
                    answers_line += pr + ','
                });	
				var all_answers_arr = [];
                all_checked_arr.each(function (index, element) {
                    var pr = element.value;
                    all_answers_arr[all_answers_arr.length]= pr;
                });	
				
				
				var postParams={};
				postParams['all_answers']=all_answers_arr.join(',');
				postParams['testme_answers']=answers_line;
				postParams['testme_question_m']={};
				questions_multi.each(function (index, element){
					var q_id=$(this).attr('id').replace("testme_question_id_",""),
						q_answ_arr=$(element).find(":checkbox:checked"),
						q_answ_text={};
					q_answ_arr.each(function (indexJ, elementJ){
						var pr = elementJ.value;
						q_answ_text[indexJ]=pr;
					});	
					
					postParams['testme_question_m'][index]={'id': q_id,'testme_answers':q_answ_text};
				});

				
				
                $(this).hide();
                $(this).after("<div id=\"testme_result\"><img src=\"/wp-content/plugins/wp_testme/images/loading4.gif\" alt=\"\" /></div>");
                jQuery.ajax({
                    url: testme_aj.ajax_url+'?action=testme&task=testresults&testme_id='+testme_id,
                    method: 'POST',
                    data: postParams,
                    success: function (html) {
                        $("#testme_result").html(html)
						if($('#display_rez_right').length){						
							var idList = $('#display_rez_right').val().split(/\s*,\s*/);
							for(var i=0;i<=idList.length-1;i++){
								$('#answer_id_'+idList[i]).parent().addClass('answer-right');
								
							}
						}
						if($('#display_rez_false').length){						
							var idList = $('#display_rez_false').val().split(/\s*,\s*/);
							for(var i=0;i<=idList.length-1;i++){
								$('#answer_id_'+idList[i]).parent().removeClass('answer-right').addClass('answer-false');
							}
						}
						$('.testme_question').addClass('testme_question-true')
							.find('.answer-false')
							.parent()
							.removeClass('testme_question-true')
							.addClass('testme_question-false');
						if($('#question_false_list').length){	
							var idList = $('#question_false_list').val().split(/\s*,\s*/);
							
							for(var i=0;i<=idList.length-1;i++){
								$('#testme_question_id_'+idList[i])									
									.removeClass('testme_question-true')
									.addClass('testme_question-false');								
							}							
						}
                    },
                    error: function () {
                        alert('Не удалось выполнить операцию');
                        testme_result_back()
                    }
                })
            }
        
        return false
    });

    function testme_result_back() {
        $("#testme_result").remove();
        $(".testme_button").show()
    };
    $(".testme_result_close").live('click', function () {
        testme_result_back();
        return false
    })
});