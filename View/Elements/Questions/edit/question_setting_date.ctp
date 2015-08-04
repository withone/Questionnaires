<?php
/**
 * アンケート質問の種別によって異なる詳細設定のファイル
 * このファイルでは日付け・時間入力タイプをフォローしている
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="row">
	<?php echo $this->Form->input(
	'QuestionnairePage.' . $pageIndex . '.QuestionnaireQuestion.' . $qIndex . '.question_type_option',
	array(
		'type' => 'radio',
		'before' => '<div class="col-sm-3"><div class="radio"><label>',
		'separator' => '</label></div></div><div class="col-sm-3"><div class="radio"><label>',
		'after' => '</label></div></div>',
		'options' => array(QuestionnairesComponent::TYPE_OPTION_DATE => __d('questionnaires', 'Date'),
							QuestionnairesComponent::TYPE_OPTION_TIME => __d('questionnaires', 'Time'),
							QuestionnairesComponent::TYPE_OPTION_DATE_TIME => __d('questionnaires', 'Date and Time')),
		'legend' => false,
		'label' => false,
		'ng-model' => 'question.questionTypeOption',
		'ng-click' => 'changeDatetimepickerType(pageIndex, qIndex)'
	));
	?>
	<div class="col-sm-3">
	</div>
</div>


<div class="row">
	<div class="col-sm-12">
		<div class="checkbox">
			<label>
				<?php echo $this->Form->checkbox('QuestionnairePage.' . $pageIndex . '.QuestionnaireQuestion.' . $qIndex . '.is_range',
				array(
				'value' => QuestionnairesComponent::USES_USE,
				'ng-model' => 'question.isRange',
				'ng-checked' => 'question.isRange == ' . QuestionnairesComponent::USES_USE
				));
				?>
				<?php echo __d('questionnaires', 'set range to answer date and time'); ?>
			</label>
			<?php echo $this->element(
			'Questionnaires.errors', array(
			'errorArrayName' => 'question.errorMessages.isRange',
			)); ?>
		</div>
	</div>
</div>


<div class="row">
	<div ng-show="question.isRange">
		<div class="col-sm-5">
			<?php
			echo $this->element(
			'Questionnaires.Questions/edit/question_setting_date_element', array(
			'field' => 'QuestionnairePage.' . $pageIndex . '.QuestionnaireQuestion.' . $qIndex . '.min',
			'calOpenId' => 0,
			'model' => 'question.min',
			'min' => '',
			'max' => 'question.max',
			'limitTarget' => 'QuestionnairePage.' . $pageIndex . '.QuestionnaireQuestion.' . $qIndex . '.max',
			'error' => 'question.errorMessages.min',
			));
			?>
		</div>

		<div class="col-sm-2"><p class="form-control-static text-center"><?php echo __d('questionnaires', ' - '); ?></p></div>

		<div class="col-sm-5">
			<?php
			echo $this->element(
			'Questionnaires.Questions/edit/question_setting_date_element', array(
			'field' => 'QuestionnairePage.' . $pageIndex . '.QuestionnaireQuestion.' . $qIndex . '.max',
			'calOpenId' => 1,
			'model' => 'question.max',
			'min' => 'question.min',
			'max' => '',
			'limitTarget' => 'QuestionnairePage.' . $pageIndex . '.QuestionnaireQuestion.' . $qIndex . '.min',
			'error' => 'question.errorMessages.max',
			));
			?>
		</div>
	</div>
</div>