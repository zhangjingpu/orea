<?php
/**
 * Orea���дʴ���ϵͳ�ִʴ�����
 * [example]
 * $chk_words_obj = new Censor($content);
 * $content = $chk_words_obj->content;
 * [/example]
 * @copyright Powered By Yao Li.
 * @version 1.0
 */

include 'oreainfo.php';

class Censor
{
	
	var $content;
	private $RightSituation;//����Ҳ��ִ�
	private $LeftSituation;//�������ִ�
	private $ArrPos; //λ�����飨��ά��
	private $ArrNowWord;
	var $hycd;
	
	/**
	 * ���캯��
	 * �жϴ�������
	 * @param string $content ����Ĵ���������
	*/
	function __construct($content)
	{
		
		$this->content = $content;
		$this->hycd = file_get_contents(OREA_PATH.'/hycd.txt');
		$this->handlewords($content);
		unset($this->hycd);
		
	}
	
	/**
	 * ƥ�亯������������
	 * @return string $content �����������
	 */
	private function handlewords()//��ʽ�������
	{
		
		$tree = unserialize(file_get_contents(OREA_PATH.'/tree.txt'));
		
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
								 * �߼� 
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
		 * �ж��Ƿ���׼����
		 */
		if (!$this->IsAble($this->content))
		{
			
			$this->content = '����������ռ����̫�󣬽�ֹ������';
			
		}
	}
	
	/**
	 * ��һ��ƥ�亯��
	 * @param string $Left ���ʻ�
	 * @param string $Right �Ҳ�ʻ�
	 * @return boolean true||false �Ƿ����
	 * @return boolean RightSituation �Ҳ��Ƿ���
	 * @return boolean LeftSituation ����Ƿ���
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
	 * ����ȷ�Ϻ���
	 * @param string $Vo ����ѯ�Ĵ�
	 * @return true||false boolean ��ѯ���
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
	 * �滻���������� �������ú����滻������������ֵ
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
	 * ָ���滻����
	 * @param string $content
	 * @param string $tmpc
	 * @param string $which
	 * @return string �滻�������
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
	 * �滻������֧��ģ���棩ReturnContent()����ͬ��
	 * �����whileѭ����Ϊ�˱���ѱ��������ߺ�����$Pe��ԭ�ĵĲ������������м����ӿ���ģ����λ��
	 * ����Ҫ����������ѭ�������Ϊ40
	 * @param $content string �����������
	 * @param $Pe string ƥ����ʽ
	 * @return $content string *���滻�������
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
	 * �滻��������ģ����
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
	 * �ж������Ƿ񷢲�
	 * @param $content string �����������в��账��������
	 * @return true||false �Ƿ���׼����
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
	 * ��ȡ����λ��
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
