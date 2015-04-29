<?php
class Jaar{
	public $ingangsDatum = '2012-01-01';
	public $toegekendQuotum = 0;
	public $verloopNaUitgifte = 0; // maanden + 1 jaar

	public function __construct(DateTime $ingangsDatum, $toegekendQuotum, $verloopNaUitgifte){
		$this->ingangsDatum = $ingangsDatum;
		$this->toegekendQuotum = $toegekendQuotum;
		$this->verloopNaUitgifte = $verloopNaUitgifte;
	}

	public function getAfloopDatum(){
		$afloopNaDagen = 12 + $this->verloopNaUitgifte;
		$ingangsDatum = clone $this->ingangsDatum;
		return $ingangsDatum->add(new DateInterval('P' . $afloopNaDagen . 'M'));
	}
}

class Berekenaar{
	protected $acties = array();
	public $jaren = array();
	public function addJaar(Jaar $jaar){
		$this->jaren[] = $jaar;
		$this->herbereken();
	}

	protected function herbereken(){
		$this->acties = array();
		//Loop over jaren
		foreach($this->jaren as $jaar){
			// Doe actie voor jaar
			$this->acties[] = $this->opwaardeerActie($jaar);
			$this->acties[] = $this->tegoedVerlooptActie($jaar);
		}
		usort($this->acties, function($a, $b){
			if($a->datum == $b->datum){
				return 0;
			}
			return ($a->datum < $b->datum) ? -1 : 1;
		});
	}

	public function getTegoedOpDatum(DateTime $datum){
		$actueelTegoed = 0;
		foreach($this->acties as $actie){
			if($actie->datum <= $datum){
				$actueelTegoed -= $actie->afwaardeerOperatie;
				$actueelTegoed += $actie->opwaardeerOperatie;
			}else{
				break;
			}
		}
		return $actueelTegoed;
	}

	protected function opwaardeerActie(Jaar $jaar){
		return new Actie($jaar->ingangsDatum, $jaar->toegekendQuotum, 0);
	}

	protected function tegoedVerlooptActie(Jaar $jaar){
		return new Actie($jaar->getAfloopDatum(), 0, $jaar->toegekendQuotum);
	}
}

class Actie{
	public $datum = null;
	public $opwaardeerOperatie = 0;
	public $afwaardeerOperatie = 0;

	public function __construct(DateTime $datum, $opwaardeerOperatie, $afwaardeerOperatie){
		$this->datum = $datum;
		$this->opwaardeerOperatie = $opwaardeerOperatie;
		$this->afwaardeerOperatie = $afwaardeerOperatie;
	}
}

$jaar2012 = new Jaar(new DateTime('2012-01-01'), 20, 6);
$jaar2013 = new Jaar(new DateTime('2013-01-01'), 20, 6);
$jaar2014 = new Jaar(new DateTime('2014-01-01'), 20, 6);
$jaar2015 = new Jaar(new DateTime('2015-01-01'), 20, 6);

$berekenaar = new Berekenaar();
$berekenaar->addJaar($jaar2012);
$berekenaar->addJaar($jaar2013);
$berekenaar->addJaar($jaar2014);
$berekenaar->addJaar($jaar2015);

$testDatums = array('2012-01-01', '2012-12-31', '2013-01-01', '2013-07-01', '2013-12-31', '2014-01-01', '2014-07-01', '2014-12-31', '2015-01-01', '2015-07-01', '2015-12-31');

foreach ($testDatums as $testDatum) {
	echo "\r\n". $testDatum. ' = ';
	echo $berekenaar->getTegoedOpDatum(new DateTime($testDatum));
}

