<?php

require('./vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use SimpleDb\Field;

class FieldTest extends TestCase {

	public function testEmpty() {
		$this->assertEquals([
			Field::isEmpty(false, Field::INT),
			Field::isEmpty(null, Field::INT),
			Field::isEmpty(0, Field::INT),
			Field::isEmpty('0', Field::INT),
			Field::isEmpty('', Field::INT),
			Field::isEmpty(1, Field::INT),
			Field::isEmpty('test', Field::INT),
			Field::isEmpty([], Field::INT),

			Field::isEmpty(false, Field::STRING),
			Field::isEmpty(null, Field::STRING),
			Field::isEmpty(0, Field::STRING),
			Field::isEmpty('0', Field::STRING),
			Field::isEmpty('', Field::STRING),
			Field::isEmpty(1, Field::STRING),
			Field::isEmpty('test', Field::STRING),
			Field::isEmpty([], Field::STRING)
		], [
			true,
			true,
			false,
			false,
			true,
			false,
			false,
			true,

			true,
			true,
			false,
			false,
			false,
			false,
			false,
			true
		]);
	}

	public function testIntToPrimitive() {
		$this->assertSame([
			Field::toPrimitive(0, Field::INT),
			Field::toPrimitive('0', Field::INT),
			Field::toPrimitive('', Field::INT),
			Field::toPrimitive(false, Field::INT),
			Field::toPrimitive(null, Field::INT),
			Field::toPrimitive(123, Field::INT)
		], [
			'0',
			'0',
			null,
			null,
			null,
			'123'
		]);
	}

	public function testIntToRefined() {
		$this->assertSame([
			Field::toRefined(0, Field::INT),
			Field::toRefined('0', Field::INT),
			Field::toRefined('', Field::INT),
			Field::toRefined(false, Field::INT),
			Field::toRefined(null, Field::INT),
			Field::toRefined(123, Field::INT),
			Field::toRefined('0832', Field::INT)
		], [
			0,
			0,
			null,
			null,
			null,
			123,
			832
		]);
	}

	public function testStringToPrimitive() {
		$this->assertSame([
			Field::toPrimitive('', Field::STRING),
			Field::toPrimitive('hi', Field::STRING),
			Field::toPrimitive(0, Field::STRING),
			Field::toPrimitive(456, Field::STRING),
			Field::toPrimitive(false, Field::STRING),
			Field::toPrimitive(null, Field::STRING)
		], [
			'',
			'hi',
			'0',
			'456',
			null,
			null
		]);
	}

	public function testStringToRefined() {
		$this->assertSame([
			Field::toRefined('', Field::STRING),
			Field::toRefined('hi', Field::STRING),
			Field::toRefined(0, Field::STRING),
			Field::toRefined(456, Field::STRING),
			Field::toRefined('0123', Field::STRING),
			Field::toRefined(false, Field::STRING),
			Field::toRefined(null, Field::STRING)
		], [
			'',
			'hi',
			'0',
			'456',
			'0123',
			null,
			null
		]);
	}

	public function testDatetimeToPrimitive() {
		$this->assertSame([
			Field::toPrimitive('', Field::DATETIME),
			Field::toPrimitive('0', Field::DATETIME),
			Field::toPrimitive('2017-01-01', Field::DATETIME),
			Field::toPrimitive(false, Field::DATETIME),
			Field::toPrimitive(null, Field::DATETIME),
			Field::toPrimitive(new DateTime('2017-01-01'), Field::DATETIME),
		], [
			null,
			null,
			'2017-01-01 00:00:00',
			null,
			null,
			'2017-01-01 00:00:00'
		]);
	}

	public function testDatetimeToRefined() {
		// TODO.
	}

	public function testJsonToPrimitive() {
		// TODO.
	}

	public function testJsonToRefined() {
		// TODO.
	}

}