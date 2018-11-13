<?php
namespace Home\Controller;
use Think\Controller;
use Think\Tree;
class DataController extends CommonController {
    public function all(){
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
                $where['url'] = array('like','%'.$hiturl.'%');
            }
            if(I('hitdomain')!=''){
                $hitdomain = I('hitdomain');
                $where['domain'] = $hitdomain;
            }
            if(I('lab_id')!=''){
                $lab_id = I('lab_id');
                $where['lab_id'] = $lab_id;
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
        $where['audit_status'] = '1';
        $count = M('hit_url_info')->where($where)->count();
        $Page  = new \Think\Page($count,15);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");
        $res = M('hit_url_info')->where($where)->order('hit_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($res as $key => $value) {
            //$res[$key]['audit_status'] = $this->info_value('audit_status',$value['audit_status']);
            $res[$key]['content_type'] = $this->info_value('content_type',$value['content_type']);
            $res[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
        }

        /****图表****按类型****/
        //文本
        $where['hit_type'] = '1';
        $text_res = M('hit_url_info')->where($where)->group('content_type')->field('content_type,count(content_type) as count')->select();
        foreach ($text_res as $key => $value) {
            $t_res .= $value['count'].',';
            # code...
        }
        //图片
        $where['hit_type'] = '2';
        $pic_res =  M('hit_url_info')->where($where)->group('content_type')->field('content_type,count(content_type) as count')->select();
        foreach ($pic_res as $key => $value) {
            $p_res .= $value['count'].',';
            # code...
        }
        //视频
        $where['hit_type'] = '3';
        $video_res = M('hit_url_info')->where($where)->group('content_type')->field('content_type,count(content_type) as count')->select();
        foreach ($video_res as $key => $value) {
            $v_res .= $value['count'].',';
            # code...
        }
        //音频
        $where['hit_type'] = '4';
        $audio_res = M('hit_url_info')->where($where)->group('content_type')->field('content_type,count(content_type) as count')->select();
        foreach ($audio_res as $key => $value) {
            $a_res .= $value['count'].',';
            # code...
        }
        /*****按机房*****/
        unset($where['hit_type']);
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
        $labs = M('lab')->select();
        $this->assign('labs',$labs);
        

        $show = $Page->show();
        $this->assign('t_res',$t_res);
        $this->assign('p_res',$p_res);
        $this->assign('v_res',$v_res);
        $this->assign('a_res',$a_res);
        $this->assign('l_res',$l_res);
        $this->assign('seven_res',$seven_res);
        $this->assign('show', $show);
        $this->assign('res',$res);
        $this->assign('hitip',$hitip);
        $this->assign('hiturl',$hiturl);
        $this->assign('hitdomain',$hitdomain);
        $this->assign('time',$time);
        Layout('Layout/layout');
        $this->display();
    }
    public function lab_tree(){
        $tree_data = array();
        $data = M('lab_tree')->group('lab_id')->select();
        foreach ($data as $key => $value) {
            $value['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $tree_data[$value['id']] = array(
                'id'=>$value['id'],
                'parentid'=>$value['parent_id'],
                'name'=>$value['lab_name'],

            );
             $tree_data[$value['id']]['parentid_node'] = ($t['parentid']) ? ' class="child-of-node-' . $t['parentid'] . '"' : '';
        }
        $str = "<tr id='node-\$id' \$parentid_node>
                       <td style='color:white'>\$spacer<a href='#' data='\$name' class='lab_info'> \$name</a></td>
                    </tr>";
        $tree = new Tree();
        $tree->icon = array('│ ', '├─ ', '└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $tree->init($tree_data);
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        layout('Layout/layout');
        $this->display();
        
    }
    public function lab_info(){
        if($_GET){
            if(I('ip')!=''){
                $ip = I('ip');
                $where['ip'] = array('like',$ip.'%');
            }
            if(I('hiturl')!=''){
                $hiturl = I('hiturl'); 
                $where['url'] = array('like','%'.$hiturl.'%');
            }
            if(I('hitdomain')!=''){
                $domain = I('hitdomain');
                $where['domain'] = array('like','%'.$domain.'%');
            }
            if(I('time')!=''){
                $time = I('time');
                if($time !=''){
                    $start = strtotime(explode(' - ',$time)[0]);
                    $end = strtotime(explode(' - ',$time)[1]);                   
                }
                $where['hit_time'] = array('between',array($start,$end));
            } 
            if(I('lab_name')!=''){
                $where['lab_id'] = M('lab')->where(array('lab_name'=>I('lab_name')))->select()[0]['lab_id'];
            }
            
            $where['audit_status'] = '1';

            
        }
        $data = M('hit_url_info')->where($where)->order('hit_time desc')->select();
        foreach ($data as $key => $value) {
            $data[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $data[$key]['hit_time'] = date('Y-m-d',$value['hit_time']);
            $data[$key]['audit_time']=date('Y-m-d',$value['audit_time']);
        }
        $this->ajaxReturn(array('data'=>$data));
    }
    public function domain_tree(){
    	$tree_data = array();
        $where['hit_url_info.audit_status'] = '1';
        $data = M('domain_tree')->join(' as a left join hit_url_info on a.domain = hit_url_info.domain ')->group('a.domain')->field('a.*,hit_url_info.audit_status')->where($where)->select();
        foreach ($data as $key => $value) {
            $tree_data[$value['id']] = array(
                'id'=>$value['id'],
                'parentid'=>$value['parent_id'],
                'name'=>$value['domain'],

            );
             $tree_data[$value['id']]['parentid_node'] = ($t['parentid']) ? ' class="child-of-node-' . $t['parentid'] . '"' : '';
        }
        $str = "<tr id='node-\$id' \$parentid_node>
                       <td style='color:white'>\$spacer<a href='#' data='\$name' class='domain_info'> \$name</a></td>
                    </tr>";
        $tree = new Tree();
        $tree->icon = array('│ ', '├─ ', '└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $tree->init($tree_data);
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        layout('Layout/layout');
        $this->display();
        
    }
    public function domain_info(){
        if($_GET){
            if(I('ip')!=''){
                $ip = I('ip');
                $where['ip'] = array('like',$ip.'%');
            }
            if(I('hiturl')!=''){
                $hiturl = I('hiturl'); 
                $where['url'] = array('like','%'.$hiturl.'%');
            }
            if(I('domain')!=''){
                $domain = I('domain');
                $where['domain'] = array('like','%'.$domain.'%');
            }
            if(I('time')!=''){
                $time = I('time');
                if($time !=''){
                    $start = strtotime(explode(' - ',$time)[0]);
                    $end = strtotime(explode(' - ',$time)[1]);                   
                }else{
                    $start = strtotime(date('Y-m-d 00:00:00'));
                    $end = strtotime(date('Y-m-d 23:59:59'));
                }
                $where['hit_time'] = array('between',array($start,$end));
            } 
            
            $where['audit_status'] = '1';

            
        }
        $data = M('hit_url_info')->where($where)->order('hit_time desc')->select();
        foreach ($data as $key => $value) {
            $data[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $data[$key]['hit_time'] = date('Y-m-d',$value['hit_time']);
            $data[$key]['audit_time']=date('Y-m-d',$value['audit_time']);
        }
        $this->ajaxReturn(array('data'=>$data));
    }
    public function ip_tree(){
        $tree_data = array();
        $data = M('ip_tree')->group('ip')->select();
        foreach ($data as $key => $value) {
            $tree_data[$value['id']] = array(
                'id'=>$value['id'],
                'parentid'=>$value['parent_id'],
                'name'=>$value['ip'],

            );
             $tree_data[$value['id']]['parentid_node'] = ($t['parentid']) ? ' class="child-of-node-' . $t['parentid'] . '"' : '';
        }
        $str = "<tr id='node-\$id' \$parentid_node>
                       <td style='color:white'>\$spacer<a href='#' data='\$name' class='ip_info' > \$name</a></td>
                    </tr>";
        $tree = new Tree();
        $tree->icon = array('│ ', '├─ ', '└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $tree->init($tree_data);
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        layout('Layout/layout');
        $this->display();

    }
    public function ip_info(){
        if($_GET){
            if(I('ip')!=''){
                $ip = I('ip');
                $where['ip'] = array('like',$ip.'%');
            }
            if(I('hiturl')!=''){
                $hiturl = I('hiturl'); 
                $where['url'] = array('like','%'.$hiturl.'%');
            }
            if(I('time')!=''){
                $time = I('time');
                if($time !=''){
                    $start = strtotime(explode(' - ',$time)[0]);
                    $end = strtotime(explode(' - ',$time)[1]);                   
                }
                $where['hit_time'] = array('between',array($start,$end));
            } 
           
            $where['audit_status'] = '1';

            
        }
       /* $count = M('hit_url_info')->where($where)->count();
        $Page  = new \Think\Page($count,4);// 实例化分页类 传入总记录数
        //dump($Page);die;
        $Page->setConfig('theme', "<ul class='pagination'></li><li>%FIRST%</li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li><li><a> %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页</a></ul>");*/

        $data = M('hit_url_info')->where($where)->order('hit_time desc')->select();
        foreach ($data as $key => $value) {
            $data[$key]['lab_name'] = M('lab')->where(array('lab_id'=>$value['lab_id']))->select()[0]['lab_name'];
            $data[$key]['hit_time'] = date('Y-m-d',$value['hit_time']);
            $data[$key]['audit_time']=date('Y-m-d',$value['audit_time']);
        }
        //dump($where);
        //dump($data);die;
        $this->ajaxReturn(array('data'=>$data));
    }
}