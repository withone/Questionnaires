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
<?php echo $this->Html->script(
array(
'/components/moment/min/moment.min.js',
'/components/moment/min/moment-with-locales.min.js',
),
array(
'plugin' => false,
'once' => true,
'inline' => false
)
);
?>
<?php echo $this->Html->script('Questionnaires.questionnaires_edit.js');?>
<?php echo $this->Html->script('Questionnaires.questionnaires_edit_question.js');?>

<div id="nc-questionnaires-setting-list-<?php echo (int)$frameId; ?>"
	 ng-controller="Questionnaires.edit.question"
	 ng-init="initialize(<?php echo (int)$frameId; ?>,
	 						<?php echo (int)$isPublished; ?>,
							<?php echo h(json_encode($jsQuestionnaire)); ?>)">

	<?php echo $this->Form->create('Questionnaire', array(
	'type' => 'post',
	'novalidate' => true,
	'ng-keydown' => 'handleKeydown($event)'
	)); ?>
		<?php $this->Form->unlockField('QuestionnairePage'); ?>

		<?php echo $this->Form->hidden('id'); ?>
		<?php echo $this->Form->hidden('Frame.id', array(
		'value' => $frameId,
		)); ?>
		<?php echo $this->Form->hidden('Block.id', array(
		'value' => $blockId,
		)); ?>

		<div class="modal-body">

			<?php echo $this->element('Questionnaires.edit_flow_chart', array('current' => '2')); ?>

			<div class="page-header">
			<h1><?php echo __d('questionnaires', 'Results setting'); ?></h1>
			</div>

			<div class="bg-info">
				<h2 class="questionnaire-setting-ttl">{{questionnaire.questionnaire.title}}</h2>
				<span class="help-block questionnaire-setting-ttl-help"><?php echo __d('questionnaires', 'Please edit return to create the question screen if you want to change the questionnaire title'); ?></span>
			</div>

			<div class="row form-group questionnaire-group">
				<label ><?php echo __d('questionnaires', 'Published aggregate results'); ?></label>
					<?php echo $this->Form->input('is_total_show',
							array('type' => 'radio',
							'options' => array(QuestionnairesComponent::EXPRESSION_NOT_SHOW => __d('questionnaires', 'not disclose the total result'), QuestionnairesComponent::EXPRESSION_SHOW => __d('questionnaires', 'publish aggregate result')),
							'legend' => false,
							'label' => false,
							'before' => '<div class="radio"><label>',
							'separator' => '</label></div><div class="radio"><label>',
							'after' => '</label></div>',
							'ng-model' => 'questionnaire.questionnaire.isTotalShow'
					)); ?>
			</div>

			<div ng-show="questionnaire.questionnaire.isTotalShow == <?php echo QuestionnairesComponent::EXPRESSION_SHOW; ?>">

				<div class="form-group questionnaire-group">
					<label><?php echo __d('questionnaires', 'Text to be displayed in the aggregate results page'); ?></label>
					<div class="nc-wysiwyg-alert">
						<?php echo $this->Form->textarea('Questionnaire.total_comment',
						array(
						'class' => 'form-control',
						'ng-model' => 'questionnaire.questionnaire.totalComment',
						'ui-tinymce' => 'tinymce.options',
						'rows' => 5,
						)) ?>
					</div>
				</div>

				<div class="questionnaire-group">
					<label><?php echo __d('questionnaires', 'Question you want to display the aggregate results'); ?></label>
					<accordion ng-repeat="(pageIndex, page) in questionnaire.questionnairePage">
						<accordion-group ng-repeat="(qIndex, question) in page.questionnaireQuestion" ng-class="{'panel-danger':(question.hasError)}">
							<accordion-heading>
								<span class="glyphicon"
									ng-class="{
									'glyphicon-eye-open': question.isResultDisplay == <?php echo QuestionnairesComponent::EXPRESSION_SHOW; ?>,
									'glyphicon-eye-close': question.isResultDisplay == <?php echo QuestionnairesComponent::EXPRESSION_NOT_SHOW; ?>}">
								</span>
								{{question.questionValue|htmlToPlaintext}}
								<span ng-if="question.hasError">
									<?php echo __d('questionnaires', 'There is an error'); ?>
								</span>
							</accordion-heading>

							<div class="form-group">
								<label><?php echo __d('questionnaires', 'aggregate display');?></label>
								<div class="radio"><label>
									<!-- Formヘルパー使うとAngularのrepeatとバッティングしてradioのcheckedがうまく動作しなくなる -->
									<input type="radio"
									   ng-attr-name="data[QuestionnairePage][{{pageIndex}}][QuestionnaireQuestion][{{qIndex}}][is_result_display]"
									   value="<?php echo QuestionnairesComponent::EXPRESSION_NOT_SHOW; ?>"
									   ng-model="question.isResultDisplay"
									   ng-disabled="isDisabledDisplayResult(question.questionType)"
									   />
									<?php echo __d('questionnaires', 'The results of this question will not be displayed'); ?>
								</label></div>
								<div class="radio"><label>
									<input type="radio"
										   ng-attr-name="data[QuestionnairePage][{{pageIndex}}][QuestionnaireQuestion][{{qIndex}}][is_result_display]"
										   value="<?php echo QuestionnairesComponent::EXPRESSION_SHOW; ?>"
										   ng-model="question.isResultDisplay"
										   ng-disabled="isDisabledDisplayResult(question.questionType)"
											/>
									<?php echo __d('questionnaires', 'The results of this question will be displayed'); ?>
								</label></div>
								<?php echo $this->Form->hidden(
									'QuestionnairePage.{{pageIndex}}.QuestionnaireQuestion.{{qIndex}}.is_result_display',
									array('ng-value' => 'question.isResultDisplay'));
								?>
								<?php echo $this->element(
								'Questionnaires.errors', array(
								'errorArrayName' => 'question.errorMessages.isResultDisplay',
								)); ?>
							</div>
							<div ng-show="question.isResultDisplay == <?php echo QuestionnairesComponent::EXPRESSION_SHOW; ?>">
								<div class="form-group">
									<label><?php echo __d('questionnaires', 'display type');?></label>

									<div class="radio"><label>
										<input type="radio"
											   ng-attr-name="data[QuestionnairePage][{{pageIndex}}][QuestionnaireQuestion][{{qIndex}}][result_display_type]"
											   value="<?php echo QuestionnairesComponent::RESULT_DISPLAY_TYPE_BAR_CHART; ?>"
											   ng-model="question.resultDisplayType"
												/>
										<?php echo __d('questionnaires', 'Bar Chart'); ?>
									</label></div>
									<div class="radio"><label>
										<input type="radio"
											   ng-attr-name="data[QuestionnairePage][{{pageIndex}}][QuestionnaireQuestion][{{qIndex}}][result_display_type]"
											   value="<?php echo QuestionnairesComponent::RESULT_DISPLAY_TYPE_PIE_CHART; ?>"
											   ng-model="question.resultDisplayType"
												/>
										<?php echo __d('questionnaires', 'Pie Chart'); ?>
									</label></div>
									<div class="radio"><label>
										<input type="radio"
											   ng-attr-name="data[QuestionnairePage][{{pageIndex}}][QuestionnaireQuestion][{{qIndex}}][result_display_type]"
											   value="<?php echo QuestionnairesComponent::RESULT_DISPLAY_TYPE_TABLE; ?>"
											   ng-model="question.resultDisplayType"
												/>
										<?php echo __d('questionnaires', 'Table'); ?>
									</label></div>
									<?php echo $this->Form->hidden(
									'QuestionnairePage.{{pageIndex}}.QuestionnaireQuestion.{{qIndex}}.result_display_type',
									array('ng-value' => 'question.resultDisplayType'));
									?>
								</div>
								<?php echo $this->element(
								'Questionnaires.errors', array(
								'errorArrayName' => 'question.errorMessages.resultDisplayType',
								)); ?>

								<div class="form-group" ng-show="question.resultDisplayType != <?php echo QuestionnairesComponent::RESULT_DISPLAY_TYPE_TABLE; ?>">
									<label><?php echo __d('questionnaires', 'graph color');?></label>
									<table class="table table-condensed"
										ng-show="question.questionType != <?php echo QuestionnairesComponent::TYPE_MATRIX_SELECTION_LIST; ?>
											&& question.questionType != <?php echo QuestionnairesComponent::TYPE_MATRIX_MULTIPLE; ?>">
										<tr ng-repeat="(cIndex, choice) in question.questionnaireChoice">
											<td>
												<div class="col-sm-9">
													{{choice.choiceLabel}}

													<?php echo $this->element(
													'Questionnaires.errors', array(
													'errorArrayName' => 'choice.errorMessages.graphColor',
													)); ?>

												</div>
												<div class="col-sm-3">
													<color-palette-picker
															selected='selected'
															ng-attr-name='data[QuestionnairePage][{{pageIndex}}][QuestionnaireQuestion][{{qIndex}}][QuestionnaireChoice][{{choice.choiceSequence}}][graph_color]'
															ng-model='choice.graphColor'>
													</color-palette-picker>
												</div>
											</td>
										</tr>
									</table>
									<table class="table table-condensed"
										ng-show="question.questionType == <?php echo QuestionnairesComponent::TYPE_MATRIX_SELECTION_LIST; ?>
										|| question.questionType == <?php echo QuestionnairesComponent::TYPE_MATRIX_MULTIPLE; ?>">
										<tr ng-repeat="(cIndex, choice) in question.questionnaireChoice | filter : {matrixType:<?php echo QuestionnairesComponent::MATRIX_TYPE_COLUMN; ?>}">
											<td>
												<div class="col-sm-9">
													{{choice.choiceLabel}}

													<?php echo $this->element(
													'Questionnaires.errors', array(
													'errorArrayName' => 'choice.errorMessages.graphColor',
													)); ?>

												</div>
												<div class="col-sm-3">
													<color-palette-picker
															selected='selected'
															ng-attr-name='data[QuestionnairePage][{{pageIndex}}][QuestionnaireQuestion][{{qIndex}}][QuestionnaireChoice][{{choice.choiceSequence}}][graph_color]'
															ng-model='choice.graphColor'>
													</color-palette-picker>
												</div>
											</td>
										</tr>
									</table>

								</div>
							</div>
						</accordion-group>
					</accordion>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<div class="text-center">
				<?php echo $this->BackToPage->backToPageButton(__d('net_commons', 'Cancel'), 'remove'); ?>
				<a class="btn btn-default" href="<?php echo $backUrl; ?>">
					<span class="glyphicon glyphicon-chevron-left"></span>
					<?php echo __d('net_commons', 'BACK'); ?>
				</a>
				<?php echo $this->Form->button(
				__d('net_commons', 'NEXT') . ' <span class="glyphicon glyphicon-chevron-right"></span>',
				array(
					'class' => 'btn  btn-primary',
					'type' => 'button',
					'onclick' => 'submit()',
					'name' => 'next_' . '',
				)) ?>
			</div>
		</div>
	<?php echo $this->Form->end(); ?>
</div>