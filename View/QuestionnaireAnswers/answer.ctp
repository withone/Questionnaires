<?php
/**
 * questionnaire page setting view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php echo $this->element('Questionnaires.scripts'); ?>

<article id="nc-questionnaires-answer-<?php echo (int)$frameId; ?>"
		 ng-controller="QuestionnairesAnswer">

	<?php echo $this->element('Questionnaires.Answers/answer_test_mode_header'); ?>


	<header>
		<h1>
			<?php echo $questionnaire['Questionnaire']['title']; ?>
			<?php if (isset($questionnaire['Questionnaire']['sub_title'])): ?>
			<small><?php echo $questionnaire['Questionnaire']['sub_title'];?></small>
			<?php endif ?>
		</h1>
	</header>


	<?php if ($questionPage['page_sequence'] > 0): ?>
	<?php $progress = round((($questionPage['page_sequence']) / count($questionnaire['QuestionnairePage'])) * 100); ?>
	<div class="row">
		<div class="col-sm-8">
		</div>
		<div class="col-sm-4">
			<div class="progress">
				<progressbar class="progress-striped" value="<?php echo $progress ?>" type="warning"><?php echo $progress ?>%</progressbar>
			</div>
		</div>
	</div>
	<?php endif ?>

	<?php if (count($errors) > 0): ?>
		<div class="alert alert-danger" role="alert"><?php echo __d('questionnaires', 'Error occurred. Please check your answer.'); ?></div>
	<?php endif ?>

	<?php echo $this->Form->create('QuestionnaireAnswer', array(
	'name' => 'questionnaire_form_answer',
	'type' => 'post',
	'novalidate' => true,
	)); ?>
	<?php echo $this->Form->hidden('Frame.id', array('value' => $frameId)); ?>
	<?php echo $this->Form->hidden('Block.id', array('value' => $blockId)); ?>
	<?php echo $this->Form->hidden('Questionnaire.id', array('value' => $questionnaire['Questionnaire']['id'])); ?>
	<?php echo $this->Form->hidden('QuestionnairePage.id', array('value' => $questionPage['id'])); ?>
	<?php echo $this->Form->hidden('Questionnaire.origin_id', array('value' => $questionnaire['Questionnaire']['origin_id'])); ?>
	<?php echo $this->Form->hidden('QuestionnairePage.origin_id', array('value' => $questionPage['origin_id'])); ?>
	<?php echo $this->Form->hidden('QuestionnairePage.page_sequence', array('value' => $questionPage['page_sequence'])); ?>


	<?php foreach($questionPage['QuestionnaireQuestion'] as $index => $question): ?>
		<div class="form-group
							<?php if (isset($errors[$question['origin_id']]['answer_value'])): ?>
							has-error
							<?php endif ?>">
			<?php if ($question['is_require'] == QuestionnairesComponent::REQUIRES_REQUIRE): ?>
			<div class="pull-left">
				<?php echo $this->element('NetCommons.required'); ?>
			</div>
			<?php endif ?>
			<label class="control-label">
				<?php echo $question['question_value']; ?>
			</label>
			<div class="help-block">
				<?php echo $question['description']; ?>
			</div>
			<?php if ($question['question_type'] == QuestionnairesComponent::TYPE_TEXT): ?>
			<?php $elementName = 'Questionnaires.Answers/question_text'; ?>
			<?php elseif ($question['question_type'] == QuestionnairesComponent::TYPE_SELECTION): ?>
			<?php $elementName = 'Questionnaires.Answers/question_selection'; ?>
			<?php elseif ($question['question_type'] == QuestionnairesComponent::TYPE_MULTIPLE_SELECTION): ?>
			<?php $elementName = 'Questionnaires.Answers/question_multiple_selection'; ?>
			<?php elseif ($question['question_type'] == QuestionnairesComponent::TYPE_TEXT_AREA): ?>
			<?php $elementName = 'Questionnaires.Answers/question_text_area'; ?>
			<?php elseif ($question['question_type'] == QuestionnairesComponent::TYPE_MATRIX_SELECTION_LIST): ?>
			<?php $elementName = 'Questionnaires.Answers/question_matrix_selection_list'; ?>
			<?php elseif ($question['question_type'] == QuestionnairesComponent::TYPE_MATRIX_MULTIPLE): ?>
			<?php $elementName = 'Questionnaires.Answers/question_matrix_multiple'; ?>
			<?php elseif ($question['question_type'] == QuestionnairesComponent::TYPE_DATE_AND_TIME): ?>
			<?php $elementName = 'Questionnaires.Answers/question_date_and_time'; ?>
			<?php elseif ($question['question_type'] == QuestionnairesComponent::TYPE_SINGLE_SELECT_BOX): ?>
			<?php $elementName = 'Questionnaires.Answers/question_single_select_box'; ?>
			<?php endif ?>

			<?php echo $this->element($elementName,
			array('index' => $question['origin_id'],
			'question' => $question,
			'answer' => isset($answers[$question['origin_id']]) ? $answers[$question['origin_id']] : null,
			'readonly' => false)); ?>

			<?php echo $this->Form->hidden('QuestionnaireAnswer.' . $question['origin_id'] . '.questionnaire_question_origin_id', array(
			'value' => $question['origin_id']
			));?>
			<?php if (isset($answers[$question['origin_id']]['questionnaire_answer_summary_id'])): ?>
				<?php echo $this->Form->hidden('QuestionnaireAnswer.' . $question['origin_id'] . '.questionnaire_answer_summary_id', array(
				'value' => $answers[$question['origin_id']]['questionnaire_answer_summary_id']
				));?>
			<?php endif; ?>
			<?php if ($question['question_type'] != QuestionnairesComponent::TYPE_MATRIX_SELECTION_LIST && $question['question_type'] != QuestionnairesComponent::TYPE_MATRIX_MULTIPLE): ?>
			<?php echo $this->Form->hidden('QuestionnaireAnswer.' . $question['origin_id'] . '.id', array(
			'value' => isset($answers[$question['origin_id']][0]['id']) ? $answers[$question['origin_id']][0]['id'] : null,
			));?>
			<?php endif ?>


			<?php if (isset($errors[$question['origin_id']]['answer_value'])): ?>
				<?php foreach ($errors[$question['origin_id']]['answer_value'] as $message): ?>
					<div class="has-error">
						<div class="help-block">
							<?php echo $message ?>
						</div>
					</div>
				<?php endforeach ?>
			<?php endif ?>
		</div>
	<?php endforeach; ?>





	<div class="text-center">
		<?php echo $this->Form->button(
		__d('net_commons', 'NEXT') . ' <span class="glyphicon glyphicon-chevron-right"></span>',
		array(
		'class' => 'btn btn-primary',
		'name' => 'next_' . '',
		)) ?>
	</div>
	<?php echo $this->Form->end(); ?>

</article>
                                                                                                                               