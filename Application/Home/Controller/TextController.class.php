<?php
namespace Home\Controller;
use Think\Controller;
use Think\Tree;
/*2018-5-31 wnn update*/
class TextController extends CommonController {
    //文本初审页面展示
    public function text_first_trial(){

        $ch =curl_init();
        $hitip = '';
        $hiturl = '';
        $hitdomain = '';
        $time=date('m/d/Y H:i',strtotime("-1 week"))." - ".date('m/d/Y H:i',time());
        $start = strtotime(explode(' - ',$time)[0]);
        $end = strtotime(explode(' - ',$time)[1]);
        
        if($_GET){
            $hitip = I('hitip');
            $hiturl = I('hiturl');
            $hitdomain = I('hitdomain');
            $time = I('time');
            if($time !=''){
                $start = strtotime(explode(' - ',$time)[0]);
                $end = strtotime(explode(' - ',$time)[1]);
            }
            
        }

        $es_res = M('es_setting')->where(array('status'=>1))->select()[0];
        $es_info_res = M('es_setting')->where(array('status'=>2))->select()[0];
        $pagenum = 1;
        $url = 'http://'.$es_res['ip'].":".$es_res['port'].'/hitData?hitip='.$hitip.'&hitdomain='.$hitdomain.'&start='.$start.'&pagesize=10&hiturl='.$hiturl.'&end='.$end.'&pagenum='.$pagenum.'&type=txt';
        //dump($url);die;

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $outpu = curl_exec($ch);
        $outpu = json_decode($outpu);
        $count = $outpu->total;
        //dump($count);die;
        $Page  = new \Think\Page($count,15);// 实例化分页类 传入总记录数
        if(!isset($Page->parameter['p'])){
            $Page->parameter['p'] = 0;
        }
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        //$Page->firstRow = 2;
        $url = 'http://'.$es_res['ip'].":".$es_res['port'].'/hitData?hitip='.$hitip.'&hitdomain='.$hitdomain.'&start='.$start.'&pagesize='.$Page->listRows.'&hiturl='.$hiturl.'&end='.$end.'&pagenum='.$Page->parameter['p'].'&type=txt';
        //dump($url);die;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($ch);
        $output = json_decode($output);
        $output = $this->obj_array($output);
        
        foreach ($output['data'] as $key => $value) {
            if(is_object($value)){
                $output['data'][$key] = $this->obj_array($value);
            }
            foreach ($output['data'][$key]['textkeywordlist'] as $ke => $valu) {
                if(is_object($valu)){
                    $output['data'][$key]['textkeywordlist'][$ke] = $this->obj_array($valu);
                } 
            }
            foreach ($output['data'][$key]['textiptotalhitlist'] as $ke => $valu) {
                if(is_object($valu)){
                    $output['data'][$key]['textiptotalhitlist'][$ke] = $this->obj_array($valu);
                } 
            }
                
        }
        //dump($output);die;
       
        foreach ($output['data'] as $kee => $vale) {
            foreach ($vale['textkeywordlist'] as $ke => $valu) {
                $result[$kee]['textword'] .= $valu['textkeyword'].",";
                $result[$kee]['textkeywordtype'] = $this->info_value('content_type',$valu['textkeywordtype']);                             
            }             
            
            $result[$kee]['lab_id'] = $vale['textlabtotallist']['textlabid'];
            $result[$kee]['lab_name'] = M('lab')->where(array('lab_id'=>$result[$kee]['lab_id']))->select()[0]['lab_name'];
            foreach($vale['textiptotalhitlist'] as $k => $val){
                $result[$kee]['hit_data_num'] = $result[$kee]['hit_data_num']+$val['texttotalipcnt'];
                $result[$kee]['textdstip'] .= $val['textdstip'].",";
            }
           //dump($result);die;
            $result[$kee]['textword'] = substr($result[$kee]['textword'],0,-1);
            $result[$kee]['textdstip'] = substr($result[$kee]['textdstip'],0,-1);
            $result[$kee]['textdomain'] = $vale['textdomain'];
            $result[$kee]['id'] = $vale['id'];
            $result[$kee]['textfilepath'] = $vale['textfilepath'];
            $result[$kee]['textfilename'] = $vale['textfilename'];
            $result[$kee]['texturl'] = $vale['texturl']."/".$vale['textfilename']; 
            $result[$kee]['ruleurl'] = 'http://'.$es_info_res['ip'].":".$es_info_res['port'].$result[$kee]['textfilepath']."/".$result[$kee]['textfilename'];
            $result[$kee]['textupdatetime'] = substr($vale['textupdatetime'],0,-3);
            $result[$kee]['textarrtime'] = substr($vale['textarrtime'],0,-3);
            
        }
        //dump($result);die;
        $show = $Page->show();
        $this->assign('show', $show);
        curl_close($ch);
        $this->assign('count',$count);
        
        $this->assign('res',$result);
        $this->assign('hitip',$hitip);
        $this->assign('hiturl',$hiturl);
        $this->assign('hitdomain',$hitdomain);
        $this->assign('time',$time);
        Layout('Layout/layout');
        $this->display();

    }
    //执行文本初审
    public function do_text_first_trial(){
        if($_GET){
            //dump($_GET);die;
            $id = I('id');
            $status = I('status');
            $index_time = date('Y-m-d',I('index_time'));
            $es_res = M('es_setting')->where(array('status'=>1))->select()[0];
            $es_info_res = M('es_setting')->where(array('status'=>2))->select()[0];
            /******告知给Java那边的es接口******/
            $url = 'http://'.$es_res['ip'].":".$es_res['port'].'/updateFlag?type=txt&index='.$index_time.'&id='.$id;
            //dump($url);die;
            $ch =curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            $out = curl_exec($ch);
            curl_close($ch);
            /******需要插入到hit_url_info表里面,并且创建三棵树******/

                
            /******ip树******/
            $ips = explode(',',I('ip'));
            foreach ($ips as $key => $value) {
                if($value !='' ){
                    $ip = explode('.',$value);//获取IP段 
                    $ip_info = $ip[0].'.'.$ip[1].'.'.$ip[2];            
                    $ip_res = M('ip_tree')->where(array('ip'=>$ip_info))->order('id')->select()[0];
                    if($ip_res){//该IP段已经被记录
                        $ip_data['ip'] = $value;
                        $ip_data['parent_id'] = $ip_res['id'];
                        $ip_data['domain'] = I('domain');
                        $check_ip = M('ip_tree')->where(array('ip'=>$value))->select()[0]['ip'];
                        if(!$check_ip){
                             M('ip_tree')->add($ip_data);
                        }
                    }else{//该IP段未记录
                        $ip_data['parent_id'] = 0;
                        $ip_data['ip'] = $ip_info;
                        $ip_data['domain'] = I('domain');
                        M('ip_tree')->add($ip_data);//先记录该IP段
                        $data['ip_id'] .=M('ip_tree')->where(array('parent_id'=>0))->order('id desc')->select()[0]['id'].",";//ip_id与hit_url_info表里面的ip_id做关联
                        
                        $ip_data1['parent_id'] = M('ip_tree')->where(array('parent_id'=>0))->order('id desc')->select()[0]['id'];
                        $ip_data1['ip'] = $value;
                        $ip_data1['domain'] = I('domain');
                        $check_ip = M('ip_tree')->where(array('ip'=>$value))->select()[0]['ip'];
                        if(!$check_ip){
                            M('ip_tree')->add($ip_data1);//再记录那个真实的IP
                        }
                    }
                } 
            }

            /******域名树******/
            $domains = explode('.',I('domain'));
               
            //此处通过“.”的个数来判断属于几级域名，如果你有更好的方法欢迎修改。      
            if(count($domains)=='2'){//含有一个点的属于一级域名
                $domain_data['domain_type'] = 0;

            }elseif(count($domains)=='3'){//含有二个点的属于二级域名,以下依次类推
                $domain_data['domain_type'] = 1;
               
            }elseif(count($domains)=='4'){//三级域名
                $domain_data['domain_type'] = 2;
                
            }elseif(count($domains)=='5'){//四级域名
                $domain_data['domain_type'] = 3;
               
            }elseif(count($domains)=='6'){//五级域名
                $domain_data['domain_type'] = 4;
            }
            $domain_data['parent_id'] = $this->domain_parent_id($domains);                
            $domain_data['ip'] = I('ip');
            $domain_data['domain'] = I('domain');
            $domain_data['insert_time'] = time();
            //有四个待利用的字段需要填为空
            $domain_data['domain_status'] = '';
            $domain_data['domain_alloc_status'] = '';
            $doamin_data['draw_time'] = '';
            $domain_data['draw_person'] = '';
            M('domain_tree')->add($domain_data);


            /******机房树******/
            $lab_data['lab_id'] = I('lab_id');
            $lab_data['domain'] = I('domain');
            $lab_data['parent_id'] = '';
            M('lab_tree')->add($lab_data);

            /******中标信息表（hit_url_info）******/
            $data['parent_id'] = '';
            $data['audit_status'] = $status;
            $data['domain'] = I('domain');
            $data['url'] = I('url');
            $data['ip'] = I('ip');
            $data['ip_id'] = substr($data['ip_id'],0,-1);;
            $data['hit_time'] = strtotime(I('hit_time'));
            $data['content_type'] = $this->info_value('content_type_value',I('content_type'));
            $data['hit_content'] = I('textword');
            $data['hit_type'] = '1';
            $data['file_path'] = I('file_path');
            $data['file_name'] = I('file_name');
            $data['draw_person'] = $_SESSION['username'];
            $data['audit_time'] = time();
            $data['update_time'] = I('update_time');
            $data['hit_data_num'] = I('hit_data_num');
            $data['lab_id'] = I('lab_id');
            //复审才需要用到的字段
            $data['review_auditor'] = '';
            M('hit_url_info')->add($data);
        }
        A('Home/Common')->log('Web', '执行文本初审', 5);
        $this->ajaxReturn(array('status'=>'success'));

    }
    //计算域名树各个域名的parent_id
    public function domain_parent_id($domains){
        if(count($domains)=='2'){//含有一个点的属于一级域名
            $domain_data['domain_type'] = 0;
            $domain = I('domain');
            $domain_res = M('domain_tree')->where(array('domain'=>$domain))->order('id')->select()[0];
            if($domain_res){
                $domain_data['parent_id'] = $domain_res['id'];
            }else{
                $domain_data['parent_id'] = 0;
            }

        }elseif(count($domains)=='3'){//含有二个点的属于二级域名,以下依次类推
            $domain_data['domain_type'] = 1;
            $domain0 = $domains[1].".".$domains[2];
            $domain1 = I('domain');                   
            //一级域名是否存在
            $domain0_res = M('domain_tree')->where(array('domain'=>$domain0))->order('id')->select()[0];
             //二级域名是否存在
            $domain1_res = M('domain_tree')->where(array('domain'=>$domain1))->order('id')->select()[0];                  
            if($domain0_res){
                if($domain1_res){
                    $domain_data['parent_id'] = $domain1_res['id'];
                }else{
                    $domain_data['parent_id'] = $domain0_res['id'];
                }
            }else{
                $domain_data['parent_id'] = 0;
            }
        }elseif(count($domains)=='4'){//三级域名
            $domain_data['domain_type'] = 2;
            $domain0 = $domains[2].".".$domains[3];
            $domain1 = $domains[1].".".$domains[2].'.'.$domains[3];
            $domain2 = I('domain');                 
            //一级域名是否存在
            $domain0_res = M('domain_tree')->where(array('domain'=>$domain0))->order('id')->select()[0];
            //二级域名是否存在
            $domain1_res = M('domain_tree')->where(array('domain'=>$domain1))->order('id')->select()[0];
            //三级域名是否存在
            $domain2_res = M('domain_tree')->where(array('domain'=>$domain2))->order('id')->select()[0];                  
            if($domain0_res){
                if($domain1_res){
                    if($domain2_res){
                        $domain_data['parent_id'] = $domain2_res['id'];
                    }else{
                        $domain_data['parent_id'] = $domain1_res['id'];
                    }                           
                }else{
                    $domain_data['parent_id'] = $domain0_res['id'];
                }
            }else{
                $domain_data['parent_id'] = 0;
            }
        }elseif(count($domains)=='5'){//四级域名
            $domain_data['domain_type'] = 3;
            $domain0 = $domains[3].".".$domains[4];
            $domain1 = $domains[2].".".$domains[3].'.'.$domains[4];
            $domain2 = $domains[1].".".$domains[2].".".$domains[3].'.'.$domains[4];
            $domain3 = I('domain');                 
            //一级域名是否存在
            $domain0_res = M('domain_tree')->where(array('domain'=>$domain0))->order('id')->select()[0];
            //二级域名是否存在
            $domain1_res = M('domain_tree')->where(array('domain'=>$domain1))->order('id')->select()[0];
            //三级域名是否存在
            $domain2_res = M('domain_tree')->where(array('domain'=>$domain2))->order('id')->select()[0];
            //四级域名是否存在
            $domain3_res = M('domain_tree')->where(array('domain'=>$domain3))->order('id')->select()[0];                  
            if($domain0_res){
                if($domain1_res){
                    if($domain2_res){
                        if($domain3_res){
                            $domain_data['parent_id'] = $domain3_res['id'];
                        }else{
                            $domain_data['parent_id'] = $domain2_res['id'];
                        }                                
                    }else{
                        $domain_data['parent_id'] = $domain1_res['id'];
                    }                           
                }else{
                    $domain_data['parent_id'] = $domain0_res['id'];
                }
            }else{
                $domain_data['parent_id'] = 0;
            }
        }elseif(count($domains)=='6'){//五级域名
            $domain_data['domain_type'] = 4;
            $domain0 = $domains[4].'.'.$domains[5];
            $domain1 = $domains[3].'.'.$domains[4].'.'.$domains[5];
            $domain2 = $domains[2].".".$domains[3].'.'.$domains[4].'.'.$domains[5];
            $domain3 = $domains[1].".".$domains[2].".".$domains[3].'.'.$domains[4].'.'.$domains[5];
            $domain4 = I('domain');                 
            //一级域名是否存在
            $domain0_res = M('domain_tree')->where(array('domain'=>$domain0))->order('id')->select()[0];
            //二级域名是否存在
            $domain1_res = M('domain_tree')->where(array('domain'=>$domain1))->order('id')->select()[0];
            //三级域名是否存在
            $domain2_res = M('domain_tree')->where(array('domain'=>$domain2))->order('id')->select()[0];
            //四级域名是否存在
            $domain3_res = M('domain_tree')->where(array('domain'=>$domain3))->order('id')->select()[0];
            //五级域名是否存在
            $domain4_res = M('domain_tree')->where(array('domain'=>$domain4))->order('id')->select()[0];                  
            if($domain0_res){
                if($domain1_res){
                    if($domain2_res){
                        if($domain3_res){
                            if($domain4_res){
                                $domain_data['parent_id'] = $domain4_res['id'];
                            }else{
                                $domain_data['parent_id'] = $domain3_res['id'];
                            }                                   
                        }else{
                            $domain_data['parent_id'] = $domain2_res['id'];
                        }                                
                    }else{
                        $domain_data['parent_id'] = $domain1_res['id'];
                    }                           
                }else{
                    $domain_data['parent_id'] = $domain0_res['id'];
                }
            }else{
                $domain_data['parent_id'] = 0;
            }
        }
        return $domain_data['parent_id'];

    }
    //文本复审页面
    public function text_re_trial(){
        $time=date('m/d/Y H:i',strtotime("-1 week"))." - ".date('m/d/Y H:i',time());
        $start = strtotime(explode(' - ',$time)[0]);
        $end = strtotime(explode(' - ',$time)[1]);
        $where['hit_time'] = array('between',array($start,$end));
        if($_GET){
            if(I('hitip')!=''){
                $hitip = I('hitip');
                $where['ip'] = $hitip;
            }
            if(I('hiturl')!=''){
                $hiturl = I('hiturl'); 
                $where['url'] = array('like','%'.$hiturl.'%');;
            }
            if(I('hitdomain')!=''){
                $hitdomain = I('hitdomain');
                $where['domain'] = $hitdomain;
            }
            if(I('time')!=''){
                $time = I('time');
                if($time !=''){
                    $start = strtotime(explode(' - ',$time)[0]);
                    $end = strtotime(explode(' - ',$time)[1]);                   
                }
                $where['hit_time'] = array('between',array($start,$end));
            }  
        }
        $where['hit_type'] = 1;
        $where['audit_status'] = array('neq','3');

        $where['review_auditor'] = '';
        //dump($where);die;
        $count = M('hit_url_info')->where($where)->count();
        $Page  = new \Think\Page($count,15);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $res = M('hit_url_info')->where($where)->order('hit_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($res as $key => $value) {
            $res[$key]['content_type'] = $this->info_value('content_type',$value['content_type']);
            $res[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
        }
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('show', $show);
        $this->assign('res',$res);
        $this->assign('hitip',$hitip);
        $this->assign('hiturl',$hiturl);
        $this->assign('hitdomain',$hitdomain);
        $this->assign('time',$time);
        Layout('Layout/layout');
        $this->display();

    }
    //执行文本复审
    public function do_text_re_trial(){
        if($_GET){
            $id = I('id');
            if(I('status')){
               $data['audit_status'] = I('status'); 
            }
            $data['review_auditor'] = $_SESSION['username'];
            $data['audit_time'] = time();
            $res = M('hit_url_info')->where(array('id'=>$id))->save($data);
        }
        if($res){
             A('Home/Common')->log('Web', '执行文本复审', 5);
            $this->ajaxReturn(array('status'=>'success'));
        }

    }
    //不确定文本内容
    public function text_nosure(){
        $time=date('m/d/Y H:i',strtotime("-1 week"))." - ".date('m/d/Y H:i',time());
        $start = strtotime(explode(' - ',$time)[0]);
        $end = strtotime(explode(' - ',$time)[1]);
        $where['hit_time'] = array('between',array($start,$end));
        if($_GET){
            if(I('hitip')!=''){
                $hitip = I('hitip');
                $where['ip'] = $hitip;
            }
            if(I('hiturl')!=''){
                $hiturl = I('hiturl'); 
                $where['url'] = array('like','%'.$hiturl.'%');;
            }
            if(I('hitdomain')!=''){
                $hitdomain = I('hitdomain');
                $where['domain'] = $hitdomain;
            }
            if(I('time')!=''){
                $time = I('time');
                if($time !=''){
                    $start = strtotime(explode(' - ',$time)[0]);
                    $end = strtotime(explode(' - ',$time)[1]);                   
                }
                $where['hit_time'] = array('between',array($start,$end));
            }  
        }
        $where['hit_type'] = 1;
        $where['audit_status'] = 2;
        $count = M('hit_url_info')->where($where)->count();
        $Page  = new \Think\Page($count,15);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $res = M('hit_url_info')->where($where)->order('hit_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($res as $key => $value) {
            $res[$key]['content_type'] = $this->info_value('content_type',$value['content_type']);
            $res[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            if($value['audit_time']!=''){
                $res[$key]['audit_time'] = $value['audit_time'];
            }
            if($value['audit_time'] !=''){
                $res[$key]['audit_time'] =$value['audit_time'];
            }
        }
        $show = $Page->show();
        $this->assign('show', $show);
        $this->assign('res',$res);
        $this->assign('hitip',$hitip);
        $this->assign('hiturl',$hiturl);
        $this->assign('hitdomain',$hitdomain);
        $this->assign('time',$time);
        Layout('Layout/layout');
        $this->display();
        
    }
    //执行不确定文本审核
    public function do_text_nosure(){
        if($_GET){
            $id = I('id');
            if(I('status')){
               $data['audit_status'] = I('status'); 
            }
            $data['review_auditor'] = $_SESSION['username'];
            $data['audit_time'] = time();
            $res = M('hit_url_info')->where(array('id'=>$id))->save($data);
            if($res){
                A('Home/Common')->log('Web', '执行文本不确定审核', 5);
                $this->ajaxReturn(array('status'=>'success'));
            }
        }
    }
    //文本审核结果
    public function text_result(){
        $time=date('m/d/Y H:i',strtotime("-1 week"))." - ".date('m/d/Y H:i',time());
        $start = strtotime(explode(' - ',$time)[0]);
        $end = strtotime(explode(' - ',$time)[1]);
        $where['hit_time'] = array('between',array($start,$end));
        if($_GET){
            if(I('hitip')!=''){
                $hitip = I('hitip');
                $where['ip'] = $hitip;
            }
            if(I('hiturl')!=''){
                $hiturl = I('hiturl'); 
                $where['url'] = array('like','%'.$hiturl.'%');;
            }
            if(I('hitdomain')!=''){
                $hitdomain = I('hitdomain');
                $where['domain'] = $hitdomain;
            }
            if(I('audit_status')!=''){
                $audit_status = I('audit_status');
                $where['audit_status'] = $audit_status;
            }
            if(I('time')!=''){
                $time = I('time');
                if($time !=''){
                    $start = strtotime(explode(' - ',$time)[0]);
                    $end = strtotime(explode(' - ',$time)[1]);                   
                }
                $where['hit_time'] = array('between',array($start,$end));
            }  
        }
        $where['hit_type'] = 1;
        $count = M('hit_url_info')->where($where)->count();
        $Page  = new \Think\Page($count,15);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $res = M('hit_url_info')->where($where)->order('hit_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($res as $key => $value) {
            $res[$key]['audit_status'] = $this->info_value('audit_status',$value['audit_status']);
            $res[$key]['content_type'] = $this->info_value('content_type',$value['content_type']);
            $res[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $blacks = explode(',',$value['is_blackormiss']);
            if($value['audit_status'] == '1'){
                if(in_array('1',$blacks) ){
                    $res[$key]['is_ip'] = '1';
                }else{
                    $res[$key]['is_ip'] = '0';
                }
                if(in_array('2',$blacks)){
                    $res[$key]['is_url'] = '1';
                }else{
                    $res[$key]['is_url'] = '0';
                }
                $res[$key]['is_miss']='2';
            }elseif($value['audit_status'] == '3'){
                $res[$key]['is_ip'] = '2';
                $res[$key]['is_url'] = '2';
               if(in_array('3',$blacks)){
                    $res[$key]['is_miss'] = '1';
                }else{
                    $res[$key]['is_miss'] = '0';
                } 
            }
        }
        //按类型
        $content_type_res = M('hit_url_info')->where($where)->group('audit_status')->field('audit_status,count(audit_status) as count')->select();
        foreach($content_type_res as $ker=>$valr){
            $valr['audit_status'] = $this->info_value('audit_status',$valr['audit_status']);
            $c_res .= "[".'"'.$valr['audit_status'].'"'.','.$valr['count'].'],';
        }
        //按机房
        $lab_res = M('hit_url_info')->where($where)->group('lab_id')->field('lab_id,count(lab_id) as count')->select();
        foreach($lab_res as $ker=>$valr){
            $valr['lab_name'] = M('lab')->where(array('lab_id'=>$valr['lab_id']))->select()[0]['lab_name'];
            $l_res .= "[".'"'.$valr['lab_name'].'"'.','.$valr['count'].'],';
        }
        /****近七天趋势****/
         //今天
        $start = strtotime(date('Y-m-d 00:00:00'));
        $end = strtotime(date('Y-m-d 23:59:59'));
        $where['hit_time'] = array('between',array($start,$end));
        $today_count = M('hit_url_info')->where($where)->count();
        $today_res['date'] = date('Y,m-1,d');
        $today_res['count'] = $today_count;
        //前一天
        $start = strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
        $end = strtotime(date('Y-m-d 23:59:59',strtotime('-1 day'))); 
        $where['hit_time'] = array('between',array($start,$end));
        $yesterday_count = M('hit_url_info')->where($where)->count();
        $yesterday_res['date'] = date('Y,m-1,d',strtotime('-1 day'));
        $yesterday_res['count'] = $yesterday_count;
        //前两天
        $start = strtotime(date('Y-m-d 00:00:00',strtotime('-2 day')));
        $end = strtotime(date('Y-m-d 23:59:59',strtotime('-2 day'))); 
        $where['hit_time'] = array('between',array($start,$end));
        $before_yesterday_count = M('hit_url_info')->where($where)->count();
        $before_yesterday_res['date'] = date('Y,m-1,d',strtotime('-2 day'));
        $before_yesterday_res['count'] = $before_yesterday_count;
        //前三天
        $start = strtotime(date('Y-m-d 00:00:00',strtotime('-3 day')));
        $end = strtotime(date('Y-m-d 23:59:59',strtotime('-3 day'))); 
        $where['hit_time'] = array('between',array($start,$end));
        $threeday_count = M('hit_url_info')->where($where)->count();
        $threeday_res['date'] = date('Y,m-1,d',strtotime('-3 day'));
        $threeday_res['count'] = $threeday_count;
        //前四天
        $start = strtotime(date('Y-m-d 00:00:00',strtotime('-4 day')));
        $end = strtotime(date('Y-m-d 23:59:59',strtotime('-4 day'))); 
        $where['hit_time'] = array('between',array($start,$end));
        $fourday_count = M('hit_url_info')->where($where)->count();
        $fourday_res['date'] = date('Y,m-1,d',strtotime('-4 day'));
        $fourday_res['count'] = $fourday_count;
        //前五天
        $start = strtotime(date('Y-m-d 00:00:00',strtotime('-5 day')));
        $end = strtotime(date('Y-m-d 23:59:59',strtotime('-5 day'))); 
        $where['hit_time'] = array('between',array($start,$end));
        $fiveday_count = M('hit_url_info')->where($where)->count();
        $fiveday_res['date'] = date('Y,m-1,d',strtotime('-5 day'));
        $fiveday_res['count'] = $fiveday_count;
        //前六天
        $start = strtotime(date('Y-m-d 00:00:00',strtotime('-6 day')));
        $end = strtotime(date('Y-m-d 23:59:59',strtotime('-6 day'))); 
        $where['hit_time'] = array('between',array($start,$end));
        $sixday_count = M('hit_url_info')->where($where)->count();
        $sixday_res['date'] = date('Y,m-1,d',strtotime('-6 day'));
        $sixday_res['count'] = $sixday_count;
        //组合成需要的格式
        $seven_res = '['.'Date.UTC('.$sixday_res['date'].")".",".$sixday_count.'],['.'Date.UTC('.$fiveday_res['date'].")".",".$fiveday_count.'],['.'Date.UTC('.$fourday_res['date'].")".",".$fourday_count.'],['.'Date.UTC('.$threeday_res['date'].")".",".$threeday_count.'],['.'Date.UTC('.$before_yesterday_res['date'].")".",".$before_yesterday_count.'],['.'Date.UTC('.$yesterday_res['date'].")".",".$yesterday_count."],[".'Date.UTC('.$today_res['date'].")".",".$today_count."]";
        $this->assign('three_res',$seven_res);
        $this->assign('l_res',$l_res);
        $this->assign('c_res',$c_res);
        $show = $Page->show();
        $this->assign('show', $show);
        $this->assign('res',$res);
        $this->assign('audit_status',$audit_status);
        $this->assign('hitip',$hitip);
        $this->assign('hiturl',$hiturl);
        $this->assign('hitdomain',$hitdomain);
        $this->assign('time',$time);
        Layout('Layout/layout');
        $this->display();
    }
    //加入IP黑名单
    public function do_result_ip(){
        if($_GET){
            $id = I('id');
            $result = M('hit_url_info')->where(array('id'=>$id))->select()[0];
            if($result['is_blackormiss']){
                $dat['is_blackormiss'] = $result['is_blackormiss'].',1';
            }else{
                $dat['is_blackormiss'] = '1';
            }
            
            M('hit_url_info')->where(array('id'=>$id))->save($dat);
            $data['control_url_type'] = $result['content_type'];
            $data['ip_location'] = '';
            $data['lab_id'] = $result['lab_id'];
            $data['control_ip'] = $result['ip'];
            $data['block_flag'] = '0';
            $data['affective_time'] = time();           
            $data['flag'] = 1;
            $res = M('blacklist_ip_strategy')->add($data);
        }
        if($res){
             A('Home/Common')->log('Web', '加入黑名单IP', 5);
            $this->ajaxReturn(array('status'=>'success'));
        }

    }
    //加入URL黑名单
    public function do_result_url(){
        if($_GET){
            $id = I('id');
            $result = M('hit_url_info')->where(array('id'=>$id))->select()[0];
            if($result['is_blackormiss']){
                $dat['is_blackormiss'] = $result['is_blackormiss'].',2';
            }else{
                $dat['is_blackormiss'] = '2';
            }
            M('hit_url_info')->where(array('id'=>$id))->save($dat);
            $data['control_url_type'] = $result['content_type'];
            $data['ip_location'] = '';
            $data['lab_id'] = $result['lab_id'];
            $data['control_url'] = $result['url'];
            $data['block_flag'] = '0';
            $data['affective_time'] = time();
            $data['flag'] = 1;
            $res = M('blacklist_url_strategy')->add($data);
        }
        if($res){
             A('Home/Common')->log('Web', '加入黑名单URL', 5);
            $this->ajaxReturn(array('status'=>'success'));
        }

    }
    //加入误判库
    public function do_result_missjudge(){
        if($_GET){
            $id = I('id');
            $result = M('hit_url_info')->where(array('id'=>$id))->select()[0];
            if($result['is_blackormiss']){
                $dat['is_blackormiss'] = $result['is_blackormiss'].',3';
            }else{
                $dat['is_blackormiss'] = '3';
            }
            M('hit_url_info')->where(array('id'=>$id))->save($dat);
            $data['ip'] = $result['ip'];
            $data['domain'] =$result['domain'];
            $data['url'] = $result['url'];
            $data['flag'] = 1;
            $res = M('misjudgement_library_strategy')->add($data);
        }
        if($res){
             A('Home/Common')->log('Web', '加入误判库', 5);
            $this->ajaxReturn(array('status'=>'success'));
        }
        
    }
    //将对象转化为数组
    public function obj_array($obj){
        if(is_object($obj)){
            foreach($obj as $key=>$value){
                if(is_object($value)){
                    $res=$this->obj_array($value);
                    $result[$key] = $res;
                }else{
                    $result[$key] = $value; 
                }                
            }
        }       
        return $result; 
    }
    //文本详情
    public function text_info(){ 
        if($_POST){
            //dump($_POST);die;
            $keyword = I('textword');
            $file_path = I('filepath');
            $encode = mb_detect_encoding($keyword,array('ASCII','UTF-8','GB2312','GBK','BIG5','CP936'));           
            $contents = file_get_contents($file_path);
            $encode2 = mb_detect_encoding($contents,array('ASCII','UTF-8','GB2312','GBK','BIG5','CP936'));
            $contents = iconv($encode2, 'UTF-8', $contents);
            $keys = explode(',',$keyword);
            $preg = "/<script[\s\S]*?<\/script>/i";
            $preg2 = '/<img.*?src=\"(.*?)\".*?>/';
            $preg3 = '/<link(.*?)[^>]*>/i';
            $preg4 = '/<a.*?href="(.*?)".*?>*<\/a>/is';
            $contents = preg_replace($preg, '', $contents,20);
            $contents = preg_replace($preg2, '', $contents,50);
            $contents = preg_replace($preg3, '', $contents,20);
            $contents = preg_replace($preg4, '', $contents,20);
            //$encode = mb_detect_encoding($contents,array('ASCII','UTF-8','GB2312','GBK','BIG5','CP936'));
            //$contents = str_replace('UTF-8', $encode, $contents);
            //$contents = mb_convert_encoding($contents,'utf-8',$encode);
            //dump($encode);die;
            /*$fp=fopen($file_path,'a+');
            $contents=fread($fp,filesize($file_path));*/ 
            foreach ($keys as $key => $value) {
                $ke[$key] = $value;
                $contents = str_replace($ke[$key], '<span style="background-color:red;color:white">'.$ke[$key]."</span>", $contents);
            }
            echo $contents;           
        }      
    }  
}