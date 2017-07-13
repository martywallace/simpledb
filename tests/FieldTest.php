<?php

require('./vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use SimpleDb\Field;

class FieldTest extends TestCase {

	public function testEmpty() {
		$this->assertEquals([
			Field::isNull(false, Field::INT),
			Field::isNull(null, Field::INT),
			Field::isNull(0, Field::INT),
			Field::isNull('0', Field::INT),
			Field::isNull('', Field::INT),
			Field::isNull(1, Field::INT),
			Field::isNull('test', Field::INT),
			Field::isNull([], Field::INT),

			Field::isNull(false, Field::STRING),
			Field::isNull(null, Field::STRING),
			Field::isNull(0, Field::STRING),
			Field::isNull('0', Field::STRING),
			Field::isNull('', Field::STRING),
			Field::isNull(1, Field::STRING),
			Field::isNull('test', Field::STRING),
			Field::isNull([], Field::STRING)
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

	public function testFloatToRefined() {
		$this->assertSame([
			Field::toRefined(0, Field::FLOAT),
			Field::toRefined('0', Field::FLOAT),
			Field::toRefined('0.0', Field::FLOAT),
			Field::toRefined(0.000000, Field::FLOAT),
			Field::toRefined('', Field::FLOAT),
			Field::toRefined(false, Field::FLOAT),
			Field::toRefined(null, Field::FLOAT),
			Field::toRefined(123.5, Field::FLOAT),
			Field::toRefined('0832', Field::FLOAT)
		], [
			0,
			0.0,
			0.0,
			0.0,
			null,
			null,
			null,
			123.5,
			832.0
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
		$this->assertEquals([
			Field::toRefined('', Field::DATETIME),
			Field::toRefined('0', Field::DATETIME),
			Field::toRefined('2017-01-01', Field::DATETIME),
			Field::toRefined(false, Field::DATETIME),
			Field::toRefined(null, Field::DATETIME),
			Field::toRefined(new DateTime('2017-01-01'), Field::DATETIME),
		], [
			null,
			null,
			new DateTime('2017-01-01 00:00:00'),
			null,
			null,
			new DateTime('2017-01-01 00:00:00')
		]);
	}

	public function testJsonToPrimitive() {
		$data = new stdClass();
		$data->test = 5;

		$this->assertSame([
			Field::toPrimitive('', Field::JSON),
			Field::toPrimitive('0', Field::JSON),
			Field::toPrimitive(0, Field::JSON),
			Field::toPrimitive(123, Field::JSON),
			Field::toPrimitive([], Field::JSON),
			Field::toPrimitive($data, Field::JSON),
			Field::toPrimitive(null, Field::JSON),
			Field::toPrimitive(false, Field::JSON)
		], [
			null,
			null,
			null,
			'123',
			'[]',
			'{"test":5}',
			null,
			null
		]);
	}

	public function testJsonToRefined() {
		$data = new stdClass();
		$data->test = 5;

		$this->assertEquals([
			Field::toRefined('', Field::JSON),
			Field::toRefined('0', Field::JSON),
			Field::toRefined(0, Field::JSON),
			Field::toRefined(123, Field::JSON),
			Field::toRefined([], Field::JSON),
			Field::toRefined($data, Field::JSON),
			Field::toRefined(null, Field::JSON),
			Field::toRefined(false, Field::JSON),
			Field::toRefined('{"test":5}', Field::JSON),
			Field::toRefined('[]', Field::JSON)
		], [
			null,
			null,
			null,
			123,
			[],
			$data,
			null,
			null,
			$data,
			[]
		]);
	}

}