<?php
/**
 * QuestionnaireAnswer::validate()のテスト
 *
 * @property QuestionnaireAnswer $QuestionnaireAnswer
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('QuestionnaireAnswerValidateTest', 'Questionnaires.TestSuite');
App::uses('QuestionnairesComponent', 'Questionnaires.Controller/Component');
App::uses('QuestionnaireQuestionFixture', 'Questionnaires.Test/Fixture');
App::uses('QuestionnaireChoiceFixture', 'Questionnaires.Test/Fixture');

/**
 * QuestionnaireAnswer::validate()のテスト
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Questionnaires\Test\Case\Model\QuestionnaireAnswer
 */
class ValidateAnswerSingleMatrixTest extends QuestionnaireAnswerValidateTest {

/**
 * __getData
 *
 * @param string $qKey 質問キー
 * @param int $summaryId サマリID
 * @return array
 */
	private function __getData($qKey, $summaryId) {
		$answerData = array(
			array(
				'questionnaire_answer_summary_id' => $summaryId,
				'questionnaire_question_key' => $qKey,
				'matrix_choice_key' => 'choice_9',
				'id' => '',
				'other_answer_value' => '',
				'answer_value' => '|choice_11:choice label11',
			),
			array(
				'questionnaire_answer_summary_id' => $summaryId,
				'questionnaire_question_key' => $qKey,
				'matrix_choice_key' => 'choice_10',
				'id' => '',
				'other_answer_value' => '',
				'answer_value' => '|choice_12:choice label12',
			)
		);
		return $answerData;
	}

/**
 * testValidationErrorのDataProvider
 *
 * #### 戻り値
 *  - array 取得するキー情報
 *  - array 期待値 （取得したキー情報）
 *
 * @return array
 */
	public function dataProviderValidationError() {
		$data = $this->__getData('qKey_9', 4);
		// 通常の質問
		$normalQuestion = $this->_getQuestion(10);
		// 解答必須質問
		$requireQuestion = Hash::merge($normalQuestion, array('is_require' => QuestionnairesComponent::REQUIRES_REQUIRE));
		// その他回答がある質問
		$otherQuestion = Hash::merge($normalQuestion, array('QuestionnaireChoice' => array(array('other_choice_type' => 1))));

		$extraAnswer = array(
			'questionnaire_answer_summary_id' => 4,
			'questionnaire_question_key' => 'qKey_9',
			'matrix_choice_key' => 'choice_1000',
			'id' => '',
			'other_answer_value' => '',
			'answer_value' => '|choice_12:choice label12',
		);

		return array(
			// 選択肢にないものを答える
			array(Hash::merge($data, array(array('answer_value' => 'aaa'))), 3, $normalQuestion, null, null,
				__d('questionnaires', 'Invalid choice')),
			array(Hash::merge($data, array(2 => $extraAnswer)), 3, $normalQuestion, null, null,
				__d('questionnaires', 'Invalid choice')),
			// 必須回答なのに回答なし
			array(Hash::merge($data, array(array('answer_value' => ''), array('answer_value' => ''))), 3, $requireQuestion, null, null,
				__d('questionnaires', 'Input required')),
			// その他を書かない
			array(Hash::merge($data, array()), 3, $otherQuestion, null, null,
				__d('questionnaires', 'Please enter something in other item')),
			// マトリクスなのに1つだけ答えてあと放置
			array(Hash::merge($data, array(array('answer_value' => ''))), 3, $normalQuestion, null, null,
				__d('questionnaires', 'Please answer about all rows.')),
		);
	}
}
