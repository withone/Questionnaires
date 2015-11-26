<?php
/**
 * QuestionnaireBlocksController
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryo Ozawa <ozawa.ryo@withone.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * BlocksController
 *
 * @author Ryo Ozawa <ozawa.ryo@withone.co.jp>
 * @package NetCommons\Questionnaires\Controller
 */
class QuestionnaireBlocksController extends QuestionnairesAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';

/**
 * use models
 *
 * @var array
 */
	public $uses = array(
		'Questionnaires.Questionnaire',
		'Questionnaires.QuestionnaireFrameSetting',
		'Questionnaires.QuestionnairePage',
		'Questionnaires.QuestionnaireQuestion',
		'Questionnaires.QuestionnaireChoice',
		'Questionnaires.QuestionnaireAnswerSummary',
		'Questionnaires.QuestionnaireAnswerSummaryCsv',
	);

/**
 * use components
 *
 * @var array
 */
	public $components = array(
		'Blocks.BlockTabs' => array(
			'mainTabs' => array('block_index'),
			'blockTabs' => array('block_settings', 'role_permissions'),
		),
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'index,add,edit,delete' => 'block_editable',
			),
		),
		'Questionnaires.QuestionnairesDownload',
		'Questionnaires.QuestionnairesWysIsWyg',
		'Paginator',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Session',
		'NetCommons.NetCommonsForm',
		'NetCommons.Date',
	);

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny('index');
	}

/**
 * index
 *
 * @return void
 * @throws Exception
 */
	public function index() {
		// 条件設定値取得
		// 条件設定値取得
		$conditions = $this->Questionnaire->getCondition();

		// データ取得
		// Modelの方ではカスタムfindメソッドを装備している
		// ここではtype属性を指定することでカスタムFindを呼び出すように指示している
		$subQuery = $this->Questionnaire->getQuestionnaireSubQuery();
		$this->paginate = array(
			'conditions' => $conditions,
			'page' => 1,
			'sort' => QuestionnairesComponent::DISPLAY_SORT_TYPE_NEW_ARRIVALS,
			'limit' => QuestionnairesComponent::QUESTIONNAIRE_DEFAULT_DISPLAY_NUM_PER_PAGE,
			'direction' => 'desc',
			'recursive' => 0,
			'joins' => $subQuery,
		);
		$questionnaire = $this->paginate('Questionnaire');
		if (! $questionnaire) {
			// FUJI
		}
		$this->set('questionnaires', $questionnaire);
	}

/**
 * download
 *
 * @param int $questionnaireKey questionnaire Key
 * @return void
 */
	public function download($questionnaireKey) {
		// viewを使用しない
		$this->autoRender = false;

		try {
			$questionnaire = $this->_getQuestionnaireForAnswerCsv($questionnaireKey);

			// テンポラリフォルダ作成とカレントディレクトリ変更
			$folder = $this->QuestionnairesDownload->createTemporaryFolder($this);
			// ダウンロードファイル作成とオープン
			$fileName = date('Ymd_his') . '.csv';
			$fp = $this->QuestionnairesDownload->createDownloadFile($folder, $fileName);

			// 回答データを一気に全部取得するのは、データ爆発の可能性があるので
			// QUESTIONNAIRE_CSV_UNIT_NUMBER分に制限して取得する
			$offset = 0;
			do {
				$datas = $this->QuestionnaireAnswerSummaryCsv->getAnswerSummaryCsv($questionnaire, QuestionnairesComponent::QUESTIONNAIRE_CSV_UNIT_NUMBER, $offset);
				// テンポラリファイルにCSV形式で書きこみ
				foreach ($datas as $data) {
					fputcsv($fp, $data);
				}
				$dataCount = count($datas);	// データ数カウント
				$offset += $dataCount;		// 次の取得開始位置をずらす
			} while ($dataCount == QuestionnairesComponent::QUESTIONNAIRE_CSV_UNIT_NUMBER);
			// データ取得数が制限値分だけとれている間は繰り返す

			// ファイルクローズ
			fclose($fp);

			// 暗号圧縮？現時点ではコマンドでしか実行できない
			$filePath = $this->QuestionnairesDownload->compressFile($this->Auth->user('username'));
		} catch (Exception $e) {
			$this->Session->setFlash(__d('questionnaires', 'download error'));
			$this->redirect('/questionnaires/questionnaire_blocks/index/?frame_id=' . Current::read('Frame.id'));
			return false;
		}
		// ダウンロードファイル名
		$downloadFileName = $questionnaire['Questionnaire']['title'] . '.' . $this->QuestionnairesDownload->getDownloadFileExtension();
		// 出力
		$this->response->file($filePath, array('download' => true, 'name' => rawurlencode($downloadFileName)));

		return $this->response;
	}

/**
 * export
 *
 * template file about questionnaire export action
 *
 * @param int $frameId frame id
 * @param int $questionnaireId questionnaire origin id
 * @return void
 */
	public function export($frameId, $questionnaireId) {
		// viewを使用しない
		$this->autoRender = false;

		try {
			// テンポラリフォルダとカレントディレクトリ変更
			$folder = $this->QuestionnairesDownload->createTemporaryFolder($this);

			// ダウンロード用のZIPアーカイブ作成,オープン
			$zip = $this->QuestionnairesDownload->createDownloadZipFile($folder, QuestionnairesComponent::QUESTIONNAIRE_TEMPLATE_EXPORT_FILENAME);

			// バージョン情報を取得するためComposer情報を得る
			$composer = $this->Plugin->getComposer('netcommons/questionnaires');

			// アンケートデータZIPファイルを取得
			$questionnaireZipFile = $this->_createQuestionnaireZip($folder, $composer['version'], $questionnaireId);

			// アンケートデータZIPファイルをアーカイブに加える
			$zip->addFile($questionnaireZipFile, QuestionnairesComponent::QUESTIONNAIRE_TEMPLATE_FILENAME);

			// アンケートデータファイルのフィンガープリントを得る
			$fingerPrint = sha1_file($questionnaireZipFile, false);

			// フィンガープリントをアーカイブに加える
			$zip->addFromString(QuestionnairesComponent::QUESTIONNAIRE_FINGER_PRINT_FILENAME, $fingerPrint);

			// アーカイブ閉じる
			$zip->close();

			// アンケートデータファイルを削除（アーカイブを閉じてからでないと削除はエラーになる）
			unlink($questionnaireZipFile);

			// ダウンロード用ファイル名取得
			$questionnaire = $this->_getQuestionnaireForExport($questionnaireId);
			$questionnaireTitle = $questionnaire['Questionnaire']['title'];

			// ダウンロード出力ファイル名確定
			$exportFilePath = $this->QuestionnairesDownload->getDownloadFilePath();
			$exportFileName = $questionnaireTitle . '.zip';

		} catch(Exception $e) {
			$this->Session->setFlash(__d('questionnaires', 'export error') . $e->getMessage());
			$this->redirect('/questionnaires/questionnaire_blocks/index/' . $this->viewVars['frameId']);
			return false;
		}
		// export-key 設定
		$this->Questionnaire->saveExportKey($questionnaire['Questionnaire']['id'], $fingerPrint);

		// 出力
		$this->response->file($exportFilePath, array('download' => true, 'name' => rawurlencode($exportFileName)));
		return $this->response;
	}
/**
 * _createQuestionnaireZip
 *
 * @param Folder $folder folder object
 * @param string $version version
 * @param int $questionnaireId questionnaire origin_id
 * @return string zip file path
 */
	protected function _createQuestionnaireZip($folder, $version, $questionnaireId) {
		// アンケートデータをjsonにして記述した内容を含むZIPファイルを作成する
		$questionnaires = array();
		$zipData = array();
		$zipData['version'] = $version;

		// ZIPファイル作成＆オープン
		$qZipFilePath = $folder->pwd() . DS . QuestionnairesComponent::QUESTIONNAIRE_TEMPLATE_FILENAME;
		$zip = new ZipArchive();
		$ret = $zip->open($qZipFilePath, ZipArchive::CREATE);
		if ($ret === false) {
			return false;
		}

		// 言語数分
		$langs = array(
			'english' => 1,
			'japanese' => 0,
			'chinese' => 3
		);
		foreach ($langs as $langId) {
			// 指定のアンケートデータを取得
			$questionnaire = $this->Questionnaire->find('first', array(
				'conditions' => array(
					'Questionnaire.origin_id' => $questionnaireId,
					'Questionnaire.is_active' => true,
					'Questionnaire.is_latest' => true,
					'Questionnaire.language_id' => $langId
				),
				'recursive' => -1
			));
			// 指定の言語データがない場合もあることを想定
			if (empty($questionnaire)) {
				continue;
			}
			// アンケートデータの中でもWYSISWYGデータのものについては
			// フォルダ別に確保(フォルダの中にZIPがある）
			$this->_createWysIsWygZIP($folder, $zip, $questionnaire);
			$questionnaires[] = $questionnaire;
		}
		$zipData['Questionnaires'] = $questionnaires;
		// jsonデータにして書き込み
		$zip->addFromString(QuestionnairesComponent::QUESTIONNAIRE_JSON_FILENAME, json_encode($zipData));
		// アーカイブ閉じる
		$zip->close();

		return $qZipFilePath;
	}
/**
 * _getQuestionnaireForExport
 *
 * @param int $questionnaireId questionnaire origin_id
 * @throws NotFoundException
 * @return array questionnaire data
 */
	protected function _getQuestionnaireForExport($questionnaireId) {
		// 指定のアンケートデータを取得
		$questionnaire = $this->Questionnaire->find('first', array(
			'conditions' => array(
				'Questionnaire.origin_id' => $questionnaireId,
				'Questionnaire.is_active' => true,
				'Questionnaire.is_latest' => true,
			),
			'recursive' => -1
		));
		if (!$questionnaire) {
			throw new NotFoundException();
		}
		return $questionnaire;
	}
/**
 * _getQuestionnaireForAnswerCsv
 *
 * @param int $questionnaireId questionnaire key
 * @throws NotFoundException
 * @return array questionnaire data
 */
	protected function _getQuestionnaireForAnswerCsv($questionnaireId) {
		// 指定のアンケートデータを取得
		$questionnaire = $this->Questionnaire->find('first', array(
			'conditions' => array(
				'Questionnaire.key' => $questionnaireId,
				'Questionnaire.is_active' => true,
			),
			'recursive' => -1
		));
		if (!$questionnaire) {
			throw new NotFoundException();
		}
		return $questionnaire;
	}
/**
 * _createWysIsWygZIP
 *
 * @param Folder $folder フォルダオブジェクト
 * @param ZipArchive $zip zipファイルオブジェクト
 * @param array &$questionnaire questionnaire data
 * @return void
 */
	protected function _createWysIsWygZIP($folder, $zip, &$questionnaire) {
		// アンケートデータの中でもWYSISWYGデータのものについては
		// フォルダ別に確保(フォルダの中にZIPがある）
		$flatQuestionnaire = Hash::flatten($questionnaire);
		foreach ($flatQuestionnaire as $key => &$value) {
			$model = null;
			if (strpos($key, 'QuestionnaireQuestion.') !== false) {
				$model = $this->QuestionnaireQuestion;
			} elseif (strpos($key, 'QuestionnairePage.') !== false) {
				$model = $this->QuestionnairePage;
			} elseif (strpos($key, 'Questionnaire.') !== false) {
				$model = $this->Questionnaire;
			}
			if (!$model) {
				continue;
			}
			$columnName = substr($key, strrpos($key, '.') + 1);
			if ($model->hasField($columnName)) {
				if ($model->getColumnType($columnName) == 'text') {
					$zip->addEmptyDir($key);
					$wysiswygZipFile = $this->QuestionnairesWysIsWyg->createWysIsWygZIP($folder, $key, $value);
					$zip->addFile($wysiswygZipFile, $key . DS . $key . '.zip');
					$value = $key;
				}
			}
		}
		$questionnaire = Hash::expand($flatQuestionnaire);
	}
/**
 * initTabs
 *
 * @param string $activeTab Block active tab
 * @return void
 */
	public function initTabs($activeTab) {
		$block = $this->Block->find('first', array(
			'conditions' => array(
				'Block.room_id' => $this->viewVars['roomId'],
				'Block.plugin_key' => 'questionnaires'
			)
		));
		if (isset($block['Block']['id'])) {
			$blockId = (int)$block['Block']['id'];
		} else {
			$blockId = null;
		}

		//タブの設定
		$settingTabs = array(
			'tabs' => array(
				'block_index' => array(
					'url' => array(
						'plugin' => $this->params['plugin'],
						'controller' => 'questionnaire_blocks',
						'action' => 'index',
						$this->viewVars['frameId'],
					)
				),
				'frame_settings' => array(
					'url' => array(
						'plugin' => $this->params['plugin'],
						'controller' => 'questionnaire_frame_settings',
						'action' => 'edit',
						$this->viewVars['frameId'],
						$blockId
					)
				),
				'role_permissions' => array(
					'url' => array(
						'plugin' => $this->params['plugin'],
						'controller' => 'questionnaire_block_role_permissions',
						'action' => 'edit',
						$this->viewVars['frameId'],
						$blockId
					)
				),
			),
			'active' => $activeTab
		);
		$this->set('settingTabs', $settingTabs);
	}
}