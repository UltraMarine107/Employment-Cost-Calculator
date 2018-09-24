<?php

/*
Calculate all data entries for employment cost & net income
*/

require(plugin_dir_path( __FILE__ ) . "InternalUser.php");

class Calculator{
	public $salary;
	public $city;  // a City object
	public $chinese;  // a boolean
	public $internal;  // an InternalUser object

	function __construct($s, $c, $ch, $i = null){
		$this->salary = $s;
		$this->city = $c;
		$this->chinese = $ch;
		$this->internal = $i;

		if (!$i)
			$this->internal = new InternalUser(true);
		else
			$this->adjust_internal();
	}

	function adjust_internal(){
		if (!$this->internal->has_legal_entity)
			$this->internal->is_dispatching = true;

		if (!$this->internal->has_legal_entity && $this->chinese)
			$this->internal->com_insur = 0;

		if ($this->internal->is_dispatching){
			$this->internal->er_lia_insur = true;
			$this->internal->has_union = true;
		}
	}

	function approximate($rough){
		$decimal_point = pow(10, $this->city->SI_DeciN);

		if ($this->city->SI_DeciR == "4R5I"){
			return round($rough, $this->city->SI_DeciN);
		}
		elseif ($this->city->SI_DeciR == "In") {
			return ceil($rough * $decimal_point) / $decimal_point;
		}
		else{
			return floor($rough * $decimal_point) / $decimal_point;
		}
	}

	function PeER(){
		return $this->approximate(max(min($this->salary, $this->city->PeER_BaTo), $this->city->PeER_BaBo) * $this->city->PeER + $this->city->PeER_Ad);
	}
	
	function MeER(){
		return $this->approximate(max(min($this->salary, $this->city->MeER_BaTo), $this->city->MeER_BaBo) * $this->city->MeER + $this->city->MeER_Ad);
	}

	function UeER(){
		if (!$this->chinese && !$this->internal->need_UeMa)
			return 0;

		return $this->approximate(max(min($this->salary, $this->city->UeER_BaTo), $this->city->UeER_BaBo) * $this->city->UeER + $this->city->UeER_Ad);
	}

	function WriER(){
		return $this->approximate(max(min($this->salary, $this->city->WriER_BaTo), $this->city->WriER_BaBo) * $this->city->WriER + $this->city->WriER_Ad);
	}

	function MaER(){
		if (!$this->chinese && !$this->internal->need_UeMa)
			return 0;

		return $this->approximate(max(min($this->salary, $this->city->MaER_BaTo), $this->city->MaER_BaBo) * $this->city->MaER + $this->city->MaER_Ad);
	}

	function DF_ER(){
		return $this->approximate(max(min($this->salary, $this->city->DF_ER_BaTo), $this->city->DF_ER_BaBo) * $this->city->DF_ER);
	}

	function HF_ER(){
		$HF = $this->city->HF_ER_default;

		if ($this->internal->HF_ER){
			$HF = $this->internal->HF_ER;
		}
		// echo $HF . " " . $this->city->HF_ER_BaBo . " " . $this->city->HF_ER_BaTo;

		$raw = max(min($this->salary, $this->city->HF_ER_BaTo), $this->city->HF_ER_BaBo) * $HF;

		// return $raw;
		return $this->approximate(max(min($raw, $this->city->HF_ER_To), $this->city->HF_ER_Bo));
	}

	function com_insur(){
		$scheme = $this->internal->com_insur;
		if ($scheme == 0)
			return 0;
		if ($scheme == 1)
			return 200;
		if ($scheme == 2)
			return 240;
		if ($scheme == 3)
			return 270;
		if ($scheme == 4)
			return 1200;
		if ($scheme == 5)
			return 1900;
		return 2800;
	} 

	function ELI(){
		if (!$this->internal->er_lia_insur)
			return 0;

		return $this->approximate($this->salary * $this->city->ELI);
	}

	function UnionER(){
		if (!$this->internal->has_union)
			return 0;

		return $this->approximate($this->salary * $this->city->UnionER);
	}

	function foreign_allowance(){
		if ($this->chinese)
			return 0;

		return $this->internal->foreign_allowance;
	} 

	function service_fee(){
		if ($this->internal->default)
			return 0;

		if ($this->internal->is_dispatching && !$this->chinese)
			return max(max(4000, $this->salary * 0.2), $this->internal->month_service);

		return $this->internal->month_service;
	} 

	function other_monthly(){
		return $this->internal->other_monthly;
	}

	function monthly_tax(){
		if ($this->internal->is_dispatching && !$this->internal->has_legal_entity)
			return $this->approximate(($this->salary + $this->PeER() + $this->MeER() + $this->UeER() + $this->WriER() + $this->MaER() + $this->DF_ER() + $this->HF_ER() + $this->com_insur() + $this->ELI() + $this->UnionER() + $this->foreign_allowance() + $this->service_fee() + $this->other_monthly()) * $this->internal->VAT_rate);

		if ($this->internal->is_dispatching)
			return $this->approximate(($this->service_fee() + $this->other_monthly()) * $this->internal->dispatch_tax);

		return $this->approximate(($this->service_fee() + $this->other_monthly()) * $this->internal->agency_tax);
	} 

	function deposit(){
		return $this->approximate(($this->salary + $this->PeER() + $this->MeER() + $this->UeER() + $this->WriER() + $this->MaER() + $this->DF_ER() + $this->HF_ER() + $this->com_insur() + $this->ELI() + $this->UnionER() + $this->foreign_allowance()) * $this->internal->deposit);
	}

	function ER_Month_Total(){
		return $this->salary + $this->PeER() + $this->MeER() + $this->UeER() + $this->WriER() + $this->MaER() + $this->DF_ER() + $this->HF_ER() + $this->com_insur() + $this->ELI() + $this->UnionER() + $this->foreign_allowance() + $this->other_monthly() + $this->deposit() + $this->service_fee() + $this->monthly_tax();
	}

	function annual_bonus(){
		return $this->internal->annual_bonus;
	}

	function union_annual_bonus(){
		if (!$this->internal->has_union)
			return 0;

		return $this->internal->annual_bonus * $this->city->UnionER;
	}

	function ELI_annual_bonus(){
		if (!$this->internal->er_lia_insur)
			return 0;

		return $this->internal->annual_bonus * $this->city->ELI;
	}

	function service_fee_annual_bonus(){
		if (!$this->internal->annual_bonus)
			return 0;

		return $this->service_fee();
	} 

	function other_annual(){
		return $this->internal->other_annual;
	}

	function total_deposit(){
		return $this->approximate($this->deposit() * 12 + ($this->annual_bonus() + $this->ELI_annual_bonus() + $this->union_annual_bonus()) * $this->internal->deposit);
	}

	function annual_tax(){
		if ($this->internal->is_dispatching && !$this->internal->has_legal_entity)
			return $this->approximate(($this->annual_bonus() + $this->ELI_annual_bonus() + $this->union_annual_bonus() + $this->service_fee_annual_bonus() + $this->other_annual()) * $this->internal->VAT_rate);

		if ($this->internal->is_dispatching) 
			return $this->approximate(($this->service_fee_annual_bonus() + $this->other_annual()) * $this->internal->dispatch_tax);

		return $this->approximate(($this->service_fee_annual_bonus() + $this->other_annual()) * $this->internal->agency_tax);
	} 

	function ER_Annual_Total(){
		return round($this->ER_Month_Total() * 12 + $this->internal->annual_bonus + $this->internal->other_annual + $this->union_annual_bonus() + $this->ELI_annual_bonus() + $this->service_fee_annual_bonus() - $this->deposit() * 12 + $this->total_deposit() + $this->annual_tax());
	}

	function PeEE(){
		// echo $this->city->PeEE_BaTo;

		return $this->approximate(max(min($this->salary, $this->city->PeEE_BaTo), $this->city->PeEE_BaBo) * $this->city->PeEE + $this->city->PeEE_Ad);
	}

	function MeEE(){
		return $this->approximate(max(min($this->salary, $this->city->MeEE_BaTo), $this->city->MeEE_BaBo) * $this->city->MeEE + $this->city->MeEE_Ad);
	}

	function UeEE(){
		if (!$this->chinese && !$this->internal->need_UeMa)
			return 0;

		return $this->approximate(max(min($this->salary, $this->city->UeEE_BaTo), $this->city->UeEE_BaBo) * $this->city->UeEE + $this->city->UeEE_Ad);
	}

	function HF_EE(){
		$HF = $this->city->HF_EE_default;
		if ($this->internal->HF_EE != 0){
			$HF = $this->internal->HF_EE;
		}

		return $this->approximate(min(max(max(min($this->salary, $this->city->HF_EE_BaTo), $this->city->HF_EE_BaBo) * $HF, $this->city->HF_EE_Bo), $this->city->HF_EE_To));
	}
	// QUESTION: 公式只用最终结果上下限 没有考虑基数上下限

	function HF_EE_TF(){
		return $this->approximate(max(min($this->salary, $this->city->HF_EE_BaToTF), $this->city->HF_EE_BaBo) * $this->city->HF_ER_TFL);
	}

	function EE_Tax(){ 
		$tax_free_line = 3500;
		if (!$this->chinese) {
			$tax_free_line = 4800;
		}

		$base = $this->salary - $this->PeEE() - $this->MeEE() - $this->UeEE() - $this->HF_EE() - $tax_free_line + $this->com_insur();

		if ($base <= 0)
			return 0;
		elseif ($base <= 1500) 
			return $this->approximate($base * 0.03);
		elseif ($base <= 4500)
			return $this->approximate($base * 0.1 - 105);
		elseif ($base <= 9000)
			return $this->approximate($base * 0.2 - 555);
		elseif ($base <= 35000)
			return $this->approximate($base * 0.25 - 1005);
		elseif ($base <= 55000)
			return $this->approximate($base * 0.3 - 2755);
		elseif ($base <= 80000)
			return $this->approximate($base * 0.35 - 5505);
		else
			return $this->approximate($base * 0.45 - 13505);
	}

	// =MAX((I22-SUM(I9:I12)+C16-IF(I26="N",4800,3500))*{0.03,0.1,0.2,0.25,0.3,0.35,0.45}-{0,105,555,1005,2755,5505,13505},0)

	function Monthly_Net(){
		return $this->salary - $this->PeEE() - $this->MeEE() - $this->UeEE() - $this->HF_EE() - $this->EE_Tax() + $this->foreign_allowance();
	} 

	function annual_bonus_tax(){
		$tax_free_line = 3500;
		if (!$this->chinese) {
			$tax_free_line = 4800;
		}

		$base = $this->internal->annual_bonus - max($tax_free_line - $this->salary + $this->PeEE() + $this->MeEE() + $this->UeEE() + $this->HF_EE(), 0);

		if ($base <= 0)
			return 0;
		elseif ($base / 12 <= 1500) 
			return $this->approximate($base * 0.03);
		elseif ($base / 12 <= 4500)
			return $this->approximate($base * 0.1 - 105);
		elseif ($base / 12 <= 9000)
			return $this->approximate($base * 0.2 - 555);
		elseif ($base / 12 <= 35000)
			return $this->approximate($base * 0.25 - 1005);
		elseif ($base / 12 <= 55000)
			return $this->approximate($base * 0.3 - 2755);
		elseif ($base / 12 <= 80000)
			return $this->approximate($base * 0.35 - 5505);
		else
			return $this->approximate($base * 0.45 - 13505);
	} 

	function Annual_Net(){
		return round($this->Monthly_Net() * 12 + $this->internal->annual_bonus - $this->annual_bonus_tax());
	}

	function guest_soc_insur_ER(){
		return $this->PeER() + $this->MeER() + $this->UeER() + $this->WriER() + $this->MaER() + $this->DF_ER() + $this->HF_ER();
	}

	function guest_soc_insur_EE(){
		return $this->PeEE() + $this->MeEE() + $this->UeEE() + $this->HF_EE();
	}
}

