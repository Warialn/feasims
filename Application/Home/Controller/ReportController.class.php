<?php
namespace Home\Controller;
use Think\Controller;
class ReportController extends CommonController {
    public function pro_time(){
        if($_GET){
            if(I('audit_person') !=''){
                $audit_person = I('audit_person');
                $where['audit_person'] = $audit_person;
            }
            if(I('time')!=''){
                $time = I('time');
                $start = strtotime(explode(' - ',$time)[0]);
                $end = strtotime(explode(' - ',$time)[1]);
                $where['audit_time']  = array('between',array($start,$end));
            }
        }
        $res = M('audit_workload')->where($where)->select();
        $this->assign('audit_person',$audit_person);
        $this->assign('time',$time);
        $this->assign('res',$res);
        Layout('Layout/layout');
    	$this->display();
    }
    public function record(){
        $hitip = '';
        $hiturl = '';
        $hitdomain = '';
        $time=date('m/d/Y H:i',strtotime("-1 week"))." - ".date('m/d/Y H:i',time());
        $start = strtotime(explode(' - ',$time)[0]);
        $end = strtotime(explode(' - ',$time)[1]);
        
        if($_GET){
            if(I('lab_id')!=''){
                $where['lab_id'] = I('lab_id');
                $lab_id = I('lab_id');
            }
            if(I('time')!=''){
                $time = I('time');
                $start = strtotime(explode(' - ',$time)[0]);
                $end = strtotime(explode(' - ',$time)[1]);
            }
        }
        $where['audit_time']  = array('between',array($start,$end));
        //$where['audit_status'] = '1';
        //总机房数
        $all_lab = M('lab')->count();
        //中标机房数
        $hit_lab = M('hit_url_info')->group('lab_id')->select();
        $hit_lab_count = count($hit_lab);
        //中标总IP数
        $all_ip = M('hit_url_info')->group('ip')->select();
        $all_ip_count = count($all_ip);
        //网内ip数
        $innet_res = M('ip_address_library')->where(array('ip_type'=>0))->select();
        foreach ($innet_res as $key => $value) {
            $begin_ip = explode('.',$value['begin_ip']);//获取IP段 
            $begin_ip_info = $begin_ip[0].'.'.$begin_ip[1].'.'.$begin_ip[2];
            $end_ip = explode('.',$value['end_ip']);//获取IP段 
            $end_ip_info = $end_ip[0].'.'.$end_ip[1].'.'.$end_ip[2];
            $map['_string'] = " ip like '".$begin_ip_info."%"."' AND ip like '".$end_ip_info."%"."'";
            $net_in[] = count(M('hit_url_info')->group('ip')->where($map)->select());
        }
       // dump($net_in);die;

        foreach ($net_in as $key => $value) {
            $netin_count = $netin_count + $value;
        }
        
        //WAP
        $wap_res = M('ip_address_library')->where(array('ip_type'=>1))->select();
        foreach ($wap_res as $key => $value) {
            $begin_ip = explode('.',$value['begin_ip']);//获取IP段 
            $begin_ip_info = $begin_ip[0].'.'.$begin_ip[1].'.'.$begin_ip[2];
            $end_ip = explode('.',$value['end_ip']);//获取IP段 
            $end_ip_info = $end_ip[0].'.'.$end_ip[1].'.'.$end_ip[2];
            $map['_string'] = " ip like '".$begin_ip_info."%"."' AND ip like '".$end_ip_info."%"."'";
            $wap[] = count(M('hit_url_info')->group('ip')->where($map)->select());
        }

        foreach ($wap as $key => $value) {
            $wap_count = $wap_count + $value;
        }
        //国内
        $china_res = M('ip_address_library')->where(array('ip_type'=>2))->select();
        foreach ($china_res as $key => $value) {
            $begin_ip = explode('.',$value['begin_ip']);//获取IP段 
            $begin_ip_info = $begin_ip[0].'.'.$begin_ip[1].'.'.$begin_ip[2];
            $end_ip = explode('.',$value['end_ip']);//获取IP段 
            $end_ip_info = $end_ip[0].'.'.$end_ip[1].'.'.$end_ip[2];
            $map['_string'] = " ip like '".$begin_ip_info."%"."' AND ip like '".$end_ip_info."%"."'";
            $china[] = count(M('hit_url_info')->group('ip')->where($map)->select());
        }

        foreach ($china as $key => $value) {
            $china_count = $china_count + $value;
        }

        //其他
       /* $other_res = M('ip_address_library')->where(array('ip_type'=>array('notin',array(0,1,2))))->select();
        foreach ($other_res as $key => $value) {
            $begin_ip = explode('.',$value['begin_ip']);//获取IP段 
            $begin_ip_info = $begin_ip[0].'.'.$begin_ip[1].'.'.$begin_ip[2];
            $end_ip = explode('.',$value['end_ip']);//获取IP段 
            $end_ip_info = $end_ip[0].'.'.$end_ip[1].'.'.$end_ip[2];
            $map['_string'] = " ip like '".$begin_ip_info."%"."' AND ip like '".$end_ip_info."%"."'";
            $other[] = M('hit_url_info')->where($map)->count();
        }*/
        $other_count = $all_ip_count - $netin_count - $wap_count -$china_count;

        //中标域名数
        $domain = M('hit_url_info')->group('domain')->select();
        $domain_count = count($domain);
        //中标URL数
        $url = M('hit_url_info')->group('url')->select();
        $url_count = count($url);
        //中标文本数
        $text_count = M('hit_url_info')->where(array('hit_type'=>'1'))->count();
        //中标图片数
        $pic_count = M('hit_url_info')->where(array('hit_type'=>'2'))->count();
        //中标机房统计表
        $lab = M('lab')->select();
        $lab_name = '[';
        $lab_ip_count = '[';
        $lab_url_count = '[';
        foreach ($lab as $key => $value) {
            $lab_name .= "'".$value['lab_name']."'".',';
            $labs[] = $value['lab_name']; 
            $where['lab_id'] = $value['lab_id'];
            $lab_ip = M('hit_url_info')->where($where)->group('lab_id,ip')->select();

            $lab_ip_count .= count($lab_ip).',';
            $lab_url = M('hit_url_info')->where($where)->group('lab_id,url')->select();
            $lab_url_count .= count($lab_url).',' ;

        }
        $lab_name .= ']';
        $lab_ip_count .= ']';
        $lab_url_count .= ']';
        //审核结果统计表
        unset($where['lab_id']);
        $result = M('hit_url_info')->where($where)->group('audit_status')->field('audit_status,count(audit_status) as count')->select();
        foreach ($result as $key => $value) {
            $result[$key]['audit_status'] = $this->info_value('audit_status',$value['audit_status']);
            # code...
        }
        //未处理文本数
        $ch =curl_init();
        
        $pagenum = 1;
        $url = 'http://10.84.1.99:777/hitData?hitip='.$hitip.'&hitdomain='.$hitdomain.'&start='.$start.'&pagesize=10&hiturl='.$hiturl.'&end='.$end.'&pagenum='.$pagenum.'&type=txt';
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $outpu = curl_exec($ch);
        $outpu = json_decode($outpu);
        $no_text_count = $outpu->total;
        //未处理图片数
         $url = 'http://10.84.1.99:777/hitData?hitip='.$hitip.'&hitdomain='.$hitdomain.'&start='.$start.'&pagesize=30&hiturl='.$hiturl.'&end='.$end.'&pagenum=1&type=pic';
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($ch);
        $output = json_decode($output);
        $no_pic_count = $output->total;
        $no_count = $no_text_count+$no_pic_count;
        $array = array(0=>array('audit_status'=>'未处理','count'=>$no_count));
        $result = array_merge($result,$array);
        foreach ($result as $key => $value) {
            $res_count .= '['."'".$value['audit_status']."'".','.$value['count'].'],';
        }
        $type_res = '['.$text_count.",".$pic_count.']';
        $this->assign('type_res',$type_res);
        $this->assign('lab',$lab);
        $this->assign('lab_id',$lab_id);
        $this->assign('time',$time);
        $this->assign('lab_name',$lab_name);
        $this->assign('lab_ip_count',$lab_ip_count);
        $this->assign('netin_count',$netin_count);
        $this->assign('wap_count',$wap_count);
        $this->assign('china_count',$china_count);
        $this->assign('other_count',$other_count);
        $this->assign('lab_url_count',$lab_url_count);
        $this->assign('all_lab',$all_lab);
        $this->assign('hit_lab_count',$hit_lab_count);
        $this->assign('all_ip_count',$all_ip_count);
        $this->assign('domain_count',$domain_count);
        $this->assign('url_count',$url_count);
        $this->assign('text_count',$text_count);
        $this->assign('pic_count',$pic_count);
        $this->assign('result_count',$res_count);

        Layout('Layout/layout');
    	$this->display();
    }
    public function  misjudgement(){
    	Layout('Layout/layout');
    	$this->display();
    }
    public function  IP(){
        Layout('Layout/layout');
        $this->display();
    }
    public function  false(){
        Layout('Layout/layout');
        $this->display();
    }
    public function  realm(){
        Layout('Layout/layout');
        $this->display();
    }
    //参数管理
    public function info_value($col,$value){
        $c = [
            'content_type' => function($v){
                $cc = [
                  '1'                                   => '涉黄',
                  '2'                                   => '涉恐',
                  '3'                                   => '反动',
                  '4'                                   => '其他',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            },
            'content_type_value' => function($v){
                $cc = [
                  '涉黄'                                =>'1',
                  '涉恐'                                =>'2',
                  '反动'                                =>'3',
                  '其他'                                =>'4',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            }, 
            'audit_status' => function($v){
                $cc = [
                  '1'                                   => '违规',
                  '2'                                   => '不确定',
                  '3'                                   => '正常',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            },   
        ];
        return isset($c[$col]) ? $c[$col]($value) : $value;
    }  
   

}