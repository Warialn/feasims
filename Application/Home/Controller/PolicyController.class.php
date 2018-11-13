<?php
namespace Home\Controller;
use Think\Controller;
use Home\Common\Export;
use Home\Common\Import;
use Home\Model\Policy;
class PolicyController extends CommonController {
    /*IP地址库*/
    //页面展示
    public function ip(){
        $count = M('ip_address_library')->count();
        $Page  = new \Think\Page($count,9);// 实例化分页类 传入总记录数
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $res = M('ip_address_library')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($res as $key => $value) {
            $res[$key]['ip_type'] = $this->info_value('ip_type',$value['ip_type']);
        }
        $show = $Page->show();
        $this->assign('show', $show);
        $rooms = M('lab')->select();
        $this->assign('rooms',$rooms);
        $this->assign('res',$res);
        Layout('Layout/layout');
        $this->display();

    }
    //IP地址添加
    public function ip_add(){
        if($_GET){
            $data = $_GET;
            $data['flag'] = 1;
            $res =M('ip_address_library')->add($data);
            if($res){
                $this->ajaxReturn(array('status'=>'success'));
            }
        }
    }
    //IP地址删除
    public function ip_del(){
        if($_GET){
            $id = I('id');
            $res =M('ip_address_library')->where(array('id'=>$id))->delete();
            if($res){
                $this->ajaxReturn(array('status'=>'success'));
            }
        }
    }
    //IP地址库导入
    public function upload_iplib(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName = 'time';
        $upload->autoSub  = false;

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
             $this->error($upload->getError());
        }
        $result = Import::excel($info['file']['savename']);
        $config = Policy::iplib_col();
        $error_msg = "";
        $status = 'success';
        if(!empty($result)){          
            foreach ($result as $key => $value) { 
                $data = array();
                foreach ($value as $k => $v) {
                    if(is_null($v)){
                        continue;
                    }
                    if (isset($config[$k])) {
                        if ($v!='') {
                          $data[$config[$k]] = Policy::col2value($k, $v);
                        }
                    }        
                }
                if($result['status'] === false) {
                    $status = 'failed';
                    $error_msg .= $res['message']."(excel:".($key + 1).")     ";
                }else{
                    M('ip_address_library')->add($data);
                } 
            }

            $this->ajaxReturn(['status' => $status]);
        }else{
            $status='error';
            $error_msg ='没有导入任何信息！';
            $this->ajaxReturn(['status' => $status,'message' => $error_msg]);
        }
        
    }
    //IP地址库导出
    public function export_iplib(){
        $where = [];
        if(I('begin_ip') !=''){
           $begin_ip = I('post.begin_ip');
           $where['begin_ip']  = $begin_ip;
        }
        if(I('end_ip') !=''){
            $end_ip = I('end_ip');
            $where['end_ip'] = $end_ip;
        }
        if(I('ip_type') !=''){
            $ip_type = I('ip_type');
            $where['ip_type'] = I('ip_type');
        }
        
        $filename = I('get.filename');
        $count = I('get.count');
        $offset = $count * 10000;
        $dates = str_replace("-","",$date);
        $table = M('ip_address_library');
        $data = $table->where($where)->limit($offset.', 10000')->select();
        

        $colFilter = function($col){
            return $this->iplib_col2name($col);
        };
        $valueFilter = function($col, $value) {
            return $this->info_value($col, $value);
        };
        $res = Export::excel($data, $colFilter, $valueFilter, $filename, $count);
        A('Home/Common')->log('Web','导出ip地址库',5);
        $this->ajaxreturn($res);
    } 

    public function black_list(){
        Layout('Layout/layout');
        $this->display();
    }
    /*黑名单*/
    //黑名单展示
    public function black_list_url(){
        if($_POST){
            if(I('domain')!=''){
                $domain = I('domain');
                $where['domain'] = $domain;
            }
            if(I('content_type') !=''){
                $content_type =I('content_type');
                $where['content_type'] = $content_type;
            }
            if(I('block_flag')!=''){
                $block_flag = I('block_flag');
                $where['block_flag'] = $block_flag;
            }
        }
        
        $url_count = M('blacklist_url_strategy')->where($where)->count();
        $Page  = new \Think\Page($url_count,9);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $url_res = M('blacklist_url_strategy')->where($where)->order('affective_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($url_res as $key => $value) {
            $url_res[$key]['content_type'] = $this->info_value('content_type',$value['control_url_type']);
            $url_res[$key]['lab_name'] = M('front_end_device')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $url_res[$key]['block_flag'] = $this->info_value('block_flag',$value['block_flag']);
        }
        $rooms = M('lab')->select();
        $show = $Page->show();
        $this->assign('show', $show);
        $this->assign('rooms',$rooms);
        $this->assign('domain',$domain);
        $this->assign('content_type',$content_type);
        $this->assign('block_flag',$block_flag);
        $this->assign('ip_res',$ip_res);
        $this->assign('url_res',$url_res);
        Layout('Layout/layout');
        $this->display();
    }
    public function black_list_ip(){
        if($_POST){
            if(I('domain')!=''){
                $domain = I('domain');
                $where['domain'] = $domain;
            }
            if(I('content_type') !=''){
                $content_type =I('content_type');
                $where['content_type'] = $content_type;
            }
            if(I('block_flag')!=''){
                $block_flag = I('block_flag');
                $where['block_flag'] = $block_flag;
            }
        }
        
        $ip_count = M('blacklist_ip_strategy')->where($where)->count();
        $Page  = new \Think\Page($ip_count,9);// 实例化分页类 传入总记录数
       // dump($ip_count);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $ip_res = M('blacklist_ip_strategy')->where($where)->order('affective_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($ip_res as $key => $value) {
            $ip_res[$key]['content_type'] = $this->info_value('content_type',$value['control_url_type']);
            $ip_res[$key]['lab_name'] = M('front_end_device')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $ip_res[$key]['block_flag'] = $this->info_value('block_flag',$value['block_flag']);
            $ips = explode('.',$value['control_ip']);
            $ip_part = $ips[0].'.'.$ips[1].".".$ips[2].'.';
            $ip_where['begin_ip'] = array('like',$ip_part.'%');
            $ip_where['end_ip'] = array('like',$ip_part.'%');
            $ip_res[$key]['location'] = $this->info_value('ip_type',M('ip_address_library')->where($ip_where)->select()[0]['ip_type']);

        }
        
        $rooms = M('lab')->select();
        $show = $Page->show();
        $this->assign('show', $show);
        $this->assign('rooms',$rooms);
        $this->assign('domain',$domain);
        $this->assign('content_type',$content_type);
        $this->assign('block_flag',$block_flag);
        $this->assign('ip_res',$ip_res);
        $this->assign('url_res',$url_res);
        Layout('Layout/layout');
        $this->display();
    }
    //黑名单URL导出
    public function export_black_url(){
        $where = [];
        if(I('domain') !=''){
           $domain = I('post.domain');
           $where['domain']  = $domain;
        }
        if(I('content_type') !=''){
            $content_type = I('conetnt_type');
            $where['content_type'] = $content_type;
        }
        if(I('block_flag') !=''){
            $block_flag = I('block_flag');
            $where['block_flag'] = I('block_flag');
        }
        
        $filename = I('post.filename');
        $count = I('post.count');
        $offset = $count * 10000;
        $dates = str_replace("-","",$date);
        $table = M('blacklist_url_strategy');
        $data = $table->where($where)->limit($offset.', 10000')->select();
        foreach ($data as $key => $value) {
            $data[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $data[$key]['affective_time'] = date('Y-m-d',$value['affective_time']);
        }

        $colFilter = function($col){
            return $this->black_url_col2name($col);
        };
        $valueFilter = function($col, $value) {
            return $this->info_value($col, $value);
        };
        $res = Export::excel($data, $colFilter, $valueFilter, $filename, $count);
        //dump($res);die;
        A('Home/Common')->log('Web','导出黑名单URL',5);
        $this->ajaxreturn($res);
    }  
    //黑名单ip导出
    public function export_black_ip(){
        $where = [];
        if(I('domain') !=''){
           $domain = I('post.domain');
           $where['domain']  = $domain;
        }
        if(I('content_type') !=''){
            $content_type = I('conetnt_type');
            $where['content_type'] = $content_type;
        }
        if(I('block_flag') !=''){
            $block_flag = I('block_flag');
            $where['block_flag'] = I('block_flag');
        }
        
        $filename = I('post.filename');
        $count = I('post.count');
        $offset = $count * 10000;
        $table = M('blacklist_ip_strategy');
        $data = $table->where($where)->limit($offset.', 10000')->select();
        foreach ($data as $key => $value) {
            $data[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $data[$key]['affective_time'] = date('Y-m-d',$value['affective_time']);
            $data[$key]['location'] = '浙江';
        }

        $colFilter = function($col){
            return $this->black_ip_col2name($col);
        };
        $valueFilter = function($col, $value) {
            return $this->info_value($col, $value);
        };
        $res = Export::excel($data, $colFilter, $valueFilter, $filename, $count);
         A('Home/Common')->log('Web','导出黑名单IP',5);
        $this->ajaxreturn($res);
    } 
    //黑名单封堵/解封堵
    public function plug_blacklist(){
        if($_GET){
            $type = I('type');
            $id = I('id');
            if($type == 'plugurl'){
                $data['block_flag'] = 1;
                foreach ($id as $key => $value) {
                    $res = M('blacklist_url_strategy')->where(array('id'=>$value))->save($data);
                }

            }elseif($type == 'replugurl'){
                $data['block_flag'] = 0;
                foreach ($id as $key => $value) {
                    $res = M('blacklist_url_strategy')->where(array('id'=>$value))->save($data);
                }
            }elseif($type == 'plugip'){
                $data['block_flag'] = 1;
                foreach ($id as $key => $value) {
                    $res = M('blacklist_ip_strategy')->where(array('id'=>$value))->save($data);
                }
            }elseif($type == 'replugip'){
                $data['block_flag'] = 0;
                foreach ($id as $key => $value) {
                    $res = M('blacklist_ip_strategy')->where(array('id'=>$value))->save($data);
                }
            }
            if($res){
                $this->ajaxReturn(array('status'=>'success'));
            }
        }

    }


    /*误判库*/
    public function miss_judge(){
        if($_GET){
            if(I('ip') !=''){
                $ip = I('ip');
                $where['ip'] = $ip;
            }
            if(I('domain') !=''){
                $domain = I('domain');
                $where['domain'] = $domain; 
            }
            if(I('url') !=''){
                $url = I('url');
                $where['url'] = $url; 
            }
            if(I('monitor_flag') !=''){
                $monitor_flag = I('monitor_flag');
                $where['monitor_flag'] = $monitor_flag; 
            }
        }
        $count = M('misjudgement_library_strategy')->where($where)->count();
        $Page  = new \Think\Page($count,9);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $res = M('misjudgement_library_strategy')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($res as $key => $value) {
            $res[$key]['monitor_flag'] = $this->info_value('monitor_flag',$value['monitor_flag']);
            # code...
        }
        $show = $Page->show();
        $this->assign('show', $show);
        $rooms = M('lab')->select();
        $this->assign('rooms',$rooms);
        $this->assign('ip',$ip);
        $this->assign('domain',$domain);
        $this->assign('url',$url);
        $this->assign('monitor_flag',$monitor_flag);
        $this->assign('res',$res);
        Layout('Layout/layout');
        $this->display();
    }
    //误判库开启/关闭监测
    public function missjudge_handle(){
        if($_GET){
            $id = I('id');
            //$ids = explode(',',$id);
            $data['monitor_flag'] = I('monitor_flag');
            foreach ($id as $key => $value) {
                $where['id'] = $value;
                $res = M('misjudgement_library_strategy')->where($where)->save($data);
            }
            
            if($res){
                $this->ajaxReturn(array('status'=>'success'));
            }
        }
    }
   
    //误判库导出
    public function export_missjudge(){
        if($_POST){
            if(I('ip') !=''){
                $ip = I('ip');
                $where['ip'] = $ip;
            }
            if(I('domain') !=''){
                $domain = I('domain');
                $where['domain'] = $domain; 
            }
            if(I('url') !=''){
                $url = I('url');
                $where['url'] = $url; 
            }
            if(I('monitor_flag') !=''){
                $monitor_flag = I('monitor_flag');
                $where['monitor_flag'] = $monitor_flag; 
            }
        }

        $filename = I('post.filename');
        $count = I('post.count');
        $offset = $count * 10000;
        $data = M('misjudgement_library_strategy')->where($where)->limit($offset.', 10000')->select();
        $colFilter = function($col){
            return $this->missjudge_col2name($col);
        };
        $valueFilter = function($col, $value) {
            return $this->info_value($col, $value);
        };
        $res = Export::excel($data, $colFilter, $valueFilter, $filename, $count);
        A('Home/Common')->log('Web','导出知名网站列表',5);
        $this->ajaxreturn($res);

    } 
    /*知名网站*/
    //知名网站展示
    public function famous_list(){
        if($_GET){
            if(I('popularweb_name')!=''){
                $popularweb_name = I('popularweb_name');
                $where['popularweb_name'] = $popularweb_name;
            }
            if(I('popularweb_url')!=''){
                $popularweb_url = I('popularweb_url');
                $where['popularweb_url'] = $popularweb_url;
            }
            if(I('monitor_flag')!=''){
                $monitor_flag = I('monitor_flag');
                $where['monitor_flag'] = $monitor_flag;
            }
        }
       
        $rooms = M('lab')->select();
        $this->assign('rooms',$rooms);
        $count = M('white_list_strategy')->where($where)->count();
        $Page  = new \Think\Page($count,9);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $res = M('white_list_strategy')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($res as $key => $value) {
            $res[$key]['monitor_flag'] = $this->info_value('monitor_flag',$value['monitor_flag']);
        }
        $show = $Page->show();
        $this->assign('show', $show);
        $this->assign('res',$res);
        Layout('Layout/layout');
        $this->display();
    }
    //知名网站开启/关闭监测
    public function famous_handle(){
        if($_GET){
            $id = I('id');
            $data['monitor_flag'] = I('monitor_flag');
            foreach ($id as $key => $value) {
                $where['popularweb_id'] = $value;
                $res = M('white_list_strategy')->where($where)->save($data);
            }
            if($res){
                A('Home/Common')->log('Web','知名网站开启/关闭监测',5);
                $this->ajaxReturn(array('status'=>'success'));
            }
        }
    }
    //知名网站导入
    public function famous_upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName = 'time';
        $upload->autoSub  = false;

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
             $this->error($upload->getError());
        }
        
        $result = Import::excel($info['user_file']['savename']);
        $config = Policy::famous_col();
        $error_msg = "";
        $status = 'success';
        if(!empty($result)){          
            foreach ($result as $key => $value) {
                $data = array();
                foreach ($value as $k => $v) {
                    if(is_null($v)){
                        continue;
                    }
                    if (isset($config[$k])) {
                        if ($v!='') {
                          $data[$config[$k]] = Policy::col2value($k, $v);
                        }
                    } 
                            
                }
                if ($result['status'] === false) {
                  $status = 'failed';
                  $error_msg .= $res['message']."(excel:".($key + 1).")     ";
                } else {
                   $res = M('white_list_strategy')->add($data);

                } 
            }
            if($res){
                $this->ajaxReturn(['status' => $status]);
            }
        
        }else{
        $status='error';
        $error_msg ='没有导入任何信息！';
        }
        $this->ajaxReturn(['status' => $status,'message' => $error_msg]);

    }
    //知名网站导出
    public function famous_export(){
        if($_GET){
            if(I('popularweb_name')!=''){
                $popularweb_name = I('popularweb_name');
                $where['popularweb_name'] = $popularweb_name;
            }
            if(I('popularweb_url')!=''){
                $popularweb_url = I('popularweb_url');
                $where['popularweb_url'] = $popularweb_url;
            }
            if(I('monitor_flag')!=''){
                $monitor_flag = I('monitor_flag');
                $where['monitor_flag'] = $monitor_flag;
            }
        }
        $data = M('white_list_strategy')->where($where)->limit($offset.', 10000')->select();
        
        $colFilter = function($col){
            return $this->famous_col2name($col);
        };
        $valueFilter = function($col, $value) {
            return $this->info_value($col, $value);
        };
        $res = Export::excel($data, $colFilter, $valueFilter, $filename, $count);
         A('Home/Common')->log('Web','导出知名网站列表',5);
        $this->ajaxreturn($res);

    }

    //策略下发
    public function policy_issued(){
        if($_GET){
            $house_id = I('house_id');
            $type = I('type');
            $re_commandid = I('commandid');
            $fail_version = I('fail_version');
            $map['flag'] = 1;
            if($type == '1'){
                $table = 'blacklist_url_strategy';
               
            }elseif($type == '2'){
                $table = 'blacklist_ip_strategy';
                
            }elseif($type == '3'){
                $table = 'white_list_strategy';
               
            }elseif($type == '4'){
                $table = 'ip_address_library';
              
            }elseif($type == '6'){
                $table = 'text_strategy';
                if($re_commandid==''){
                    $da['text_page_hit'] = I('text_page_hit');
                    $da['text_strategy_type'] = I('text_strategy_type');
                    $da['flag'] = 1;
                    $commandid = M('command')->order('id desc')->limit(0,1)->select()[0]['commandid'];
                    if($commandid){
                        $da['commandid'] = $commandid+1;
                    }else{
                       $da['commandid'] = 1; 
                    }
                    $d['commandid'] = $da['commandid'];
                    M('command')->add($d);
                /*if($type_res){
                    M('text_strategy')->where(1)->save($da);
                }else{*/
                    M('text_strategy')->add($da);
                }else{
                    $da['text_page_hit'] = I('text_page_hit');
                    $da['text_strategy_type'] = I('text_strategy_type');
                    $da['flag'] = 1;
                    M('text_strategy')->where(array('commandid'=>$re_commandid))->save($da);
                }
                
                //}
                if($house_id==''){
                    $lab_res = M('lab')->field('lab_id')->select();
                    foreach ($lab_res as $key => $value) {
                        $house_id[] = $value['lab_id'];
                    }
                }else{
                    $house_id = explode(',',$house_id);
                }
                
               
            }elseif($type == '7'){
                $table = 'pic_strategy';
                if($re_commandid==''){

                    $dat['pic_algorithmvalue'] = I('pic_algorithmvalue');
                    $dat['pic_page_hit_type'] = I('pic_page_hit_type');
                    $dat['flag'] = 1;
                    $commandid = M('command')->order('id desc')->limit(0,1)->select()[0]['commandid'];
                    if($commandid){
                        $dat['commandid'] = $commandid+1;
                    }else{
                       $dat['commandid'] = 1; 
                    }
                    $d['commandid'] = $dat['commandid'];
                    M('command')->add($d);
                    /*if($type_res){
                        M('pic_strategy')->where(1)->save($data);
                    }else{*/
                        M('pic_strategy')->add($dat);
                    //}
                }else{
                    $dat['flag'] = 1;
                     M('pic_strategy')->where(array('commandid'=>$re_commandid))->save($dat);

                }
                
                if($house_id==''){
                    $lab_res = M('lab')->field('lab_id')->select();
                    foreach ($lab_res as $key => $value) {
                        $house_id[] = $value['lab_id'];
                    }
                }else{
                    $house_id = explode(',',$house_id);
                }
              
            }elseif($type == '12'){
                $table = 'misjudgement_library_strategy';  
            }elseif($type == '13'){
                $table = 'spider_config';
            }elseif($type == '14'){
                $table = 'host_list';

            }
            $result = M($table)->where($map)->select();
            if(empty($result)){
                $this->ajaxReturn(array('status'=>'error','info'=>'没有未下发的策略'));

            }
            foreach ($house_id as $key => $value) {
                $version = M('ui_handle')->order('version desc')->limit(0,1)->select()[0]['version'];
                if($version == ''){
                    $version = 1;
                }else{
                    $version = $version +1;
                }
                if($type == '1'){
                    $table = 'blacklist_url_strategy';
                    $sql = "SELECT * FROM ".$table."  where flag = '1'   INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";
                }elseif($type == '2'){
                    $table = 'blacklist_ip_strategy';
                    $sql = "SELECT * FROM ".$table." where flag = '1'   INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";
                }elseif($type == '3'){
                    $table = 'white_list_strategy';
                    $sql = "SELECT popularweb_id,popularweb_name,popularweb_url,monitor_flag FROM ".$table." where monitor_flag = '1' AND flag = '1' INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";
                }elseif($type == '4'){
                    $table = 'ip_address_library';
                    $sql = "SELECT name,discription,begin_ip,end_ip,ip_type,id FROM ".$table." where flag = '1'  INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";
                }elseif($type == '6'){
                    $table = 'text_strategy';
                    
                    $sql = "SELECT text_page_hit,text_strategy_type FROM ".$table." where flag = '1' INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";
                }elseif($type == '7'){
                    $table = 'pic_strategy';
                   
                    $sql = "SELECT pic_algorithmvalue,pic_page_hit_type FROM ".$table." where flag = '1' INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";
                }elseif($type == '12'){
                    $table = 'misjudgement_library_strategy';
                    $sql = "SELECT id,url,ip,domain,monitor_flag FROM ".$table." where monitor_flag = '1' AND flag = '1'  INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";
                }elseif($type == '13'){
                    $table = 'spider_config';
                    $sql = "SELECT id,spider_frequency,config_path,redis_frequency,upload_zip_size,upload_zip_time,upload_zip_file_num,pic_ip,pic_port,txt_ip,txt_port  FROM ".$table." where monitor_flag = '1' AND flag = '1'  INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";

                }elseif($type == '14'){
                    $table = 'host_list';
                    $sql = "SELECT id,host,username,password,remark,monitor_flag FROM ".$table." where monitor_flag = '1' AND flag = '1'  INTO OUTFILE '/usr/local/nginx/html/policy/".$version.".txt' fields terminated by '\t' lines terminated by '\r\n'";

                }
                $sql_result = M()->execute($sql);
                $file_path = "/usr/local/nginx/html/policy/".$version.".txt";
                if (file_exists($file_path)){
                    $status = true;
                    $data['version'] = $version;
                    if($re_commandid) {
                        $data['exversion'] = M('ui_handle')->where(array('house_id'=>$value,'commandid'=>$re_commandid))->select()[0]['version'];
                    }
                    $data['exversion'] = '1';
                    $data['policy_type'] = $type;
                    $data['flag'] = '1';
                    $data['policy_path'] =$file_path;
                    $data['house_id'] = $value;
                    if($type == '6' || $type == '7'){
                        if($re_commandid==''){
                            $data['commandid'] = $d['commandid'];
                        }else{
                            $data['exversion'] = '2';
                            $data['commandid'] = $re_commandid; 
                        }
                        
                    }
                    $res = M('ui_handle')->add($data);
                    $dat['flag'] = '0';
                    $res2 = M($table)->where(1)->save($dat);
                    
                    
                }
            }
            if($res){
                $this->ajaxReturn(array('status'=>'success'));
            }           
        }
    }
    
    public function pic_vid(){      
        Layout('Layout/layout');
        $this->display();
    }
    public function picture(){      
        $res = M('pic_strategy')->select();
        foreach ($res as $key => $value) {
            $check = M('ui_handle')->where(array('commandid'=>$value['commandid'],'exversion'=>array('neq','1')))->select();
            if($check){
                foreach ($check as $kc => $valc) {
                    $version[] = M('ui_handle')->where(array('commandid'=>$valc['commandid'],'house_id'=>$valc['house_id']))->find()['version'];
                }

                $map = array('version'=>array('not in',$version));
                $ui_res = M('ui_handle')->where($map)->select();
            }else{ 
                $ui_res = M('ui_handle')->where(array('commandid'=>$value['commandid']))->select();
            }
            

            foreach ($ui_res as $k => $val) {
                $fa_res[] = M('policy_result')->group('house_id')->where(array('status'=>'2','policy_type'=>'7','version'=>$val['version']))->field('house_id,version')->select();
                $res[$key]['shibai'] = $res[$key]['shibai']+ M('policy_result')->group('house_id')->where(array('status'=>'2','version'=>$val['version'],'policy_type'=>'7'))->count();
            }          
            foreach ($fa_res as $kee => $valuu) {
                foreach ($valuu as $k => $val) {
                    $fail_res[$key][] =$val['house_id'];
                    $fail_version[$key][] = $val['version'];
                }
            }
            $res[$key]['fail_lab'] = implode(',', array_unique($fail_res[$key]));
            if(!empty($res[$key]['fail_lab'])){
               $labs[$key] = M('lab')->where(array('lab_id'=>array('not in',array_unique($fail_res[$key]))))->field('lab_id')->select();
                foreach ($labs[$key] as $kel => $vall) {
                    $success_lab_ids[$key][] = $vall['lab_id'];
                    # code...
                }
                $res[$key]['success'] = count($success_lab_ids[$key]);
                $res[$key]['success_lab'] =implode(',',$success_lab_ids[$key]); 
            }else{
                $labs[$key] = M('lab')->field('lab_id')->select();
                foreach ($labs[$key] as $kel => $vall) {
                    $success_lab_ids[$key][] = $vall['lab_id'];
                    # code...
                }
                $res[$key]['success'] = count($success_lab_ids[$key]);
                $res[$key]['success_lab'] =implode(',',$success_lab_ids[$key]);
            }
            
            if($res[$key]['shibai']){
                $res[$key]['handle'] = 1;
            }else{
                $res[$key]['handle'] = 2;
            }
            $res[$key]['pic_type'] = $this->info_value('pic_type',$value['pic_page_hit_type']);
        }
        $this->assign('res',$res);
        Layout('Layout/layout');
        $this->display();
    }
    public function text(){  
        
        $res = M('text_strategy')->select();
        foreach ($res as $key => $value) {
            $check = M('ui_handle')->where(array('commandid'=>$value['commandid'],'exversion'=>array('neq','1')))->select();
            if($check){
                foreach ($check as $kc => $valc) {
                    $version[] = M('ui_handle')->where(array('commandid'=>$valc['commandid'],'house_id'=>$valc['house_id']))->find()['version'];
                }

                $map = array('version'=>array('not in',$version));
                $ui_res = M('ui_handle')->where($map)->select();
            }else{
                $ui_res = M('ui_handle')->where(array('commandid'=>$value['commandid']))->select();
            }
            foreach ($ui_res as $k => $val) {
                $fa_res[] = M('policy_result')->group('house_id')->where(array('status'=>'2','policy_type'=>'6','version'=>$val['version']))->field('house_id,version')->select();
                $res[$key]['shibai'] = $res[$key]['shibai'] + M('policy_result')->group('house_id')->where(array('status'=>'2','version'=>$val['version'],'policy_type'=>'6'))->count();
            }          
            foreach ($fa_res as $kee => $valuu) {
                foreach ($valuu as $k => $val) {
                    $fail_res[$key][] =$val['house_id'];
                    $fail_version[$key] = $val['version'];
                }
            }
            $res[$key]['version'] = $fail_version[$key];
            $res[$key]['fail_lab'] = implode(',', array_unique($fail_res[$key]));
            if(!empty($res[$key]['fail_lab'])){
               $labs[$key] = M('lab')->where(array('lab_id'=>array('not in',array_unique($fail_res[$key]))))->field('lab_id')->select();
                foreach ($labs[$key] as $kel => $vall) {
                    $success_lab_ids[$key][] = $vall['lab_id'];
                    # code...
                }
                $res[$key]['success'] = count($success_lab_ids[$key]);
                $res[$key]['success_lab'] =implode(',',$success_lab_ids[$key]); 
            }else{
                $labs[$key] = M('lab')->field('lab_id')->select();
                foreach ($labs[$key] as $kel => $vall) {
                    $success_lab_ids[$key][] = $vall['lab_id'];
                    # code...
                }
                $res[$key]['success'] = count($success_lab_ids[$key]);
                $res[$key]['success_lab'] =implode(',',$success_lab_ids[$key]);
            }
            
            if($res[$key]['shibai']){
                $res[$key]['handle'] = 1;
            }else{
                $res[$key]['handle'] = 2;
            }
            $res[$key]['text_type'] = $this->info_value('text_type',$value['text_strategy_type']);
        }
        $this->assign('res',$res);
        Layout('Layout/layout');
        $this->display();
    }
    public function text_device_info(){
        if($_GET){
            $type = I('type');
            $house_id = I('house_id');
            $commandid = I('commandid');
            $labs = explode(',',$house_id);
            if($type == '1'){
                $res = M('policy_result')->where(array('house_id'=>array('in',$labs),'status'=>'1','commandid'=>$commandid,'policy_type'=>'6'))->select();

            }else{
                $res = M('policy_result')->where(array('house_id'=>array('in',$labs),'commandid'=>$commandid,'policy_type'=>'6'))->select();
            }
            foreach ($res as $key => $value) {
                $res[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['house_id']))->select()[0]['lab_name'];
                $res[$key]['status'] = $this->info_value('device_status',$value['status']);
            }
            $this->ajaxReturn(array('data'=>$res));
        }

    }
    public function pic_device_info(){
        if($_GET){
            $type = I('type');
            $house_id = I('house_id');
            $commandid = I('commandid');
            $labs = explode(',',$house_id);
            if($type == '1'){
                $res = M('policy_result')->where(array('house_id'=>array('in',$labs),'status'=>'1','commandid'=>$commandid,'policy_type'=>'7'))->select();

            }else{
                $res = M('policy_result')->where(array('house_id'=>array('in',$labs),'commandid'=>$commandid,'policy_type'=>'7'))->select();
            }
            foreach ($res as $key => $value) {
                $res[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['house_id']))->select()[0]['lab_name'];
                $res[$key]['status'] = $this->info_value('device_status',$value['status']);
            }
            $this->ajaxReturn(array('data'=>$res));
        }

    }
    public function txt_pic(){      
        Layout('Layout/layout');
        $this->display();
    }
    public function video(){        
        Layout('Layout/layout');
        $this->display();
    }
    public function key_word(){
        Layout('Layout/layout');
        $this->display();
    }
    public function pic_lib(){
        Layout('Layout/layout');
        $this->display();
    }
     public function pic_lib_a(){
        Layout('Layout/layout');
        $this->display();
    }
    public function domain_setting(){
        if($_GET){
            if(I('host')!=''){
                $host = I('host');
                $where['host'] = $host;
            }
            if(I('username')!=''){
                $username = I('username');
                $where['username'] = $username;
            }
            if(I('monitor_flag')!=''){
                $monitor_flag = I('monitor_flag');
                $where['monitor_flag'] = $monitor_flag;
            }
        }
        $rooms = M('lab')->select();
        $this->assign('rooms',$rooms);
        $res = M('host_list')->where($where)->select();
        foreach ($res as $key => $value) {
            $res[$key]['monitor_flag'] = $this->info_value('monitor_flag',$value['monitor_flag']);
            # code...
        }
        $this->assign('host',$host);
        $this->assign('username',$username);
        $this->assign('monitor_flag',$monitor_flag);
        $this->assign('res',$res);
        Layout('Layout/layout');
        $this->display();
    }
    public function domain_add(){
        if($_GET){
            $data = $_GET;
            $data['flag'] = 1;
            $data['monitor_flag'] = 1;
            $res = M('host_list')->add($data);
            if($res){
                $this->ajaxReturn(['status' => 'success']);
            }else{
                $this->ajaxReturn(['status'=>'failed','message'=>'添加失败']);
            }
        }

    }
    public function domain_edit(){
        if($_GET){
            $id = I('id');
            $data = $_GET;
            $data['flag'] = 1;
            $res = M('host_list')->where(array('id'=>$id))->save($data);
            if($res){
                $this->ajaxReturn(array('status'=>'success'));
            }else{
                $this->ajaxReturn(array('status'=>'failed','message'=>'修改失败或没有做任何修改！'));
            }

        }
    }
   /* public function switch(){
        if($_POST){
            $id = I('id');
            $type = I('type');
            if($type == '1'){
                $data['monitor_flag'] = '1';
            }elseif($type == '2'){
                $data['monitor_flag'] = '0';
            }
            $res = M('host_list')->where(array('id'=>$id))->save($data);
        }
    }*/
    public function domain_delete(){
        if($_GET){
            $id = I('id');
            $res = M('host_list')->where(array('id'=>$id))->delete();
            if($res){
                $this->ajaxReturn(array('status'=>'success'));
            }else{
                $this->ajaxReturn(array('status'=>'failed','message'=>'删除失败！'));
            }

        }
    }
    public function domain_issued(){
        if($_GET){
            $table = 'host_list';
            $check_res = M($table)->where(array('monitor_flag'=>1))->select();
            if($check_res){
                $sql = "SELECT host FROM ".$table." where monitor_flag = '1' INTO OUTFILE '/usr/local/nginx/html/policy/tem/".time().".txt' fields terminated by '\t' lines terminated by '\r\n'";
                $sql_result = M()->execute($sql);
                $file = '/usr/local/nginx/html/policy/tem/.ok';
                $res=file_put_contents($file,'   ');
                /*$data['flag'] = '0';
                M($table)->where(1)->save($data);*/
                $this->ajaxReturn(array('status'=>'success'));
            }else{
                $this->ajaxReturn(['status'=>'failed','info'=>'下发失败或者没有要下发的内容！']); 

            }
        }
    }
    public function domain_upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName = 'time';
        $upload->autoSub  = false;

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
             $this->error($upload->getError());
        }
        
        $result = Import::excel($info['user_file']['savename']);
        $config = Policy::domain_col();
        $error_msg = "";
        $status = 'success';
        if(!empty($result)){          
            foreach ($result as $key => $value) {
                $data = array();
                foreach ($value as $k => $v) {
                    if(is_null($v)){
                        continue;
                    }
                    if (isset($config[$k])) {
                        if ($v!='') {
                          $data[$config[$k]] = Policy::col2value($k, $v);
                        }
                    } 
                            
                }
                if ($result['status'] === false) {
                  $status = 'failed';
                  $error_msg .= $res['message']."(excel:".($key + 1).")     ";
                } else {
                   $res = M('host_list')->add($data);

                } 
            }
            if($res){
                $this->ajaxReturn(['status' => $status]);
            }
        
        }else{
            $status='error';
            $error_msg ='没有导入任何信息！';
            $this->ajaxReturn(['status' => $status,'message' => $error_msg]);
        }
        

    }
    public function python_setting(){
        $res = M('spider_config')->select()[0];
        $this->assign('data',$res);
        $rooms = M('lab')->select();
        $this->assign('rooms',$rooms);
        Layout('Layout/layout');
        $this->display();
    }
    public function python_addoredit(){
        $res = M('spider_config')->select()[0];
        $data = $_GET;
        $id = $data['id'];
        $data['id']=1;
        $data['monitor_flag'] = 1;
        if($res){        
            $result = M('spider_config')->where(array('id'=>$id))->save($data);
            if($result){
                //A('Home/common')->log('Web', '修改爬虫配置参数', 5);
                $this->ajaxReturn(array('status'=>'success'));
            }
        }else{
            $result = M('spider_config')->add($data);
            if($result){
               //A('Home/common')->log('Web', '添加爬虫配置参数', 5);
               $this->ajaxReturn(array('status'=>'ad_success','info'=>'添加成功！'));
            }

          }
    }
    
     //导出文件打包
    public function actionPackage()
    {

        $status = false;
        $filePath = I('filepath');
        $rootPath = C("DOWN_PATH");
        if (is_dir($rootPath.$filePath))
        { 
  
    
            $cmd = "cd ". $rootPath . $filePath . "/" . "\n";
            $cmd .= "tar -zcf " . $rootPath . $filePath . ".tar.gz  *";
            $result = passthru($cmd);
            if (file_exists($rootPath . $filePath . ".tar.gz"))
            {
                $status = true;
            }
            $cmd = "cd ". $rootPath . "\n";
            $cmd .= "rm " . $filePath . " -rf";
            $result = system($cmd);
        } 
        
        $this->ajaxReturn(['status' => $status, 'filename' => $filePath . ".tar.gz "]);
    }
    

    
    public function iplib_col2name($col) {
        $c = [
            'name'                                 => '名称',
            'begin_ip'                             => '起始IP地址',
            'end_ip'                               => '终止IP地址',
            'ip_type'                              => 'ip地址类型',
            'discription'                          => '描述',
        ];

        return $col === null ? array_keys($c) : (isset($c[$col]) ? $c[$col] : "");
    }
    public function famous_col2name($col) {
        $c = [
            'popularweb_name'                      => '网站名称',
            'popularweb_url'                       => '域名',
            'monitor_flag'                         => '监测标识',
        ];

        return $col === null ? array_keys($c) : (isset($c[$col]) ? $c[$col] : "");
    }
    public function missjudge_col2name($col) {
        $c = [
            'ip'                                   => 'IP',
            'domain'                               => '域名',
            'url'                                  => 'URL',
            'monitor_flag'                         => '监测标识',
        ];

        return $col === null ? array_keys($c) : (isset($c[$col]) ? $c[$col] : "");
    }
    public function black_url_col2name($col) {
        $c = [
            'control_url_type'                     => '内容类型',
            'lab_name'                             => '机房名称',
            'control_url'                          => '黑名单内的域名/URL',
            'block_flag'                           => '封堵/解封堵标识',
            'affective_time'                       => '操作时间',
        ];

        return $col === null ? array_keys($c) : (isset($c[$col]) ? $c[$col] : "");
    }
    public function black_ip_col2name($col) {
        $c = [
            'control_url_type'                     => '内容类型',
            'lab_name'                             => '机房名称',
            'location'                             => '归属情况',
            'control_ip'                           => '黑名单IP地址',
            'block_flag'                           => '封堵/解封堵标识',
            'affective_time'                       => '操作时间',
        ];

        return $col === null ? array_keys($c) : (isset($c[$col]) ? $c[$col] : "");
    } 


}