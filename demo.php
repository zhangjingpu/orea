<?php 
include 'filter.class.php';
$c = <<<EOT
����һ�����дʲ���ʵ��
˫������Υ����ͬ�Ͽɵ���Լ
��Ҫ����
˫������Υ��������������ͬ�Ͽɵ���Լ
��Ҫ������˫��������
EOT;
$chk_words_obj = new Censor($c);
echo ($chk_words_obj->content);
?>