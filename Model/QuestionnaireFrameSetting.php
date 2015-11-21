<?php
/**
 * QuestionnaireFrameSetting Model
 *
 * @property Frame $Frame
 * @property QuestionnaireFrameDisplayQuestionnaire $QuestionnaireFrameDisplayQuestionnaire
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('QuestionnairesAppModel', 'Questionnaires.Model');

/**
 * Summary for QuestionnaireFrameSetting Model
 */
class QuestionnaireFrameSetting extends QuestionnairesAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Frame' => array(
			'className' => 'Frames.Frame',
			'foreignKey' => 'frame_key',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'frame_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					//'message' => 'Your custom message here',
					'allowEmpty' => false,
					//'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
				),
			),
			'display_type' => array(
				'inList' => array(
					'rule' => array('inList', array(QuestionnairesComponent::DISPLAY_TYPE_SINGLE, QuestionnairesComponent::DISPLAY_TYPE_LIST)),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'display_num_per_page' => array(
				'inList' => array(
					'rule' => array('inList', array_keys(QuestionnairesComponent::getDisplayNumberOptions())),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'sort_type' => array(
				'inList' => array(
					'rule' => array('inList', array_keys(QuestionnairesComponent::getSortOrders())),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));

		parent::beforeValidate($options);

		return true;
	}

/**
 * getQuestionnaireFrameSettingConditions 指定されたframe_keyの設定要件をSQL検索用の配列で取り出す
 *
 * @param string $frameKey frame key
 * @return array ... displayNum sortField sortDirection
 */
	public function getQuestionnaireFrameSettingConditions($frameKey) {
		list(, $limit, $sort, $dir) = $this->getQuestionnaireFrameSetting($frameKey);
		return array(
			'offset' => 0,
			'limit' => $limit,
			'order' => 'Questionnaire.' . $sort . ' ' . $dir);
	}
/**
 * getFrameSetting 指定されたframe_keyの設定要件を取り出す
 *
 * @param string $frameKey frame key
 * @return array ... displayNum sortField sortDirection
 */
	public function getQuestionnaireFrameSetting($frameKey) {
		$frameSetting = $this->find('first', array(
			'conditions' => array(
				'frame_key' => $frameKey
			),
			'recursive' => -1
		));

		if (!$frameSetting) {
			$frameSetting = $this->prepareFrameSetting($frameKey);
		}

		$setting = $frameSetting['QuestionnaireFrameSetting'];
		$displayType = $setting['display_type'];
		$displayNum = $setting['display_num_per_page'];
		if ($setting['sort_type'] == QuestionnairesComponent::QUESTIONNAIRE_SORT_MODIFIED) {
			$sort = 'modified';
			$dir = 'DESC';
		} elseif ($setting['sort_type'] == QuestionnairesComponent::QUESTIONNAIRE_SORT_CREATED) {
			$sort = 'created';
			$dir = 'DESC';
		} elseif ($setting['sort_type'] == QuestionnairesComponent::QUESTIONNAIRE_SORT_TITLE) {
			$sort = 'title';
			$dir = 'ASC';
		} elseif ($setting['sort_type'] == QuestionnairesComponent::QUESTIONNAIRE_SORT_END) {
			$sort = 'end_period';
			$dir = 'ASC';
		}
		return array($displayType, $displayNum, $sort, $dir);
	}

/**
 * getDefaultFrameSetting
 * return default frame setting
 *
 * @return array
 */
	public function getDefaultFrameSetting() {
		$frame = array(
			'QuestionnaireFrameSetting' => array(
				'id' => '',
				'display_type' => QuestionnairesComponent::DISPLAY_TYPE_LIST,
				'display_num_per_page' => QuestionnairesComponent::QUESTIONNAIRE_DEFAULT_DISPLAY_NUM_PER_PAGE,
				'sort_type' => QuestionnairesComponent::DISPLAY_SORT_TYPE_NEW_ARRIVALS,
			)
		);
		return $frame;
	}

/**
 * prepareFrameSetting
 *
 * @param string $frameKey frame key
 * @return mix
 * @throws Exception
 * @throws InternalErrorException
 */
	public function prepareFrameSetting($frameKey) {
		$frameSetting = $this->getDefaultFrameSetting();
		$this->saveFrameSettings($frameKey, $frameSetting);
		$ret = $this->find('first', array(
			'conditions' => array(
				'frame_key' => $frameKey
			),
			'recursive' => -1
		));
		return $ret;
	}

/**
 * saveFrameSettings
 *
 * @param string $frameKey frame key
 * @param array $data save data
 * @return bool
 * @throws InternalErrorException
 */
	public function saveFrameSettings($frameKey, $data) {
		$this->loadModels([
			'QuestionnaireFrameDisplayQuestionnaire' => 'Questionnaires.QuestionnaireFrameDisplayQuestionnaire',
		]);

		//トランザクションBegin
		$this->begin();
		try {
			$data['QuestionnaireFrameSetting']['frame_key'] = $frameKey;

			// フレーム設定のバリデート
			$this->create();
			$this->set($data);
			if (! $this->validates()) {
				return false;
			}

			// フレームに表示するアンケート一覧設定のバリデート
			// 一覧表示タイプと単独表示タイプ
			if (isset($data['QuestionnaireFrameDisplayQuestionnaires'])) {
				$ret = $this->QuestionnaireFrameDisplayQuestionnaire->validates($data);
				if ($ret === false) {
					return false;
				}
			}
			/*
			if ($data['QuestionnaireFrameSetting']['display_type'] == QuestionnairesComponent::DISPLAY_TYPE_LIST) {
				$displayQs = $data['QuestionnaireFrameDisplayQuestionnaires']['List']['questionnaire_key'];
				$ret = $this->QuestionnaireFrameDisplayQuestionnaire->validateDisplayQuestionnaireForList($frameKey, $displayQs);
			} else {
				$displayQs = $data['QuestionnaireFrameDisplayQuestionnaires']['Single']['questionnaire_key'];
				$ret = $this->QuestionnaireFrameDisplayQuestionnaire->validateDisplayQuestionnaireForSingle($frameKey, $displayQs);
			}
			if ($ret == false) {
				$this->validationErrors = Hash::merge($this->validationErrors, $this->QuestionnaireFrameDisplayQuestionnaire->validationErrors);
				return false;
			}
			*/
			// フレーム設定の登録
			if (! $this->save($data, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			// フレームに表示するアンケート一覧設定の登録
			// 一覧表示タイプと単独表示タイプ
			if (isset($data['QuestionnaireFrameDisplayQuestionnaires'])) {
				$ret = $this->QuestionnaireFrameDisplayQuestionnaire->saveMany($data['QuestionnaireFrameDisplayQuestionnaires']);
				if ($ret === false) {
					return false;
				}
			}
			/*
			if ($data['QuestionnaireFrameSetting']['display_type'] == QuestionnairesComponent::DISPLAY_TYPE_LIST) {
				$displayQs = $data['QuestionnaireFrameDisplayQuestionnaires']['List']['questionnaire_key'];
				$ret = $this->QuestionnaireFrameDisplayQuestionnaire->saveDisplayQuestionnaireForList($frameKey, $displayQs);
			} else {
				$displayQs = $data['QuestionnaireFrameDisplayQuestionnaires']['Single']['questionnaire_key'];
				$ret = $this->QuestionnaireFrameDisplayQuestionnaire->saveDisplayQuestionnaireForSingle($frameKey, $displayQs);
			}
			if ($ret == false) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			*/

			//トランザクションCommit
			$this->commit();
		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback();
			CakeLog::error($ex);
			throw $ex;
		}

		return true;
	}
}
