<?php
    /*
     *  data:  2014-8-7
     *  email: github#hilong.com
     *  
     *  目的：理解utf-8编码的字符，使用php
     *  
     *  具体步骤： 产生一个utf-8文件， 
     *  每次都去一个字节(8bit);  然后解析为一个无符号的int类型，之后转换为01码
     */
	
	 /*
	  * 名字是UTF-8   英文全写是  8-bit unicode transformation format
可以理解是 用 可以8-bit 来统一转换格式， 说明这是里 8-bit 是 n个8-bit组。
所以这个 utf-8 这种编码格式是变成长的。意识是说： 可以有1组8bit， 也可以有多组8bit .
具体是 可以用 1 到6 组。 所以最短的utf-8格式的字符需要有一组 8bit， 最多有6组8bit.

因为所有的字符，最终的表示都是0101的串。 这里最重要的一个问题是如何确定一个字符的开始。 
如果知道了，字符的开始，那木字符的结尾也基本确定了。
在utf-8中，字符的开始有六中分别是： 0； 110； 1110； 1111 0； 1111 10；1111 110；
分别对应的是字符长度是 一个8-bit; 两个8-bit;三个8-bit; 四个 8-bit; ..... 
如果一个字符的长度， 多于1组8-bit，那木其余的8-bit组都是10开始的。
比如如果是三个8-bit组的字符串，那木格式应该是 （* 表示可以是0也可以是一）
1110 ****
10** ****
10** ****
这种格式可以很方便的处理。比如一个字符的开始， 必是6个开始中的一个。否则就是不是开始。
字符的开始的8-bit确定了，那木这个字符的长度也就确定了。
还可以很轻松的检查出是否有不争取的字符。 	
	  *
	  */


     /*
      * 对于0101这样的字符串，获得，开始1的个数 
      */
      function get_1_count_in_str01_begin($str01) {
         $cnt = 0;
         foreach(str_split($str01) as $v) {
             if($v == '1') $cnt++;
             else return $cnt;
         }
         return $cnt;
      } 
      


     /*
      *  解析一个字符串，这个字符串是有01组成的， 按照utf8格式解析如果成功了， 就返回解析后的数组
      *  失败返回false
      */
     function parse_utf8_01( $str01 ) {
         $ans = array();
         

         if(strlen($str01)%8 !== 0) return false;
         
         foreach(str_split($str01) as $v) {
             if( ($v != '0') && ($v !='1') ) return false;
         }
      
         $tmp = str_split($str01, 8);

         $nn = 100;
         while(1) {
             $n = get_1_count_in_str01_begin(current($tmp));
             if($n == 0) {
                 $ans[] = current($tmp);
                 next($tmp);
                 
             } else {
                 $t = current($tmp);
                 for($i = 1; $i < $n; $i++) {
                     $t .= next($tmp);
                     if(get_1_count_in_str01_begin(current($tmp)) != '1') return false;
                 }
                 next($tmp);
                 $ans[] = $t;
                 if(!current($tmp)) break;
             }
         }
         return $ans;
     };
    
     
     
    /*
     *  $x 是一个无符号的int类型获得， 它的第 $i 个bit的值
     *  01234567 假设左边是低
     */
    function getbit($x, $i) {
        if( $x & (1 << (7- $i)))    return 1;
        else                    return 0;
    }
    
    /*
     *  把一个无符号的8bit 转为为01的字符串
     */
    function uchar_to_01string($x) {
        if(gettype($x) == 'integer') {
            $str = '';
            for($i = 0; $i < 8; $i++) {
                $str .= getbit($x, $i);
            }
            return $str;
        } 
        return false;
    }
    
    
    header("Content-type:text/html;charset=utf-8");
    
    define("NEWLINE", '</br>');
    $character = 'i我';
    // 获得编码
    $format =  mb_detect_encoding($character);  
    
    // 如果不是utf-8 转换是 utf-8
    if(strtoupper($format) === 'UTF-8' ) {
        // null
    } else {
        $character = iconv($format, 'UTF-8', $character);
    }
    
    
    $fname = tempnam(dirname(__FILE__), 'tmp_'); 
    $f = fopen($fname, 'w+');
    fwrite($f, $character);
    fclose($f);

    //echo filesize($fname);
    // 用二进制的形式获取数据
    $f = fopen($fname, 'rb');
    
    $arr = array();
    while($c = fread($f, 1)) {
        $t = unpack("C", $c);
        $arr[] = $t[1];
    }
    fclose($f);
   
    //var_dump($arr); 
    
    foreach($arr as $v) {
        echo uchar_to_01string($v);
        echo NEWLINE;
    }
    
    
    $str01 = "111001101000100010010001";
    $str02 = "01101001111001101000100011010001";
    var_dump(parse_utf8_01($str02));
    
    
    
    
    