<?php
/**
 * Questionnaire frame display setting
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php echo $this->NetCommonsForm->label(__d('questionnaires', 'select display questionnaires.')); ?>
<?php $this->NetCommonsForm->unlockField('List'); ?>

<div class="questionnaire-list-wrapper">
	<table class="table table-hover questionnaire-table-vcenter">
		<tr>
			<th>
				<?php /* echo __d('questionnaires', 'Display'); */?>
				<div class="text-center" ng-if="questionnaireFrameSettings.displayType == <?php echo QuestionnairesComponent::DISPLAY_TYPE_LIST; ?>">
					<?php $this->NetCommonsForm->unlockField('all_check'); ?>
					<?php echo $this->NetCommonsForm->checkbox('all_check', array(
					'ng-model' => 'WinBuf.allCheck',
					'ng-change' => 'allCheckClicked()',
					'label' => false,
					'div' => false,
					'class' => '',
					)); ?>
				</div>
			</th>
			<th>
				<a href="" ng-click="status=!status;sort('questionnaire.status', status)"><?php echo __d('questionnaires', 'Status'); ?></a>
			</th>
			<th>
				<a href="" ng-click="title=!title;sort('questionnaire.title', title)"><?php echo __d('questionnaires', 'Title'); ?></a>
			</th>
			<th>
				<a href="" ng-click="answerStartPeriod=!answerStartPeriod;sort('questionnaire.answerStartPeriod', answerStartPeriod)"><?php echo __d('questionnaires', 'Implementation date'); ?></a>
			</th>
			<th>
				<a href="" ng-click="isTotalShow=!isTotalShow;sort('questionnaire.isTotalShow', isTotalShow)"><?php echo __d('questionnaires', 'Aggregates'); ?></a>
			</th>
			<th>
				<a href="" ng-click="modified=!modified;sort('questionnaire.modified', modified)"><?php echo __d('questionnaires', 'Updated date'); ?></a>
			</th>
		</tr>
		<tr class="animate-repeat btn-default" ng-repeat="(index, quest) in questionnaires">
			<td>
				<div class="text-center" ng-show="questionnaireFrameSettings.displayType == <?php echo QuestionnairesComponent::DISPLAY_TYPE_LIST; ?>">
					<?php echo $this->NetCommonsForm->checkbox('List.QuestionnaireFrameDisplayQuestionnaire.{{index}}.is_display', array(
					'options' => array(true => ''),
					'label' => false,
					'div' => false,
					'class' => '',
					'ng-model' => 'isDisplay[index]'
					));
					?>
					<?php echo $this->NetCommonsForm->hidden('List.QuestionnaireFrameDisplayQuestionnaire.{{index}}.questionnaire_key', array('ng-value' => 'quest.questionnaire.key')); ?>
				</div>
				<div class="text-center"  ng-show="questionnaireFrameSettings.displayType == <?php echo QuestionnairesComponent::DISPLAY_TYPE_SINGLE; ?>">
					<?php echo $this->NetCommonsForm->radio('Single.QuestionnaireFrameDisplayQuestionnaire.questionnaire_key',
					array('{{quest.questionnaire.key}}' => ''), array(
					'legend' => false,
					'label' => false,
					'div' => false,
					'class' => false,
					'hiddenField' => false,
					'ng-model' => 'quest.questionnaireFrameDisplayQuestionnaire.questionnaireKey',
					));
					?>
				</div>
			</td>
			<td>
				<?php echo $this->element('Questionnaires.ng_status_label', array('status' => 'quest.questionnaire.status', 'periodRangeStat' => 'quest.questionnaire.periodRangeStat')); ?>
			</td>
			<td>
				<img ng-if="quest.questionnaire.titleIcon != ''" ng-src="{{quest.questionnaire.titleIcon}}" class="nc-title-icon" />
				{{quest.questionnaire.title}}
			</td>
			<td>
				<span ng-if="quest.questionnaire.answerTiming == <?php echo QuestionnairesComponent::USES_USE; ?>">
				(
					{{quest.questionnaire.answerStartPeriod | ncDatetime}}
					<?php echo __d('questionnaires', ' - '); ?>
					{{quest.questionnaire.answerEndPeriod | ncDatetime}}
					<?php echo __d('questionnaires', 'Implementation'); ?>
					)
				</span>
			</td>
			<td>
				<span ng-if="quest.questionnaire.isTotalShow == <?php echo QuestionnairesComponent::EXPRESSION_SHOW ?>">
					<?php echo __d('questionnaires', 'On'); ?>
				</span>
			</td>
			<td>
				{{quest.questionnaire.modified | ncDatetime}}
			</td>
		</tr>
	</table>
</div>

<?php echo $this->element('NetCommons.paginator');

