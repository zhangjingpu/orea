<?php
include 'oreainfo.php';
$dicm = new DicManage();
if (isset($_POST['sbadd']))
{
	if ($_POST['addnew']!='')
	{
		$dicm->AddNew($_POST['addnew']);
	}
}elseif (isset($_POST['sbadds'])){
	if ($_POST['addmh'] != '')
	{
		$dicm->AddNewArr($_POST['addmh']);
	}
}elseif (isset($_POST['sbdel'])){
	if ($_POST['del'] != '')
	{
		$dicm->DeleteWord($_POST['del']);
	}
}
/**
 * Orea敏感词处理系统词库维护类
 * [example]
 * $DicTask = new DicManage();
 * $DicTask->AddNew($_POST['content']);
 * [/example]
 * @copyright Powered By Yao Li. All rights reserved.
 * @version 1.0
 */
class DicManage
{
	/**
	 * 添加新词至mgc.txt
	 * @param string $addnew 欲添加的新词
	 */
	function AddNew($addnew)
	{
		$DicContent = file_get_contents(OREA_PATH.'/mgcdb.txt').$addnew.'<hh>';
		file_put_contents('../mgcdb.txt', $DicContent);
		$this->BuildNewDic();
		echo '<script>alert("添加新词操作成功！");history.go(-1);</script>';
		/*首字表准备
		 * $Header = mb_substr($addnew, 0, CODING_TYPE);
		 */
	}
	/**
	 * 批量添加新词至mgc.txt
	 * @param string $words 要添加的词组
	 */
	function AddNewArr($words)
	{
		$arr = (explode(';',$words));
		foreach ($arr as $tmp)
		{
			$this->AddNew($tmp);
		}
	}
	/**
	 * 构建敏感词条正则
	 * @param string $entry 待处理敏感词
	 * @return string $Pe 生成的正则
	 */
	function BuildEntry($entry)
	{
		$entry=trim($entry);//去除空格防止没有一个能够匹配！
		//敏感词拆分&正则表达式组装
		$rlength = mb_strlen($entry, CODING_TYPE);
		$wlength = strlen($entry);
		if ($rlength != 0)
		{
			$result = $wlength / $rlength;
		}else{
			$result = 0;
		}
		if ($result != 2)//商不为2说明使用explode()时，产生了一个LF换行符，应该截取掉，不然会成乱码
		{
			$entry = substr($entry, 1);
		}
		$len = mb_strlen($entry, CODING_TYPE);
		$Pe = '/';
		for ($i = 0; $i < $len; $i++)
		{
		$char = mb_substr($entry, $i, 1, CODING_TYPE);
		if (strlen($Pe) == 1)
		{
		$Pe = $Pe.$char.'.{0,8}';
		}else{
		$Pe = $Pe.$char.'.{0,8}';
		}
		}
		$Pe = $Pe.'/';
		$Pe = str_replace('.{0,8}/', '/', $Pe);
		return $Pe;
	}
	/**
	 * 删除词汇
	 * @param string $word 欲删除词汇
	 */
	function DeleteWord($word)
	{
		$DicContent = str_replace($word.'<hh>', '', file_get_contents(OREA_PATH.'/mgc.txt'));
		file_put_contents(OREA_PATH.'/mgc.txt', $DicContent);
		$this->BuildNewDic();
		echo '<script>alert("删除成功！");history.go(-1);</script>';
	}
	/**
	 * 列出词库
	 * @param int $lower 列表下限
	 */
	function ListAll($lower)
	{
		$arr = array();
		$DicContent = file_get_contents(OREA_PATH.'/mgcdb.txt');
		$arr = (explode('<hh>',$DicContent));
		/*分页*/
		echo ('<table>');
		if ($lower+10 < count($arr))
		{
			//echo ('CP1');
			for ($i = $lower; $i < $lower + 10; $i++)
			{
				echo ('<tr><td>'.$arr[$i].'</td></tr>');
			}
			if ($i-10 > 0)
			{
				//echo ('CP2');
				$next = $i;
				$upper = $i-20;
				echo ("<tr><td><a href='?t=$next'>下一页</a><a href='?t=$upper'>上一页</a></td></tr>");
			}else{
				//echo ('CP3');
				$next = $i;
				echo ("<tr><td><a href='?t=$next'>下一页</a></td></tr>");
			}
		}else{
			//echo ('CP4');
			for ($i = $lower; $i < count($arr); $i++)
			{
				echo ('<table><tr><td>'.$arr[$i].'</td></tr>');
			}
			$upper = $i-20-($i%10);
			$next = $i - 10 + (count($arr) - $i);
			echo ("<tr><td><a href='?t=$upper'>上一页</a></td></tr>");
		}
		echo('</table>');
	}
	/**
	 * 构建新汉语词典
	 * 只取关联词汇
	 */
	function BuildNewDic()
	{
		$HeadWord = array();
		$TailWord = array();
		$ar = array();
		$NewDic = array();
		$mgc = array(array(),array());
		$TheWord = array();
		$tree = array();
		$NewHead = '';
		$NewTail = '';
		$DicContent = file_get_contents(OREA_PATH.'/mgcdb.txt');
		echo ($DicContent);
		$arr = (explode('<hh>',$DicContent));
		unset ($arr[count($arr)-1]);//删除数组末尾的换行符或休止符
		foreach ($arr as $tmp)
		{
			$NewHead = mb_substr($tmp, 1,1,'gb2312');
			//echo ($tmp.'<br>');
			$SW = $this->BuildEntry($tmp);
			$mgc["$NewHead"][] = $SW;
            $NewTail = mb_substr($tmp, strlen($tmp)-2,2);
			if (array_search($NewHead, $HeadWord) == false)
			{
				$HeadWord[] = $NewHead;
			}
			if (array_search($NewTail, $TailWord) == false)
			{
				$TailWord[] = $NewTail;
			}
		}
		$tree['head'] = $HeadWord;
		$tree['mgc'] = $mgc;
		file_put_contents('tree.txt', serialize($tree));
		file_put_contents(OREA_PATH.'/mgc.txt', serialize($mgc));
		//print_r($HeadWord);
		file_put_contents(OREA_PATH.'/head.txt', serialize($HeadWord));
		unset($arr);
		$file = fopen(OREA_PATH.'/hyck.txt', 'r');
		while(! feof($file))
		{
			$cd = fgets($file);
			$cdh = mb_substr($cd, 0,1,CODING_TYPE);
			if (array_search($cdh, $HeadWord) || array_search($cdh, $TailWord))
			{
				$NewDic[] = preg_replace('/\n/', '', $cd);
			}
		}
		$t = '';
		foreach ($NewDic as $n)
		{
			$t = $t."\r\n".$n;
		}
		//print_r($NewDic);
		file_put_contents(OREA_PATH.'/hycd.txt', $t);
	}
}
?>