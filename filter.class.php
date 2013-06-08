<?php
/**
 * Orea敏感词处理系统字词处理类
 * [example]
 * $chk_words_obj = new Censor($content);
 * $content = $chk_words_obj->content;
 * [/example]
 * @copyright Powered By Yao Li.
 * @version 1.0
 */

include 'class/oreainfo.php';

class Censor
{
	
	var $content;
	private $RightSituation;//检测右侧字词
	private $LeftSituation;//检测左侧字词
	private $ArrPos; //位置数组（二维）
	private $ArrNowWord;
	var $hycd;
	
	/**
	 * 构造函数
	 * 判断处理类型
	 * @param string $content 传入的待处理内容
	*/
	function __construct($content)
	{
		
		$this->content = $content;
		$this->hycd = file_get_contents(OreaPath.'/hycd.txt');
		$this->handlewords($content);
		unset($this->hycd);
		
	}

	function consult()
	{
		return $this->content;
	}
	
	/**
	 * 匹配函数（主函数）
	 * @return string $content 处理完的文字
	 */
	private function handlewords()//正式处理过程
	{
		
		$tree = unserialize(file_get_contents(OreaPath.'/tree.txt'));
		
		foreach ($tree['head'] as $tmp)
		{
			if (strpos($this->content, $tmp))
			{
				$res[]=$tmp;
			
				foreach ($tree['mgc']["$tmp"] as $Pe)
				{
				
					if (@preg_match_all($Pe, $this->content, $matches) != 0)
					{
					
						foreach ($matches[0] as $tmp)
						{
						
							$count = substr_count($this->content, $tmp);
							$this->ArrNowWord[$tmp] = 0;
							$which = 0;
						
							if ($count > 1)
							{
							
								if (!isset($this->ArrPos[$tmp]))
								{
								
									$position_arr = $this->GetPositions($this->content, $tmp, $count);
								
								}
							
								$this->ArrNowWord[$tmp] = $this->ArrNowWord[$tmp]+1;
								$which = $this->ArrNowWord[$tmp];
								$position = $this->ArrPos[$tmp][0];
								array_splice($this->ArrPos[$tmp], 0, count($this->ArrPos[$tmp])-1);
							
							}else{
								
								$position = @mb_strpos($this->content, $tmp, 0, CODING_TYPE);
								
							}

							$LeftWord = mb_substr($this->content, $position-1, 2, CODING_TYPE);
							$RightWord = mb_substr($this->content, $position+1, 2, CODING_TYPE);
						
							if (mb_strlen($LeftWord, CODING_TYPE) != 1)
							{
								$this->ReCheck($LeftWord, $RightWord);
								/*
								 * 逻辑 
								 */
								if ($this->RightSituation == false && $this->LeftSituation == false)
								{
									
									$leftwordlength = mb_strlen($LeftWord, CODING_TYPE);
									$rightwordlength = mb_strlen($RightWord, CODING_TYPE);
									
									if ($leftwordlength >= 1)
									{
										
										if ($this->RightSituation == false)
										{
										
										}else{
											
											$this->OreaReplace($this->content, $tmp, $count, $which);
										
										}
									}elseif ($rightwordlength >= 1){
										
										if ($this->LeftSituation == false)
										{
										
										}else{

											$this->OreaReplace($this->content, $tmp, $count, $which);

										}

									}

								}else{
									
									if ($RightWord == $tmp)
									{

										$this->OreaReplace($this->content, $tmp, $count, $which);
								
									}else{
									
										if (mb_strlen($RightWord,"gb2312") == 1)
										{
											
											$this->OreaReplace($this->content, $tmp, $count, $which);
										
										}else{
											
											if ($this->ReCheckVo($RightWord))
											{

												$this->OreaReplace($this->content, $tmp, $count, $which);
											
											}
										}
									}
								}
								
							}else{
								
								$this->OreaReplace($this->content, $tmp, $count, $which);
							
							}
						}
					}
				}
			}
		}
		
		/*
		 * 判断是否批准发布
		 */
		if (!$this->IsAble($this->content))
		{
			
			$this->content = '敏感内容所占比重太大，禁止发布！';
			
		}
	}
	
	/**
	 * 进一步匹配函数
	 * @param string $Left 左侧词汇
	 * @param string $Right 右侧词汇
	 * @return boolean true||false 是否存在
	 * @return boolean RightSituation 右侧是否有
	 * @return boolean LeftSituation 左侧是否有
	 */
	private function ReCheck($Left, $Right)
	{
			
		if (strpos($this->hycd, $Left))
		{
			
			$this->LeftSituation = false;
			return false;
			
		}else{
			
			$this->LeftSituation = true;
			return true;
			
		}
		
		if (strpos($this->hycd, $Right))
		{
			
			$this->RightSituation = false;
			return false;
			
		}else{
			
			$this->RightSituation = true;
			return true;
			
		}
		
	}
	
	/**
	 * 单个确认函数
	 * @param string $Vo 待查询的词
	 * @return true||false boolean 查询结果
	 */
	private function ReCheckVo($Vo)
	{
		
		if (strpos($this->hycd, $Vo))
		{
			
			return false;
			
		}else{
			
			return true;
			
		}

	}
	
	/**
	 * 替换函数（根） 决定采用何种替换函数，并返回值
	 * @param string $content
	 * @param string $tmp
	 * @param string $which
	 * @return Ambigous <string, mixed, $content, string>
	 */
	private function OreaReplace($content, $tmp, $count,$which)
	{
		
		$content = $this->content;
		
		if ($which >= 1)
		{
			
			$content = $this->ReturnContentT($content, $tmp, $count, $which);
			$this->content = $content;
			
		}else{
			
			$content = $this->ReturnContentX($content, $tmp);
			
		}

		$this->content = $content;
		unset($content);
		
	}
	
	/**
	 * 指定替换函数
	 * @param string $content
	 * @param string $tmpc
	 * @param string $which
	 * @return string 替换后的内容
	 */
	private function ReturnContentT($content, $tmpc, $count, $which)
	{
		
		$content = $content.'{_OendO_}';
		$content = str_replace($tmpc, '<hh>', $content);
		$tmp = explode('<hh>', $content);
		unset($content);
		$len = mb_strlen($tmpc, CODING_TYPE);
		$replace = str_repeat('*', $len);
		$max = count($tmp);
		$content_new = '';
		
		for ($i = 0; $i < $max; $i++) {
			
			if ($i != $which)
			{
				
				if ($i != $max-1)
				{
					
					$content_new = $content_new.$tmp[$i].$tmpc;
					
				}else{
					
					$content_new = $content_new.$tmp[$i];
					
				}
				
			}else{
				
				$content_new = $content_new.$tmp[$i].$replace;
				
			}
		}
		
		$content_new = str_replace('{_OendO_}', '', $content_new);
		return $content_new;
		unset($content_new);
		unset($tmp);
	}
	
	/**
	 * 替换函数（支持模糊版）ReturnContent()函数同此
	 * 这里加while循环是为了避免把标点符号拿走后，引起$Pe与原文的不符，所以在中间增加可以模糊的位数
	 * 但是要避免陷入死循环，最大为40
	 * @param $content string 待处理的内容
	 * @param $Pe string 匹配表达式
	 * @return $content string *号替换后的内容
	 */
	private function ReturnContentX($content, $Pe)
	{
		
		if (strpos($content, $Pe) != false)
		{
			
			$len = mb_strlen($Pe, CODING_TYPE);
			$replace = str_repeat('*', $len);
			$content = str_replace($Pe, $replace, $content);
			
		}else{
			
			$i = 8;
			while (strpos($content, $Pe) == false)
			{
				
				$Pe = str_replace($i, $i++, $Pe);
				
				if ($i > 40)
				{
					
					break;
					
				}
			}
			
			$len = mb_strlen($Pe, CODING_TYPE);
			$replace = str_repeat('*', $len);
			
		}
		
		return $content;
		unset($content);
		unset($replace);
		unset($len);
	}
	/**
	 * 替换函数（非模糊）
	 */
	private function ReturnContent($content, $tmp)
	{
		
		$len = mb_strlen($tmp, CODING_TYPE);
		$replace = str_repeat('*', $len);
		$content = strtr($content, $tmp, $replace);
		return $content;
		unset($content);
		unset($replace);
		unset($len);
		
	}
	/**
	 * 判断文字是否发布
	 * @param $content string 经过上述所有步骤处理后的文字
	 * @return true||false 是否批准发布
	 */
	private function IsAble($content)
	{
		
		$length = mb_strlen($content);
		$t = strspn($content, "*");
		$result = $t / $length;
		
		if ($result > 0.3)
		{
			
			return false;
			
		}else{
			
			return true;
			
		}
	}
	
	/**
	 * 获取所有位置
	 * @param string $content
	 * @param string $tmp
	 * @return array $arr positions
	 */
	private function GetPositions($content, $tmp, $count)
	{
		$j = 0;
		$arr = array();
		
		for($i = 0; $i < $count; $i++)
		{
			
			$j = mb_strpos($content, $tmp, $j, CODING_TYPE);
			$arr[] = $j;
			$j = $j+1;
			
		}
		
		$this->ArrPos[$tmp] = $arr;
		unset($arr);
	}
}
?>
