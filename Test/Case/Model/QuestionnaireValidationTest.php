<?php
/**
 * Questionnaire Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('QuestionnaireValidationTestBase', 'Questionnaires.Test/Case/Model');

/**
 * Summary for Questionnaire Test Case
 */
class QuestionnaireValidationTest extends QuestionnaireValidationTestBase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
	}

/**
 * __assertValidationError
 *
 * @param string $field Field name
 * @param array $data Save data
 * @param array $expected Expected value
 * @return void
 */
	private function __assertValidationError($field, $data, $expected) {
		$this->setUp();

		$this->Questionnaire->set($data);
		$result = $this->Questionnaire->validates();

		$expectMessage = 'Expect `' . $field . '` field, error data: ' . print_r($data, true);
		$this->assertFalse($result, $expectMessage);

		$this->assertEquals($this->Questionnaire->validationErrors, $expected);

		$this->tearDown();
	}

/**
 * validate method
 * @return void
 */
	public function testbeforeValidate1() {
		$field = 'block_id';

		$data = array(
			'title' => 'test1',
			'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
			'block_id' => 'a',
		);

		$expected = array(
				$field => array(
				__d('net_commons', 'Invalid request.')));

		$this->__assertValidationError($field, $data, $expected);
	}

/**
 * validate method
 * @return void
 */
	public function testbeforeValidate2() {
		$field = 'title';

		$data = array(
			'block_id' => 1,
			'is_auto_translated' => 1,
			'title' => '',
			'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
		);
		$expected = array(
				$field => array( sprintf( __d('net_commons', 'Please input %s.'), __d('questionnaires', 'Title'))));

		$this->__assertValidationError($field, $data, $expected);
	}

/**
 * validate method
 * @return void
 */
	public function testbeforeValidate3() {
		$field = 'start_period';

		$data = array(
			'block_id' => 1,
			'is_auto_translated' => 1,
			'title' => 'title',
			'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
			'start_period' => 'aaa',
			'end_period' => '',
			'is_period' => 0,
		);
		$expected = array(
				$field => array( __d('questionnaires', 'Invalid datetime format.')));

		$this->__assertValidationError($field, $data, $expected);
	}

/**
 * validate method
 * @return void
 */
	public function testbeforeValidate4() {
		$field = 'end_period';

		$data = array(
			'block_id' => 1,
			'is_auto_translated' => 1,
			'title' => 'title',
			'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
			'start_period' => '2015-06-04 11:45:22',
			'end_period' => 'aaa',
			'is_period' => 0,
		);
		$expected = array(
				$field => array( __d('questionnaires', 'Invalid datetime format.')));

		$this->__assertValidationError($field, $data, $expected);
	}

/**
 * validate method
 * @return void
 */
	public function testbeforeValidateDateComp() {
		$field = 'end_period';

		$data = array(
			'block_id' => 1,
			'is_auto_translated' => 1,
			'title' => 'title',
			'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
			'start_period' => '2015-06-04 11:45:22',
			'end_period' => '2015-06-02 11:45:22',
			'is_period' => 0,
		);
		$expected = array(
				$field => array( __d('questionnaires', 'start period must be smaller than end period')));

		$this->__assertValidationError($field, $data, $expected);
	}

/**
 * validate method
 * @return void
 */
	public function testbeforeValidate5() {
		$field = 'total_show_start_period';

		$data = array(
			'block_id' => 1,
			'is_auto_translated' => 1,
			'title' => 'title',
			'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
			'start_period' => '2015-06-04 11:45:22',
			'end_period' => '2015-06-04 11:45:22',
			'is_period' => 0,
			'total_show_start_period' => 'a',
		);
		$expected = array(
				$field => array( __d('questionnaires', 'Invalid datetime format.')));

		$this->__assertValidationError($field, $data, $expected);
	}

}
