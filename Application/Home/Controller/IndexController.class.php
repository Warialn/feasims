<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {
	//全省数据
    public function index(){
    	//if($_GET){

    		//dump($_GET);die;
    		//$province_name = str_replace('省','',I('data'));
            $province_name = '浙江';
    		$province = M('city')->where(array('name'=>$province_name))->select()[0]['id'];
            if($province!=''){
                $text_count = 0;//不良文本数量
            $pic_count = 0;//图片总数
            $video_count = 0;//视频总数
            $audio_count = 0;//音频总数
            $lab = M('lab')->where(array('province'=>$province))->select();
            $lab_count =count($lab);//机房总数
            foreach ($lab as $key => $value) {
                $text_count = $text_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'1'))->count();
                $pic_count = $pic_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'2'))->count();
                $video_count = $video_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'3'))->count();
                $audio_count = $audio_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'4'))->count();
                //按类型
                $content_type_res[$key] = M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->group('content_type')->field('content_type,count(content_type) as count')->select();
                foreach ($content_type_res[$key] as $ke => $valu) {
                    $valu['content_type'] = $this->info_value('content_type',$valu['content_type']);
                    if(!empty($valu)){
                        $c_result[] = $valu;
                    }
                }
                
                //按机房
                $lab_res[$key] = M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->group('lab_id')->field('lab_id,count(lab_id) as count')->select();
                foreach ($lab_res[$key] as $ke => $valu) {
                    $valu['lab_id'] = M('lab')->where(array('lab_id'=>$valu['lab_id']))->select()[0]['lab_name']; 
                    if(!empty($valu)){
                        $l_result[] = $valu;
                    }
                }
                /****近七天趋势****/
                 //今天
                $where['lab_id'] = $value['lab_id'];
                $start = strtotime(date('Y-m-d 00:00:00'));
                $end = strtotime(date('Y-m-d 23:59:59'));
                $where['hit_time'] = array('between',array($start,$end));
                $today_count = $today_count+M('hit_url_info')->where($where)->count();
                
                //前一天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-1 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $yesterday_count = $yesterday_count+M('hit_url_info')->where($where)->count();
                
                //前两天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-2 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-2 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $before_yesterday_count = $before_yesterday_count + M('hit_url_info')->where($where)->count();
                
                //前三天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-3 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-3 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $threeday_count = $threeday_count+M('hit_url_info')->where($where)->count();
                
                //前四天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-4 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-4 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $fourday_count = $fourday_count+M('hit_url_info')->where($where)->count();
               
                //前五天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-5 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-5 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $fiveday_count = $fiveday_count+M('hit_url_info')->where($where)->count();
                
                //前六天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-6 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-6 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $sixday_count =$sixday_count+ M('hit_url_info')->where($where)->count();
                
            }
            //七天趋势
            $today_res['date'] = date('Y,m-1,d');
            $today_res['date'] = explode(',',$today_res['date']);
            $today_res['count'] = $today_count;
            $yesterday_res['date'] = date('Y,m-1,d',strtotime('-1 day'));
            $yesterday_res['date'] = explode(',',$yesterday_res['date']);
            $yesterday_res['count'] = $yesterday_count;
            $before_yesterday_res['date'] = date('Y,m-1,d',strtotime('-2 day'));
            $before_yesterday_res['date'] = explode(',',$before_yesterday_res['date']);
            $before_yesterday_res['count'] = $before_yesterday_count;
            $threeday_res['date'] = date('Y,m-1,d',strtotime('-3 day'));
            $threeday_res['date'] = explode(',',$threeday_res['date']);
            $threeday_res['count'] = $threeday_count;
            $fourday_res['date'] = date('Y,m-1,d',strtotime('-4 day'));
            $fourday_res['date'] = explode(',',$fourday_res['date']);
            $fourday_res['count'] = $fourday_count;
            $fiveday_res['date'] = date('Y,m-1,d',strtotime('-5 day'));
            $fiveday_res['date'] = explode(',',$fiveday_res['date']);
            $fiveday_res['count'] = $fiveday_count;
            $sixday_res['date'] = date('Y,m-1,d',strtotime('-6 day'));
            $sixday_res['date'] = explode(',',$sixday_res['date']);
            $sixday_res['count'] = $sixday_count;
            //组合成需要的格式
            $seven_res = '['.'Date.UTC('.$sixday_res['date'].")".",".$sixday_count.'],['.'Date.UTC('.$fiveday_res['date'].")".",".$fiveday_count.'],['.'Date.UTC('.$fourday_res['date'].")".",".$fourday_count.'],['.'Date.UTC('.$threeday_res['date'].")".",".$threeday_count.'],['.'Date.UTC('.$before_yesterday_res['date'].")".",".$before_yesterday_count.'],['.'Date.UTC('.$yesterday_res['date'].")".",".$yesterday_count."],[".'Date.UTC('.$today_res['date'].")".",".$today_count."]";
            /*为按类型分布的数据按照类型做去重*/
            $new = array();
            //先做去空
            foreach($c_result as $kk=> $row){
                if($row['content_type'] == NULL){
                    unset($c_result[$kk]);
                }
                
            }
            //再去重
            foreach ($c_result as $key => $row) {
                if(isset($new[$row['content_type']])){
                    $new[$row['content_type']]['count'] += $row['count'];
                }else{
                    $new[$row['content_type']] = $row;
                }
            }
            //拼成需要的格式
            /*foreach($new as $kes=>$vals){               
                $c_res[] = [$vals['content_type'],(int)$vals['count']];                 
            }*/
            foreach($new as $kes=>$vals){               
                $c_res .= "[".'"'.$vals['content_type'].'"'.','.(int)$vals['count'].'],';
                //"[".'"'.$valr['lab_name'].'"'.','.$valr['count'].'],'                 
            }
            //dump($c_res);die;
            /*机房数据去重*/
             $new2 = array();
            foreach ($l_result as $key => $value) {
                if($value['lab_id'] == NULL){
                    unset($l_result[$key]);
                }
            }
            foreach($l_result as $row){
                if(isset($new2[$row['lab_id']])){
                    $new2[$row['lab_id']]['count'] += $row['count'];
                }else{
                    $new2[$row['lab_id']] = $row;
                }
            }
            /*foreach($new2 as $ker=>$valr){
                $l_res[] = [$valr['lab_id'],(int)$valr['count']];
            }*/
            foreach($new2 as $ker=>$valr){
                $l_res .= "[".'"'.$valr['lab_id'].'"'.','.(int)$valr['count'].'],';
            }

        
$this->assign('text_count',$text_count);
$this->assign('pic_count',$pic_count);
$this->assign('video_count',$video_count);
$this->assign('audio_count',$audio_count);
$this->assign('lab_count',$lab_count);
$this->assign('c_res',$c_res);
$this->assign('l_res',$l_res);
$this->assign('seven_res',$seven_res);

/*$this->assign('today_res',$today_res);
$this->assign('yesterday_res',$yesterday_res);
$this->assign('before_yesterday_res',$before_yesterday_res);
$this->assign('threeday_res',$threeday_res);
$this->assign('fourday_res',$fourday_res);
$this->assign('fiveday_res',$fiveday_res);
$this->assign('sixday_res',$sixday_res);*/

        /*$this->ajaxReturn(array('data'=>array('text_count'=>$text_count,'pic_count'=>$pic_count,'video_count'=>$video_count,'audio_count'=>$audio_count,'lab_count'=>$lab_count,'c_res'=>$c_res,'l_res'=>$l_res,'today_res'=>$today_res,'yesterday_res'=>$yesterday_res,'before_yesterday_res'=>$before_yesterday_res,'threeday_res'=>$threeday_res,'fourday_res'=>$fourday_res,'fiveday_res'=>$fiveday_res,'sixday_res'=>$sixday_res)));
            }*/
    		
    	}

            $city_name = '嘉兴';
            $city = M('city')->where(array('name'=>$city_name))->select()[0]['id'];
            $buliang_count = 0;//不良文件数量
            $text_count = 0;//文本总数
            $pic_count = 0;//图片总数
            $video_count = 0;//视频总数
            $audio_count = 0;//音频总数
            $lab = M('lab')->where(array('city'=>$city))->select();
            $lab_count = count($lab);//机房总数
            foreach ($lab as $key => $value) {
                $buliang_count = $buliang_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->count();
                $text_count = $text_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'1'))->count();
                $pic_count = $pic_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'2'))->count();
                $video_count = $video_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'3'))->count();
                $audio_count = $audio_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'4'))->count();
                $lab_buliangcount = M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->count();
                 if($lab_buliangcount == 0){
                    $lab[$key]['buliang_count'] = 0;
                 }else{
                    $lab[$key]['buliang_count'] = $lab_buliangcount;
                 }
             } 
             $data['buliang_count'] = $buliang_count;
             $data['text_count'] = $text_count;
             $data['pic_count'] = $pic_count;
             $data['video_count'] = $video_count;
             $data['audio_count'] = $audio_count;
             $data['lab_count'] = $lab_count;
        $this->assign('data',$data);
        $this->assign('lab_data',$lab);

    	Layout('Layout/layout');
    	$this->display();
    }
    //备用index
    public function index2(){
        if($_GET){

            //dump($_GET);die;
            $province_name = str_replace('省','',I('data'));
            $province = M('city')->where(array('name'=>$province_name))->select()[0]['id'];
            if($province!=''){
                $text_count = 0;//不良文本数量
            $pic_count = 0;//图片总数
            $video_count = 0;//视频总数
            $audio_count = 0;//音频总数
            $lab = M('lab')->where(array('province'=>$province))->select();
            $lab_count =count($lab);//机房总数
            foreach ($lab as $key => $value) {
                $text_count = $text_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'1'))->count();
                $pic_count = $pic_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'2'))->count();
                $video_count = $video_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'3'))->count();
                $audio_count = $audio_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'4'))->count();
                //按类型
                $content_type_res[$key] = M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->group('content_type')->field('content_type,count(content_type) as count')->select();
                foreach ($content_type_res[$key] as $ke => $valu) {
                    $valu['content_type'] = $this->info_value('content_type',$valu['content_type']);
                    if(!empty($valu)){
                        $c_result[] = $valu;
                    }
                }
                
                //按机房
                $lab_res[$key] = M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->group('lab_id')->field('lab_id,count(lab_id) as count')->select();
                foreach ($lab_res[$key] as $ke => $valu) {
                    $valu['lab_id'] = M('lab')->where(array('lab_id'=>$valu['lab_id']))->select()[0]['lab_name']; 
                    if(!empty($valu)){
                        $l_result[] = $valu;
                    }
                }
                /****近七天趋势****/
                 //今天
                $where['lab_id'] = $value['lab_id'];
                $start = strtotime(date('Y-m-d 00:00:00'));
                $end = strtotime(date('Y-m-d 23:59:59'));
                $where['hit_time'] = array('between',array($start,$end));
                $today_count = $today_count+M('hit_url_info')->where($where)->count();
                
                //前一天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-1 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $yesterday_count = $yesterday_count+M('hit_url_info')->where($where)->count();
                
                //前两天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-2 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-2 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $before_yesterday_count = $before_yesterday_count + M('hit_url_info')->where($where)->count();
                
                //前三天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-3 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-3 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $threeday_count = $threeday_count+M('hit_url_info')->where($where)->count();
                
                //前四天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-4 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-4 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $fourday_count = $fourday_count+M('hit_url_info')->where($where)->count();
               
                //前五天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-5 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-5 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $fiveday_count = $fiveday_count+M('hit_url_info')->where($where)->count();
                
                //前六天
                $start = strtotime(date('Y-m-d 00:00:00',strtotime('-6 day')));
                $end = strtotime(date('Y-m-d 23:59:59',strtotime('-6 day'))); 
                $where['hit_time'] = array('between',array($start,$end));
                $sixday_count =$sixday_count+ M('hit_url_info')->where($where)->count();
                
            }
            //七天趋势
            $today_res['date'] = date('Y,m-1,d');
            $today_res['date'] = explode(',',$today_res['date']);
            $today_res['count'] = $today_count;
            $yesterday_res['date'] = date('Y,m-1,d',strtotime('-1 day'));
            $yesterday_res['date'] = explode(',',$yesterday_res['date']);
            $yesterday_res['count'] = $yesterday_count;
            $before_yesterday_res['date'] = date('Y,m-1,d',strtotime('-2 day'));
            $before_yesterday_res['date'] = explode(',',$before_yesterday_res['date']);
            $before_yesterday_res['count'] = $before_yesterday_count;
            $threeday_res['date'] = date('Y,m-1,d',strtotime('-3 day'));
            $threeday_res['date'] = explode(',',$threeday_res['date']);
            $threeday_res['count'] = $threeday_count;
            $fourday_res['date'] = date('Y,m-1,d',strtotime('-4 day'));
            $fourday_res['date'] = explode(',',$fourday_res['date']);
            $fourday_res['count'] = $fourday_count;
            $fiveday_res['date'] = date('Y,m-1,d',strtotime('-5 day'));
            $fiveday_res['date'] = explode(',',$fiveday_res['date']);
            $fiveday_res['count'] = $fiveday_count;
            $sixday_res['date'] = date('Y,m-1,d',strtotime('-6 day'));
            $sixday_res['date'] = explode(',',$sixday_res['date']);
            $sixday_res['count'] = $sixday_count;
            /*为按类型分布的数据按照类型做去重*/
            $new = array();
            //先做去空
            foreach($c_result as $kk=> $row){
                if($row['content_type'] == NULL){
                    unset($c_result[$kk]);
                }
                
            }
            //再去重
            foreach ($c_result as $key => $row) {
                if(isset($new[$row['content_type']])){
                    $new[$row['content_type']]['count'] += $row['count'];
                }else{
                    $new[$row['content_type']] = $row;
                }
            }
            //拼成需要的格式
            foreach($new as $kes=>$vals){               
                $c_res[] = [$vals['content_type'],(int)$vals['count']];                 
            }
            /*机房数据去重*/
             $new2 = array();
            foreach ($l_result as $key => $value) {
                if($value['lab_id'] == NULL){
                    unset($l_result[$key]);
                }
            }
            foreach($l_result as $row){
                if(isset($new2[$row['lab_id']])){
                    $new2[$row['lab_id']]['count'] += $row['count'];
                }else{
                    $new2[$row['lab_id']] = $row;
                }
            }
            foreach($new2 as $ker=>$valr){
                $l_res[] = [$valr['lab_id'],(int)$valr['count']];
            }

        //组合成需要的格式
       /* $seven_res = [Date.UTC($sixday_res['date']),$sixday_count,Date.UTC(.$fiveday_res['date']),$fiveday_count,'Date.UTC('.$fourday_res['date'].")".",".$fourday_count,'Date.UTC('.$threeday_res['date'].")".",".$threeday_count,'Date.UTC('.$before_yesterday_res['date'].")".",".$before_yesterday_count,'Date.UTC('.$yesterday_res['date'].")".",".$yesterday_count,'Date.UTC('.$today_res['date'].")".",".$today_count];
        dump($seven_res);die;*/

        $this->ajaxReturn(array('data'=>array('text_count'=>$text_count,'pic_count'=>$pic_count,'video_count'=>$video_count,'audio_count'=>$audio_count,'lab_count'=>$lab_count,'c_res'=>$c_res,'l_res'=>$l_res,'today_res'=>$today_res,'yesterday_res'=>$yesterday_res,'before_yesterday_res'=>$before_yesterday_res,'threeday_res'=>$threeday_res,'fourday_res'=>$fourday_res,'fiveday_res'=>$fiveday_res,'sixday_res'=>$sixday_res)));
            }
            
        }
        Layout('Layout/layout');
        $this->display();
    }
    //左边数据
    public function get_city(){

    	if($_GET){
            //dump($_GET);die;
    		$city_name = str_replace('市','',I('data'));
    		$city = M('city')->where(array('name'=>$city_name))->select()[0]['id'];
    		$buliang_count = 0;//不良文件数量
    		$text_count = 0;//文本总数
    		$pic_count = 0;//图片总数
    		$video_count = 0;//视频总数
    		$audio_count = 0;//音频总数
    		$lab = M('lab')->where(array('city'=>$city))->select();
    		$lab_count = count($lab);//机房总数
    		foreach ($lab as $key => $value) {
    			$buliang_count = $buliang_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->count();
    			$text_count = $text_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'1'))->count();
    			$pic_count = $pic_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'2'))->count();
    			$video_count = $video_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'3'))->count();
    			$audio_count = $audio_count + M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1','hit_type'=>'4'))->count();
                $lab_buliangcount = M('hit_url_info')->where(array('lab_id'=>$value['lab_id'],'audit_status'=>'1'))->count();
                 if($lab_buliangcount == 0){
                    $lab[$key]['buliang_count'] = 0;
                 }else{
                    $lab[$key]['buliang_count'] = $lab_buliangcount;
                 }
    		 } 


    		 
    		 $this->ajaxReturn(array('data'=>array('buliang_count'=>$buliang_count,'text_count'=>$text_count,'pic_count'=>$pic_count,'video_count'=>$video_count,'audio_count'=>$audio_count,'lab_count'=>$lab_count),'lab_data'=>$lab));
    	}

    }
    public function count_data(){
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
        $outpu2 = curl_exec($ch2);
        $outpu2 = json_decode($outpu2);
        $count2 = $outpu2->total;
        $coun = $count+$count2;
        $this->ajaxReturn($coun);
       
         
    }
    
}