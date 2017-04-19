<?php 
function getTestmeIcons(){
	return '<span class="testme-icons-edit"><input type="image" src="'. WP_PLUGIN_URL .'/testme/images/icons/bold.png" data-action="bold" /><input type="image" src="'. WP_PLUGIN_URL .'/testme/images/icons/italics.png" data-action="italics" /><input type="image" src="'. WP_PLUGIN_URL .'/testme/images/icons/underline.png" data-action="underline" /><input type="image" src="'. WP_PLUGIN_URL .'/testme/images/icons/photoicon.png" data-action="photoicon" /><input type="image" src="'. WP_PLUGIN_URL .'/testme/images/icons/piicon.png" data-action="piicon" /><input type="image" src="'. WP_PLUGIN_URL .'/testme/images/icons/quoteicon.png" data-action="quoteicon" /></span>';
} 
$i = 1; ?>

<form name="post" action="" method="post" id="post">
	<div id="poststuff">	
		<div class="postbox" id="testme_questions_div">
			<h2 class="hndle">Добавить тест</h2>
			<div class="testme_inside">

				<div class="testme_head_info">
					<p><input type="text" name="test_name_new" id="test_name_new" placeholder="Название теста" /></p>
					<p><?php echo getTestmeIcons(); ?></p>
					<p><textarea name="test_description" id="" cols="30" rows="10" placeholder="Описание теста"></textarea></p>
					<p><input name="test_random_questions" id="test_random_questions" type="checkbox" value="1"><label for="test_random_questions">Выводить вопросы теста в случайном порядке.</label></p>
					<p><input name="test_random_answers" id="test_random_answers" type="checkbox" value="1"><label for="test_random_answers">Выводить ответы к вопросам в случайном порядке.</label></p>
				</div>

				<div class="testme_question_block_for_users" id="testme_q_new-<?php print $i; ?>">
					<p><strong>Вопрос <?php print $i; ?></strong></p>
					<div class="testme_question_block_for_users_inner">
						<p>
							<input class="testme_change" type="text" name="question_text_new[<?php print $i; ?>]" />
							<input type="hidden" name="question_text_style_new[<?php print $i; ?>]" value="" />
						</p>
						<p>
							<label><input type="checkbox" name="multiple_new[<?php print $i; ?>]" value="1" /><small>Несколько ответов<small></label>
							<?php echo getTestmeIcons(); ?>
						</p>
						<p class="testme_answer">
							<input type="text" size="5" name="answer_points_new[<?php print $i; ?>][]" /> 
							<input type="text" class="testme_change" name="answer_text_new[<?php print $i; ?>][]" />
							<input type="hidden" name="answer_text_style_new[<?php print $i; ?>][]" value="" />

						</p>
						<p class="testme_answer">
							<input type="text" size="5" name="answer_points_new[<?php print $i; ?>][]" /> 
							<input type="text" class="testme_change" name="answer_text_new[<?php print $i; ?>][]" />
							<input type="hidden" name="answer_text_style_new[<?php print $i; ?>][]" value="" />

						</p> 
						<p class="testme_add_answer"><a href="#">Новый ответ</a></p>
					</div>
				</div>

				<p class="testme_add_question"><a href="#"><strong>Добавить вопрос</strong></a></p>

				<div class="testme_test_results">
					<div class="testme_test_results_header_info">
						<h3>Результаты:</h3>
						<p>Минимальное количество баллов в этом тесте: </p>
						<p>Максимальное количество баллов в этом тесте: </p>
					</div>
					<div class="testme_single_result">
						<div class="testme_single_result_inner">
							<h4>Результат 1</h4>
							<p>Баллы от : <input class="testme_min_points" type="text" size="3" name="result_point_start_new[]" maxlength="3" /> до <input class="testme_max_points" type="text" size="3" name="result_point_end_new[]" maxlength="3" /></p>
							<h5>Заголовок результата</h5>
							<p><input type="text" name="result_title_new[]" /></p>
						</div>
					</div>
					<p class="testme_add_result"><a href="#"><strong>Добавить результат</strong></a></p>
				</div>

			</div>
		</div>
		<p class="submit">
			<input type="hidden" name="testme_users_test_create" value="yes" />
			<input type="submit" name="submit" value="Сохранить" tabindex="4" />
		</p>
	</div>
</form>

<script type="text/javascript">

	function testme_add_answer_q_old(q_id) {
		var anum = jQuery("#testme_q_old-" + q_id + " p.testme_answer_for_old").length + 1;
		var buttonsIcon = '<?php echo getTestmeIcons(); ?>';

		jQuery("#testme_add_answer-" + q_id).before("<p class=\"testme_answer_for_old\"><input type=\"text\" size=\"5\" name=\"answer_points_for_old[" + q_id + "][" + anum + "]\" style=\"background-color:#e7ffe7\" /> <input type=\"text\" name=\"answer_text_for_old[" + q_id + "][" + anum + "]\"  style=\"width:60%;\" /><input type=\"hidden\" name=\"answer_class_for_old[" + q_id + "][" + anum + "]\" value=\"\" /></p>");
				//$('div.testme_question_block:eq(0)').css('border','3px solid black');
				//alert("#testme_add_answer-"+q_id+" "+anum);
				return false;
			}

jQuery(document).ready(function($) {

	$("body").on("focus", ".testme_question_block_for_users .testme_change" ,function(){
		var focusInput = $(this);
		$("body").on("click", "span.testme-icons-edit input[type='image']" ,function(e){
			e.preventDefault();
			console.log(focusInput);
		});
	});

	$("body").on("click", "span.testme-icons-edit input[type='image']" ,function(e){
		e.preventDefault();
		var action = $(this).data('action');
		switch( action ){
			case 'bold': ;
			case 'italics': ;
			case 'underline': ;
			case 'quoteicon': getStyle($(this), action);break;
			case 'photoicon': getPhoto(); break;
			case 'piicon': break;
			default: ; break;
		}
	});

	function getStyle( clickObj, action ){
		var siblings = clickObj.parent().siblings();

		$(siblings).each(function(){
			var attrName = ( $(this).attr('name') ) ? $(this).attr('name') : '';

			if( 'answer_text' == attrName.substr(0, 11) || 'question_text' == attrName.substr(0, 13) ){

				var hiddenSiblings = $(this).siblings('input[type="hidden"]');

				switch( action ){
					case 'bold': 
					$(this).toggleClass( "testme-bold" );
					$(hiddenSiblings).val( $(this).attr('class') ); break;
					case 'italics': 
					$(this).toggleClass( "testme-italic" );
					$(hiddenSiblings).val( $(this).attr('class') ); break;
					case 'underline': 
					$(this).toggleClass( "testme-underline" );
					$(hiddenSiblings).val( $(this).attr('class') ); break;
					case 'quoteicon': setBlockquote($(this), action); break;
					default: ; break;
				}
			}

		});
	}

	function setBlockquote(clickObj, action){
		var currentValue = clickObj.val();
		var select = clickObj.selectionStart;

		if( currentValue.indexOf('<blockquote>') != -1 ){
			var deleteTags = currentValue.replace('<blockquote>', '');
			deleteTags = deleteTags.replace('</blockquote>', '');
			clickObj.val(deleteTags);
			return;
		}

		switch( action ){
			/*case 'bold': clickObj.val('<strong>'+ currentValue +'</strong>'); break;
			case 'italics': clickObj.val('<em>'+ currentValue +'</em>'); break;
			case 'underline': clickObj.val('<span style="text-decoration: underline;">'+ currentValue +'</span>'); break;*/
			case 'quoteicon': clickObj.val('<blockquote>'+ currentValue +'</blockquote>'); break;
		}
	}

	function getPhoto(){}

	$("p.testme_add_question").click(function() {
		var qnum = $('div.testme_question_block_for_users').length + 1;
		var buttonsIcon = '<?php echo getTestmeIcons(); ?>';
		$(this).before("<div class=\"testme_question_block_for_users\" id=\"testme_q_new-" + qnum + "\"><p><strong>Вопрос " + qnum + "</strong></p><div class=\"testme_question_block_for_users_inner\"><p><input class=\"testme_change\" type=\"text\" name=\"question_text_new[" + qnum + "]\" /><input type=\"hidden\" name=\"question_text_style_new[" + qnum + "]\" /></p><p><label><input type=\"checkbox\" name=\"multiple_new[" + qnum + "]\" value=\"1\" /><small>Несколько ответов<small></label>"+ buttonsIcon +"</p><p class=\"testme_answer\"><input type=\"text\" size=\"5\" name=\"answer_points_new[" + qnum + "][]\" /> <input type=\"text\" class=\"testme_change\" name=\"answer_text_new[" + qnum + "][]\" /><input type=\"hidden\" name=\"answer_text_style_new[" + qnum + "][]\" /></p><p class=\"testme_answer\"><input type=\"text\" size=\"5\" name=\"answer_points_new[" + qnum + "][]\" /> <input type=\"text\" class=\"testme_change\" name=\"answer_text_new[" + qnum + "][]\" /><input type=\"hidden\" name=\"answer_text_style_new[" + qnum + "][]\" /></p> <p class=\"testme_add_answer\"><a href=\"#\">Новый ответ</a></p></div></div>");
		return false;
	});


	$("p.testme_add_answer").live('click', function() {
		var anum = $(this).parent('div.testme_question_block_for_users').index('div.testme_question_block') + 1;

		$(this).before("<p class=\"testme_answer\"><input type=\"text\" size=\"5\" name=\"answer_points_new[" + anum + "][]\" /> <input type=\"text\" name=\"answer_text_new[" + anum + "][]\" /><input type=\"hidden\" name=\"answer_text_style_new[" + anum + "][]\" /></p>");
		return false;
	});

	$("p.testme_add_result").click(function() {
    var rnum = $('div.testme_single_result').length + 1;
    
    $(this).before("<div class=\"testme_single_result\"><div class=\"testme_single_result_inner\"><h4>Результат " + rnum + "</h4><p>Баллы от : <input class=\"testme_min_points\" type=\"text\" size=\"3\" name=\"result_point_start_new[]\" maxlength=\"3\" /> до <input class=\"testme_max_points\" type=\"text\" size=\"3\" name=\"result_point_end_new[]\" maxlength=\"3\" /></p><h5>Заголовок результата</h5><p><input type=\"text\" name=\"result_title_new[]\" /></p></div></div>");
    return false;
	});

});
</script>