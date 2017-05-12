<?php
class Currencies {
	private $code;
	private $currencies = array();

	public function __construct($currency="") {
		$this->db = &Syspage::getdb();
		$srch = new SearchBase('tbl_currencies', 'tc');
	    $rs = $srch->getResultSet();
		$row = $this->db->fetch_all($rs);
		foreach ($row as $result) {
			$this->currencies[$result['currency_code']] = array(
				'currency_id'   => $result['currency_id'],
				'title'         => $result['currency_title'],
				'symbol_left'   => $result['currency_symbol_left'],
				'symbol_right'  => $result['currency_symbol_right'],
				'decimal_place' => 2,
				'value'         => $result['currency_value']
			);
		}
		if (isset($currency) && (array_key_exists($currency, $this->currencies))) {
			$this->set($currency);
		}
		else{	
			$this->set(Settings::getSetting("CONF_CURRENCY"));
		}
	}

	public function set($currency) {
		$this->code = $currency;
	}

	public function format($number, $currency = '', $value = '', $format = true) {
		if ($currency && $this->has($currency)) {
			$symbol_left   = $this->currencies[$currency]['symbol_left'];
			$symbol_right  = $this->currencies[$currency]['symbol_right'];
			$decimal_place = $this->currencies[$currency]['decimal_place'];
		} else {
			$symbol_left   = $this->currencies[$this->code]['symbol_left'];
			$symbol_right  = $this->currencies[$this->code]['symbol_right'];
			$decimal_place = $this->currencies[$this->code]['decimal_place'];

			$currency = $this->code;
		}

		if ($value) {
			$value = $value;
		} else {
			$value = $this->currencies[$currency]['value'];
		}

		if ($value) {
			$value = (float)$number * $value;
		} else {
			$value = $number;
		}

		$string = '';

		if (($symbol_left) && ($format)) {
			$string .= $symbol_left;
		}

		$decimal_point = '.';
		$thousand_point = ',';
		$string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);
		if (($symbol_right) && ($format)) {
			$string .= $symbol_right;
		}
		return $string;
	}

	public function convert($value, $from, $to) {
		if (isset($this->currencies[$from])) {
			$from = $this->currencies[$from]['value'];
		} else {
			$from = 1;
		}

		if (isset($this->currencies[$to])) {
			$to = $this->currencies[$to]['value'];
		} else {
			$to = 1;
		}

		return $value * ($to / $from);
	}

	public function getId($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_id'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_id'];
		} else {
			return 0;
		}
	}

	public function getSymbolLeft($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['symbol_left'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['symbol_left'];
		} else {
			return '';
		}
	}

	public function getSymbolRight($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['symbol_right'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['symbol_right'];
		} else {
			return '';
		}
	}

	public function getDecimalPlace($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['decimal_place'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['decimal_place'];
		} else {
			return 0;
		}
	}

	public function getCode() {
		return $this->code;
	}

	public function getValue($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['value'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['value'];
		} else {
			return 0;
		}
	}

	public function has($currency) {
		return isset($this->currencies[$currency]);
	}
}
