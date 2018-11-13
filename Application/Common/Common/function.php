<?php
    function subtext($text, $length)
    {
        if(mb_strlen($text, 'utf8') > $length) 
        return mb_substr($text, 0, $length, 'utf8').'...';
        return $text;
    }
    function authcheck($rule,$t,$f='没有权限'){
    $uid = session('uid');
    if ($uid == 1) {
        return $t;
    }   
    $auth = new \Think\Auth();
    return $auth->check($rule,$uid)?$t:$f;
	}
    function dataCount(){
        $ch =curl_init();
        $hitip = '';
        $hiturl = '';
        $hitdomain = '';
        $time=date('m/d/Y 00:00')." - ".date('m/d/Y 23:59');
        //$time=date('m/d/Y H:i',strtotime("-1 week"))." - ".date('m/d/Y H:i',time());
        $start = strtotime(explode(' - ',$time)[0]);
        $end = strtotime(explode(' - ',$time)[1]);
        $es_res = M('es_setting')->where(array('status'=>1))->select()[0];
        $es_info_res = M('es_setting')->where(array('status'=>2))->select()[0];
        $pagenum = 1;
        $url = 'http://'.$es_res['ip'].":".$es_res['port'].'/hitData?hitip='.$hitip.'&hitdomain='.$hitdomain.'&start='.$start.'&pagesize=10&hiturl='.$hiturl.'&end='.$end.'&pagenum='.$pagenum.'&type=txt';
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $outpu = curl_exec($ch);
        $outpu = json_decode($outpu);
        $count = $outpu->total;
        //dump($count);die;
        $ch2 =curl_init();
        $url2 = 'http://'.$es_res['ip'].":".$es_res['port'].'/hitData?hitip='.$hitip.'&hitdomain='.$hitdomain.'&start='.$start.'&pagesize=30&hiturl='.$hiturl.'&end='.$end.'&pagenum=1&type=pic';
        curl_setopt($ch2,CURLOPT_URL,$url);
        curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
        $url = 'http://'.$es_res['ip'].":".$es_res['port'].'/hitData?hitip='.$hitip.'&hitdomain='.$hitdomain.'&start='.$start.'&pagesize=10&hiturl='.$hiturl.'&end='.$end.'&pagenum='.$pagenum.'&type=txt';
        // $outpu2 = curl_exec($ch2);
        $outpu2 = json_decode($outpu2);
        $count2 = $outpu2->total;
        $coun = $count + $count2;
        return (int)$coun;
        //dump($count+$count2);die;
         
    }