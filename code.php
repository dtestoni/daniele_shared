<?php
/** ******************************************************************
* Classe code per il controllo di partita iva e codice fiscale
*
* Elenco dei metodi:
* - type		restituisce il tipo di codice
* - check		controlla il codice
* - explode		restituisce i dettagli del codice
* - printable	restituisce il codice stampabile
* - error		restituisce la descrizione dell'errore
* - dump		dump dell'oggetto
*
* @package	ct.base
* @author	ConsulTes info@consultes.it
* ***************************************************************** */

class ct_code {

	/** ******************************************************************
	* Proprietà private
	* ***************************************************************** */

	/** @var string codice in esame */
	var $_code;
	/** @var string tipo codice in esame */
	var $_type;
	/** @var string errore */
	var $_error;

	/** @var array tipi di codici riconosciuti */
	var $_types = array(
		'PI' => 'Partita IVA',
		'CF' => 'Codice fiscale',
		'IB' => 'IBAN'
		);
	/** @var string tipo di codice non controllabile */
	var $_type_nc = '**';

	/** @var array tabella di conversione lettere in numeri */
	var $_letter_number = array(
		'A' =>  1, 'B' =>  0, 'C' =>  5, 'D' =>  7, 'E' =>  9, 'F' => 13,
		'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21, 'K' =>  2, 'L' =>  4,
		'M' => 18, 'N' => 20, 'O' => 11, 'P' =>  3, 'Q' =>  6, 'R' =>  8,
		'S' => 12, 'T' => 14, 'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25,
		'Y' => 24, 'Z' => 23
		);

	/** @var array tabella di identificazione lettera mese */
	var $_month = array(
		'A' =>  1, 'B' =>  2, 'C' =>  3, 'D' =>  4, 'E' =>  5, 'H' =>  6,
		'L' =>  7, 'M' =>  8, 'P' =>  9, 'R' => 10, 'S' => 11, 'T' => 12,
		);

	/** @var array tabella delle posizioni dei numeri */
	var $_number_position = array(6, 7, 9, 10, 12, 13, 14);

	/** @var array tabella di conversione numeri in lettere per omonimi */
	var $_number_letter = array(
		'L' => 0, 'M' => 1, 'N' => 2, 'P' => 3, 'Q' => 4,
		'R' => 5, 'S' => 6, 'T' => 7, 'U' => 8, 'V' => 9
		);

	/** @var array tabella di conversione lettere in numeri */
	var $_iban_letter_number = array(
		'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15,
		'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21,
		'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27,
		'S' => 28, 'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33,
		'Y' => 34, 'Z' => 35
		);

	/** @var array costanti */
	var $_gettext = 'ct/base/templates/code.html';
	var $_cst = array();

	/** ******************************************************************
	* Costruttore
	* @param	string	$p_code		codice
	* @param	string	$p_type		tipo di codice
	* @return	NULL
	* ***************************************************************** */
	function ct_code($p_code, $p_type = '')
	{
		global $application, $session;

		$this->_cst = $application->gettext($this->_gettext, '*');

		// Fissa il codice
		$this->_code = strtoupper($p_code);

		// Se non specificato cerca di indovinare il tipo
		if ($p_type) {
			if ($this->_types[$p_type]) $this->_type = $p_type;
		} else {
			foreach ($this->_types as $type => $description) {
				$guess = "_guess_$type";
				$t = $this->$guess($type);
				if ($t) $this->_type = $t;
			}
		}
	}

	/** ******************************************************************
	* Restituisce il tipo di codice
	* @param	NULL
	* @return	string	tipo di codice
	* ***************************************************************** */
	function type()
	{
		if ($this->_type == $this->_type_nc) return;
		return $this->_type;
	}

	/** ******************************************************************
	* Controlla il codice
	* @param	NULL
	* @return	string	eventuale errore
	* ***************************************************************** */
	function check()
	{
		if ($this->_type == $this->_type_nc) return;
		if ($type = $this->_type) {
			$check = "_check_$type";
			return $this->$check();
		} else {
			return $this->_cst['unknowen'];
		}
	}

	/** ******************************************************************
	* Restituisce i dettagli del codice
	* @param	NULL
	* @return	array	dettagli del codice
	* ***************************************************************** */
	function explode()
	{
		if ($this->_type == $this->_type_nc) return array();
		if ($type = $this->_type) {
			$explode = "_explode_$type";
			return $this->$explode();
		} else {
			return array();
		}
	}

	/** ******************************************************************
	* Restituisce il codice stampabile
	* @param	NULL
	* @return	string	codice stampabile
	* ***************************************************************** */
	function printable()
	{
		// Formatta il codice
		if ($this->_type != $this->_type_nc && $type = $this->_type) {
			$printable = "_printable_$type";
			$code = $this->$printable();
		}

		// Se non presente restituisce almeno quello originale
		if (!$code) {
			$code = $this->_code;
		}

		// Restituisce il codice stampabile
		return $code;
	}

	/** ******************************************************************
	* Restituisce la descrizione dell'errore
	* @param	NULL
	* @return	string	descrizione dell'errore
	* ***************************************************************** */
	function error()
	{
		return $this->_error;
	}

	/** ******************************************************************
	* Dump dell'oggetto
	* @param	NULL
	* @return	NULL
	* ***************************************************************** */
	function dump()
	{
		print("<pre>\n");
		print_r($this);
		print("</pre>\n");
	}

	/** ******************************************************************
	* Indovina se è una partita iva
	* @param	string	$p_type		tipo da indovinare
	* @return	string	tipo corrispondente
	* ***************************************************************** */
	function _guess_PI($p_type)
	{
		// Partita IVA lunga esattamente
		if (strlen($this->_code) == 11) return $p_type;

		// Prefisso IT con o senza spazio
		if (preg_match("/^[A-Z]{2} ?([0-9]{4})([0-9]+)/", $this->_code, $reg)) {
			return $p_type;
		}

		// Partita IVA straniera
		/*
		Disattivato in attesa di definizione di un set di
		regole più precise. Al momento il controllo è eseguito
		solo per gli elementi riconosciuti come italiani.
		if (preg_match("/^[A-Z]{2} ?([0-9])+/", $this->_code, $reg)) {
			return $this->_type_nc;
		}
		*/

		return '';
	}

	/** ******************************************************************
	* Controlla la partita iva
	* @param	NULL
	* @return	string	messaggio di errore
	* ***************************************************************** */
	function _check_PI()
	{
		$code = $this->_code;
		if (preg_match("/^([A-Z]){2} ?([0-9]{4})([0-9]+)/", $code, $reg)) {
			$country = $reg[1];
			$code    = $reg[2].$reg[3];
		} else {
			$country = 'IT';
		}
		if ($country != 'IT') return;

		// --------------------------------------------------
		// Controlla la lunghezza del codice (11 cifre)
		// --------------------------------------------------
		if (strlen($code) != 11)
			return sprintf($this->_cst['pi_11'], $code);

		// --------------------------------------------------
		// Controlla che il codice contenga solo numeri
		// --------------------------------------------------
		if (preg_match("/[^0-9]/", $code))
			return sprintf($this->_cst['pi_number'], $code);

		// --------------------------------------------------
		// Calcola il totale delle cifre di ordine dispari
		// --------------------------------------------------
		for ($i = 1; $i < 11; $i = $i + 2) {
			// Somma la cifra così com'è
			// Cifra=5 ... somma
			$odds += $code[$i-1];
		}

		// --------------------------------------------------
		// Calcola il totale delle cifre di ordine pari
		// --------------------------------------------------
		for ($i = 2; $i < 11; $i = $i + 2) {
			// Raddoppia la cifra
			// Cifra=8 ... 8*2=16
			$double = $code[$i-1] * 2;
			// Totalizza le decine e le unità del risultato precedente
			// Cifra=16 ... 1+6=7
			$double = ($double < 10) ? 'A0'.$double : 'A'.$double;
			$double = $double[1] + $double[2];
			// Aggiunge alla somma
			// Cifra=7 ... somma
			$pair  += $double;
		}

		// --------------------------------------------------
		// Controlla che il codice di controllo sia esatto (cifra 11)
		// --------------------------------------------------
		// Somma i totali delle cifre di ordine dispari e pari
		$total = $odds + $pair;
		// Sottrae le unità della somma da 10, quella è la cifra di controllo
		$char = substr(10 - substr($total, -1), -1);
		// Controlla
		if ($code[11-1] != $char)
			return sprintf($this->_cst['pi_wrong'], $code);

		// --------------------------------------------------
		// Codice corretto
		// --------------------------------------------------
		return '';
	}

	/** ******************************************************************
	* Restituisce i dettagli sulla partita iva
	* @param	NULL
	* @return	array	dettagli
	* ***************************************************************** */
	function _explode_PI()
	{
		$code = $this->_code;
		if (preg_match("/^([A-Z]){2} ?([0-9]{4})([0-9]+)/", $code, $reg)) {
			$country = $reg[1];
			$code    = $reg[2].$reg[3];
		} else {
			$country = 'IT';
		}

		// Sviluppo dettagli
		$error = $this->_check_PI();
		if (!$error) {
			$detail = array(
				'code'     => $code,
				'country'  => $country,
				'sequence' => substr($code, 0, 10),
				'check'    => substr($code, 10, 1)
				);
		} else {
			$detail = array();
		}

		return $detail;
	}

	/** ******************************************************************
	* Restituisce il codice stampabile
	* @param	NULL
	* @return	string	codice stampabile
	* ***************************************************************** */
	function _printable_PI()
	{
		$code = $this->_code;

		// Codice stampabile
		$error = $this->_check_PI();
		if (!$error) {
			$printable = $code;
		} else {
			$printable = '';
		}

		return $printable;
	}

	/** ******************************************************************
	* Indovina se è un codice fiscale
	* @param	string	$p_type		tipo da indovinare
	* @return	string	tipo corrispondente
	* ***************************************************************** */
	function _guess_CF($p_type)
	{
		if (strlen($this->_code) == 16) return $p_type;

		return '';
	}

	/** ******************************************************************
	* Controlla il codice fiscale
	* @param	NULL
	* @return	string	messaggio di errore
	* ***************************************************************** */
	function _check_CF()
	{
		$code = $this->_code;

		// --------------------------------------------------
		// Controlla la lunghezza del codice (16 caratteri)
		// --------------------------------------------------
		if (strlen($code) != 16)
			return sprintf($this->_cst['cf_16'], $code);

		// --------------------------------------------------
		// Controlla che il codice contenga solo numeri o lettere
		// --------------------------------------------------
		if (preg_match("/[^0-9A-Z]/", $code))
			return sprintf($this->_cst['cf_alfanum'], $code);

		// --------------------------------------------------
		// Calcola il totale dei caratteri di ordine dispari
		// --------------------------------------------------
		$number_number = array_values($this->_letter_number);
		for ($i = 1; $i < 16; $i = $i + 2)
			if (preg_match("/[0-9]/", $char = $this->_code[$i - 1])) {
				// Somma un numero
				// Carattere=4 ... letter_number ... somma 9 (E=9)
				$odds += $number_number[$char];
			} else {
				// Somma una lettera
				// Carattere=A ... letter_number ... somma 1 (A=1)
				$odds += $this->_letter_number[$char];
			}

		// --------------------------------------------------
		// Calcola il totale dei caratteri di ordine pari
		// --------------------------------------------------
		for ($i = 2; $i < 16; $i = $i + 2)
			if (preg_match("/[0-9]/", $char = $this->_code[$i - 1])) {
				// Somma un numero
				// Carattere=4 ... somma 4
				$pair += $char;
			} else {
				// Somma una lettera
				// Carattere=C ... alfabeto ... somma 2 (da 0 a 25)
				$pair += ord($char) - ord('A');
			}

		// --------------------------------------------------
		// Controlla che il codice di controllo sia esatto (carattere 16)
		// --------------------------------------------------
		// Somma i totali dei caratteri di ordine dispari e pari
		$sum  = $odds + $pair;
		// Determina il resto della divisione del risultato precedente e 26
		// Totali 42 e 129
		//   42+129=171 ... 171/26=6,57 ... 6*26=156 ... 171-156=15
		$char = $sum / 26;
		$char = (int) $char * 26;
		$char = $sum - $char;
		// Determina la lettera
		//   number_letter ... cifra=P (elementi numerati da 0 a 25)
		$number_letter = array_keys($this->_letter_number);
		$char = $number_letter[$char];
		// Controlla
		if ($code[16-1] != $char)
			return sprintf($this->_cst['cf_wrong'], $code);

		// --------------------------------------------------
		// Codice corretto
		// --------------------------------------------------
		return '';
	}

	/** ******************************************************************
	* Restituisce i dettagli sul codice fiscale
	* @param	NULL
	* @return	array	dettagli
	* ***************************************************************** */
	function _explode_CF()
	{
		$code = $this->_code;

		// Ripristina i numeri sostituiti dalle lettere nel caso di omonimi
		foreach ($this->_number_position as $k => $position) {
			if ($letter = $this->_number_letter[$code[$position]])
				$code[$position] = $letter;
		}

		// Sviluppo dettagli
		$error = $this->_check_CF();
		if (!$error) {
			$detail = array(
				'code'    => $code,
				'surname' => substr($code,  0, 3),
				'name'    => substr($code,  3, 3),
				'year'    => substr($code,  6, 2),
				'month'   => substr($code,  8, 1),
				'day'     => substr($code,  9, 2),
				'place'   => substr($code, 11, 4),
				'check'   => substr($code, 15, 1)
			);
			// Dettagli sesso e giorno
			if ($detail['day'] > 40) {
				$detail['sex'] = 'F';	// Femmina
				$detail['day'] -= 40;
			} else {
				$detail['sex'] = 'M';	// Maschio
			}
			// Mese
			$detail['month_code'] = $detail['month'];
			$detail['month'] = sprintf('%02s', $this->_month[$detail['month']]);
			// Anno
			// FIXME: Sostituire con la logica reale
			$detail['year_code'] = $detail['year'];
			if ($detail['year'] > 40) {
				$detail['year'] = 1900+$detail['year'];
			} else {
				$detail['year'] = 2000+$detail['year'];
			}
		} else {
			$detail = array();
		}

		return $detail;
	}

	/** ******************************************************************
	* Restituisce il codice stampabile
	* @param	NULL
	* @return	string	codice stampabile
	* ***************************************************************** */
	function _printable_CF()
	{
		$code = $this->_code;

		// Codice stampabile
		$error = $this->_check_CF();
		if (!$error) {
			$printable = $code;
		} else {
			$printable = '';
		}

		return $printable;
	}

	/** ******************************************************************
	* Indovina se è un IBAN
	* @param	string	$p_type		tipo da indovinare
	* @return	string	tipo corrispondente
	* ***************************************************************** */
	function _guess_IB($p_type)
	{
		// Inzia con usa sequenza tipo IT12A
		if (preg_match("/^[A-Z]{2}[0-9]{2}[A-Z]{1}/", $this->_code)) {
			return $p_type;
		}

		return '';
	}

	/** ******************************************************************
	* Controlla l'IBAN
	* @param	NULL
	* @return	string	messaggio di errore
	* ***************************************************************** */
	function _check_IB()
	{
		$code = $this->_code;

		// --------------------------------------------------
		// Controlla la lunghezza del codice (> 4 caratteri)
		// --------------------------------------------------
		if (strlen($code) <= 4)
			return sprintf($this->_cst['if_4'], $code);

		// --------------------------------------------------
		// Controlla se inizia con i caratteri corretti
		// --------------------------------------------------
		if (!preg_match("/^[A-Z]{2}[0-9]{2}/", $code))
			return sprintf($this->_cst['ib_begin'], $code);

		// --------------------------------------------------
		// Sposta 4 caratteri da sinistra a destra
		// --------------------------------------------------
		$left4 = substr($code, 0, 4);
		$code  = substr($code, 4).$left4;

		// --------------------------------------------------
		// Trasforma le lettere in numeri
		// --------------------------------------------------
		$code_number = '';
		for ($i = 0; $i < strlen($code); $i++) {
			if ($number = $this->_iban_letter_number[$code[$i]]) {
				$code_number .= $number;
			} else {
				$code_number .= $code[$i];
			}
		}
		$code = $code_number;

		// --------------------------------------------------
		// Controlla che il codice sia esatto
		// --------------------------------------------------
		$result = ''; 
		$module = 97; 
		$i      = 0; 
		while ($i < strlen($code)) {
			do {
				$result   .= $code[$i++]; 
				$remainder = (int)$result % $module;
			} while ($remainder == (int)$result && $i < strlen($code));
			$result = $remainder;
		}
		if ($result != 1)
			return sprintf($this->_cst['ib_wrong'], $this->_code);

		// --------------------------------------------------
		// Codice corretto
		// --------------------------------------------------
		return '';
	}

	/** ******************************************************************
	* Restituisce i dettagli sull'IBAN
	* @param	NULL
	* @return	array	dettagli
	* ***************************************************************** */
	function _explode_IB()
	{
		$code = $this->_code;

		// Sviluppo dettagli
		$error = $this->_check_IB();
		if (!$error) {
			// FIXME: Struttura italiana, non dovrebbe
			$detail = array(
				'code'    => $code,
				'country' => substr($code,  0,  2),
				'checks'  => substr($code,  2,  2),
				'check'   => substr($code,  4,  1),
				'bank'    => substr($code,  5,  5),
				'branch'  => substr($code, 10,  5),
				'account' => substr($code, 15, 12)
				);
		} else {
			$detail = array();
		}

		return $detail;
	}

	/** ******************************************************************
	* Restituisce il codice stampabile
	* @param	NULL
	* @return	string	codice stampabile
	* ***************************************************************** */
	function _printable_IB()
	{
		$code = $this->_code;

		// Codice stampabile
		$error = $this->_check_IB();
		if (!$error) {
			$printable = implode(' ', explode("\n", wordwrap($code, 4, "\n", TRUE)));
		} else {
			$printable = '';
		}

		return $printable;
	}

}

?>
