<?php
/**
 * Questionnares App Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//App::uses('AppHelper', 'View/Helper');

/**
 * Questionnares Utility Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Questionnaires\View\Helper
 */
class QuestionnaireUtilHelper extends AppHelper {

/**
 * __construct
 *
 * @param View $view View
 * @param array $settings 設定値
 * @return void
 */
	public function __construct(View $view, $settings = array()) {
		parent::__construct($view, $settings);
	}

/**
 * getSubTitle サブタイトル表示
 *
 * @param string $subTitle サブタイトル
 * @return string
 */
	public function getSubTitle($subTitle) {
		if (!empty($subTitle)) {
			return '<small>' . h($subTitle) . '</small>';
		}
		return '';
	}

/**
 * transformPeriodYmd システム既定の日時文字列を/区切りに変更する
 *
 * @param string $period 日時表示文字列
 * @return string
 */
	public function transformPeriodYmd($period) {
		if (empty($period)) {
			return str_repeat('&nbsp;', 10); // YYYY/MM/DD
		}
		$year = date('Y');
		if (strncmp($year, $period, 4) == 0) {
			return str_replace('-', '/', substr($period, 5, strpos($period, ' ')));
		} else {
			return str_replace('-', '/', substr($period, 0, strpos($period, ' ')));
		}
	}

/**
 * transformPeriodYmdhi システム既定の日時文字列を年月日時分に変更する
 *
 * @param string $period 日時表示文字列
 * @return string
 */
	public function transformPeriodYmdhi($period) {
		$period = substr($period, 0, strrpos($period, ':'));
		if (empty($period)) {
			return str_repeat('&nbsp;', 16); // YYYY/MM/DD HH:ii
		}
		if (strstr(date('Y-m-d'), $period)) {
			return substr($period, strpos($period, ' '));
		} elseif (strncmp(date('Y'), $period, 4) == 0) {
			return str_replace('-', '/', substr($period, 5));
		} else {
			return str_replace('-', '/', substr($period, 0));
		}
	}

/**
 * getAnswerButtons 回答済み 回答する テストのボタン表示
 *
 * @param string $frameId フレームID
 * @param array $questionnaire 回答データ
 * @return string
 */
	public function getAnswerButtons($frameId, $questionnaire) {
		//
		//回答ボタンの(回答済み|回答する|テスト)の決定
		//
		// satus != 公開状態 つまり編集者が見ている場合は「テスト」
		//
		// 公開状態の場合が枝分かれする
		// 公開時期にマッチしていない = 回答前＝回答する（disabled） 回答後＝回答済み（disabled）
		//
		// 公開期間中
		// 繰り返しの回答を許さない = 回答前＝回答する　回答後＝回答済み（Disabled）
		// 繰り返しの回答を許す = いずれの状態でも「回答する」

		$id = $questionnaire['Questionnaire']['origin_id'];

		// 編集権限がない人が閲覧しているとき、未公開アンケートはFindされていないので対策する必要はない
		// ボタン表示ができるかできないか
		// 編集権限がないのに公開状態じゃないアンケートの場合はボタンを表示しない
		//
		//if ($questionnaire['Questionnaire']['status'] != NetCommonsBlockComponent::STATUS_PUBLISHED && !$editable) {
		//	return '';
		//}

		$buttonStr = '<a class="btn btn-%s questionnaire-listbtn %s" href="/questionnaires/questionnaire_answers/answer/%d/%d/">%s</a>';

		// ボタンの色
		// ボタンのラベル
		if ($questionnaire['Questionnaire']['status'] != NetCommonsBlockComponent::STATUS_PUBLISHED) {
			$answerButtonClass = 'info';
			$answerButtonLabel = __d('questionnaires', 'Test');
			return sprintf($buttonStr, $answerButtonClass, '', $frameId, $id, $answerButtonLabel);
		}

		// 操作できるかできないか

		// 何事もなければ回答可能のボタン
		$answerButtonLabel = __d('questionnaires', 'Answer');
		$answerButtonClass = 'success';
		$answerButtonDisabled = '';

		// 期間外だったら操作不可能
		// 繰り返し回答不可で回答済なら操作不可能
		if ($questionnaire['Questionnaire']['period_range_stat'] != QuestionnairesComponent::QUESTIONNAIRE_PERIOD_STAT_IN
			|| (isset($answerCounts[$id]['ansCnt']) && $answerCounts[$id]['ansCnt'] > 0
				&& $questionnaire['Questionnaire']['is_repeat_allow'] == QuestionnairesComponent::PERMISSION_NOT_PERMIT)) {
			$answerButtonClass = 'default';
			$answerButtonDisabled = 'disabled';
		}

		if ($questionnaire['Questionnaire']['period_range_stat'] == QuestionnairesComponent::QUESTIONNAIRE_PERIOD_STAT_BEFORE) {
			// 未公開
			$answerButtonLabel = __d('questionnaires', 'Unpublished');
		}
		if (isset($answerCounts[$id]['ansCnt']) && $answerCounts[$id]['ansCnt'] > 0) {
			// 回答済み
			$answerButtonLabel = __d('questionnaires', 'Finished');
		}
		return sprintf($buttonStr, $answerButtonClass, '', $frameId, $id, $answerButtonLabel);
	}

/**
 * getAggregateButtons 集計のボタン表示
 *
 * @param string $frameId フレームID
 * @param array $questionnaire 回答データ
 * @return string
 */
	public function getAggregateButtons($frameId, $questionnaire) {
		//
		// 集計ボタン
		// 集計表示しない＝ボタン自体ださない
		// 集計表示する＝回答すみ、または回答期間終了　集計ボタン
		// 　　　　　　　アンケート自体が公開状態にない(not editor)
		//			  未回答＆回答期間内　　　　　　　集計ボタン（disabled）
		$id = $questionnaire['Questionnaire']['origin_id'];

		if ($questionnaire['Questionnaire']['is_total_show'] == QuestionnairesComponent::EXPRESSION_NOT_SHOW) {
			return '';
		}
		// 編集権限がない人が閲覧しているとき、未公開アンケートはFindされていないので対策する必要はない
		//if ($questionnaire['Questionnaire']['status'] != NetCommonsBlockComponent::STATUS_PUBLISHED) {
		//	if (!$editable) {
		//		return '';
		//	} else {
		//		$disabled = '';
		//	}

		$disabled = '';

		// アンケート本体が始まってない
		$nowTime = time();
		if ($questionnaire['Questionnaire']['period_range_stat'] == QuestionnairesComponent::QUESTIONNAIRE_PERIOD_STAT_BEFORE) {
			$disabled = 'disabled';
		} else {
			// 始まっている
			// 集計結果公開期間外である
			if (isset($questionnaire['Questionnaire']['total_show_start_period']) &&
				$nowTime < strtotime($questionnaire['Questionnaire']['total_show_start_period'])) {
				$disabled = 'disabled';
			} else {
				// 集計結果公開期間内である
				// 一つでも回答している
				if (isset($questionnaire['CountAnswerSummary']['answer_summary_count']) && $questionnaire['CountAnswerSummary']['answer_summary_count'] > 0) {
					$disabled = '';
				} else {
					// 未回答
					$disabled = 'disabled';
				}
			}
		}

		$html = <<<EOL
<a class="btn btn-default questionnaire-listbtn {$disabled}" href="/questionnaires/questionnaire_answer_summaries/result/$frameId/$id/">
<span class="glyphicon glyphicon-stats" aria-hidden="true"></span>
</a>
EOL;
		return $html;
	}

}
