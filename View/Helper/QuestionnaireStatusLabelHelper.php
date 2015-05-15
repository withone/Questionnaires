<?php
/**
 * Questionnares App Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');

/**
 * Questionnaires Status Label Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Questionnaires\View\Helper
 */
class QuestionnaireStatusLabelHelper extends AppHelper {

/**
 * Status label
 *
 * @param array $questionnaire questionnaire
 * @return string
 */
	public function statusLabel($questionnaire) {
		$status = $questionnaire['Questionnaire']['status'];
		//初期値セット
		$lblColor = 'danger';
		$lblMsg = __d('questionnaires', 'Undefined');

		if ($status == NetCommonsBlockComponent::STATUS_IN_DRAFT) {
			//一時保存中
			$lblColor = 'info';
			$lblMsg = __d('net_commons', 'Temporary');
		} elseif ($status == NetCommonsBlockComponent::STATUS_APPROVED) {
			//承認待ち
			$lblColor = 'warning';
			$lblMsg = __d('net_commons', 'Approving');
		} elseif ($status == NetCommonsBlockComponent::STATUS_DISAPPROVED) {
			//差し戻し
			$lblColor = 'danger';
			$lblMsg = __d('net_commons', 'Disapproving');
		} else {
			if ($questionnaire['Questionnaire']['period_range_stat'] == QuestionnairesComponent::QUESTIONNAIRE_PERIOD_STAT_BEFORE) {
				//未実施
				$lblColor = 'default';
				$lblMsg = __d('questionnaires', 'Before public');
			} elseif ($questionnaire['Questionnaire']['period_range_stat'] == QuestionnairesComponent::QUESTIONNAIRE_PERIOD_STAT_END) {
				//終了
				$lblColor = 'default';
				$lblMsg = __d('questionnaires', 'End');
			} else {
				$lblMsg = '';
			}
		}
		if ($lblMsg) {
			return '<span  class="label label-' . $lblColor . '">' . $lblMsg . '</span>';
		}
		return '';
	}

}