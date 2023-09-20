<?php

class id62{
	public $dict = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	function __construct($dict = null){
		if($dict) $this->dict = $dict;
	}
	
	/**
		* 十进制数转换成其它进制
		* 可以转换成2-62任何进制
		*
		* @param integer $num
		* @param integer $to
		* @return string
	*/
	function encode($num, $to = 62) {
		if ($to == 10 || $to > 62 || $to < 2) {
			return $num;
		}
		$ret = '';
		do {
			$ret = $this->dict[bcmod($num, $to)] . $ret;
			$num = bcdiv($num, $to);
		} while ($num > 0);
		return $ret;
	}
	
	/**
		* 其它进制数转换成十进制数
		* 适用2-62的任何进制
		*
		* @param string $num
		* @param integer $from
		* @return number
	*/
	function decode($num, $from = 62) {
		if ($from == 10 || $from > 62 || $from < 2) {
			return $num;
		}
		$num = strval($num);
		$len = strlen($num);
		$dec = 0;
		for($i = 0; $i < $len; $i++) {
			$pos = strpos($this->dict, $num[$i]);
			if ($pos >= $from) {
			  	continue; // 如果出现非法字符，会忽略掉。比如16进制中出现w、x、y、z等
			}
			$dec = bcadd(bcmul(bcpow($from, $len - $i - 1), $pos), $dec);
		}
		return $dec;
	}
	
}
