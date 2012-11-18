<?php 
include 'filter.class.php';
$c = <<<EOT
这是一个敏感词测试实例
双方不得违反共同认可的条约
我要卖肾
双方不得违反。。啊。。共同认可的条约
我要卖。。双方。。肾
EOT;
$chk_words_obj = new Censor($c);
echo ($chk_words_obj->content);
?>