<?php
namespace Home\Controller;

use Think\Controller;
use Think\Auth;

Class CommonController extends Controller
{

    public function _initialize()
    {
        
        $this->check_login();
        $auth = new Auth();
        if(!$auth->check(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME,session('uid'))){
            $this ->error('没有权限或该模块不存在');
        }
        $this->assign('module', $think . MODULE_NAME);
        $this->assign('controller', $think . CONTROLLER_NAME);
        $this->assign('action', $think . ACTION_NAME);
        $report_type = array('FTP' => 'FTP', 'VSFTP' => 'VSFTP');
        $user_type   = array('static' => '静态用户', 'dial' => '拨号用户', 'msisdn' => 'MSISDN', 'imsi' => 'IMSI', 'imei' => 'IMEI');
        $site_type   = array('综合', '新闻', '视频', '娱乐', '汽车', '科技', '教育', '公益', '财经', '体育', '时尚', '房产', '游戏', '文化');
        $task_list = array('UpgradeOs'=>'升级OS',
                           'UpgradeDpi'=>'升级DPI特征库',
                           'UpgradeVirus'=>'升级病毒库',
                           'UpgradeConfig'=>'升级CONFIG',
                           'DataBakup'=>'远程备份',
                           'SystermReboot'=>'远程重启',
                           'BackupSnap'=>'快照文件备份',
                           'Sniff'=>'抓包',
                           'UpgradeUrl'=>'升级URL库');
        $this->assign('report_type', $report_type);
        $this->assign('user_type', $user_type);
        $this->assign('site_type', $site_type);
        $this->assign('task_list',$task_list);
    }

    
    public function log($app, $message='', $level=5, $data=[]){
        $log = M('log');
        $data['app'] = $app;
        // $str = '';
        // foreach ($data as $k => $v) {
        //     $str .= $k.'='.$v.' ';
        // }
        $data['ip'] = get_client_ip();
        $data['username'] = $_SESSION['username'];
        $data['message'] = $message;
        $data['level'] = $level;
        $data['uid'] = session('uid') ? session('uid'):0;
        $data['time'] = time();
        $log->add($data);
    }


    public function check_rooms(){
        $users = M('users');
        $room = M('room');
        $uid = session('uid');

        if ($uid == "1") {  
            $where = [];
        } else {
            $room_id = $users->where(['id' => $uid])->field('local_roomname')->find();
            $room_ids = explode(" ", trim($room_id['local_roomname']));
            $where['r_id'] = array('in', $room_ids); 
        }

        $rooms = $room->field('r_id,room_name,city')->where($where)->order('city')->select();
        return $rooms;
    }
    public function check_physics_rooms(){
         $users = M('users');
        $room = M('room');
        $uid = session('uid');

        if ($uid == "1") {  
            $where['room_type'] =array('neq',2);
        } else {
            $room_id = $users->where(['id' => $uid])->field('local_roomname')->find();
            $room_ids = explode(" ", trim($room_id['local_roomname']));
            $where['r_id'] = array('in', $room_ids); 
             $where['room_type'] =array('neq',2);
        }

        $rooms = $room->field('r_id,room_name,city')->where($where)->order('city')->select();
        return $rooms;
    }
    public function check_logic_rooms(){
         $users = M('users');
        $room = M('room');
        $uid = session('uid');

        if ($uid == "1") {  
            $where['room_type'] =array('neq',1);
        } else {
            $room_id = $users->where(['id' => $uid])->field('local_roomname')->find();
            $room_ids = explode(" ", trim($room_id['local_roomname']));
            $where['r_id'] = array('in', $room_ids); 
             $where['room_type'] =array('neq',1);
        }

        $rooms = $room->field('r_id,room_name,city')->where($where)->order('city')->select();
        return $rooms;

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
            'control_url_type' => function($v){
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
            'block_flag' => function($v){
                $cc = [
                  '0'                                   => '未封堵',
                  '1'                                   => '已封堵',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            }, 
            'ip_type' => function($v){
                $cc = [
                  '0'                                   => '网内',
                  '1'                                   => 'WAP',
                  '2'                                   => '国外',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            }, 
            'device_status' => function($v){
                $cc = [
                  '1'                                   => '成功',
                  '2'                                   => '失败',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            },
            'text_type' => function($v){
                $cc = [
                  '1'                                   => '淫秽色情',
                  '2'                                   => '违法信息',
                  '3'                                   => '钓鱼网站',
                  '4'                                   => '其他',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            },
            'pic_type' => function($v){
                $cc = [
                  '1'                                   => '涉黄信息',
                  '2'                                   => '违法信息',
                  '3'                                   => '钓鱼',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            },
            'monitor_flag' => function($v){
                $cc = [
                  '0'                                   => '关闭',
                  '1'                                   => '开启',
                 
                ];
                return isset($cc[$v]) ? $cc[$v] : $v;
            },
        ];
        return isset($c[$col]) ? $c[$col]($value) : $value;
    } 
    /**
     *  检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param int $roleid 需要检查的角色ID
     */
    private function _is_checked($menu, $roleid, $priv_data)
    {

        $app    = $menu['app'];
        $model  = $menu['model'];
        $action = $menu['action'];
        $name   = strtolower("$app/$model/$action");
        if ($priv_data) {
            if (in_array($name, $priv_data)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0)
    {

        if ($array[$id]['parentid'] == 0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid'] == $id) {
            return $i;
        } else {
            $i++;

            return $this->_get_level($array[$id]['parentid'], $array, $i);
        }

    }


    public function check_login()
    {
        if (!session('username') && !session('uid')) {
            $this->redirect('Login/index');
        }
    }

    //获取策略组
    public function get_plcygroup_list()
    {
        $plcygroup      = M('plcygroup');
        $plcygroup_data = $plcygroup->select();

        return $plcygroup_data;
    }

    public function get_controle_type_list()
    {
        $controle_type      = M('control_type');
        $controle_type_list = $controle_type->select();

        return $controle_type_list;
    }

    //获取上报模板
    public function get_report_template_list()
    {
        $report_template      = M('report_template');
        $report_template_data = $report_template->select();

        return $report_template_data;
    }

    public function get_report_type_list()
    {
        $report_type      = M('report_plcy_type');
        $report_type_data = $report_type->where(array('type' => '0'))->select();

        return $report_type_data;
    }

    public function get_report_sub_type_list()
    {
        $report_type      = M('report_plcy_sub_type');
        $report_type_data = $report_type->select();

        return $report_type_data;
    }

    public function get_controle_action_list()
    {
        $controle_action      = M('control_action');
        $controle_action_data = $controle_action->select();

        return $controle_action_data;
    }

    public function get_controle_condition_list()
    {
        $controle_condition      = M('control_condition');
        $controle_condition_data = $controle_condition->select();

        return $controle_condition_data;
    }

    public function get_report_type()
    {
        $type        = $_POST['type'];
        $report_type = M('report_plcy_sub_type');
        $data        = $report_type->where(array('type' => $type))->select();
        //dump($data);
        if ($data)
            echo json_encode(array('status' => 'success', 'data' => $data));
        else
            echo json_encode(array('status' => 'error'));
    }

    public function get_report_content()
    {
        $type        = $_POST['type'];
        $report_type = M('report_content');
        $data        = $report_type->where(array('type' => $type))->select();
        if ($data)
            echo json_encode(array('status' => 'success', 'data' => $data));
        else
            echo json_encode(array('status' => 'error'));
    }

    public function get_condition()
    {
        $type           = $_POST['type'];
        $condition      = M('report_condition');
        $condition_data = $condition->where(array('type' => $type))->select();
        if ($condition_data)
            echo json_encode(array('status' => 'success', 'condition' => $condition_data));
        else
            echo json_encode(array('status' => 'error'));
    }

    public function get_condition_list($type = '')
    {
        $condition      = M('report_condition');
        $condition_data = $condition->where(array('type' => $type))->select();

        return $condition_data;
    }

    public function get_room_list()
    {
        $room      = M('room');
        $room_data = $room->select();

        return $room_data;
    }

    public function get_usergroup_list()
    {
        $usergroup      = M('usergroup');
        $usergroup_data = $usergroup->select();

        return $usergroup_data;
    }

    public function get_report_type_name($id)
    {
        $report_plcy = M('report_plcy_type');
        $result      = $report_plcy->where(array('id' => $id))->find();

        return $result;
    }

    public function disbuild_xml($xml)
    {
        $xml  = simplexml_load_string($xml);
        $data = json_decode(json_encode($xml), TRUE);
        return $data;
    }

    public function get_data_source()
    {
        $report = M('report_plcy');
        $data   = $report->select();

        return $data;
    }

    public function get_area_name($id)
    {
        $area = M('area_ip');
        $data = $area->where(array('id' => $id))->field('area_name')->find();

        return $data;
    }

    public function get_dev()
    {
        //$dav = M('dev');
        return array('设备1', '设备2', '设备3');
    }

    public function get_name($id, $table)
    {
        $table = M($table);
        $data  = $table->where(array('id' => $id))->find();
        return $data;
    }

    public function get_plcygroup($field, $name, $table)
    {
        $table        = M($table);
        $plcygroup_id = $table->where($field = $name)->field('plcygroup_id')->find();

        return $plcygroup_id;
    }

    public function update_time($id)
    {
        $plcygroup           = M('plcygroup');
        $data['modify_time'] = time();
        $plcygroup->where(array('id' => $id))->save($data);

        return TRUE;
    }

    public function initMenu()
    {
        $Menu = F("Menu");
        if (!$Menu) {
            $Menu = D("Common/Menu")->menu_cache();
        }

        return $Menu;
    }

}